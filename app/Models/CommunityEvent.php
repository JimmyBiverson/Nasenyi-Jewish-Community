<?php

namespace App\Models;

use Database\Factories\CommunityEventFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['title', 'slug', 'description', 'location', 'starts_at', 'ends_at', 'is_published'])]
class CommunityEvent extends Model
{
    /** @use HasFactory<CommunityEventFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return ['starts_at' => 'datetime', 'ends_at' => 'datetime', 'is_published' => 'boolean'];
    }
}
