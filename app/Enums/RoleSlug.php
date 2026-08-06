<?php

namespace App\Enums;

enum RoleSlug: string
{
    case SuperAdmin = 'super_admin';
    case Admin = 'admin';
    case Editor = 'editor';
    case Author = 'author';
}
