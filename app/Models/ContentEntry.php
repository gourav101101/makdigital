<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentEntry extends Model
{
    use HasFactory;

    public const KINDS = ['project', 'service', 'article', 'testimonial', 'page'];

    protected $fillable = ['kind', 'slug', 'title', 'summary', 'body', 'category', 'image_path', 'image_alt', 'details', 'published', 'position', 'source_url'];

    protected function casts(): array
    {
        return ['details' => 'array', 'published' => 'boolean', 'position' => 'integer'];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('published', true);
    }

    public function scopeOfKind(Builder $query, string $kind): Builder
    {
        return $query->where('kind', $kind);
    }

    public function imageUrl(): ?string
    {
        return $this->image_path ? asset($this->image_path) : null;
    }

    public function publicUrl(): ?string
    {
        return match ($this->kind) {
            'project' => route('work.show', $this->slug),
            'service' => route('services.show', $this->slug),
            'article' => route('insights.show', $this->slug),
            'page' => url('/'.$this->slug),
            default => null,
        };
    }
}
