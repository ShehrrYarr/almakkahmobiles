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

            <div class="ml-1">
                <form method="GET" action="{{ route('mobile.sales.report') }}" class="mb-3 d-flex align-items-center flex-wrap" style="gap:10px;">
                    <div>
                        <input type="date" class="form-control" name="start_date" value="{{ $start }}" style="max-width: 180px;">
                    </div>
                    <span>to</span>
                    <div>
                        <input type="date" class="form-control" name="end_date" value="{{ $end }}" style="max-width: 180px;">
                    </div>
                    <div>
                        <select id="vendor_filter" name="vendor_id" class="form-control" style="min-width: 240px;">
                            @if($vendorId)
                            <option value="{{ $vendorId }}" selected>{{ optional($vendors->firstWhere('id', $vendorId))->name }}</option>
                            @endif
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('mobile.sales.report') }}" class="btn btn-secondary">Reset</a>
                </form>
            </div>

            <div class="row ml-1 mb-2">
                <div class="col-12 col-md-4">
                    <h5>Total Selling Price: Rs. {{ number_format($totalSelling, 2) }}</h5>
                </div>
                <div class="col-12 col-md-4">
                    <h5>Total Profit: Rs. {{ number_format($totalProfit, 2) }}</h5>
                </div>
            </div>

            <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-12 latest-update-tracking mt-1">
                <div class="card">
                    <div class="card-header latest-update-heading d-flex justify-content-between">
                        <h4 class="latest-update-heading-title text-bold-500">Mobile Sales Report</h4>
                    </div>
                    <div class="table-responsive">
                        <table id="mobileSalesReportTable" class="table table-striped table-bordered zero-configuration">
                            <thead>
                                <tr>
                                    <th>Sale #</th>
                                    <th>Date</th>
                                    <th>Customer/Vendor</th>
                                    <th>Items</th>
                                    <th>Total</th>
                                    <th>Profit</th>
                                    <th>Receipt</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rows as $row)
                                @php $sale = $row['sale']; @endphp
                                <tr>
                                    <td>{{ $sale->id }}</td>
                                    <td>{{ \Carbon\Carbon::parse($sale->sale_date)->format('d M Y, H:i') }}</td>
                                    <td>
                                        @if($sale->vendor)
                                        Vendor: {{ $sale->vendor->name }}
                                        @elseif($sale->customer_name)
                                        Customer: {{ $sale->customer_name }}
                                        @else
                                        Walk-in
                                        @endif
                                    </td>
                                    <td>
                                        {{ $row['item_count'] }}
                                        @if($row['returned_count'] > 0)
                                        <span class="badge badge-danger ml-1">{{ $row['returned_count'] }} returned</span>
                                        @endif
                                    </td>
                                    <td><strong>Rs. {{ number_format($sale->total_amount, 2) }}</strong></td>
                                    <td>
                                        <strong class="{{ $row['profit'] < 0 ? 'text-danger' : 'text-success' }}">
                                            Rs. {{ number_format($row['profit'], 2) }}
                                        </strong>
                                        @if($row['returned_count'] > 0)
                                        <div class="small text-muted">excl. returned item(s)</div>
                                        @endif
                                    </td>
                                    <td>
                                        <a class="btn btn-sm btn-outline-primary" target="_blank" href="{{ route('mobile.pos.invoice', $sale->id) }}">
                                            Receipt
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center">No sales found.</td>
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
        $('#vendor_filter').select2({
            theme: 'bootstrap4', width: '100%', placeholder: 'All Vendors', allowClear: true,
            ajax: {
                url: '{{ route('mobile.vendors.search') }}',
                dataType: 'json', delay: 200,
                data: params => ({ q: params.term || '' }),
                processResults: data => ({ results: data }),
                cache: true
            },
            minimumInputLength: 0
        });

        $('#mobileSalesReportTable').DataTable({ order: [[0, 'desc']] });
    });
</script>

@endsection
