<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Payroll;
use App\Models\Branch;
use Illuminate\Support\Facades\View;

class PayslipService
{
    /**
     * Generate payslip HTML content
     */
    public function generatePayslipHtml(Payroll $payroll): string
    {
        $employee = $payroll->employee;
        $branch = $employee->branch;
        
        $data = [
            'payroll' => $payroll,
            'employee' => $employee,
            'branch' => $branch,
            'generatedAt' => now()->format('Y-m-d H:i:s'),
        ];

        return View::make('payslips.template', $data)->render();
    }

    /**
     * Get payslip breakdown
     */
    public function getPayslipBreakdown(Payroll $payroll): array
    {
        return [
            'basic_salary' => [
                'label' => __('Basic Salary'),
                'amount' => $payroll->basic,
                'type' => 'earning',
            ],
            'allowances' => [
                'label' => __('Allowances'),
                'amount' => $payroll->allowances,
                'type' => 'earning',
            ],
            'gross_salary' => [
                'label' => __('Gross Salary'),
                'amount' => $payroll->basic + $payroll->allowances,
                'type' => 'subtotal',
            ],
            'deductions' => [
                'label' => __('Deductions'),
                'amount' => $payroll->deductions,
                'type' => 'deduction',
            ],
            'net_salary' => [
                'label' => __('Net Salary'),
                'amount' => $payroll->net,
                'type' => 'total',
            ],
        ];
    }

    /**
     * Calculate payroll for employee
     */
    public function calculatePayroll(int $employeeId, string $period): array
    {
        $employee = \App\Models\HREmployee::findOrFail($employeeId);
        
        // Basic salary from employee record
        $basic = $employee->salary;
        
        // TODO: Calculate allowances based on company rules
        // This could include: transport, housing, meal, etc.
        $allowances = 0;
        
        // TODO: Calculate deductions based on company rules
        // This could include: tax, insurance, loans, advances, etc.
        $deductions = 0;
        
        // Net salary
        $net = $basic + $allowances - $deductions;
        
        return [
            'employee_id' => $employeeId,
            'period' => $period,
            'basic' => $basic,
            'allowances' => $allowances,
            'deductions' => $deductions,
            'net' => $net,
            'status' => 'draft',
        ];
    }

    /**
     * Process payroll for all employees in a branch
     */
    public function processBranchPayroll(int $branchId, string $period): array
    {
        $employees = \App\Models\HREmployee::where('branch_id', $branchId)
            ->where('is_active', true)
            ->get();

        $processed = [];
        $errors = [];

        foreach ($employees as $employee) {
            try {
                $payrollData = $this->calculatePayroll($employee->id, $period);
                $payroll = Payroll::create($payrollData);
                $processed[] = $payroll;
            } catch (\Exception $e) {
                $errors[] = [
                    'employee_id' => $employee->id,
                    'employee_name' => $employee->name,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return [
            'processed' => $processed,
            'errors' => $errors,
            'total' => count($employees),
            'success' => count($processed),
            'failed' => count($errors),
        ];
    }
}
