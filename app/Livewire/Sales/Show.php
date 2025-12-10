<?php

declare(strict_types=1);

namespace App\Livewire\Sales;

use App\Models\Sale;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Show extends Component
{
    public Sale $sale;

    public function mount(Sale $sale): void
    {
        $this->authorize('sales.view');
        $this->sale = $sale->load(['items.product', 'customer', 'branch', 'payments']);
    }

    public function render()
    {
        return view('livewire.sales.show');
    }
}
