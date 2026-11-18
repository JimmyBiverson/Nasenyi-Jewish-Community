<?php

namespace App\Models;

use Database\Factories\DonationCauseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['title', 'slug', 'description', 'is_active'])]
class DonationCause extends Model
{
    /** @use HasFactory<DonationCauseFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
