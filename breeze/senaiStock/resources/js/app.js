import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.data('spotlightSearch', (books = [], pages = [], routeTemplate = '/dashboard/__VIEW__') => ({
	books,
	pages,
	query: '',

	normalize(value) {
		return String(value ?? '')
			.normalize('NFD')
			.replace(/[\u0300-\u036f]/g, '')
			.toLowerCase();
	},

	pageUrl(id) {
		return routeTemplate.replace('__VIEW__', id);
	},

	get results() {
		const term = this.normalize(this.query);

		if (term.length < 2) {
			return [];
		}

		const pageResults = this.pages
			.filter((page) => this.normalize(`${page.label} ${page.id}`).includes(term))
			.slice(0, 4)
			.map((page) => ({
				type: 'Pagina',
				title: page.label,
				subtitle: 'Abrir area do sistema',
				url: this.pageUrl(page.id),
			}));

		const bookResults = this.books
			.filter((book) => this.normalize(`${book.title} ${book.isbn} ${book.subject}`).includes(term))
			.slice(0, 5)
			.map((book) => ({
				type: 'Livro',
				title: book.title,
				subtitle: `${book.subject} • ${book.quantity} un`,
				url: this.pageUrl('library'),
			}));

		return [...pageResults, ...bookResults].slice(0, 7);
	},
}));

Alpine.data('libraryBrowser', (books = [], threshold = 8) => ({
	books,
	threshold,
	selectedBook: books[0] ?? null,

	select(bookId) {
		this.selectedBook = this.books.find((book) => String(book.id) === String(bookId)) ?? this.selectedBook;
	},

	isCritical(book) {
		return Number(book?.quantity ?? 0) < this.threshold;
	},
}));

Alpine.data('purchaseCartForm', (catalog = [], initialItems = []) => ({
	catalog,
	reqType: 'restock',
	bookId: '',
	newTitle: '',
	quantity: 1,
	justification: '',
	items: initialItems,
	itemsJson: '[]',

	get selectedBook() {
		return this.catalog.find((item) => String(item.id) === String(this.bookId));
	},

	addItem() {
		const title = this.reqType === 'restock' ? this.selectedBook?.title : this.newTitle.trim();

		if (!title || Number(this.quantity) <= 0) {
			return;
		}

		this.items.push({
			id: `${Date.now()}-${Math.random().toString(36).slice(2)}`,
			type: this.reqType,
			bookId: this.reqType === 'restock' ? this.selectedBook?.id ?? null : null,
			title,
			quantity: Number(this.quantity),
			justification: this.justification.trim() || 'Reposição de estoque.',
		});

		this.bookId = '';
		this.newTitle = '';
		this.quantity = 1;
		this.justification = '';
		this.reqType = 'restock';
	},

	removeItem(index) {
		this.items.splice(index, 1);
	},

	get totalItems() {
		return this.items.reduce((total, item) => total + Number(item.quantity || 0), 0);
	},

	prepareSubmit() {
		this.itemsJson = JSON.stringify(this.items);
	},
}));

Alpine.data('withdrawCartForm', (catalog = [], turmas = []) => ({
	catalog,
	turmas,
	destination: '',
	rows: [{ id: `${Date.now()}`, bookId: '', quantity: 1 }],

	init() {
		if (this.turmas.length > 0 && !this.destination) {
			this.destination = this.turmas[0].nome_turma;
		}
	},

	addRow() {
		this.rows.push({ id: `${Date.now()}-${Math.random().toString(36).slice(2)}`, bookId: '', quantity: 1 });
	},

	removeRow(id) {
		this.rows = this.rows.filter((row) => row.id !== id);
		if (this.rows.length === 0) {
			this.addRow();
		}
	},

	bookFor(row) {
		return this.catalog.find((item) => String(item.id) === String(row.bookId));
	},

	maxAllowed(row) {
		const selected = this.bookFor(row);

		if (!selected) {
			return null;
		}

		const allocatedElsewhere = this.rows
			.filter((otherRow) => otherRow.id !== row.id && String(otherRow.bookId) === String(row.bookId))
			.reduce((sum, otherRow) => sum + Number(otherRow.quantity || 0), 0);

		return Math.max(selected.quantity - allocatedElsewhere, 0);
	},

	sanitizeQuantity(row) {
		const maxAllowed = this.maxAllowed(row);

		if (maxAllowed === null) {
			return;
		}

		let normalized = Number(row.quantity || 0);

		if (Number.isNaN(normalized) || normalized < 0) {
			normalized = 0;
		}

		if (normalized > maxAllowed) {
			normalized = maxAllowed;
		}

		row.quantity = normalized;
	},

	totalQuantity() {
		return this.rows.reduce((sum, row) => sum + Number(row.quantity || 0), 0);
	},

	canSubmit() {
		return Boolean(this.destination) && this.rows.some((row) => row.bookId && Number(row.quantity || 0) > 0);
	},
}));

Alpine.start();
