@component('mail::message')
# New Comment on Bug {{ $comment->commentable->bug_no }}

{{ $comment->getAuthorName() }} commented on a bug you're subscribed to.

**Comment:**
{{ Str::limit(strip_tags($comment->getBody()), 200) }}

@component('mail::button', ['url' => url("/admin/bugs/{$comment->commentable->id}")])
View Comment
@endcomponent

Thank you for using our platform!

Regards,<br>
{{ config('app.name') }}
@endcomponent
