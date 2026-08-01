<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ticket {{ $ticket->ticket_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #111827; background: #fff; padding: 32px; }
        .brand { display: flex; align-items: center; gap: 10px; margin-bottom: 6px; }
        .brand img { width: 28px; height: 28px; }
        .brand span { font-size: 18px; font-weight: 700; color: #1e3a8a; }
        .subtitle { color: #6b7280; font-size: 13px; margin-bottom: 20px; }
        .ticket-header { border: 1.5px solid #1e3a8a; border-radius: 8px; padding: 16px 20px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; }
        .ticket-header h1 { font-size: 22px; color: #1e3a8a; }
        .ticket-header .status { background: #f59e0b; color: #fff; font-size: 12px; font-weight: 700; padding: 4px 10px; border-radius: 999px; text-transform: uppercase; letter-spacing: 0.5px; }
        .section { margin-bottom: 18px; }
        .section h2 { font-size: 13px; text-transform: uppercase; letter-spacing: 1px; color: #6b7280; margin-bottom: 8px; border-bottom: 1px solid #e5e7eb; padding-bottom: 4px; }
        table.info { width: 100%; border-collapse: collapse; }
        table.info td { padding: 5px 0; font-size: 13.5px; vertical-align: top; }
        table.info td.label { width: 45%; color: #6b7280; }
        table.info td.value { font-weight: 600; color: #111827; }
        .description { font-size: 13.5px; line-height: 1.65; white-space: pre-line; }
        .actions { margin-top: 24px; }
        .actions button { background: #1e3a8a; color: #fff; border: none; border-radius: 6px; padding: 10px 20px; font-size: 14px; font-weight: 600; cursor: pointer; }
        .actions button.secondary { background: #fff; color: #1e3a8a; border: 1px solid #1e3a8a; }
        @media print {
            .actions { display: none; }
            body { padding: 12px; }
        }
    </style>
</head>
<body>
    <div class="brand">
        <img src="{{ asset('image/wifi-svgrepo-com.svg') }}" alt="WiFi">
        <span>Smart ISP</span>
    </div>
    <p class="subtitle">Field Technician Service Ticket — Downloadable / Printable</p>

    <div class="ticket-header">
        <h1>{{ $ticket->ticket_number }}</h1>
        <span class="status">{{ $ticket->status === 'open' ? 'Open / Pending Technical Visit' : ucfirst(str_replace('_', ' ', $ticket->status)) }}</span>
    </div>

    <div class="section">
        <h2>Subscriber Information</h2>
        <table class="info">
            <tr><td class="label">Subscriber Name</td><td class="value">{{ $ticket->subscriber->user->name }}</td></tr>
            <tr><td class="label">Account / Subscriber ID</td><td class="value">{{ $ticket->subscriber->subscriber_id }}</td></tr>
            <tr><td class="label">Contact Number</td><td class="value">{{ $ticket->subscriber->contact ?? 'Not provided' }}</td></tr>
            <tr><td class="label">Plan</td><td class="value">{{ $ticket->subscriber->plan?->name ?? '—' }}</td></tr>
        </table>
    </div>

    <div class="section">
        <h2>Ticket Details</h2>
        <table class="info">
            <tr><td class="label">Problem Category</td><td class="value">{{ ucfirst(str_replace('_', ' ', $ticket->category)) }}</td></tr>
            <tr><td class="label">Priority</td><td class="value">{{ ucfirst($ticket->priority) }}</td></tr>
            <tr><td class="label">Subject</td><td class="value">{{ $ticket->subject }}</td></tr>
            <tr><td class="label">Date &amp; Time Reported</td><td class="value">{{ $ticket->created_at->format('F j, Y g:i A') }}</td></tr>
            <tr><td class="label">Status</td><td class="value">{{ $ticket->status === 'open' ? 'Open / Pending Technical Visit' : ucfirst(str_replace('_', ' ', $ticket->status)) }}</td></tr>
            @if ($ticket->assignee)
                <tr><td class="label">Assigned To</td><td class="value">{{ $ticket->assignee->user->name }}</td></tr>
            @endif
        </table>
    </div>

    <div class="section">
        <h2>Detailed Description</h2>
        <p class="description">{{ $ticket->description }}</p>
    </div>

    @if ($ticket->resolution_notes)
        <div class="section">
            <h2>Resolution Notes</h2>
            <p class="description">{{ $ticket->resolution_notes }}</p>
        </div>
    @endif

    <div class="actions">
        <button onclick="window.print()">🖨️ Download PDF / Print</button>
        <button class="secondary" onclick="window.history.back()">Back</button>
    </div>
</body>
</html>

