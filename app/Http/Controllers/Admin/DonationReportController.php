<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DonationReportController extends Controller
{
    public function index(Request $request): View
    {
        $statuses = ['pending', 'completed', 'failed', 'cancelled', 'reversed'];
        $status = $request->string('status')->toString();

        return view('admin.donations.index', [
            'status' => $status,
            'statuses' => $statuses,
            'donations' => Donation::query()
                ->with('cause')
                ->when(in_array($status, $statuses, true), fn ($query) => $query->where('status', $status))
                ->latest('id')
                ->paginate(25)
                ->withQueryString(),
            'totals' => Donation::query()
                ->where('status', 'completed')
                ->selectRaw('currency, SUM(amount_minor) as total, COUNT(*) as donations_count')
                ->groupBy('currency')
                ->orderBy('currency')
                ->get(),
        ]);
    }

    public function show(Donation $donation): View
    {
        return view('admin.donations.show', ['donation' => $donation->load('cause')]);
    }
}
