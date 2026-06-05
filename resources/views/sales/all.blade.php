@extends('user_navbar')
@section('content')

<!-- Custom Return Modal -->
<style>
    #returnOverlay {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 99999;
        background: rgba(0,0,0,0.55);
        justify-content: center;
        align-items: center;
    }
    #returnOverlay.open { display: flex; }
    #returnBox {
        background: #fff;
        border-radius: 12px;
        width: 100%;
        max-width: 540px;
        max-height: 90vh;
        display: flex;
        flex-direction: column;
        box-shadow: 0 8px 40px rgba(0,0,0,0.25);
    }
    #returnBox .r-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px 20px;
        border-bottom: 1px solid #eee;
    }
    #returnBox .r-header h5 { margin: 0; font-weight: 700; }
    #returnBox .r-close {
        background: none;
        border: none;
        font-size: 1.4em;
        cursor: pointer;
        color: #666;
        line-height: 1;
    }
    #returnBox .r-close:hover { color: #000; }
    #returnBox .r-body {
        padding: 20px;
        overflow-y: auto;
        flex: 1;
    }
    #returnBox .r-footer {
        padding: 14px 20px;
        border-top: 1px solid #eee;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }
</style>

<div id="returnOverlay">
    <div id="returnBox">
        <div class="r-header">
            <h5>Sale Items — Process Return</h5>
            <button type="button" class="r-close" id="returnCloseBtn">&times;</button>
        </div>
        <form id="return-items-form" method="POST" action="">
            @csrf
            <div class="r-body" id="returnModalBody">
                <div class="text-center py-3"><i class="fa fa-spinner fa-spin"></i> Loading...</div>
            </div>
            <div class="r-footer">
                <button type="button" class="btn btn-secondary" id="returnCancelBtn">Cancel</button>
                <button type="submit" class="btn btn-primary" id="returnSubmitBtn">Submit Return</button>
            </div>
        </form>
    </div>
</div>

<div class="app-content content">
    <div class="content-overlay"></div>
    <div class="content-wrapper">
        <div class="content-header row">
        </div>
        <div class="content-body">
            @if (session('success'))
            <div class="alert alert-success" id="successMessage">
                {{ session('success') }}
            </div>
            @endif

            @if (session('danger'))
            <div class="alert alert-danger" id="dangerMessage" style="color: red;">
                {{ session('danger') }}
            </div>
            @endif

            <div class="ml-1">
                <form method="GET" action="{{ route('sales.all') }}" class="mb-3 d-flex align-items-center">
                    <input type="date" class="form-control mr-2" name="start_date" value="{{ request('start_date') }}"
                        style="max-width: 180px;">
                    <span class="mx-1">to</span>
                    <input type="date" class="form-control mr-2" name="end_date" value="{{ request('end_date') }}"
                        style="max-width: 180px;">
                    <button type="submit" class="btn btn-primary mx-1">Filter</button>
                    <a href="{{ route('sales.all') }}" class="btn btn-secondary mx-1">Reset</a>
                </form>
            </div>
            @php
            $userId = auth()->id();
            @endphp
            @if (in_array($userId, [1, 2]))
            {{-- Expanded totals row: keeps your two totals, adds Profit + Transferred (Bank/Counter) --}}
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

            <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-12 latest-update-tracking mt-1 ">
                <div class="card ">
                    <div class="card-header latest-update-heading d-flex justify-content-between">
                        <h4 class="latest-update-heading-title text-bold-500">All Sales</h4>
                    </div>
                    <div class="table-responsive">
                        <table id="loginTable" class="table table-striped table-bordered zero-configuration">
                            <thead>
                                <tr>
                                    <th>Sale #</th>
                                    <th>Date</th>
                                    <th>Customer/Vendor</th>
                                    <th>Total</th>
                                    <th>Paid Amount</th>
                                    <th>Items</th>
                                    <th>Receipt</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="sales-table-body">
                                @foreach($sales as $sale)
                                @php
                                $subtotal = $sale->items->sum('subtotal');
                                $discount = (float) ($sale->discount_amount ?? 0);
                                @endphp
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
                                        <strong>Rs. {{ number_format($sale->total_amount, 2) }}</strong>
                                        @if($discount > 0)
                                        <div style="font-size:12px; color:#666; line-height:1.2; margin-top:4px;">
                                            <div>Subtotal: Rs. {{ number_format($subtotal, 2) }}</div>
                                            <div>Discount: - Rs. {{ number_format($discount, 2) }}</div>
                                        </div>
                                        @endif
                                    </td>
                                    <td>
                                        {{-- Pay Amount (unchanged logic) --}}
                                        @if($sale->vendor)
                                        Rs. {{ number_format($sale->pay_amount ?? 0, 2) }}
                                        @else
                                        Rs. {{ number_format($sale->total_amount, 2) }}
                                        @endif
                                    </td>
                                    <td>
                                        <a href="javascript:void(0)" class="sale-items-link"
                                            data-sale="{{ $sale->id }}">
                                            <ul style="list-style:none; margin:0; padding:0;">
                                                @foreach($sale->items as $item)
                                                <li>
                                                    {{ $item->batch->accessory->name ?? '-' }} x{{ $item->quantity }}
                                                    ({{ number_format($item->price_per_unit, 2) }} each
                                                    @if($discount > 0)
                                                    — before discount
                                                    @endif
                                                    )
                                                </li>
                                                @endforeach
                                            </ul>
                                        </a>
                                    </td>
                                    <td>
                                        <a class="btn btn-sm btn-outline-primary" target="_blank"
                                            href="{{ route('sales.invoice', $sale->id) }}">
                                            Receipt
                                        </a>
                                        {{-- <a class="btn btn-sm btn-outline-secondary"
                                            href="{{ route('sales.show',$sale->id) }}">View</a> --}}
                                    </td>
                                    <td>
                                        @if($sale->status == 'approved')
                                        <span class="badge bg-success">Approved</span>
                                        @else
                                        <span class="badge bg-warning text-dark">Pending</span>
                                        @endif
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

