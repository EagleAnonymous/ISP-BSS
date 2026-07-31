<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'ticket_number', 'subscriber_id', 'category', 'subject', 'description',
    'priority', 'status', 'assigned_to', 'created_by',
    'claimed_at', 'started_at', 'resolved_at', 'closed_at', 'resolution_notes',
])]
class Ticket extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'claimed_at' => 'datetime',
            'started_at' => 'datetime',
            'resolved_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function subscriber(): BelongsTo
    {
        return $this->belongsTo(Subscriber::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(TechnicalStaff::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Work out the next ticket number, e.g. "TCK-00042". Locks the tickets
     * table briefly while counting, so two tickets created at the same
     * moment (from the admin form or the staff form) can't be handed the
     * same number.
     */
    public static function nextNumber(): string
    {
        $next = static::lockForUpdate()->count() + 1;

        return 'TCK-'.str_pad((string) $next, 5, '0', STR_PAD_LEFT);
    }
}
