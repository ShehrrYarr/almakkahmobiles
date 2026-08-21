@extends('user_navbar')
@section('content')

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

            <div class="ml-1 d-flex align-items-center flex-wrap">
                <a href="{{ route('shops.stats') }}" class="btn btn-secondary mr-2 mb-2">
                    <i class="fa fa-arrow-left mr-1"></i> Back to Shop Stats
                </a>
                <form method="GET" action="{{ route('shops.stats.sold', $shop->id) }}" class="mb-2 d-flex align-items-center">
                    <input type="date" class="form-control mr-2" name="start_date" value="{{ $start }}" style="max-width: 180px;">
                    <span class="mx-1">to</span>
                    <input type="date" class="form-control mr-2" name="end_date" value="{{ $end }}" style="max-width: 180px;">
                    <button type="submit" class="btn btn-primary mx-1">Filter</button>
                    <a href="{{ route('shops.stats.sold', $shop->id) }}" class="btn btn-secondary mx-1">Reset</a>
                </form>
            </div>

            <div class="col-12 mt-1">
                <div class="card">
                    <div class="card-header">
                        <h4 class="text-bold-500">Sold Units — {{ $shop->name }}</h4>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered zero-configuration">
                            <thead>
                                <tr>
                                    <th>Sale Date</th>
                                    <th>Sale #</th>
                                    <th>Mobile</th>
                                    <th>IMEI</th>
                                    <th>Customer</th>
                                    <th>Sale Price</th>
                                    <th>Profit</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($items as $item)
                                @php
                                    $returned = $item->returnItems->isNotEmpty();
                                    $profit = (float) $item->price - (float) ($item->unit->purchase_price ?? 0);
                                @endphp
                                <tr class="{{ $returned ? 'text-muted' : '' }}">
                                    <td>{{ \Carbon\Carbon::parse($item->sale->sale_date)->format('d M Y, H:i') }}</td>
                                    <td>{{ $item->sale->id }}</td>
                                    <td>{{ $item->unit->name ?? '-' }}</td>
                                    <td>{{ $item->unit->imei ?? '-' }}</td>
                                    <td>{{ $item->sale->customer_name ?: 'Walk-in' }}</td>
                                    <td>Rs. {{ number_format($item->price, 2) }}</td>
                                    <td>Rs. {{ number_format($profit, 2) }}</td>
                                    <td>
                                        @if($returned)
                                        <span class="badge badge-danger">Returned</span>
                                        @else
                                        <span class="badge badge-success">Sold</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center">No sold units in this range.</td>
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

@endsection
