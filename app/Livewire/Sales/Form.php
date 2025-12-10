<?php

declare(strict_types=1);

namespace App\Livewire\Sales;

use App\Models\Sale;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Form extends Component
{
    public ?Sale $sale = null;
    public bool $editMode = false;

    public function mount(?Sale $sale = null): void
    {
        $this->authorize($sale ? 'sales.manage' : 'sales.manage');
        
        if ($sale) {
            $this->sale = $sale;
            $this->editMode = true;
        }
    }

    public function render()
    {
        return view('livewire.sales.form');
    }
}
