<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffSchedule extends Model
{
    protected $table = 'staff_schedules';

    protected $fillable = [
        'technical_staff_id',
        'day_of_week',
        'shift_type',
        'start_time',
        'end_time',
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
    ];

    public function technicalStaff(): BelongsTo
    {
        return $this->belongsTo(TechnicalStaff::class);
    }
}
