import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.data('demoAccessCard', () => ({
	open: false,
	users: [
		{ label: 'Coordenador', role: 'Gestão e relatórios', nif: '111111', password: 'senai123' },
		{ label: 'Professor', role: 'Solicitações de materiais', nif: '654321', password: 'senai123' },
	],

	fill(user) {
		const nif = document.querySelector('#nif');
		const password = document.querySelector('#password');

		if (nif && password) {
			nif.value = user.nif;
			password.value = user.password;
			nif.dispatchEvent(new Event('input', { bubbles: true }));
			password.dispatchEvent(new Event('input', { bubbles: true }));
		}
	},
}));

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

	bookUrl(book) {
		if (this.pages.some((page) => page.id === 'library')) {
			return this.pageUrl('library');
		}

		if (this.pages.some((page) => page.id === 'teacher_requests')) {
			return `${this.pageUrl('teacher_requests')}?book_id=${book.id}`;
		}

		return this.pageUrl(this.pages[0]?.id || 'insights');
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
				type: 'Página',
				title: page.label,
				subtitle: 'Abrir área do sistema',
				url: this.pageUrl(page.id),
			}));

		const bookResults = this.books
			.filter((book) => this.normalize(`${book.title} ${book.isbn} ${book.subject}`).includes(term))
			.slice(0, 5)
			.map((book) => ({
				type: 'Livro',
				title: book.title,
				subtitle: `${book.subject} • ${book.quantity} un`,
				url: this.bookUrl(book),
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
		this.$nextTick(() => document.querySelector('#book-details')?.scrollIntoView({ behavior: 'smooth', block: 'start' }));
	},

	isCritical(book) {
		return Number(book?.quantity ?? 0) < this.threshold;
	},
}));

Alpine.data('preservedTabs', (initialTab = 'entrada') => ({
	tab: initialTab,

	selectTab(tab) {
		const scrollY = window.scrollY;
		this.$el.style.minHeight = `${this.$el.offsetHeight}px`;
		this.tab = tab;
		this.$nextTick(() => {
			requestAnimationFrame(() => {
				const maxScroll = Math.max(document.documentElement.scrollHeight - window.innerHeight, 0);
				window.scrollTo({ top: Math.min(scrollY, maxScroll), behavior: 'auto' });
				this.$el.style.minHeight = '';
			});
		});
	},
}));

