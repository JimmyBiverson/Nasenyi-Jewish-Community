@extends('admin.layout')
@section('title', 'Donations')
@section('main')
    <header class="page-head"><h1>Donations</h1></header>
    @if($totals->isNotEmpty())
        <div class="metric-grid" aria-label="Verified donation totals">
            @foreach($totals as $total)
                <div class="metric">
                    <strong>{{ $total->currency }} {{ (new \App\Models\Donation(['currency' => $total->currency, 'amount_minor' => $total->total]))->amount() }}</strong>
                    <span>{{ $total->donations_count }} verified {{ Str::plural('donation', $total->donations_count) }}</span>
                </div>
            @endforeach
        </div>
    @endif
    <section class="panel">
        <form class="toolbar" action="{{ route('admin.donations.index') }}" method="get">
            <label class="label" for="payment-status">Payment status</label>
            <div><select id="payment-status" name="status">
                <option value="">All statuses</option>
                @foreach($statuses as $value)
                    <option value="{{ $value }}" @selected($value === $status)>{{ ucfirst($value) }}</option>
                @endforeach
            </select></div>
            <button class="ghost" type="submit">Apply filter</button>
        </form>
        @if($donations->isEmpty())
            <p class="subtle">No donations match this view.</p>
        @else
            <div class="table-scroll"><table>
                <thead><tr><th scope="col">Reference</th><th scope="col">Donor</th><th scope="col">Cause</th><th scope="col">Amount</th><th scope="col">Status</th><th scope="col">Created</th></tr></thead>
                <tbody>
                @foreach($donations as $donation)
                    <tr>
                        <td><a href="{{ route('admin.donations.show', $donation) }}">{{ $donation->reference }}</a></td>
                        <td>{{ $donation->donor_name }}</td>
                        <td>{{ $donation->cause?->title ?? 'General donation' }}</td>
                        <td>{{ $donation->currency }} {{ $donation->amount() }}</td>
                        <td><span class="pill">{{ ucfirst($donation->status) }}</span></td>
                        <td>{{ $donation->created_at?->format('j M Y') }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table></div>
            @include('admin.pagination', ['results' => $donations])
        @endif
    </section>
@endsection
