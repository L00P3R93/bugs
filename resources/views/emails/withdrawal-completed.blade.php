@component('mail::message')
# Withdrawal Successful!

Your withdrawal has been processed successfully.

**Amount:** KES {{ number_format($withdraw->amount, 2) }}
**Phone:** {{ $withdraw->phone }}
**Transaction:** {{ $withdraw->transaction->transaction_no }}
**Reference:** {{ $withdraw->transaction_ref }}

@component('mail::button', ['url' => url("/admin/transactions/{$withdraw->transaction_id}")])
View Transaction
@endcomponent

Thank you for using our platform!

Regards,<br>
{{ config('app.name') }}
@endcomponent
