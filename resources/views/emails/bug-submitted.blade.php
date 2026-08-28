@component('mail::message')
# New Bug Report

A new bug has been submitted and requires your review.

**Bug Number:** {{ $bug->bug_no }}
**Title:** {{ $bug->title }}
**Reporter:** {{ $bug->reporter->name }}
**Category:** {{ $bug->category->name }}
**Severity:** {{ $bug->severity->name }}

**Potential Payout:** KES {{ number_format($bug->final_amount, 2) }}

@component('mail::button', ['url' => url("/admin/bugs/{$bug->id}")])
View Bug
@endcomponent

Please review this bug report.

Regards,<br>
{{ config('app.name') }}
@endcomponent
