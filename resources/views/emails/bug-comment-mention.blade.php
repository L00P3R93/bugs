@component('mail::message')
# You Were Mentioned!

{{ $comment->getAuthorName() }} mentioned you in a comment on bug {{ $comment->commentable->bug_no }}.

**Comment:**
{{ Str::limit(strip_tags($comment->getBody()), 200) }}

@component('mail::button', ['url' => url("/admin/bugs/{$comment->commentable->id}")])
View Comment
@endcomponent

Thank you for using our platform!

Regards,<br>
{{ config('app.name') }}
@endcomponent
