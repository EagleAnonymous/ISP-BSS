<?php

namespace App\Console\Commands;

use App\Jobs\ProcessOverdueInvoice;
use App\Models\Invoice;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('billing:process-overdue')]
#[Description('Dispatch overdue-invoice processing (reminders, late fees, suspension) for every unpaid invoice past its due date.')]
class ProcessOverdueInvoices extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $invoices = Invoice::overdue()->get();

        foreach ($invoices as $invoice) {
            ProcessOverdueInvoice::dispatch($invoice);
        }

        $this->info("Dispatched overdue processing for {$invoices->count()} invoice(s).");
    }
}