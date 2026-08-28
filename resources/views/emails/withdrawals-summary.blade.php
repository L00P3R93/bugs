@component('mail::message')
# Withdrawals Processing Summary

**Total Processed:** {{ $processedCount }}
**Successful:** {{ $successCount }}
**Failed:** {{ $failedCount }}

**Total Amount:** KES {{ number_format($totalAmount, 2) }}

@if(count($details) > 0)
## Withdrawal Details

| User | Phone | Amount | Status |
|------|-------|--------|--------|
@foreach($details as $detail)
| {{ $detail['user'] }} | {{ $detail['phone'] }} | KES {{ number_format($detail['amount'], 2) }} | {{ ucfirst($detail['status']) }} |
@endforeach
@endif

@component('mail::button', ['url' => url('/admin/withdrawals')])
View Withdrawals
@endcomponent

Thank you for using our platform!

Regards,<br>
{{ config('app.name') }}
@endcomponent
