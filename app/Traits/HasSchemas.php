<?php

namespace App\Traits;

use App\Models\Schema as SchemaModel;

trait HasSchemas
{
    public function schemas()
    {
        return $this->morphMany(SchemaModel::class, 'schemable');
    }
}
