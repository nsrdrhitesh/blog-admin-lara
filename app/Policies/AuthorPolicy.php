<?php

namespace App\Policies;

use App\Models\Author;
use App\Models\User;

class AuthorPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('authors.view');
    }

    public function view(User $user, Author $author): bool
    {
        return $user->hasPermissionTo('authors.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('authors.create');
    }

    public function update(User $user, Author $author): bool
    {
        return $user->hasPermissionTo('authors.edit') || $author->user_id === $user->id;
    }

    public function delete(User $user, Author $author): bool
    {
        return $user->hasPermissionTo('authors.delete');
    }
}
