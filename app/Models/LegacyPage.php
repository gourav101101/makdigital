<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LegacyPage extends Model
{
    protected $fillable = ['source_url', 'title', 'content', 'metadata', 'archived_at'];

    protected function casts(): array
    {
        return ['metadata' => 'array', 'archived_at' => 'datetime'];
    }
}
