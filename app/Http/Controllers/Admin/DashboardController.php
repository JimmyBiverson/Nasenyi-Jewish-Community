<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\ContentEntry;
use App\Models\Donation;
use App\Models\GalleryImage;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $counts = [
            'Pages' => ContentEntry::query()->where('type', 'page')->count(),
            'Published posts' => ContentEntry::query()->where('type', 'post')->where('status', 'published')->count(),
            'Drafts' => ContentEntry::query()->where('status', 'draft')->count(),
            'Gallery images' => GalleryImage::query()->count(),
        ];

        if ($request->user()->role === 'admin') {
            $counts['Unread messages'] = ContactMessage::query()->whereNull('read_at')->count();
            $counts['Verified donations'] = Donation::query()->where('status', 'completed')->count();
        }

        return view('admin.dashboard', [
            'counts' => $counts,
            'recent' => ContentEntry::query()->latest('updated_at')->limit(8)->get(),
        ]);
    }
}
