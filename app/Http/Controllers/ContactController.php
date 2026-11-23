<?php

namespace App\Http\Controllers;

use App\Mail\ContactFormMessage;
use App\Models\ContactMessage;
use App\Models\ContentEntry;
use App\Models\SiteSetting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Throwable;

class ContactController extends Controller
{
    public function show(): View
    {
        return view('site.contact', [
            'entry' => ContentEntry::published()->where('type', 'page')->where('slug', 'contact-us')->first(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $fields = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:254'],
            'message' => ['required', 'string', 'max:5000'],
            'website' => ['prohibited'],
        ]);

        $message = ContactMessage::create([
            'name' => $fields['first_name'].' '.$fields['last_name'],
            'email' => $fields['email'],
            'message' => $fields['message'],
        ]);

        $recipient = SiteSetting::where('key', 'contact_email')->value('value');
        $delivered = false;

        if (filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            try {
                Mail::to($recipient)->send(new ContactFormMessage($message));
                $delivered = ! in_array(config('mail.default'), ['log', 'array'], true);
            } catch (Throwable $exception) {
                report($exception);
            }
        }

        return to_route('site.contact')->with('contact_status', $delivered
            ? 'Thank you. Your message has been sent to the community.'
            : 'Thank you. Your message was saved in the community inbox.');
    }
}
