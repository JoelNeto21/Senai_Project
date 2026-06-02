<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class AppLayout extends Component
{
    public function __construct(
        public string $activeView = 'insights',
        public array $navigationItems = [],
        public array $employee = [],
        public int $purchaseCartCount = 0,
        public int $withdrawCartCount = 0,
        public int $pendingTeacherRequests = 0,
        public int $alertCount = 0,
        public int $supplierCount = 0,
    ) {
    }

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.app');
    }
}
