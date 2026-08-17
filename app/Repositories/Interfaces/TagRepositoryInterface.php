<?php

namespace App\Repositories\Interfaces;

interface TagRepositoryInterface extends BaseRepositoryInterface
{
    public function findOrCreateByNames(array $names): array;
}
