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

            <div class="col-12 mt-1">
                <div class="card">
                    <div class="card-header">
                        <h4 class="text-bold-500">Shop Stats</h4>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered zero-configuration">
                            <thead>
                                <tr>
                                    <th>Shop</th>
                                    <th>Status</th>
                                    <th>In Stock</th>
                                    <th>Sold</th>
                                    <th>Total Purchase Value</th>
                                    <th>Total Sales</th>
                                    <th>Total Profit</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($shops as $shop)
                                <tr>
                                    <td>{{ $shop->name }}</td>
                                    <td>{{ $shop->is_active ? 'Active' : 'Inactive' }}</td>
                                    <td>{{ $shop->in_stock_units }}</td>
                                    <td>{{ $shop->sold_units_count }}</td>
                                    <td>Rs. {{ number_format($shop->total_purchase_value, 2) }}</td>
                                    <td>Rs. {{ number_format($shop->total_sales, 2) }}</td>
                                    <td>Rs. {{ number_format($shop->total_profit, 2) }}</td>
                                    <td>
                                        <a href="{{ route('shops.stats.sold', $shop->id) }}" class="btn btn-sm btn-outline-primary">
                                            View Sold Units
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center">No shops yet.</td>
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