<script>
    // --- Custom modal helpers ---
    function openReturnModal()  { document.getElementById('returnOverlay').classList.add('open'); }
    function closeReturnModal() { document.getElementById('returnOverlay').classList.remove('open'); }

    // Close on overlay click, X button, Cancel button
    document.getElementById('returnOverlay').addEventListener('click', function(e) {
        if (e.target === this) closeReturnModal();
    });
    document.getElementById('returnCloseBtn').addEventListener('click', closeReturnModal);
    document.getElementById('returnCancelBtn').addEventListener('click', closeReturnModal);

    // Open modal on any .sale-items-link click (event delegation — survives DataTables re-render)
    document.addEventListener('click', function(e) {
        const link = e.target.closest('.sale-items-link');
        if (!link) return;

        const saleId = link.getAttribute('data-sale');
        document.getElementById('return-items-form').action = '/sales/' + saleId + '/return';
        document.getElementById('returnModalBody').innerHTML =
            '<div class="text-center py-3"><i class="fa fa-spinner fa-spin"></i> Loading...</div>';
        openReturnModal();

        fetch('/sales/' + saleId + '/items', { headers: { 'Accept': 'application/json' } })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    let html = '<table class="table table-sm table-bordered">'
                             + '<thead><tr><th>Item</th><th class="text-center">Qty Sold</th><th class="text-center">Return Qty</th></tr></thead><tbody>';
                    data.items.forEach(function(item) {
                        html += `<tr>
                            <td>${item.accessory}</td>
                            <td class="text-center">${item.quantity}</td>
                            <td class="text-center">
                                <input type="number" min="0" max="${item.quantity}" value="0"
                                    class="form-control form-control-sm text-center"
                                    name="return_qty[${item.id}]" style="width:80px; margin:auto;">
                            </td>
                        </tr>`;
                    });
                    html += '</tbody></table>';
                    html += `<input type="hidden" name="sale_id" value="${saleId}">`;
                    document.getElementById('returnModalBody').innerHTML = html;
                } else {
                    document.getElementById('returnModalBody').innerHTML =
                        '<div class="alert alert-danger">Could not load items.</div>';
                }
            })
            .catch(() => {
                document.getElementById('returnModalBody').innerHTML =
                    '<div class="alert alert-danger">Error loading items.</div>';
            });
    });

    // Submit return
    document.getElementById('return-items-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const form      = e.target;
        const submitBtn = document.getElementById('returnSubmitBtn');
        submitBtn.disabled  = true;
        submitBtn.innerText = 'Processing...';

        fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                'Accept': 'application/json'
            },
            body: new FormData(form)
        })
        .then(res => res.json())
        .then(data => {
            submitBtn.disabled  = false;
            submitBtn.innerText = 'Submit Return';
            if (data.success) {
                alert(data.message || 'Return processed successfully!');
                closeReturnModal();
                location.reload();
            } else {
                alert(data.message || 'Could not process return.');
            }
        })
        .catch(() => {
            submitBtn.disabled  = false;
            submitBtn.innerText = 'Submit Return';
            alert('Network error. Please try again.');
        });
    });

    // DataTables init
    $(document).ready(function () {
        $('#loginTable').DataTable({ order: [[0, 'desc']] });
    });
</script>

@endsection