<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Support\Carbon;

class BillingSummary
{
    /**
     * Billed vs. Collected for a date range, kept as two separate figures
     * (never blended into one "revenue" number) plus the derived outstanding
     * balance. "Billed" is pre-adjustment: SUM(invoices.amount), the original
     * billed amount at issue time — adjustments affect what's owed, not what
     * was billed.
     *
     * @return array{billed: float, collected: float, outstanding: float}
     */
    public function forPeriod(Carbon $start, Carbon $end): array
    {
        $billed = (float) Invoice::whereBetween('created_at', [$start, $end])->sum('amount');

        $collected = (float) Payment::where('status', 'successful')
            ->whereBetween('paid_at', [$start, $end])
            ->sum('amount');

        return [
            'billed' => round($billed, 2),
            'collected' => round($collected, 2),
            'outstanding' => round($billed - $collected, 2),
        ];
    }

    /**
     * @return array{billed: float, collected: float, outstanding: float}
     */
    public function currentPeriod(): array
    {
        return $this->forPeriod(Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth());
    }
}