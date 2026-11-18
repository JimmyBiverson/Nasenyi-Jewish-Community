<?php

namespace App\Mail;

use App\Models\Donation;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class DonationReceipt extends Mailable
{
    public function __construct(public Donation $donation) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Nasenyi Jewish Community donation confirmation');
    }

    public function content(): Content
    {
        return new Content(view: 'mail.donation-receipt');
    }
}
