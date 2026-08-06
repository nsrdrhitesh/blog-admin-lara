<?php

namespace App\Models;

use App\Enums\SchemaType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Schema extends Model
{
    protected $table = 'schemas';

    protected $fillable = ['type', 'data', 'is_active'];

    protected function casts(): array
    {
        return [
            'type' => SchemaType::class,
            'data' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function schemable(): MorphTo
    {
        return $this->morphTo();
    }
}