Alpine.data('bookEditTable', (books = []) => ({
	books,
	query: '',
	subject: '',
	selectedId: null,

	normalize(value) {
		return String(value ?? '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
	},

	matches(book) {
		const term = this.normalize(this.query);
		return (!term || this.normalize(`${book.title} ${book.isbn} ${book.subject}`).includes(term))
			&& (!this.subject || book.subject === this.subject);
	},

	get subjects() {
		return [...new Set(this.books.map((book) => book.subject).filter(Boolean))]
			.sort((a, b) => String(a).localeCompare(String(b), 'pt-BR'));
	},

	toggle(bookId) {
		this.selectedId = String(this.selectedId) === String(bookId) ? null : String(bookId);
	},
}));

Alpine.data('teacherRequestForm', (turmas = [], books = [], initialBookId = '', previous = {}) => ({
	turmas,
	books,
	turmaId: previous.turmaId ? String(previous.turmaId) : '',
	cursoId: previous.cursoId ? String(previous.cursoId) : '',
	bookId: previous.bookId ? String(previous.bookId) : (initialBookId ? String(initialBookId) : ''),

	init() {
		if (this.turmaId) {
			this.selectTurma();
			return;
		}

		const selected = this.selectedBook;
		if (selected) {
			const matchingTurmas = this.filteredTurmas;
			if (matchingTurmas.length === 1) {
				this.turmaId = String(matchingTurmas[0].id);
				this.selectTurma();
			}
		}
	},

	normalize(value) {
		return String(value ?? '')
			.normalize('NFD')
			.replace(/[\u0300-\u036f]/g, '')
			.toLowerCase()
			.replace(/[^a-z0-9]/g, '');
	},

	get selectedTurma() {
		return this.turmas.find((turma) => String(turma.id) === String(this.turmaId));
	},

	get selectedBook() {
		return this.books.find((book) => String(book.id) === String(this.bookId));
	},

	get filteredTurmas() {
		if (!initialBookId || !this.selectedBook) {
			return this.turmas;
		}

		const subject = this.normalize(this.selectedBook.subject);
		return this.turmas.filter((turma) => this.normalize(turma.curso_nome) === subject);
	},

	get filteredBooks() {
		if (!this.selectedTurma) {
			return this.selectedBook ? [this.selectedBook] : [];
		}

		const course = this.normalize(this.selectedTurma.curso_nome);
		return this.books.filter((book) => this.normalize(book.subject) === course);
	},

	selectTurma() {
		this.cursoId = this.selectedTurma ? String(this.selectedTurma.curso_id) : '';

		if (!this.filteredBooks.some((book) => String(book.id) === String(this.bookId))) {
			this.bookId = '';
		}
	},

	selectBook(bookId) {
		const id = String(bookId ?? '');
		if (this.filteredBooks.some((book) => String(book.id) === id)) {
			this.bookId = id;
		}
	},

	clearForm(form) {
		this.turmaId = '';
		this.cursoId = '';
		this.bookId = initialBookId ? String(initialBookId) : '';

		form?.querySelectorAll('input, textarea, select').forEach((field) => {
			if (field.type === 'hidden' || field.type === 'submit' || field.type === 'reset' || field.readOnly) {
				return;
			}

			field.value = '';
			field.dispatchEvent(new Event('input', { bubbles: true }));
			field.dispatchEvent(new Event('change', { bubbles: true }));
		});
	},
}));

Alpine.data('bookRegistrationForm', () => ({
	isbn: '',

	maskIsbn() {
		this.isbn = String(this.isbn ?? '').replace(/[^0-9-]/g, '').slice(0, 20);
	},
}));

Alpine.data('reportBooksTable', (books = [], threshold = 8) => ({
	books,
	threshold,
	query: '',
	subject: '',
	sortField: 'title',
	sortDirection: 'asc',

	normalize(value) {
		return String(value ?? '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
	},

	sort(field) {
		if (this.sortField === field) {
			this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
			return;
		}
		this.sortField = field;
		this.sortDirection = 'asc';
	},

	indicator(field) {
		return this.sortField === field ? (this.sortDirection === 'asc' ? '↑' : '↓') : '↕';
	},

	status(book) {
		return Number(book.quantity) < this.threshold ? 'Crítico' : 'OK';
	},

	get subjects() {
		return [...new Set(this.books.map((book) => book.subject).filter(Boolean))]
			.sort((a, b) => String(a).localeCompare(String(b), 'pt-BR'));
	},

	get rows() {
		const term = this.normalize(this.query);
		return [...this.books]
			.filter((book) => (!term || this.normalize(book.title).includes(term))
				&& (!this.subject || book.subject === this.subject))
			.sort((a, b) => {
				const left = this.sortField === 'status' ? this.status(a) : a[this.sortField];
				const right = this.sortField === 'status' ? this.status(b) : b[this.sortField];
				const result = typeof left === 'number'
					? Number(left) - Number(right)
					: String(left ?? '').localeCompare(String(right ?? ''), 'pt-BR', { numeric: true });
				return this.sortDirection === 'asc' ? result : -result;
			});
	},
}));

Alpine.data('purchaseRequestForm', (catalog = [], initialBookId = '', initialCourse = '') => ({
	catalog,
	bookId: String(initialBookId || ''),
	course: initialCourse || '',

	init() {
		const selected = this.catalog.find((item) => String(item.id) === this.bookId);
		if (selected && !this.course) {
			this.course = selected.subject;
		}
	},

	get subjects() {
		return [...new Set(this.catalog.map((book) => book.subject).filter(Boolean))]
			.sort((a, b) => String(a).localeCompare(String(b), 'pt-BR'));
	},

	get filteredBooks() {
		return this.catalog
			.filter((book) => !this.course || book.subject === this.course)
			.sort((a, b) => String(a.title).localeCompare(String(b.title), 'pt-BR'));
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

const scrollStorageKey = 'senai-stock-navigation-scroll';

function enhanceTables() {
	document.querySelectorAll('main table:not([data-table-enhanced]):not([data-table-skip])').forEach((table, index) => {
		table.dataset.tableEnhanced = 'true';

		const viewport = table.parentElement;
		if (!viewport) {
			return;
		}

		const hasNativeFilters = table.closest('[data-native-table-filters]');
		const hasNoFilters = table.hasAttribute('data-table-no-filters');
		const toolbar = document.createElement('div');
		toolbar.className = 'senai-table-tools no-print';

		const controls = document.createElement('div');
		controls.className = 'flex flex-1 flex-col gap-2 sm:flex-row';

		if (!hasNativeFilters && !hasNoFilters) {
			const normalize = (value) => value
				.normalize('NFD')
				.replace(/[\u0300-\u036f]/g, '')
				.toLowerCase()
				.trim();
			const search = document.createElement('input');
			search.type = 'search';
			search.className = 'senai-table-filter';
			search.placeholder = table.dataset.tableSearchPlaceholder || 'Buscar registros';
			search.setAttribute('aria-label', search.placeholder);

			const filterColumn = table.dataset.tableFilterColumn;
			let select = null;

			if (filterColumn !== undefined) {
				select = document.createElement('select');
				select.className = 'senai-table-filter sm:max-w-52';
				select.setAttribute('aria-label', table.dataset.tableFilterLabel || 'Filtrar registros');

				const defaultOption = document.createElement('option');
				defaultOption.value = '';
				defaultOption.textContent = table.dataset.tableFilterLabel || 'Todos os tipos';
				select.append(defaultOption);

				const values = new Set();
				table.querySelectorAll('tbody tr').forEach((row) => {
					if (row.querySelector('[colspan]')) {
						return;
					}

					const value = row.cells[Number(filterColumn)]?.textContent.trim();
					if (value) {
						values.add(value);
					}
				});

				[...values].sort((left, right) => left.localeCompare(right, 'pt-BR')).forEach((value) => {
					const option = document.createElement('option');
					option.value = value;
					option.textContent = value;
					select.append(option);
				});
			}

			const filterRows = () => {
				const term = normalize(search.value);
				const selectedValue = normalize(select?.value || '');

				table.querySelectorAll('tbody tr').forEach((row) => {
					if (row.querySelector('[colspan]')) {
						return;
					}

					const rowContent = normalize(row.textContent);
					const filterContent = filterColumn === undefined
						? ''
						: normalize(row.cells[Number(filterColumn)]?.textContent || '');
					row.hidden = (Boolean(term) && !rowContent.includes(term))
						|| (Boolean(selectedValue) && filterContent !== selectedValue);
				});
			};

			search.addEventListener('input', filterRows);
			select?.addEventListener('change', filterRows);
			controls.append(search);
			if (select) {
				controls.append(select);
			}
		}

		const toggle = document.createElement('button');
		toggle.type = 'button';
		toggle.className = 'senai-table-toggle';
		toggle.textContent = 'Ocultar tabela';
		toggle.setAttribute('aria-expanded', 'true');
		toggle.setAttribute('aria-controls', `senai-table-${index}`);
		viewport.id ||= `senai-table-${index}`;
		toggle.addEventListener('click', () => {
			const willOpen = viewport.hidden;
			viewport.hidden = !willOpen;
			toggle.textContent = willOpen ? 'Ocultar tabela' : 'Mostrar tabela';
			toggle.setAttribute('aria-expanded', String(willOpen));
		});

		toolbar.append(controls, toggle);
		viewport.before(toolbar);
	});
}

document.addEventListener('click', (event) => {
	const link = event.target.closest('[data-preserve-scroll]');
	if (link) {
		const navigation = document.querySelector('[data-navigation-scroll-container]');
		sessionStorage.setItem(scrollStorageKey, JSON.stringify({
			navigation: navigation?.scrollTop ?? 0,
		}));
	}
});

window.addEventListener('pageshow', () => {
	const storedValue = sessionStorage.getItem(scrollStorageKey);
	if (storedValue === null) {
		return;
	}

	sessionStorage.removeItem(scrollStorageKey);
	let storedScroll;
	try {
		storedScroll = JSON.parse(storedValue);
	} catch {
		storedScroll = { page: Number(storedValue), height: document.documentElement.scrollHeight, navigation: 0 };
	}
	if (typeof storedScroll !== 'object') {
		storedScroll = { navigation: 0 };
	}
	const navigation = document.querySelector('[data-navigation-scroll-container]');
	if (navigation) {
		navigation.scrollTop = Number(storedScroll.navigation);
	}
});

document.addEventListener('DOMContentLoaded', enhanceTables);
document.addEventListener('alpine:initialized', enhanceTables);
