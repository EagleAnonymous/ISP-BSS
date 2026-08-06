<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    /**
     * Work out the next ticket number, e.g. "TCK-00042".
     *
     * Uses PostgreSQL-compatible row locking on the latest record instead of
     * applying `lockForUpdate()` on an aggregate `count()`, since Postgres
     * rejects "FOR UPDATE with aggregate functions" (the same pattern used
     * by BillingController::nextInvoiceNumber()). Two tickets created at the
     * same moment can't be handed the same number because the latest row is
     * locked while the count is computed.
     */
    public static function nextNumber(): string
    {
        $last = static::query()->latest('id')->lockForUpdate()->first();

        $next = $last ? ($last->id + 1) : 1;

        return 'TCK-'.str_pad((string) $next, 5, '0', STR_PAD_LEFT);
    }
}
