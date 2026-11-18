@extends('admin.layout')
@section('title', 'Donation '.$donation->reference)
@section('main')
    <a class="breadcrumbs" href="{{ route('admin.donations.index') }}">← Donations</a>
    <header class="page-head"><h1>Donation {{ $donation->reference }}</h1></header>
    <section class="panel">
        <dl>
            <dt>Amount</dt><dd>{{ $donation->currency }} {{ $donation->amount() }}</dd>
            <dt>Status</dt><dd><span class="pill">{{ ucfirst($donation->status) }}</span></dd>
            <dt>Cause</dt><dd>{{ $donation->cause?->title ?? 'General donation' }}</dd>
            <dt>Donor</dt><dd>{{ $donation->donor_name }}</dd>
            <dt>Email address</dt><dd>{{ $donation->donor_email }}</dd>
            <dt>Phone</dt><dd>{{ $donation->donor_phone ?: '—' }}</dd>
            <dt>Pesapal tracking ID</dt><dd>{{ $donation->pesapal_tracking_id ?: '—' }}</dd>
            <dt>Received</dt><dd>{{ $donation->created_at?->format('j M Y, H:i') }}</dd>
            <dt>Payment verified</dt><dd>{{ $donation->paid_at?->format('j M Y, H:i') ?? '—' }}</dd>
            <dt>Receipt sent</dt><dd>{{ $donation->receipt_sent_at?->format('j M Y, H:i') ?? '—' }}</dd>
        </dl>
        @if(! empty($donation->payment_history))
            <h2>Payment history</h2>
            <div class="table-scroll"><table>
                <thead><tr><th scope="col">Status</th><th scope="col">Source</th><th scope="col">Date</th></tr></thead>
                <tbody>
                @foreach($donation->payment_history as $record)
                    <tr>
                        <td>{{ ucfirst($record['status']) }}</td>
                        <td>{{ ucfirst($record['source']) }}</td>
                        <td>{{ $record['at'] }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table></div>
        @endif
    </section>
@endsection
