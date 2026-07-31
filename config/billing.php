<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Overdue Invoice Rules
    |--------------------------------------------------------------------------
    |
    | Business rules applied automatically to unpaid invoices once their due
    | date has passed. All thresholds are measured in days overdue, checked
    | daily by the `billing:process-overdue` scheduled command.
    |
    */

    'overdue' => [

        // Days overdue at which a reminder email is sent. Each tier fires
        // once per invoice — running the job again will not resend it.
        'reminder_days' => [3, 7, 14],

        // A one-time late fee is added as an invoice adjustment once an
        // invoice reaches this many days overdue.
        'late_fee' => [
            'after_days' => 15,
            'amount' => 200.00,
        ],

        // The subscriber is automatically suspended once an invoice reaches
        // this many days overdue and no payment has been recorded.
        'suspend_after_days' => 30,
    ],

];