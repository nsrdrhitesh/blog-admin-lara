<?php

namespace App\Providers;

use App\Repositories\Eloquent\AuthorRepository;
use App\Repositories\Eloquent\BlogRepository;
use App\Repositories\Eloquent\CategoryRepository;
use App\Repositories\Eloquent\MediaRepository;
use App\Repositories\Eloquent\TagRepository;
use App\Repositories\Interfaces\AuthorRepositoryInterface;
use App\Repositories\Interfaces\BlogRepositoryInterface;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Repositories\Interfaces\MediaRepositoryInterface;
use App\Repositories\Interfaces\TagRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Interface => implementation bindings. Controllers/Services type-hint the
     * interface, so swapping storage strategy later never touches call sites.
     */
    public array $bindings = [
        BlogRepositoryInterface::class => BlogRepository::class,
        CategoryRepositoryInterface::class => CategoryRepository::class,
        TagRepositoryInterface::class => TagRepository::class,
        AuthorRepositoryInterface::class => AuthorRepository::class,
        MediaRepositoryInterface::class => MediaRepository::class,
    ];
}
