<?php

namespace App\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Collection;

interface CategoryRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * All categories as a flat, ordered tree (parents before children) —
     * used to render an indented <select> in blog/category forms.
     */
    public function tree(): Collection;
}
