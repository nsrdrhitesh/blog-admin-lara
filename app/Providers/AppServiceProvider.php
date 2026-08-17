<?php

namespace App\Providers;

use App\Events\BlogPublished;
use App\Listeners\ClearContentCaches;
use App\Models\Author;
use App\Models\Blog;
use App\Models\BlogComment;
use App\Models\Category;
use App\Models\Media;
use App\Models\Page;
use App\Models\Setting;
use App\Models\Tag;
use App\Models\User;
use App\Policies\AuthorPolicy;
use App\Policies\BlogCommentPolicy;
use App\Policies\BlogPolicy;
use App\Policies\CategoryPolicy;
use App\Policies\MediaPolicy;
use App\Policies\PagePolicy;
use App\Policies\SettingPolicy;
use App\Policies\TagPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(Blog::class, BlogPolicy::class);
        Gate::policy(Category::class, CategoryPolicy::class);
        Gate::policy(Tag::class, TagPolicy::class);
        Gate::policy(Author::class, AuthorPolicy::class);
        Gate::policy(Media::class, MediaPolicy::class);
        Gate::policy(BlogComment::class, BlogCommentPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Setting::class, SettingPolicy::class);
        Gate::policy(Page::class, PagePolicy::class);

        Event::listen(BlogPublished::class, ClearContentCaches::class);
    }
}
