<x-site-layout title="Support Nasenyi" description="Make a donation to support the Nasenyi Abayudaya Jewish Community." image="/media/headers/donation.jpg">
    @include('site.partials.page-header', ['heading' => 'Support Nasenyi', 'image' => '/media/headers/donation.jpg', 'subtitle' => 'Help sustain Jewish life, learning, and community care in Nasenyi.'])

    <section class="wrapper contact-layout" aria-label="Make a donation" data-reveal>
        <div class="prose">
            <h2>Give to the community</h2>
            <p>Your contribution supports the Nasenyi Abayudaya Jewish Community. Give where it is needed most or select a cause below.</p>

            @if($causes->isNotEmpty())
                <h3>Donation causes</h3>
                <ul class="cause-list">
                    @foreach($causes as $cause)
                        <li>
                            <strong>{{ $cause->title }}</strong>
                            @if(filled($cause->description))
                                <div>{{ $cause->description }}</div>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif

            <p>For other ways to help, <a href="{{ route('site.contact') }}">get in touch with the community</a>.</p>
        </div>

        @if($available)
            <form class="contact-form" method="post" action="{{ route('donations.store') }}">
                @csrf
                <input type="hidden" name="reference" value="{{ old('reference', $reference) }}">
                <h2>Make a donation</h2>
                <p class="form-intro">Continue to Pesapal to complete your payment securely.</p>

                @if($errors->any())
                    <div class="form-error" role="alert">
                        <ul>
                            @foreach($errors->all() as $message)
                                <li>{{ $message }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if($causes->isNotEmpty())
                    <label class="field">Donation cause
                        <select name="cause">
                            <option value="">Where it’s needed most</option>
                            @foreach($causes as $cause)
                                <option value="{{ $cause->slug }}" @selected(old('cause', $selectedCause) === $cause->slug)>{{ $cause->title }}</option>
                            @endforeach
                        </select>
                    </label>
                @endif

                <div class="field-grid">
                    <label class="field">Currency
                        <select name="currency" required>
                            @foreach($currencies as $currency)
                                <option value="{{ $currency }}" @selected(old('currency', $currencies[0]) === $currency)>{{ $currency }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="field">Amount
                        <input type="text" name="amount" inputmode="decimal" maxlength="12" placeholder="Amount" value="{{ old('amount') }}" required>
                    </label>
                </div>

                <label class="field">Full name
                    <input type="text" name="donor_name" autocomplete="name" maxlength="160" value="{{ old('donor_name') }}" required>
                </label>
                <label class="field">Email address
                    <input type="email" name="donor_email" autocomplete="email" maxlength="254" value="{{ old('donor_email') }}" required>
                </label>
                <label class="field">Phone number (optional)
                    <input type="tel" name="donor_phone" autocomplete="tel" maxlength="24" value="{{ old('donor_phone') }}">
                </label>

                <button class="button button-gold" type="submit">Continue to Pesapal <span aria-hidden="true">→</span></button>
            </form>
        @else
            <div class="contact-form">
                <h2>Online donations are temporarily unavailable</h2>
                <p>Please contact the community and we will be glad to help you give.</p>
                <a class="button button-gold" href="{{ route('site.contact') }}">Contact us</a>
            </div>
        @endif
    </section>
</x-site-layout>
