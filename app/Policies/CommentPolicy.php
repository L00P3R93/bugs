<?php

namespace App\Policies;

use Kirschbaum\Commentions\Comment;
use Kirschbaum\Commentions\Contracts\Commenter;
use Kirschbaum\Commentions\Policies\CommentPolicy as CommentionsPolicy;

class CommentPolicy extends CommentionsPolicy
{
    public function create(Commenter $user): bool
    {
        return $user->can('create_comment');
    }

    public function update($user, Comment $comment): bool
    {
        return $user->can('update_comment') && $comment->isAuthor($user);
    }

    public function delete($user, Comment $comment): bool
    {
        return $user->can('delete_comment') || ($comment->isAuthor($user) && $user->can('delete_comment'));
    }
}
