@component('mail::message')
# Withdrawal Failed

Your withdrawal could not be processed.

**Amount:** KES {{ number_format($withdraw->amount, 2) }}
**Phone:** {{ $withdraw->phone }}
**Reason:** {{ $withdraw->failure_reason }}

Your wallet has been refunded.

@component('mail::button', ['url' => url("/admin/transactions/{$withdraw->transaction_id}")])
View Transaction
@endcomponent

Please contact support if you need assistance.

Regards,<br>
{{ config('app.name') }}
@endcomponent
