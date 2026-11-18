<x-site-layout title="Donation status" image="/media/headers/donation.jpg" :noindex="true">
    @include('site.partials.page-header', ['heading' => 'Donation status', 'image' => '/media/headers/donation.jpg'])

    <section class="wrapper contact-layout" aria-label="Donation details" data-reveal>
        <div class="contact-form status-card status-{{ $donation->status }}">
            <h2>{{ match($donation->status) {
                'completed' => 'Payment confirmed',
                'failed' => 'Payment failed',
                'cancelled' => 'Checkout cancelled',
                'reversed' => 'Payment reversed',
                default => 'Payment pending',
            } }}</h2>

            @switch($donation->status)
                @case('completed')
                    <p>Thank you for supporting Nasenyi Jewish Community. We’ll send a confirmation to the email address you provided.</p>
                    @break
                @case('failed')
                    <p>Pesapal reported that the payment failed. You can return to the donation page and try again.</p>
                    @break
                @case('cancelled')
                    <p>You left the checkout before a payment was confirmed.</p>
                    @break
                @case('reversed')
                    <p>Pesapal reported that this payment was reversed.</p>
                    @break
                @default
                    @if($donation->pesapal_tracking_id)
                        <p>We’re waiting for payment confirmation. You can check with Pesapal for an update below.</p>
                    @else
                        <p>We couldn’t open the checkout. Keep the reference below if you contact us for help.</p>
                    @endif
            @endswitch

            <dl class="donation-meta">
                <div><dt>Amount</dt><dd>{{ $donation->currency }} {{ $donation->amount() }}</dd></div>
                <div><dt>Reference</dt><dd class="break-all">{{ $donation->reference }}</dd></div>
            </dl>

            @if(session('payment_status_warning'))
                <p class="form-error" role="alert">{{ session('payment_status_warning') }}</p>
            @endif
            @if($donation->status === 'pending' && $donation->pesapal_tracking_id)
                <form method="post" action="{{ URL::temporarySignedRoute('donations.refresh', now()->addMinutes(15), ['donation' => $donation->reference], absolute: false) }}">
                    @csrf
                    <button type="submit" class="button button-gold">Check payment status</button>
                </form>
            @endif
            <a class="button button-gold" href="{{ route('donations.show') }}">Back to donations</a>
        </div>
        <div class="prose">
            <h2>Need help?</h2>
            <p><a href="{{ route('site.contact') }}">Contact the community</a> with your donation reference and we will assist you.</p>
        </div>
    </section>
</x-site-layout>
