<?php

namespace App\View\Components;

use App\Models\ContentEntry;
use App\Support\ContentHtml;
use App\Support\ImageUpload;
use App\Support\SiteBranding;
use App\Support\SocialImage;
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
        $settings = SiteBranding::settings();
        $siteName = $settings['site_name'] ?? 'Nasenyi Abayudaya Community';
        $logo = SiteBranding::image('logo');
        $favicon = SiteBranding::image('favicon');
        $faviconType = ImageUpload::mime($favicon);
        $blogImageRatio = $settings['blog_image_ratio'] ?? '3:2';
        $blogImageRatio = in_array($blogImageRatio, ['3:2', '16:9', '4:3', '1:1'], true) ? $blogImageRatio : '3:2';
        $blogImageFit = ($settings['blog_image_fit'] ?? null) === 'contain' ? 'contain' : 'cover';
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
        $social = SocialImage::make($this->image, $siteName);
        $socialImage = $social['url'];
        $canonicalUrl = $this->canonical ?: (request()->path() === '/' ? url('/') : url(request()->path()).'/');
        $brandParts = preg_split('/\s+/', trim($siteName), 2) ?: [$siteName];
        $brandPrimary = $brandParts[0];
        $brandSecondary = $brandParts[1] ?? '';
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
            'settings', 'siteName', 'logo', 'favicon', 'faviconType', 'communityLinks', 'available', 'socialLinks', 'socialImage', 'social', 'canonicalUrl',
            'siteTitle', 'brandPrimary', 'brandSecondary', 'organization', 'blogImageRatio', 'blogImageFit',
        ));
    }
}
