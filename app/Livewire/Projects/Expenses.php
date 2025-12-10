<?php

declare(strict_types=1);

namespace App\Livewire\Projects;

use App\Models\Project;
use App\Models\ProjectExpense;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Expenses extends Component
{
    use AuthorizesRequests, WithPagination;

    public Project $project;
    public ?ProjectExpense $editingExpense = null;

    // Form fields
    public string $category = '';
    public float $amount = 0;
    public ?string $date = null;
    public ?string $vendor = null;
    public ?string $description = null;
    public bool $requires_reimbursement = false;
    public ?int $requested_by = null;

    public function mount(int $projectId): void
    {
        $this->authorize('projects.expenses.view');
        $this->project = Project::findOrFail($projectId);
        $this->date = now()->format('Y-m-d');
        $this->requested_by = auth()->id();
    }

    public function rules(): array
    {
        return [
            'category' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'date' => ['required', 'date'],
            'vendor' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'requires_reimbursement' => ['boolean'],
            'requested_by' => ['required', 'exists:users,id'],
        ];
    }

    public function createExpense(): void
    {
        $this->authorize('projects.expenses.manage');
        $this->resetForm();
        $this->editingExpense = null;
    }

    public function editExpense(int $id): void
    {
        $this->authorize('projects.expenses.manage');
        $this->editingExpense = ProjectExpense::findOrFail($id);
        $this->fill($this->editingExpense->only([
            'category', 'amount', 'date', 'vendor', 'description',
            'requires_reimbursement', 'requested_by'
        ]));
    }

    public function save(): void
    {
        $this->authorize('projects.expenses.manage');
        $this->validate();

        $data = $this->only([
            'category', 'amount', 'date', 'vendor', 'description',
            'requires_reimbursement', 'requested_by'
        ]);

        if ($this->editingExpense) {
            $this->editingExpense->update($data);
        } else {
            $this->project->expenses()->create(array_merge(
                $data,
                ['status' => 'pending']
            ));
        }

        session()->flash('success', __('Expense saved successfully'));
        $this->resetForm();
        $this->editingExpense = null;
    }

    public function approve(int $id): void
    {
        $this->authorize('projects.expenses.approve');
        $expense = ProjectExpense::findOrFail($id);
        $expense->approve();
        session()->flash('success', __('Expense approved successfully'));
    }

    public function reject(int $id, string $reason): void
    {
        $this->authorize('projects.expenses.approve');
        $expense = ProjectExpense::findOrFail($id);
        $expense->reject($reason);
        session()->flash('success', __('Expense rejected'));
    }

    public function deleteExpense(int $id): void
    {
        $this->authorize('projects.expenses.manage');
        $expense = ProjectExpense::findOrFail($id);
        $expense->delete();
        session()->flash('success', __('Expense deleted successfully'));
    }

    public function resetForm(): void
    {
        $this->reset([
            'category', 'amount', 'vendor', 'description', 'requires_reimbursement'
        ]);
        $this->date = now()->format('Y-m-d');
        $this->requested_by = auth()->id();
    }

    public function render()
    {
        $expenses = $this->project->expenses()
            ->with(['requestedBy'])
            ->orderBy('date', 'desc')
            ->paginate(15);

        $users = User::orderBy('name')->get();

        // Statistics
        $stats = [
            'total_expenses' => $this->project->expenses()->sum('amount'),
            'approved_expenses' => $this->project->expenses()->approved()->sum('amount'),
            'pending_expenses' => $this->project->expenses()->pending()->sum('amount'),
            'needs_reimbursement' => $this->project->expenses()->needsReimbursement()->sum('amount'),
        ];

        return view('livewire.projects.expenses', [
            'expenses' => $expenses,
            'users' => $users,
            'stats' => $stats,
        ]);
    }
}
