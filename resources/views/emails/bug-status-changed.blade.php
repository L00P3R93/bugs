@component('mail::message')
# Bug Status Updated

Your bug report status has been updated.

**Bug Number:** {{ $bug->bug_no }}
**Title:** {{ $bug->title }}
**Status:** {{ $newStatusLabel }}
**Updated by:** {{ $actor }}

@component('mail::button', ['url' => url("/admin/bugs/{$bug->id}")])
View Bug
@endcomponent

Thank you for using our platform!

Regards,<br>
{{ config('app.name') }}
@endcomponent
