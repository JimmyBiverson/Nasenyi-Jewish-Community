<?php

namespace App\Support;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

class AllowedImage implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value instanceof UploadedFile || ! $value->isValid()) {
            $fail('Choose a valid image file.');

            return;
        }

        $inspection = ImageUpload::inspect($value->getRealPath() ?: $value->getPathname());

        if ($inspection === null) {
            $fail('Choose a readable JPEG, PNG, WebP, AVIF, GIF, BMP, or ICO image.');

            return;
        }

        if ($inspection['width'] > 6000 || $inspection['height'] > 6000 || $inspection['width'] * $inspection['height'] > 16000000) {
            $fail('Choose an image within 6,000 pixels per side and 16 megapixels.');
        }
    }
}
