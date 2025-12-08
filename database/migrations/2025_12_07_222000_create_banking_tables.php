<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Bank Accounts table
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id')->comment('Branch ID');
            $table->string('account_number')->unique()->comment('Bank account number');
            $table->string('account_name')->comment('Account name');
            $table->string('bank_name')->comment('Bank name');
            $table->string('bank_branch')->nullable()->comment('Bank branch');
            $table->string('swift_code')->nullable()->comment('SWIFT/BIC code');
            $table->string('iban')->nullable()->comment('IBAN');
            $table->string('currency', 3)->default('USD')->comment('Account currency');
            $table->string('account_type')->default('checking')->comment('Type: checking, savings, credit');
            $table->decimal('opening_balance', 18, 4)->default(0)->comment('Opening balance');
            $table->decimal('current_balance', 18, 4)->default(0)->comment('Current balance');
            $table->date('opening_date')->comment('Account opening date');
            $table->string('status')->default('active')->comment('Status: active, inactive, closed');
            $table->text('notes')->nullable()->comment('Additional notes');
            $table->json('meta')->nullable()->comment('Additional metadata');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            
            $table->index(['branch_id', 'status']);
            $table->index('currency');
        });

        // Bank Transactions table
        Schema::create('bank_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bank_account_id')->comment('Bank account ID');
            $table->unsignedBigInteger('branch_id')->comment('Branch ID');
            $table->string('reference_number')->unique()->comment('Transaction reference');
            $table->date('transaction_date')->comment('Transaction date');
            $table->date('value_date')->nullable()->comment('Value date');
            $table->enum('type', ['deposit', 'withdrawal', 'transfer', 'fee', 'interest'])->comment('Transaction type');
            $table->decimal('amount', 18, 4)->comment('Transaction amount');
            $table->decimal('balance_after', 18, 4)->nullable()->comment('Balance after transaction');
            $table->string('payee_payer')->nullable()->comment('Payee or payer name');
            $table->string('category')->nullable()->comment('Transaction category');
            $table->text('description')->nullable()->comment('Description');
            $table->string('status')->default('pending')->comment('Status: pending, cleared, reconciled, cancelled');
            $table->unsignedBigInteger('reconciliation_id')->nullable()->comment('Reconciliation ID');
            $table->unsignedBigInteger('journal_entry_id')->nullable()->comment('Linked journal entry ID');
            $table->string('related_type')->nullable()->comment('Related entity type');
            $table->unsignedBigInteger('related_id')->nullable()->comment('Related entity ID');
            $table->json('meta')->nullable()->comment('Additional metadata');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            
            $table->foreign('bank_account_id')->references('id')->on('bank_accounts')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
            $table->foreign('journal_entry_id')->references('id')->on('journal_entries')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            
            $table->index(['bank_account_id', 'transaction_date']);
            $table->index(['branch_id', 'status']);
            $table->index('transaction_date');
            $table->index(['related_type', 'related_id']);
        });

        // Bank Reconciliations table
        Schema::create('bank_reconciliations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bank_account_id')->comment('Bank account ID');
            $table->unsignedBigInteger('branch_id')->comment('Branch ID');
            $table->string('reconciliation_number')->unique()->comment('Reconciliation number');
            $table->date('statement_date')->comment('Bank statement date');
            $table->date('reconciliation_date')->comment('Reconciliation date');
            $table->decimal('statement_balance', 18, 4)->comment('Bank statement ending balance');
            $table->decimal('book_balance', 18, 4)->comment('Book balance at reconciliation');
            $table->decimal('difference', 18, 4)->default(0)->comment('Difference to be explained');
            $table->string('status')->default('draft')->comment('Status: draft, completed, approved');
            $table->text('notes')->nullable()->comment('Reconciliation notes');
            $table->json('adjustments')->nullable()->comment('Reconciliation adjustments');
            $table->unsignedBigInteger('reconciled_by')->nullable()->comment('User who reconciled');
            $table->unsignedBigInteger('approved_by')->nullable()->comment('User who approved');
            $table->timestamps();
            
            $table->foreign('bank_account_id')->references('id')->on('bank_accounts')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
            $table->foreign('reconciled_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            
            $table->index(['bank_account_id', 'statement_date']);
            $table->index(['branch_id', 'status']);
        });

        // Cashflow Projections table
        Schema::create('cashflow_projections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id')->comment('Branch ID');
            $table->date('projection_date')->comment('Date of projection');
            $table->string('period_type')->comment('Period: daily, weekly, monthly');
            $table->decimal('opening_balance', 18, 4)->comment('Opening cash balance');
            $table->decimal('expected_inflows', 18, 4)->default(0)->comment('Expected cash inflows');
            $table->decimal('expected_outflows', 18, 4)->default(0)->comment('Expected cash outflows');
            $table->decimal('projected_balance', 18, 4)->comment('Projected ending balance');
            $table->decimal('actual_balance', 18, 4)->nullable()->comment('Actual balance (if realized)');
            $table->decimal('variance', 18, 4)->nullable()->comment('Variance (actual - projected)');
            $table->json('inflow_breakdown')->nullable()->comment('Breakdown of inflows by source');
            $table->json('outflow_breakdown')->nullable()->comment('Breakdown of outflows by category');
            $table->text('notes')->nullable()->comment('Notes');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            
            $table->index(['branch_id', 'projection_date']);
            $table->index('period_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cashflow_projections');
        Schema::dropIfExists('bank_reconciliations');
        Schema::dropIfExists('bank_transactions');
        Schema::dropIfExists('bank_accounts');
    }
};
