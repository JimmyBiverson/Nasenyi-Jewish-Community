<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class InboxController extends Controller
{
    public function index(): View
    {
        return view('admin.inbox.index', [
            'messages' => ContactMessage::query()->latest('id')->paginate(25),
        ]);
    }

    public function show(ContactMessage $message): View
    {
        return view('admin.inbox.show', compact('message'));
    }

    public function update(ContactMessage $message): RedirectResponse
    {
        $message->read_at = $message->read_at ? null : now();
        $message->save();

        return to_route('admin.inbox.show', $message)->with('status', 'Message status updated.');
    }

    public function destroy(ContactMessage $message): RedirectResponse
    {
        $message->delete();

        return to_route('admin.inbox.index')->with('status', 'Message deleted.');
    }
}
