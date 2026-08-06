<?php

namespace App\Policies;

use App\Models\Blog;
use App\Models\User;

class BlogPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('blogs.view');
    }

    public function view(User $user, Blog $blog): bool
    {
        return $user->hasPermissionTo('blogs.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('blogs.create');
    }

    public function update(User $user, Blog $blog): bool
    {
        if (! $user->hasPermissionTo('blogs.edit')) {
            return false;
        }

        // Authors may only edit their own posts; Editors/Admins/Super Admins edit any.
        if ($user->role?->slug === 'author') {
            return $blog->author?->user_id === $user->id;
        }

        return true;
    }

    public function delete(User $user, Blog $blog): bool
    {
        if (! $user->hasPermissionTo('blogs.delete')) {
            return false;
        }

        if ($user->role?->slug === 'author') {
            return $blog->author?->user_id === $user->id;
        }

        return true;
    }

    public function publish(User $user, Blog $blog): bool
    {
        return $user->hasPermissionTo('blogs.publish');
    }
}
