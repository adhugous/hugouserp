<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shift extends BaseModel
{
    protected ?string $moduleKey = 'hr';

    protected $fillable = [
        'branch_id',
        'name',
        'code',
        'start_time',
        'end_time',
        'grace_period_minutes',
        'working_days',
        'is_active',
        'description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'grace_period_minutes' => 'integer',
        'working_days' => 'array',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function employeeShifts(): HasMany
    {
        return $this->hasMany(EmployeeShift::class);
    }

    public function employees()
    {
        return $this->belongsToMany(HREmployee::class, 'employee_shifts', 'shift_id', 'employee_id')
            ->withPivot(['start_date', 'end_date', 'is_active'])
            ->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getShiftDurationAttribute(): float
    {
        if (!$this->start_time || !$this->end_time) {
            return 0;
        }

        $start = \Carbon\Carbon::parse($this->start_time);
        $end = \Carbon\Carbon::parse($this->end_time);

        if ($end->lt($start)) {
            // Shift crosses midnight
            $end->addDay();
        }

        return $start->diffInHours($end, true);
    }

    public function isWorkingDay(string $day): bool
    {
        if (!$this->working_days) {
            return true;
        }

        return in_array(strtolower($day), array_map('strtolower', $this->working_days), true);
    }
}
