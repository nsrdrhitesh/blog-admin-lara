<?php

namespace Database\Seeders;

use App\Enums\RoleSlug;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $groups = [
            'blogs' => ['view', 'create', 'edit', 'delete', 'publish'],
            'categories' => ['view', 'create', 'edit', 'delete'],
            'tags' => ['view', 'create', 'edit', 'delete'],
            'authors' => ['view', 'create', 'edit', 'delete'],
            'media' => ['view', 'upload', 'delete'],
            'comments' => ['view', 'approve', 'delete'],
            'users' => ['view', 'create', 'edit', 'delete'],
            'settings' => ['view', 'edit'],
        ];

        $permissions = [];

        foreach ($groups as $group => $actions) {
            foreach ($actions as $action) {
                $permissions[] = Permission::firstOrCreate(
                    ['slug' => "{$group}.{$action}"],
                    ['name' => ucfirst($action)." {$group}", 'group' => $group]
                );
            }
        }

        $roles = [
            RoleSlug::SuperAdmin->value => 'Full unrestricted access to every module.',
            RoleSlug::Admin->value => 'Manages content, users, and settings.',
            RoleSlug::Editor->value => 'Reviews, edits, and publishes content from any author.',
            RoleSlug::Author->value => 'Creates and manages their own blog posts.',
        ];

        foreach ($roles as $slug => $description) {
            $role = Role::firstOrCreate(
                ['slug' => $slug],
                ['name' => ucwords(str_replace('_', ' ', $slug)), 'description' => $description]
            );

            match ($slug) {
                RoleSlug::SuperAdmin->value, RoleSlug::Admin->value => $role->permissions()->sync(
                    collect($permissions)->pluck('id')
                ),
                RoleSlug::Editor->value => $role->permissions()->sync(
                    collect($permissions)
                        ->filter(fn ($p) => in_array($p->group, ['blogs', 'categories', 'tags', 'authors', 'media', 'comments']))
                        ->pluck('id')
                ),
                RoleSlug::Author->value => $role->permissions()->sync(
                    collect($permissions)
                        ->filter(fn ($p) => $p->group === 'blogs' && $p->slug !== 'blogs.publish')
                        ->pluck('id')
                ),
                default => null,
            };
        }
    }
}
