@component('mail::message')
# Daily Payouts Summary

Date: {{ $date }}

**Total Testers Processed:** {{ $totalTesters }}
**Successfully Paid:** {{ $paidCount }}
**Skipped:** {{ $skippedCount }}
**Errors:** {{ $errorCount }}

**Total Amount Disbursed:** KES {{ number_format($totalAmount, 2) }}

@if(count($paidDetails) > 0)
## Paid Details

| Tester | Games | Amount |
|--------|-------|--------|
@foreach($paidDetails as $detail)
| {{ $detail['name'] }} | {{ $detail['games'] }} | KES {{ number_format($detail['amount'], 2) }} |
@endforeach
@endif

@component('mail::button', ['url' => url('/admin/transactions')])
View Transactions
@endcomponent

Thank you for using our platform!

Regards,<br>
{{ config('app.name') }}
@endcomponent
