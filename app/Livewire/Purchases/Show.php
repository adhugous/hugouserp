<?php

declare(strict_types=1);

namespace App\Livewire\Purchases;

use App\Models\Purchase;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Show extends Component
{
    public Purchase $purchase;

    public function mount(Purchase $purchase): void
    {
        $this->authorize('purchases.view');
        $this->purchase = $purchase->load(['items.product', 'supplier', 'branch']);
    }

    public function render()
    {
        return view('livewire.purchases.show');
    }
}
