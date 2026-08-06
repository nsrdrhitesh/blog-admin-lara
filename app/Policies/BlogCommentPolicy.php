<?php

namespace App\Policies;

use App\Models\BlogComment;
use App\Models\User;

class BlogCommentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('comments.view');
    }

    public function view(User $user, BlogComment $comment): bool
    {
        return $user->hasPermissionTo('comments.view');
    }

    public function approve(User $user, BlogComment $comment): bool
    {
        return $user->hasPermissionTo('comments.approve');
    }

    public function delete(User $user, BlogComment $comment): bool
    {
        return $user->hasPermissionTo('comments.delete');
    }
}
