<x-site-layout title="Donation status" :noindex="true">
    <section class="page-hero">
        <div class="wrapper page-hero-inner">
            <h1>Donation status</h1>
        </div>
    </section>

    <section class="wrapper contact-layout" aria-label="Donation details">
        <div class="contact-form">
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
                    @if($donation->checkout_url)
                        <p>We’re waiting for payment confirmation. Refresh this page to check for an update.</p>
                    @else
                        <p>We couldn’t open the checkout. Keep the reference below if you contact us for help.</p>
                    @endif
            @endswitch

            <dl>
                <dt><strong>Amount</strong></dt>
                <dd>{{ $donation->currency }} {{ $donation->amount() }}</dd>
                <dt><strong>Reference</strong></dt>
                <dd class="break-all">{{ $donation->reference }}</dd>
            </dl>

            <a class="button button-gold" href="{{ route('donations.show') }}">Back to donations</a>
        </div>
        <div class="prose">
            <h2>Need help?</h2>
            <p><a href="{{ route('site.contact') }}">Contact the community</a> with your donation reference.</p>
        </div>
    </section>
</x-site-layout>
