@component('mail::message')
# Payment Received!

You have received a payment for your bug report.

**Transaction Number:** {{ $transaction->transaction_no }}
**Amount:** KES {{ number_format($transaction->amount, 2) }}
**Bug:** {{ $transaction->bug->bug_no ?? 'N/A' }}
**New Wallet Balance:** KES {{ number_format($transaction->wallet->balance, 2) }}

@component('mail::button', ['url' => url("/admin/transactions/{$transaction->id}")])
View Transaction
@endcomponent

Thank you for using our platform!

Regards,<br>
{{ config('app.name') }}
@endcomponent
