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
                <form method="GET" action="{{ route('mobile.sales.all') }}" class="mb-3 d-flex align-items-center">
                    <input type="date" class="form-control mr-2" name="start_date" value="{{ request('start_date') }}"
                        style="max-width: 180px;">
                    <span class="mx-1">to</span>
                    <input type="date" class="form-control mr-2" name="end_date" value="{{ request('end_date') }}"
                        style="max-width: 180px;">
                    <button type="submit" class="btn btn-primary mx-1">Filter</button>
                    <a href="{{ route('mobile.sales.all') }}" class="btn btn-secondary mx-1">Reset</a>
                </form>
            </div>

            @if (auth()->user()->isAdmin())
            <div class="row ml-1 mb-2">
                <div class="col-12 col-md-3">
                    <h5>Total Selling Price: Rs. {{ number_format($totalSellingPrice, 2) }}</h5>
                </div>
                <div class="col-12 col-md-3">
                    <h5>Total Paid Price: Rs. {{ number_format($totalPaidPrice, 2) }}</h5>
                </div>
                <div class="col-12 col-md-3">
                    <h5>Total Profit: Rs. {{ number_format($totalProfit ?? 0, 2) }}</h5>
                </div>
                <div class="col-12 col-md-3">
                    <h5 class="mb-0">Transferred</h5>
                    <div class="small">
                        Bank: <strong>Rs. {{ number_format($totalTransferredBank ?? 0, 2) }}</strong>
                        &nbsp;|&nbsp;
                        Counter: <strong>Rs. {{ number_format($totalTransferredCounter ?? 0, 2) }}</strong>
                    </div>
                </div>
            </div>
            @endif

            <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-12 latest-update-tracking mt-1">
                <div class="card">
                    <div class="card-header latest-update-heading d-flex justify-content-between">
                        <h4 class="latest-update-heading-title text-bold-500">All Mobile Sales</h4>
                    </div>
                    <div class="table-responsive">
                        <table id="mobileSalesTable" class="table table-striped table-bordered zero-configuration">
                            <thead>
                                <tr>
                                    <th>Sale #</th>
                                    <th>Date</th>
                                    <th>Customer</th>
                                    <th>Total</th>
                                    <th>Paid Amount</th>
                                    <th>Items</th>
                                    <th>Profit</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sales as $sale)
                                @php
                                $discount = (float) ($sale->discount_amount ?? 0);
                                @endphp
                                <tr>
                                    <td>{{ $sale->id }}</td>
                                    <td>{{ \Carbon\Carbon::parse($sale->sale_date)->format('d M Y, H:i') }}</td>
                                    <td>
                                        @if($sale->customer_name)
                                        {{ $sale->customer_name }}
                                        @else
                                        Walk-in
                                        @endif
                                    </td>
                                    <td>
                                        <strong>Rs. {{ number_format($sale->total_amount, 2) }}</strong>
                                        @if($discount > 0)
                                        <div style="font-size:12px; color:#666; line-height:1.2; margin-top:4px;">
                                            Discount: - Rs. {{ number_format($discount, 2) }}
                                        </div>
                                        @endif
                                    </td>
                                    <td>
                                        Rs. {{ number_format($sale->total_amount, 2) }}
                                    </td>
                                    <td>
                                        <ul style="list-style:none; margin:0; padding:0;">
                                            @foreach($sale->items as $item)
                                            @php $returned = $item->returnItems->isNotEmpty(); @endphp
                                            <li class="{{ $returned ? 'text-muted' : '' }}" style="{{ $returned ? 'text-decoration:line-through;' : '' }}">
                                                {{ $item->unit->name ?? '-' }}
                                                <span class="text-muted">(IMEI {{ $item->unit->imei ?? '-' }}, Rs. {{ number_format($item->price, 2) }})</span>
                                                @if($returned)
                                                <span class="badge badge-danger">Returned</span>
                                                @endif
                                            </li>
                                            @endforeach
                                        </ul>
                                    </td>
                                    <td>
                                        <ul style="list-style:none; margin:0; padding:0;">
                                            @foreach($sale->items as $item)
                                            @php
                                                $returned = $item->returnItems->isNotEmpty();
                                                $profit = (float) $item->price - (float) ($item->unit->purchase_price ?? 0);
                                            @endphp
                                            <li class="{{ $returned ? 'text-muted' : '' }}" style="{{ $returned ? 'text-decoration:line-through;' : '' }}">
                                                Rs. {{ number_format($profit, 2) }}
                                            </li>
                                            @endforeach
                                        </ul>
                                    </td>
                                    <td>
                                        <a class="btn btn-sm btn-outline-primary" target="_blank"
                                            href="{{ route('mobile.pos.invoice', $sale->id) }}">
                                            Receipt
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger mt-1" onclick="openReturnModal({{ $sale->id }})">
                                            Return
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div class="mt-2">
                            {{ $sales->links() }}
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- Return Modal --}}
<div class="modal fade" id="return-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold"><i class="fa fa-undo mr-1"></i> Return Items — Sale #<span id="return-sale-id"></span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body" id="return-modal-body">
                <div class="text-center py-3 text-muted"><i class="fa fa-spinner fa-spin mr-1"></i> Loading…</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-warning" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="return-submit-btn" onclick="submitReturn()">
                    <i class="fa fa-undo mr-1"></i> Process Return
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    let _returnSaleId = null;

    async function openReturnModal(saleId) {
        _returnSaleId = saleId;
        document.getElementById('return-sale-id').textContent = saleId;
        const body = document.getElementById('return-modal-body');
        body.innerHTML = '<div class="text-center py-3 text-muted"><i class="fa fa-spinner fa-spin mr-1"></i> Loading…</div>';
        $('#return-modal').modal('show');

        try {
            const res = await fetch(`/mobile/sales/${saleId}/return-items`, { credentials: 'same-origin' });
            const data = await res.json();
            if (!data.success) { body.innerHTML = '<div class="text-danger">Failed to load items.</div>'; return; }

            const returnable = data.items.filter(i => !i.already_returned);
            if (!returnable.length) {
                body.innerHTML = '<div class="text-muted">All items on this sale have already been returned.</div>';
                return;
            }

            let html = '<div class="mb-2 small text-muted">Select the phone(s) being returned:</div>';
            returnable.forEach(i => {
                html += `
                  <div class="custom-control custom-checkbox mb-2">
                    <input type="checkbox" class="custom-control-input return-item-checkbox" id="ret-item-${i.id}" value="${i.id}">
                    <label class="custom-control-label" for="ret-item-${i.id}">
                      ${i.mobile_name} <span class="text-muted">(IMEI ${i.imei})</span> — Rs. ${Number(i.price).toFixed(2)}
                    </label>
                  </div>`;
            });
            body.innerHTML = html;
        } catch (e) {
            body.innerHTML = '<div class="text-danger">Error: ' + e.message + '</div>';
        }
    }

    async function submitReturn() {
        const checked = Array.from(document.querySelectorAll('.return-item-checkbox:checked')).map(c => Number(c.value));
        if (!checked.length) return alert('Select at least one item to return.');

        const btn = document.getElementById('return-submit-btn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin mr-1"></i> Processing…';

        try {
            const res = await fetch(`/mobile/sales/${_returnSaleId}/return`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                body: JSON.stringify({ item_ids: checked }),
            });
            const data = await res.json();
            if (data.success) {
                $('#return-modal').modal('hide');
                alert('Return processed — Rs. ' + data.refund_amount + ' refunded.');
                window.location.reload();
            } else {
                alert('Return failed: ' + (data.message || 'Unknown error'));
            }
        } catch (e) {
            alert('Return failed: ' + e.message);
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa fa-undo mr-1"></i> Process Return';
        }
    }

    $(document).ready(function () {
        $('#mobileSalesTable').DataTable({ order: [[0, 'desc']] });
    });
</script>

@endsection
