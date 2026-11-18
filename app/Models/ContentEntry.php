<?php

namespace App\Models;

use Database\Factories\ContentEntryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'type', 'slug', 'title', 'body', 'summary', 'image', 'seo_title',
    'seo_description', 'social_image', 'hero_slides', 'status', 'published_at', 'source_id', 'category', 'tags',
])]
class ContentEntry extends Model
{
    /** @use HasFactory<ContentEntryFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return ['published_at' => 'datetime', 'tags' => 'array', 'hero_slides' => 'array'];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->where(function (Builder $query): void {
                $query->whereNull('published_at')->orWhere('published_at', '<=', now());
            });
    }
}
