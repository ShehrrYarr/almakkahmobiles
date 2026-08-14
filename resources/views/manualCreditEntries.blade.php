@extends('user_navbar')
@section('content')

<style>
    .card {
        border-radius: 12px;
    }
</style>

<div class="app-content content">
    <div class="content-overlay"></div>
    <div class="content-wrapper">
        <div class="content-header row">
        </div>
        <div class="content-body">

            <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-12 latest-update-tracking mt-1">
                <div class="card">
                    <div class="card-header latest-update-heading d-flex justify-content-between">
                        <h4 class="latest-update-heading-title text-bold-500">Manual Credit Entries</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('manualCredits') }}" method="GET" class="d-flex align-items-end flex-wrap" style="gap:16px;">
                            <div>
                                <label class="form-label">From Date</label>
                                <input type="date" class="form-control" name="date_from" value="{{ $dateFrom }}">
                            </div>
                            <div>
                                <label class="form-label">To Date</label>
                                <input type="date" class="form-control" name="date_to" value="{{ $dateTo }}">
                            </div>
                            <div style="min-width:260px;">
                                <label class="form-label">Vendor</label>
                                <select id="vendorSelect" name="vendor_id" class="form-control">
                                    <option value="">All Vendors</option>
                                    @foreach($vendors as $vendor)
                                        <option value="{{ $vendor->id }}" {{ request('vendor_id') == $vendor->id ? 'selected' : '' }}>
                                            {{ $vendor->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <button type="submit" class="btn btn-primary"><i class="fa fa-filter mr-1"></i> Filter</button>
                                <a href="{{ route('manualCredits') }}" class="btn btn-outline-secondary">Reset</a>
                            </div>
                        </form>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered zero-configuration">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Vendor</th>
                                    <th>Amount</th>
                                    <th>Description</th>
                                    <th>Created By</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($entries as $entry)
                                <tr>
                                    <td>{{ $entry->created_at->format('Y-m-d H:i:s') }}</td>
                                    <td>{{ $entry->vendor->name ?? 'N/A' }}</td>
                                    <td>{{ number_format($entry->Credit) }}</td>
                                    <td>{{ $entry->description }}</td>
                                    <td>{{ $entry->creator->name ?? 'N/A' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No manual credit entries found for the selected filters.</td>
                                </tr>
                                @endforelse
                            </tbody>
                            @if($entries->count())
                            <tfoot>
                                <tr>
                                    <th colspan="2">Total</th>
                                    <th>{{ number_format($entries->sum('Credit')) }}</th>
                                    <th></th>
                                    <th></th>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        $('#vendorSelect').select2({
            placeholder: "Select a Vendor",
            allowClear: true,
            width: '100%'
        });
    });
</script>

@endsection
