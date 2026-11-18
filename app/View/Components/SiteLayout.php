<?php

namespace App\View\Components;

use App\Models\ContentEntry;
use App\Models\SiteSetting;
use App\Support\ContentHtml;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Illuminate\View\Component;

class SiteLayout extends Component
{
    public function __construct(
        public string $title,
        public ?string $description = null,
        public ?string $image = null,
        public ?string $canonical = null,
        public string $kind = 'website',
        public bool $noindex = false,
    ) {}

    public function render(): View
    {
        $settings = SiteSetting::pluck('value', 'key')->all();
        $siteName = $settings['site_name'] ?? 'Nasenyi Abayudaya Community';
        $logo = $settings['logo'] ?? '/media/community/logo.jpeg';
        $navigation = [
            'community-spiritual-life' => 'Community & Spiritual Life',
            'community-life' => 'Community Life',
            'home' => 'Traditions',
            'causes' => 'Cultural Identity',
            'education-tab' => 'Education',
            'event-details' => 'Festivals & Celebrations',
            'volunteer' => 'Food Security & Farming',
            'health' => 'Health',
            'team' => 'Global Connection',
        ];
        $available = ContentEntry::published()->whereIn('slug', [...array_keys($navigation), 'about-us'])
            ->pluck('slug')->flip();
        $communityLinks = array_intersect_key($navigation, $available->all());
        $socialLinks = collect([
            'Facebook' => $settings['facebook_url'] ?? null,
            'Instagram' => $settings['instagram_url'] ?? null,
            'YouTube' => $settings['youtube_url'] ?? null,
        ])->filter(fn ($link): bool => is_string($link) && ContentHtml::safeUrl($link));
        $socialImage = $this->image ?: ($settings['social_image'] ?? '/media/community/dji-0909.jpg');
        $canonicalUrl = $this->canonical ?: (request()->path() === '/' ? url('/') : url(request()->path()).'/');
        $siteTitle = Str::contains($this->title, $siteName, ignoreCase: true)
            ? $this->title
            : $this->title.' | '.$siteName;
        $organization = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $siteName,
            'url' => url('/'),
            'logo' => url($logo),
            'sameAs' => $socialLinks->values()->all(),
        ];

        return view('components.site-layout', compact(
            'settings', 'siteName', 'logo', 'communityLinks', 'available', 'socialLinks', 'socialImage', 'canonicalUrl',
            'siteTitle', 'organization',
        ));
    }
}
