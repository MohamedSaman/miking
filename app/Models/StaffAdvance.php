<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffAdvance extends Model
{
    use HasFactory;

    protected $fillable = [
        'staff_id',
        'advance_date',
        'salary_month',
        'amount',
        'reason',
        'status',
        'created_by',
    ];

    protected $casts = [
        'advance_date' => 'date',
        'amount' => 'decimal:2',
    ];

    /**
     * Get the staff member
     */
    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    /**
     * Get who created the advance
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
