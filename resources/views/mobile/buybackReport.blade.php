@extends('user_navbar')
@section('content')

<style>
    .card { border-radius: 12px; }
</style>

<div class="app-content content">
    <div class="content-overlay"></div>
    <div class="content-wrapper">
        <div class="content-header row"></div>
        <div class="content-body">
            @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if (session('danger'))
            <div class="alert alert-danger">{{ session('danger') }}</div>
            @endif

            <div class="ml-1">
                <form method="GET" action="{{ route('mobile.buyback.report') }}" class="mb-3 d-flex align-items-center flex-wrap" style="gap:10px;">
                    <div>
                        <input type="date" class="form-control" name="start_date" value="{{ $start }}" style="max-width: 180px;">
                    </div>
                    <span>to</span>
                    <div>
                        <input type="date" class="form-control" name="end_date" value="{{ $end }}" style="max-width: 180px;">
                    </div>
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('mobile.buyback.report') }}" class="btn btn-secondary">Reset</a>
                </form>
            </div>

            <div class="row ml-1 mb-2">
                <div class="col-12 col-md-4">
                    <h5>Total Buyback Cost: Rs. {{ number_format($totalBuybackAmount, 2) }}</h5>
                </div>
            </div>

            <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-12 latest-update-tracking mt-1">
                <div class="card">
                    <div class="card-header latest-update-heading d-flex justify-content-between">
                        <h4 class="latest-update-heading-title text-bold-500">Mobile Buyback Report</h4>
                    </div>
                    <div class="table-responsive">
                        <table id="mobileBuybackReportTable" class="table table-striped table-bordered zero-configuration">
                            <thead>
                                <tr>
                                    <th>Buyback Date</th>
                                    <th>Name</th>
                                    <th>IMEI</th>
                                    <th>Original Sale #</th>
                                    <th>Buyback Price</th>
                                    <th>New Selling Price</th>
                                    <th>Bought Back From</th>
                                    <th>Phone</th>
                                    <th>Payment</th>
                                    <th>Processed By</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($buybacks as $bb)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($bb->buyback_date)->format('d M Y, H:i') }}</td>
                                    <td>{{ $bb->unit->name ?? '-' }}</td>
                                    <td>{{ $bb->unit->imei ?? '-' }}</td>
                                    <td>{{ $bb->mobile_sale_id ? '#'.$bb->mobile_sale_id : '-' }}</td>
                                    <td>Rs. {{ number_format($bb->buyback_price, 2) }}</td>
                                    <td>Rs. {{ number_format($bb->new_selling_price, 2) }}</td>
                                    <td>{{ $bb->seller_name }}</td>
                                    <td>{{ $bb->seller_phone ?: '-' }}</td>
                                    <td>
                                        @if($bb->payment_method === 'bank')
                                        Bank ({{ $bb->bank->name ?? '-' }})
                                        @else
                                        Counter
                                        @endif
                                    </td>
                                    <td>{{ $bb->user->name ?? '-' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="10" class="text-center">No buybacks found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        $('#mobileBuybackReportTable').DataTable({ order: [[0, 'desc']] });
    });
</script>

@endsection
