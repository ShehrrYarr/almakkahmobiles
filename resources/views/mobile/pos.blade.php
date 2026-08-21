@extends('user_navbar')
@section('content')

<style>
    :root {
        --lb-dark:   #4a90c4;
        --lb-mid:    #7ab8e0;
        --lb-light:  #b8d9f2;
        --lb-xlight: #e8f4fc;
        --lb-text:   #fff;
    }

    .card > .card-header {
        background: linear-gradient(135deg, var(--lb-dark) 0%, var(--lb-mid) 55%, var(--lb-light) 100%) !important;
        color: var(--lb-text) !important;
        border-bottom: none !important;
    }
    .card > .card-header span, .card > .card-header h6, .card > .card-header h5, .card > .card-header i { color: var(--lb-text) !important; }

    .payment-method-card {
        border: 2px solid var(--lb-light); border-radius: 10px; padding: 12px 18px;
        cursor: pointer; transition: all .2s; background: #fff; user-select: none;
    }
    .payment-method-card:hover { border-color: var(--lb-mid); background: var(--lb-xlight); }
    .payment-method-card.active { border-color: var(--lb-dark); background: linear-gradient(135deg, var(--lb-xlight), #fff); box-shadow: inset 0 1px 4px rgba(74,144,196,.15); }
    .payment-method-card input[type="radio"] { display: none; }

    #checkout-btn { background: linear-gradient(135deg, #1a7a3a 0%, #2ecc6a 100%) !important; border: none !important; color: #fff !important; text-shadow: 0 1px 2px rgba(0,0,0,.2); }
    #checkout-btn:hover { filter: brightness(1.08); }
    #hold-btn { background: linear-gradient(135deg, #b45309 0%, #f59e0b 100%) !important; border: none !important; color: #fff !important; text-shadow: 0 1px 2px rgba(0,0,0,.2); }
    #hold-btn:hover { filter: brightness(1.08); }
    #held-orders-btn { background: linear-gradient(135deg, #b45309 0%, #f59e0b 100%) !important; border: none !important; color: #fff !important; font-size: .8rem; }
    #held-orders-btn:hover { filter: brightness(1.08); }
    .btn-warning { background: linear-gradient(135deg, var(--lb-dark) 0%, var(--lb-light) 100%) !important; border: none !important; color: #fff !important; }
    .btn-warning:hover { filter: brightness(1.08); }
    .btn-secondary { background: linear-gradient(135deg, var(--lb-dark) 0%, var(--lb-mid) 100%) !important; border: none !important; color: #fff !important; }
    .btn-secondary:hover { filter: brightness(1.08); }
    #sync-now-btn { background: linear-gradient(135deg, var(--lb-dark), var(--lb-light)) !important; border: none !important; color: #fff !important; }
    #cart-badge { background: linear-gradient(135deg, var(--lb-dark), var(--lb-mid)) !important; color: #fff !important; font-size: .75em; vertical-align: middle; }

    .cart-item-remove { background: none; border: none; color: #dc3545; font-size: 1.1em; cursor: pointer; padding: 2px 6px; border-radius: 4px; transition: background .15s; }
    .cart-item-remove:hover { background: #fde8ea; }
    .cart-input { width: 72px; border: 1px solid var(--lb-light); border-radius: 6px; padding: 4px 6px; text-align: center; font-size: 1.05rem; font-weight: 700; }
    .cart-input:focus { outline: none; border-color: var(--lb-dark); }

    .fa-shopping-cart.text-primary { color: var(--lb-dark) !important; }
    .card { background: #e8eaed !important; border-radius: 14px !important; overflow: hidden; }
    .card .card-body, .card .card-footer { background: #e8eaed !important; }
    .card .table, .card .table thead { background: #e8eaed !important; }
    .card .table thead tr { background: #d8dadd !important; }

    @media (max-width: 991px) {
        .pos-sticky { position: static !important; }
        .content-body { padding-bottom: 86px; }
        #desktop-actions { display: none !important; }
        #mobile-action-bar {
            display: flex !important; position: fixed; left: 0; right: 0; bottom: 0; z-index: 9995;
            background: #fff; border-top: 1px solid #d8dadd; box-shadow: 0 -3px 12px rgba(0,0,0,.12);
            padding: 8px 10px; gap: 8px; align-items: center;
        }
        #mobile-action-bar .mab-total { flex: 0 0 auto; line-height: 1.15; padding-right: 4px; }
        #mobile-action-bar .mab-total .mab-label { font-size: .68rem; color: #6c757d; }
        #mobile-action-bar .mab-total .mab-amount { font-size: 1.05rem; font-weight: 800; color: #15803d; white-space: nowrap; }
        #mobile-action-bar #mobile-hold-btn { flex: 0 0 auto; background: linear-gradient(135deg, #b45309 0%, #f59e0b 100%) !important; border: none; color: #fff; font-weight: 700; padding: 10px 14px; border-radius: 8px; }
        #mobile-action-bar #mobile-checkout-btn { flex: 1 1 auto; background: linear-gradient(135deg, #1a7a3a 0%, #2ecc6a 100%) !important; border: none; color: #fff; font-weight: 700; padding: 10px 8px; border-radius: 8px; font-size: .95rem; }
        .card .card-body { padding: 10px !important; }
        .cart-input { width: 56px; padding: 6px 4px; font-size: 1rem; }
        #sale-cart-table { font-size: .9rem !important; }
        #sale-cart-table td, #sale-cart-table th { padding: 4px 3px !important; }
        input.form-control, select.form-control, textarea.form-control, .cart-input, #imei_search { font-size: 16px !important; }
        .btn-warning, .btn-secondary { padding: 10px 16px !important; }
        #daily-sales-body { display: none; }
        #daily-sales-body.mobile-open { display: block; }
        #held-orders-modal .modal-dialog { margin: 8px; max-width: none; }
        #held-orders-modal .table { font-size: .85rem !important; }
        .content-body h3 { font-size: 1.25rem; }
        #offline-banner, #sync-banner { font-size: .8em; padding: 6px 10px; }
    }
    #mobile-action-bar { display: none; }
    #daily-sales-toggle { display: none; }
    @media (max-width: 991px) { #daily-sales-toggle { display: inline-block; } }
</style>

<div class="app-content content">
    <div class="content-overlay"></div>
    <div class="content-wrapper">
        <div class="content-header row"></div>
        <div class="content-body">

            <div id="offline-banner" style="display:none;position:fixed;top:56px;left:0;right:0;z-index:9990;background:#fd7e14;color:#fff;text-align:center;padding:7px 16px;font-weight:bold;font-size:.9em;box-shadow:0 2px 8px rgba(0,0,0,.15);">
                <i class="fa fa-exclamation-triangle mr-1"></i> You are offline — sales are saved locally and will sync automatically when internet returns
                <span id="offline-count" class="badge badge-light text-dark ml-2" style="display:none;"></span>
            </div>
            <div id="sync-banner" style="display:none;position:fixed;top:56px;left:0;right:0;z-index:9991;background:#0d6efd;color:#fff;text-align:center;padding:7px 16px;font-weight:bold;font-size:.9em;">
                <i class="fa fa-refresh fa-spin mr-1"></i> Syncing offline sales to server…
            </div>

            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">{{ session('success') }}<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button></div>
            @endif
            @if(session('danger'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">{{ session('danger') }}<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button></div>
            @endif

            <div id="failed-sales-panel" style="display:none;" class="mb-2"></div>

            <div class="d-flex align-items-center justify-content-between mb-2">
                <h3 class="mb-0 font-weight-bold"><i class="fa fa-mobile text-primary mr-2"></i> Mobile POS</h3>
            </div>

            <div class="row">
                {{-- ===== LEFT COLUMN ===== --}}
                <div class="col-lg-5">

                    {{-- Customer / Vendor --}}
                    <div class="card shadow-sm mb-2">
                        <div class="card-header py-2 bg-white border-bottom">
                            <span class="font-weight-bold" style="font-size:1rem;"><i class="fa fa-user text-secondary mr-1"></i> Customer / Vendor</span>
                        </div>
                        <div class="card-body p-2">
                            <select name="vendor_id" id="vendor_id" class="form-control mb-1">
                                <option value="">Walk-in Customer</option>
                                @foreach($vendors as $vendor)
                                <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                                @endforeach
                            </select>
                            <input type="text" name="customer_name" id="customer_name" class="form-control mb-1" placeholder="Customer name (optional)">
                            <div id="customer_mobile_row" style="display:none;">
                                <input type="text" name="customer_mobile" id="customer_mobile" class="form-control mb-1" placeholder="Mobile: 923XXXXXXXXX">
                            </div>
                            <textarea id="sale_comment" name="comment" rows="1" class="form-control" placeholder="Comment (optional)"></textarea>
                        </div>
                    </div>

                    {{-- Vendor balance --}}
                    <div id="vendor-extra-fields" style="display:none;">
                        <div class="card shadow-sm mb-2 border-primary">
                            <div class="card-body p-2">
                                <div class="d-flex gap-2" style="gap:6px;">
                                    <input type="number" min="0" name="pay_amount" id="pay_amount" class="form-control form-control-sm" placeholder="Pay amount">
                                    <input type="text" id="vendor_balance" class="form-control form-control-sm font-weight-bold text-primary" placeholder="Balance" readonly>
                                    <button type="button" class="btn btn-sm btn-outline-primary text-nowrap" data-toggle="modal" data-target="#record-credit-modal" title="Record a payment/credit for this vendor without checking out a sale">
                                        <i class="fa fa-money mr-1"></i> Record Credit
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Add Phone by IMEI --}}
                    <div class="card shadow-sm mb-2">
                        <div class="card-header py-2 bg-white border-bottom">
                            <span class="font-weight-bold" style="font-size:1rem;"><i class="fa fa-barcode text-secondary mr-1"></i> Add Phone</span>
                        </div>
                        <div class="card-body p-2">
                            <div class="input-group mb-1">
                                <input type="text" id="imei_search" class="form-control" placeholder="Scan / enter IMEI…" autocomplete="off">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-warning font-weight-bold" onclick="scanImei()">
                                        <i class="fa fa-search"></i> Find
                                    </button>
                                </div>
                            </div>
                            <div class="mt-2">
                                <select id="manual_unit_select" class="form-control">
                                    <option value="">Select phone manually…</option>
                                    @foreach($units as $unit)
                                    <option value="{{ $unit->imei }}">
                                        {{ $unit->imei }} — {{ $unit->mobile->name ?? '-' }} ({{ $unit->storage }}, {{ $unit->pta_status }})
                                    </option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-secondary font-weight-bold mt-2" onclick="addSelectedUnit()">
                                    <i class="fa fa-plus"></i> Add
                                </button>
                            </div>
                        </div>
                    </div>

                    <script>
                        window.unitData = {};
                        @foreach($units as $unit)
                        window.unitData["{{ $unit->imei }}"] = {
                            id: {{ $unit->id }},
                            imei: "{{ $unit->imei }}",
                            mobile_name: "{{ addslashes($unit->mobile->name ?? '') }}",
                            storage: "{{ addslashes($unit->storage ?? '') }}",
                            pta_status: "{{ addslashes($unit->pta_status) }}",
                            price: {{ $unit->selling_price }}
                        };
                        @endforeach
                    </script>

                </div>{{-- /left --}}

                {{-- ===== RIGHT COLUMN (cart) ===== --}}
                <div class="col-lg-7">
                    <div class="pos-sticky" style="position:sticky; top:80px;">

                        <div class="card shadow-sm mb-2">
                            <div class="card-header py-2 bg-white border-bottom d-flex align-items-center justify-content-between">
                                <h6 class="mb-0 font-weight-bold">
                                    <i class="fa fa-shopping-cart text-secondary mr-1"></i> Cart
                                    <span id="cart-badge" class="badge badge-primary ml-1">0</span>
                                </h6>
                                <div class="d-flex align-items-center" style="gap:8px;">
                                    <button class="btn btn-sm font-weight-bold" id="held-orders-btn" onclick="openHeldOrdersModal()">
                                        <i class="fa fa-pause mr-1"></i> Held Orders
                                        <span id="held-badge" class="badge badge-dark ml-1" style="display:none;">0</span>
                                    </button>
                                    <span class="font-weight-bold text-success">Rs. <span id="cart-total">0.00</span></span>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0" id="sale-cart-table" style="font-size:0.97rem;">
                                        <thead style="background:#f8f9fa;">
                                            <tr>
                                                <th style="padding:6px 8px;">Phone</th>
                                                <th class="text-center" style="padding:6px 4px;">Price</th>
                                                <th class="text-center" style="padding:6px 4px;">Disc</th>
                                                <th class="text-center" style="padding:6px 4px;">Total</th>
                                                <th style="padding:6px 4px;"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr id="cart-empty-row">
                                                <td colspan="5" class="text-center text-muted py-3"><i class="fa fa-inbox mr-1"></i> Cart is empty</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            @if(auth()->user()->isAdmin())
                            <div class="card-footer bg-white text-right py-2">
                                <span class="text-muted small">Grand Total</span>
                                <span class="font-weight-bold ml-2" style="font-size:1.15em;color:#15803d;">Rs. <span id="cart-total-footer">0.00</span></span>
                            </div>
                            @endif
                        </div>

                        <button class="btn btn-warning btn-block font-weight-bold py-2 mb-2" id="sync-now-btn" onclick="syncOfflineSales()" style="display:none; font-size:1.05em;">
                            <i class="fa fa-refresh mr-1"></i> Sync Offline Sales
                            <span id="sync-count-badge" class="badge badge-dark ml-1"></span>
                        </button>

                        <div class="d-flex justify-content-end mb-2" id="desktop-actions" style="gap:8px;">
                            <button class="btn font-weight-bold py-2" id="hold-btn" onclick="holdOrder()" style="font-size:1.05em;">
                                <i class="fa fa-pause mr-1"></i> Hold Order
                            </button>
                            <button class="btn btn-primary font-weight-bold py-2" id="checkout-btn" onclick="checkoutSale()" style="font-size:1.05em; min-width:260px;">
                                <i class="fa fa-check-circle mr-1"></i> Checkout &amp; Print Invoice
                            </button>
                        </div>

                        <div class="card shadow-sm mb-2">
                            <div class="card-header py-1 bg-white border-bottom">
                                <span class="small font-weight-bold"><i class="fa fa-credit-card text-secondary mr-1"></i> Payment</span>
                            </div>
                            <div class="card-body p-2" id="payment-section">
                                <div class="d-flex mb-2" style="gap:8px;">
                                    <label class="payment-method-card active flex-fill text-center" id="label-counter" style="padding:8px 10px;">
                                        <input type="radio" name="payment_method" value="counter" checked>
                                        <div><i class="fa fa-money text-success mb-1"></i></div>
                                        <div class="font-weight-bold" style="font-size:0.75rem;">Counter (Cash)</div>
                                    </label>
                                    <label class="payment-method-card flex-fill text-center" id="label-bank" style="padding:8px 10px;">
                                        <input type="radio" name="payment_method" value="bank">
                                        <div><i class="fa fa-university text-primary mb-1"></i></div>
                                        <div class="font-weight-bold" style="font-size:0.75rem;">Bank Transfer</div>
                                    </label>
                                </div>
                                <div id="bank-select-wrap" style="display:none;" class="mb-1">
                                    <label class="small font-weight-bold text-muted mb-1">Select Bank</label>
                                    <select id="bank_id" class="form-control form-control-sm">
                                        <option value="">Select Bank</option>
                                        @foreach($banks as $bank)
                                        <option value="{{ $bank->id }}">{{ $bank->name }}{{ $bank->account_no ? ' — '.$bank->account_no : '' }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div id="bank-ref-wrap" style="display:none;" class="mb-1">
                                    <label class="small font-weight-bold text-muted mb-1">Reference / Slip #</label>
                                    <input type="text" id="bank_reference" class="form-control form-control-sm" placeholder="Optional">
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- ===== Daily Sales ===== --}}
            <div class="card shadow-sm mt-3">
                <div class="card-header bg-white d-flex align-items-start justify-content-between flex-wrap" style="gap:12px;">
                    <div>
                        <h5 class="mb-0 font-weight-bold"><i class="fa fa-list-alt text-secondary mr-1"></i> Daily Sales</h5>
                        <button type="button" class="btn btn-sm btn-outline-primary font-weight-bold mt-1" id="daily-sales-toggle" onclick="toggleDailySales()">
                            <i class="fa fa-chevron-down mr-1" id="daily-sales-chevron"></i> Show Daily Sales
                        </button>
                    </div>
                    <div class="text-right">
                        <div class="mb-1">
                            <span class="text-muted small">Selling:</span>
                            <strong class="ml-1">Rs. {{ number_format($totalSellingPrice, 2) }}</strong>
                            <span class="text-muted small ml-3">Paid:</span>
                            <strong class="ml-1">Rs. {{ number_format($totalPaidPrice, 2) }}</strong>
                        </div>
                        <div>
                            <span class="badge badge-secondary" style="font-size:.85rem;">Counter: Rs. {{ number_format($counterTotal, 2) }}</span>
                            <span class="badge badge-primary ml-1" style="font-size:.85rem;">Bank: Rs. {{ number_format($bankTotal, 2) }}</span>
                            @if(isset($bankBreakdown) && $bankBreakdown->count())
                            @foreach($bankBreakdown as $bk)
                            <span class="badge badge-light text-dark ml-1" style="font-size:.82rem;">{{ $bk['name'] }}: Rs. {{ number_format($bk['total'], 2) }}</span>
                            @endforeach
                            @endif
                        </div>
                    </div>
                </div>
                <div id="daily-sales-body">
                <div class="table-responsive">
                    <table id="loginTable" class="table table-striped table-bordered zero-configuration mb-0">
                        <thead>
                            <tr>
                                <th>Sale #</th>
                                <th>Date</th>
                                <th>Customer / Vendor</th>
                                <th>Total</th>
                                <th>Payments</th>
                                <th>Items</th>
                                <th>Comment</th>
                                <th>Receipt</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sales as $sale)
                            @php
                                $net      = (float)($sale->total_amount ?? 0);
                                $discount = (float)($sale->discount_amount ?? 0);
                            @endphp
                            <tr>
                                <td>{{ $sale->id }}</td>
                                <td>{{ \Carbon\Carbon::parse($sale->sale_date)->format('d M Y, H:i') }}</td>
                                <td>
                                    @if($sale->vendor)
                                        <span class="badge badge-info">Vendor</span> {{ $sale->vendor->name }}
                                    @elseif($sale->customer_name)
                                        <span class="badge badge-secondary">Customer</span> {{ $sale->customer_name }}
                                    @else
                                        <span class="text-muted">Walk-in</span>
                                    @endif
                                </td>
                                <td>
                                    <strong>Rs. {{ number_format($net, 2) }}</strong>
                                    @if($discount > 0)
                                    <div class="text-muted" style="font-size:.8em; line-height:1.3;">
                                        Before disc: Rs. {{ number_format($net + $discount, 2) }}<br>
                                        Disc: −Rs. {{ number_format($discount, 2) }}
                                    </div>
                                    @endif
                                </td>
                                <td>
                                    @if($sale->payments->isEmpty())
                                        <span class="badge badge-light text-dark">No Payment</span>
                                    @else
                                        @foreach($sale->payments as $p)
                                        <div>
                                            @if($p->method === 'bank')
                                                <span class="badge badge-primary">Bank</span> {{ $p->bank->name ?? 'Bank' }} — Rs. {{ number_format($p->amount, 2) }}
                                            @else
                                                <span class="badge badge-secondary">Counter</span> Rs. {{ number_format($p->amount, 2) }}
                                            @endif
                                        </div>
                                        @endforeach
                                    @endif
                                </td>
                                <td>
                                    @foreach($sale->items as $item)
                                    <div class="small">{{ $item->unit->mobile->name ?? '-' }} <span class="text-muted">(IMEI {{ $item->unit->imei ?? '-' }}, {{ number_format($item->price, 2) }})</span></div>
                                    @endforeach
                                </td>
                                <td class="small text-muted">{{ $sale->comment ?: '—' }}</td>
                                <td>
                                    <a class="btn btn-sm btn-outline-primary" target="_blank" href="{{ route('mobile.pos.invoice', $sale->id) }}">
                                        <i class="fa fa-print mr-1"></i> Receipt
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-danger mt-1" onclick="openReturnModal({{ $sale->id }})">
                                        <i class="fa fa-undo mr-1"></i> Return
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                </div>
            </div>

        </div>
    </div>
</div>

<div id="mobile-action-bar">
    <div class="mab-total">
        <div class="mab-label">Total (<span id="mobile-cart-count">0</span> items)</div>
        <div class="mab-amount">Rs. <span id="cart-total-mobile">0.00</span></div>
    </div>
    <button type="button" id="mobile-hold-btn" onclick="holdOrder()"><i class="fa fa-pause"></i></button>
    <button type="button" id="mobile-checkout-btn" onclick="checkoutSale()"><i class="fa fa-check-circle mr-1"></i> Checkout</button>
</div>

{{-- Record Credit Modal --}}
<div class="modal fade" id="record-credit-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold"><i class="fa fa-money mr-1"></i> Record Credit for <span id="record-credit-vendor-name"></span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Amount</label>
                    <input type="number" min="1" step="0.01" class="form-control" id="record-credit-amount" placeholder="Amount received/credited">
                </div>
                <div class="form-group">
                    <label class="form-label">Description (optional)</label>
                    <input type="text" class="form-control" id="record-credit-description" placeholder="e.g. Cash payment, easypaisa...">
                </div>
                <div id="record-credit-status" class="small text-muted"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-warning" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="record-credit-save-btn" onclick="recordVendorCredit()">
                    <i class="fa fa-check-square-o mr-1"></i> Save
                </button>
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

{{-- Held Orders Modal --}}
<div class="modal fade" id="held-orders-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header border-0" style="background:linear-gradient(135deg,#4a90c4 0%,#7ab8e0 100%);color:#fff;">
                <h5 class="modal-title font-weight-bold"><i class="fa fa-pause mr-2"></i> Held Orders</h5>
                <button type="button" class="close" data-dismiss="modal" style="color:#fff;opacity:1;"><span>&times;</span></button>
            </div>
            <div class="modal-body p-0" id="held-orders-body">
                <div class="text-center py-4 text-muted"><i class="fa fa-spinner fa-spin mr-1"></i> Loading…</div>
            </div>
        </div>
    </div>
</div>

<div id="loading-overlay" style="display:none; position:fixed; inset:0; z-index:99999; background:rgba(255,255,255,.6); backdrop-filter:blur(6px); justify-content:center; align-items:center;">
    <div class="card shadow-lg px-5 py-4 text-center">
        <i class="fa fa-spinner fa-spin fa-2x text-primary mb-2"></i>
        <div class="font-weight-bold" style="font-size:1.1em;">Processing Sale…</div>
    </div>
</div>

<script>
  // =====================================================================
  // OFFLINE / IndexedDB helpers — SEPARATE database from the accessory POS
  // so the two offline queues never collide if both pages are open at once.
  // =====================================================================
  const _IDB_NAME = 'amm_mobile_pos_offline';
  const _IDB_VER  = 1;
  let _idb = null;

  function idbOpen() {
    if (_idb) return Promise.resolve(_idb);
    return new Promise((res, rej) => {
      const req = indexedDB.open(_IDB_NAME, _IDB_VER);
      req.onupgradeneeded = e => {
        const db = e.target.result;
        if (!db.objectStoreNames.contains('sales')) {
          const s = db.createObjectStore('sales', { keyPath: 'id', autoIncrement: true });
          s.createIndex('status', 'status', { unique: false });
        }
        if (!db.objectStoreNames.contains('held_orders')) {
          db.createObjectStore('held_orders', { keyPath: 'id', autoIncrement: true });
        }
        if (!db.objectStoreNames.contains('credits')) {
          const c = db.createObjectStore('credits', { keyPath: 'id', autoIncrement: true });
          c.createIndex('status', 'status', { unique: false });
        }
      };
      req.onsuccess = e => { _idb = e.target.result; res(_idb); };
      req.onerror   = e => rej(e.target.error);
    });
  }

  function idbHeldAdd(data) {
    return idbOpen().then(db => new Promise((res, rej) => {
      const r = db.transaction('held_orders', 'readwrite').objectStore('held_orders').add({ ...data, held_at: new Date().toISOString() });
      r.onsuccess = () => res(r.result); r.onerror = () => rej(r.error);
    }));
  }
  function idbHeldGetAll() {
    return idbOpen().then(db => new Promise((res, rej) => {
      const r = db.transaction('held_orders', 'readonly').objectStore('held_orders').getAll();
      r.onsuccess = () => res(r.result); r.onerror = () => rej(r.error);
    }));
  }
  function idbHeldDelete(id) {
    return idbOpen().then(db => new Promise((res, rej) => {
      const r = db.transaction('held_orders', 'readwrite').objectStore('held_orders').delete(id);
      r.onsuccess = () => res(); r.onerror = () => rej(r.error);
    }));
  }

  function idbCreditAdd(payload) {
    return idbOpen().then(db => new Promise((res, rej) => {
      const r = db.transaction('credits', 'readwrite').objectStore('credits').add({ payload, queued_at: new Date().toISOString(), status: 'pending', error: null });
      r.onsuccess = () => res(r.result); r.onerror = () => rej(r.error);
    }));
  }
  function idbCreditGetByStatus(status) {
    return idbOpen().then(db => new Promise((res, rej) => {
      const idx = db.transaction('credits', 'readonly').objectStore('credits').index('status');
      const r = idx.getAll(status);
      r.onsuccess = () => res(r.result); r.onerror = () => rej(r.error);
    }));
  }
  function idbCreditSetStatus(id, status, error) {
    return idbOpen().then(db => new Promise((res, rej) => {
      const store = db.transaction('credits', 'readwrite').objectStore('credits');
      const get = store.get(id);
      get.onsuccess = () => {
        const rec = get.result; rec.status = status; rec.error = error || null;
        const put = store.put(rec);
        put.onsuccess = () => res(); put.onerror = () => rej(put.error);
      };
      get.onerror = () => rej(get.error);
    }));
  }

  function idbGetByStatus(status) {
    return idbOpen().then(db => new Promise((res, rej) => {
      const idx = db.transaction('sales', 'readonly').objectStore('sales').index('status');
      const r = idx.getAll(status);
      r.onsuccess = () => res(r.result); r.onerror = () => rej(r.error);
    }));
  }
  function idbAdd(payload) {
    return idbOpen().then(db => new Promise((res, rej) => {
      const r = db.transaction('sales', 'readwrite').objectStore('sales').add({ payload, queued_at: new Date().toISOString(), status: 'pending', error: null });
      r.onsuccess = () => res(r.result); r.onerror = () => rej(r.error);
    }));
  }
  function idbSetStatus(id, status, error) {
    return idbOpen().then(db => new Promise((res, rej) => {
      const store = db.transaction('sales', 'readwrite').objectStore('sales');
      const get = store.get(id);
      get.onsuccess = () => {
        const rec = get.result; rec.status = status; rec.error = error || null;
        const put = store.put(rec);
        put.onsuccess = () => res(); put.onerror = () => rej(put.error);
      };
      get.onerror = () => rej(get.error);
    }));
  }
  function idbDelete(id) {
    return idbOpen().then(db => new Promise((res, rej) => {
      const r = db.transaction('sales', 'readwrite').objectStore('sales').delete(id);
      r.onsuccess = () => res(); r.onerror = () => rej(r.error);
    }));
  }

  // =====================================================================
  // OFFLINE UI
  // =====================================================================
  async function updateOfflineUI() {
    const pending = await idbGetByStatus('pending');
    const failed  = await idbGetByStatus('failed');
    const isOff   = !navigator.onLine;

    document.getElementById('offline-banner').style.display = isOff ? 'block' : 'none';

    const badge = document.getElementById('offline-count');
    if (pending.length) {
      badge.textContent = pending.length + ' sale' + (pending.length > 1 ? 's' : '') + ' pending sync';
      badge.style.display = '';
    } else { badge.style.display = 'none'; }

    const syncBtn = document.getElementById('sync-now-btn');
    const syncBadge = document.getElementById('sync-count-badge');
    if (!isOff && pending.length) { syncBadge.textContent = pending.length; syncBtn.style.display = ''; }
    else { syncBtn.style.display = 'none'; }

    renderFailedPanel(failed);
  }

  function renderFailedPanel(failed) {
    const panel = document.getElementById('failed-sales-panel');
    if (!panel) return;
    if (!failed.length) { panel.style.display = 'none'; return; }
    let html = '<div class="alert alert-danger mb-2"><strong><i class="fa fa-exclamation-triangle"></i> Sync Conflicts — ' + failed.length + ' sale(s) could not be synced</strong><ul class="mb-2 mt-1">';
    failed.forEach(f => {
      const when = f.queued_at ? f.queued_at.substring(0, 16).replace('T', ' ') : '?';
      html += `<li class="small">${when} — <em>${f.error || 'Unknown error'}</em>
        <button class="btn btn-sm btn-outline-danger ml-2 py-0" onclick="discardFailedSale(${f.id})">Discard</button></li>`;
    });
    html += '</ul></div>';
    panel.innerHTML = html;
    panel.style.display = '';
  }

  async function discardFailedSale(id) {
    if (!confirm('Permanently discard this failed offline sale?')) return;
    await idbDelete(id);
    updateOfflineUI();
  }

  function showToast(msg, type) {
    const old = document.getElementById('pos-toast');
    if (old) old.remove();
    const el = document.createElement('div');
    el.id = 'pos-toast';
    const bg = type === 'success' ? '#28a745' : type === 'danger' ? '#dc3545' : '#fd7e14';
    el.style.cssText = `position:fixed;bottom:28px;left:50%;transform:translateX(-50%);background:${bg};color:#fff;padding:13px 32px;border-radius:8px;font-weight:bold;z-index:10002;font-size:1em;box-shadow:0 3px 12px rgba(0,0,0,.25);text-align:center;min-width:280px;`;
    el.textContent = msg;
    document.body.appendChild(el);
    setTimeout(() => el.remove(), 5000);
  }

  // =====================================================================
  // SYNC
  // =====================================================================
  let _syncSalesInProgress  = false;
  let _syncHeldInProgress   = false;
  let _syncCreditsInProgress = false;

  async function syncOfflineSales() {
    if (_syncSalesInProgress) return;
    _syncSalesInProgress = true;
    try {
      const pending = await idbGetByStatus('pending');
      if (!pending.length) return;

      document.getElementById('sync-banner').style.display  = 'block';
      document.getElementById('sync-now-btn').style.display = 'none';

      let csrf = '{{ csrf_token() }}';
      try {
        const tr = await fetch('/api/pos/token', { credentials: 'same-origin' });
        const td = await tr.json();
        if (td.csrf) csrf = td.csrf;
      } catch(e) {}

      let synced = 0, failed = 0;
      for (const sale of pending) {
        await idbSetStatus(sale.id, 'syncing', null);
        try {
          const res  = await fetch('{{ route('mobile.pos.checkout') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify(sale.payload)
          });
          const data = await res.json();
          if (data.success) { await idbSetStatus(sale.id, 'synced', null); synced++; }
          else { await idbSetStatus(sale.id, 'failed', data.message || 'Server error'); failed++; }
        } catch(e) {
          await idbSetStatus(sale.id, 'pending', null);
          break;
        }
      }

      document.getElementById('sync-banner').style.display = 'none';

      if (synced > 0) {
        showToast('✓ ' + synced + ' sale(s) synced successfully!' + (failed ? ' ' + failed + ' failed — see conflicts above.' : ''), 'success');
        setTimeout(() => window.location.reload(), 1800);
      } else if (failed > 0) {
        showToast(failed + ' sale(s) failed to sync. See conflicts panel.', 'danger');
        updateOfflineUI();
      } else { updateOfflineUI(); }
    } finally { _syncSalesInProgress = false; }
  }

  async function syncHeldOrders() {
    if (_syncHeldInProgress) return;
    _syncHeldInProgress = true;
    try {
      const local = await idbHeldGetAll();
      if (!local.length) return;

      let csrf = '{{ csrf_token() }}';
      try {
        const tr = await fetch('/api/pos/token', { credentials: 'same-origin' });
        const td = await tr.json();
        if (td.csrf) csrf = td.csrf;
      } catch(e) {}

      let synced = 0;
      for (const order of local) {
        await idbHeldDelete(order.id);
        try {
          const res = await fetch('{{ route('mobile.pos.hold') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({
              cart_items: order.cart_items, vendor_id: order.vendor_id || null,
              customer_name: order.customer_name || null, customer_mobile: order.customer_mobile || null,
              comment: order.comment || null,
            }),
          });
          const data = await res.json();
          if (data.success) { synced++; }
          else { await idbHeldAdd({ ...order }); }
        } catch(e) { await idbHeldAdd({ ...order }); break; }
      }

      if (synced > 0) { await refreshHeldBadge(); showToast('✓ ' + synced + ' held order(s) synced to server.', 'success'); }
    } finally { _syncHeldInProgress = false; }
  }

  async function syncOfflineCredits() {
    if (_syncCreditsInProgress) return;
    _syncCreditsInProgress = true;
    try {
      const pending = await idbCreditGetByStatus('pending');
      if (!pending.length) return;

      let csrf = '{{ csrf_token() }}';
      try {
        const tr = await fetch('/api/pos/token', { credentials: 'same-origin' });
        const td = await tr.json();
        if (td.csrf) csrf = td.csrf;
      } catch(e) {}

      let synced = 0, failed = 0;
      for (const credit of pending) {
        await idbCreditSetStatus(credit.id, 'syncing', null);
        try {
          const res  = await fetch('{{ route('mobile.creditAmount') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: JSON.stringify(credit.payload)
          });
          const data = await res.json();
          if (data.success) { await idbCreditSetStatus(credit.id, 'synced', null); synced++; }
          else { await idbCreditSetStatus(credit.id, 'failed', data.message || 'Server error'); failed++; }
        } catch(e) { await idbCreditSetStatus(credit.id, 'pending', null); break; }
      }

      if (synced > 0) {
        showToast('✓ ' + synced + ' offline credit(s) synced!' + (failed ? ' ' + failed + ' failed.' : ''), 'success');
        if (document.getElementById('vendor_id').value) $('#vendor_id').trigger('change');
      } else if (failed > 0) { showToast(failed + ' offline credit(s) failed to sync.', 'danger'); }
    } finally { _syncCreditsInProgress = false; }
  }

  // =====================================================================
  // FORM HELPERS
  // =====================================================================
  function resetSaleForm() {
    try { $('#vendor_id').val(null).trigger('change'); } catch(e) {}
    ['customer_name', 'customer_mobile', 'sale_comment', 'pay_amount', 'vendor_balance', 'bank_reference'].forEach(id => {
      const el = document.getElementById(id);
      if (el) el.value = '';
    });
    document.querySelectorAll('.payment-method-card').forEach(c => c.classList.remove('active'));
    const counterCard = document.getElementById('label-counter');
    if (counterCard) counterCard.classList.add('active');
    const counterRadio = document.querySelector('input[name="payment_method"][value="counter"]');
    if (counterRadio) counterRadio.checked = true;
    document.getElementById('bank-select-wrap').style.display = 'none';
    document.getElementById('bank-ref-wrap').style.display   = 'none';
    const bankSel = document.getElementById('bank_id');
    if (bankSel) bankSel.value = '';
  }

  function removeUnitFromDropdown(imei) {
    const select = document.getElementById('manual_unit_select');
    if (!select) return;
    const opt = Array.from(select.options).find(o => o.value === imei);
    if (opt) opt.remove();
  }
  function restoreUnitToDropdown(imei) {
    const select = document.getElementById('manual_unit_select');
    const u = window.unitData[imei];
    if (!select || !u) return;
    if (Array.from(select.options).some(o => o.value === imei)) return;
    const opt = document.createElement('option');
    opt.value = imei;
    opt.text = `${u.imei} — ${u.mobile_name} (${u.storage}, ${u.pta_status})`;
    select.appendChild(opt);
  }

  function buildPayload() {
    const vendor_id       = document.getElementById('vendor_id').value || null;
    const customer_name   = document.getElementById('customer_name').value || null;
    const customer_mobile = document.getElementById('customer_mobile')?.value || '';
    const comment         = (document.getElementById('sale_comment').value || '').trim() || null;
    const netTotal        = cart.reduce((t, it) => {
      const p = Number(it.price) || 0;
      const d = Math.min(Math.max(Number(it.discount) || 0, 0), p);
      return t + Math.max(p - d, 0);
    }, 0);
    const pay_amount_el  = document.getElementById('pay_amount');
    const raw_pay_amount = pay_amount_el ? parseFloat(pay_amount_el.value || '0') : 0;
    const methodInput    = document.querySelector('input[name="payment_method"]:checked');
    const method         = methodInput ? methodInput.value : 'counter';
    const bank_id        = document.getElementById('bank_id')?.value || '';
    const reference_no   = document.getElementById('bank_reference')?.value.trim() || '';
    const payments = [];
    if (vendor_id) {
      if (raw_pay_amount > 0) {
        payments.push({ method: method === 'bank' ? 'bank' : 'counter', bank_id: method === 'bank' ? Number(bank_id) : null, amount: Number(raw_pay_amount), reference_no: method === 'bank' ? (reference_no || null) : null });
      }
    } else {
      payments.push({ method: method === 'bank' ? 'bank' : 'counter', bank_id: method === 'bank' ? Number(bank_id) : null, amount: Number(netTotal), reference_no: method === 'bank' ? (reference_no || null) : null });
    }
    return {
      client_ref: (window.crypto && crypto.randomUUID) ? crypto.randomUUID() : ('cr_' + Date.now() + '_' + Math.random().toString(36).slice(2)),
      vendor_id, customer_name, customer_mobile, comment,
      pay_amount:     vendor_id ? Number(raw_pay_amount) : Number(netTotal),
      payment_method: method,
      bank_id:        method === 'bank' ? (bank_id ? Number(bank_id) : null) : null,
      reference_no:   method === 'bank' ? (reference_no || null) : null,
      payments,
      items: cart.map(i => ({ mobile_unit_id: i.id, price: Number(i.price), discount: Number(i.discount || 0) })),
      netTotal
    };
  }

  function validatePayload(p) {
    if (p.payment_method === 'bank' && !p.bank_id && (!p.vendor_id || p.pay_amount > 0)) {
      alert('Please select a bank for the bank payment.');
      return false;
    }
    return true;
  }

  async function saveOffline(payload) {
    await idbAdd(payload);
    cart = [];
    renderCart();
    resetSaleForm();
    await updateOfflineUI();
    showToast('Sale saved offline! Will sync automatically when internet returns.', 'warning');
  }

  // =====================================================================
  // INIT
  // =====================================================================
  $(document).ready(function () {
    $('#manual_unit_select').select2({ placeholder: "Select a phone", allowClear: true, width: '100%' });
    $('#vendor_id').select2({ placeholder: "Select a vendor", allowClear: true, width: '100%' });

    document.querySelectorAll('.payment-method-card').forEach(card => {
      card.addEventListener('click', function () {
        document.querySelectorAll('.payment-method-card').forEach(c => c.classList.remove('active'));
        this.classList.add('active');
        const isBank = this.querySelector('input[type="radio"]').value === 'bank';
        document.getElementById('bank-select-wrap').style.display = isBank ? '' : 'none';
        document.getElementById('bank-ref-wrap').style.display   = isBank ? '' : 'none';
      });
    });

    $('#vendor_id').on('change', function () {
      const vendorId    = $(this).val();
      const extraFields  = document.getElementById('vendor-extra-fields');
      const balanceInput = document.getElementById('vendor_balance');
      const mobileRow    = document.getElementById('customer_mobile_row');
      if (vendorId) {
        extraFields.style.display = '';
        mobileRow.style.display   = 'none';
        document.getElementById('customer_mobile').value = '';
        balanceInput.value = 'Loading…';
        fetch(`/mobile/vendor-balance/${vendorId}`)
          .then(r => r.json())
          .then(d => { balanceInput.value = d.balance; })
          .catch(() => { balanceInput.value = navigator.onLine ? 'Error' : 'Offline'; });
      } else {
        extraFields.style.display = 'none';
        mobileRow.style.display   = '';
        balanceInput.value = '';
      }
    });
    $('#vendor_id').trigger('change');

    idbOpen().then(async () => {
      const stuck = await idbGetByStatus('syncing');
      for (const s of stuck) await idbSetStatus(s.id, 'pending', null);

      const stuckCredits = await idbCreditGetByStatus('syncing');
      for (const c of stuckCredits) await idbCreditSetStatus(c.id, 'pending', null);
      if (navigator.onLine) syncOfflineCredits();

      updateOfflineUI();
    });

    $('#record-credit-modal').on('show.bs.modal', function () {
      const sel = document.getElementById('vendor_id');
      const name = sel.options[sel.selectedIndex] ? sel.options[sel.selectedIndex].text : '';
      document.getElementById('record-credit-vendor-name').textContent = name;
    });

    refreshHeldBadge();

    if (navigator.storage && navigator.storage.persist) { navigator.storage.persist(); }
    if ('serviceWorker' in navigator) { navigator.serviceWorker.register('/sw.js').catch(() => {}); }
  });

  document.getElementById('imei_search').addEventListener('keydown', function (e) {
    if (e.key === 'Enter') { e.preventDefault(); scanImei(); }
  });

  document.getElementById('customer_mobile').addEventListener('input', function () {
    this.value = this.value.replace(/\D/g, '');
    if (!this.value.startsWith('923')) this.value = '923' + this.value.replace(/^923*/, '');
    if (this.value.length > 12) this.value = this.value.slice(0, 12);
  });

  window.addEventListener('online',  () => { updateOfflineUI(); syncOfflineSales(); syncHeldOrders(); syncOfflineCredits(); });
  window.addEventListener('offline', () => { updateOfflineUI(); });

  // =====================================================================
  // CART — every line is exactly one serialized unit (no quantity)
  // =====================================================================
  let cart = [];

  function addUnitToCart(imei) {
    const unit = window.unitData[imei];
    if (!unit) return alert('Phone not found in available stock!');
    if (cart.some(i => i.imei === imei)) return alert('That phone is already in the cart.');
    cart.push({ id: unit.id, imei: unit.imei, mobile_name: unit.mobile_name, storage: unit.storage, pta_status: unit.pta_status, price: Number(unit.price), discount: 0 });
    removeUnitFromDropdown(imei);
    renderCart();
  }

  function scanImei() {
    const code = document.getElementById('imei_search').value.trim();
    if (!code) return alert('Enter or scan an IMEI!');
    addUnitToCart(code);
    document.getElementById('imei_search').value = '';
  }

  function addSelectedUnit() {
    const code = document.getElementById('manual_unit_select').value;
    if (!code) return alert('Select a phone to add!');
    addUnitToCart(code);
    $('#manual_unit_select').val('').trigger('change');
  }

  function renderCart() {
    const tbody = document.querySelector('#sale-cart-table tbody');
    tbody.innerHTML = '';
    if (!cart.length) {
      tbody.innerHTML = '<tr id="cart-empty-row"><td colspan="5" class="text-center text-muted py-3"><i class="fa fa-inbox mr-1"></i> Cart is empty</td></tr>';
      document.getElementById('cart-total').textContent = '0.00';
      document.getElementById('cart-badge').textContent = '0';
      const footer = document.getElementById('cart-total-footer');
      if (footer) footer.textContent = '0.00';
      const mTotal = document.getElementById('cart-total-mobile');
      if (mTotal) mTotal.textContent = '0.00';
      const mCount = document.getElementById('mobile-cart-count');
      if (mCount) mCount.textContent = '0';
      return;
    }
    cart.forEach((item, i) => {
      const unitPrice = Number(item.price) || 0;
      const unitDisc  = Math.min(Math.max(Number(item.discount) || 0, 0), unitPrice);
      item.discount   = unitDisc;
      const lineTotal = Math.max(unitPrice - unitDisc, 0);
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td class="font-weight-bold" style="padding:5px 8px;font-size:1.02rem;color:#000;">${item.mobile_name}<div class="text-muted font-weight-normal" style="font-size:.78em;">IMEI: ${item.imei} · ${item.storage || ''} · ${item.pta_status}</div></td>
        <td class="text-center" style="padding:5px 4px;"><input class="cart-input" type="number" value="${parseFloat(unitPrice.toFixed(2))}" min="0" step="0.01" onchange="updatePrice(${i}, this.value)"></td>
        <td class="text-center" style="padding:5px 4px;"><input class="cart-input" type="number" value="${parseFloat(unitDisc.toFixed(2))}" min="0" step="0.01" onchange="updateDiscount(${i}, this.value)"></td>
        <td class="text-center font-weight-bold" style="padding:5px 4px;font-size:1.1rem;">${lineTotal.toFixed(2)}</td>
        <td class="text-center" style="padding:5px 4px;"><button type="button" class="cart-item-remove" onclick="removeCartItem(${i})"><i class="fa fa-trash"></i></button></td>
      `;
      tbody.appendChild(tr);
    });
    const grandTotal = cart.reduce((t, it) => {
      const p = Number(it.price) || 0;
      const d = Math.min(Math.max(Number(it.discount) || 0, 0), p);
      return t + Math.max(p - d, 0);
    }, 0);
    document.getElementById('cart-total').textContent = grandTotal.toFixed(2);
    document.getElementById('cart-badge').textContent  = cart.length;
    const footer = document.getElementById('cart-total-footer');
    if (footer) footer.textContent = grandTotal.toFixed(2);
    const mTotal = document.getElementById('cart-total-mobile');
    if (mTotal) mTotal.textContent = grandTotal.toFixed(2);
    const mCount = document.getElementById('mobile-cart-count');
    if (mCount) mCount.textContent = cart.length;
  }

  function toggleDailySales() {
    const body = document.getElementById('daily-sales-body');
    const btn  = document.getElementById('daily-sales-toggle');
    const open = body.classList.toggle('mobile-open');
    btn.innerHTML = open ? '<i class="fa fa-chevron-up mr-1"></i> Hide Daily Sales' : '<i class="fa fa-chevron-down mr-1"></i> Show Daily Sales';
  }

  function updatePrice(i, v)    { const p = Number(v); if (!isNaN(p) && p >= 0) { cart[i].price = p; if ((Number(cart[i].discount)||0) > p) cart[i].discount = p; renderCart(); } }
  function updateDiscount(i, v) { const d = Number(v); if (!isNaN(d) && d >= 0) { cart[i].discount = d; renderCart(); } }
  function removeCartItem(i)    { restoreUnitToDropdown(cart[i].imei); cart.splice(i, 1); renderCart(); }

  // =====================================================================
  // HELD ORDERS
  // =====================================================================
  let _heldOrdersMap = {};

  async function holdOrder() {
    if (!cart.length) return alert('Cart is empty — nothing to hold!');

    const vendor_id       = document.getElementById('vendor_id').value || null;
    const customer_name   = (document.getElementById('customer_name').value || '').trim() || null;
    const customer_mobile = document.getElementById('customer_mobile')?.value || null;
    const comment         = (document.getElementById('sale_comment').value || '').trim() || null;
    const cart_items      = cart.map(i => ({ mobile_unit_id: i.id, imei: i.imei, mobile_name: i.mobile_name, price: Number(i.price), discount: Number(i.discount || 0) }));

    const btn  = document.getElementById('hold-btn');
    const mBtn = document.getElementById('mobile-hold-btn');
    btn.disabled = true;
    if (mBtn) { mBtn.disabled = true; mBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i>'; }
    btn.innerHTML = '<i class="fa fa-spinner fa-spin mr-1"></i> Holding…';

    const doHoldOffline = async () => {
      await idbHeldAdd({ cart_items, vendor_id, customer_name, customer_mobile, comment });
      cart = []; renderCart(); resetSaleForm();
      await refreshHeldBadge();
      showToast('Order held offline! Will sync when internet returns.', 'warning');
    };

    try {
      if (!navigator.onLine) {
        await doHoldOffline();
      } else {
        const res  = await fetch('{{ route('mobile.pos.hold') }}', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
          body: JSON.stringify({ cart_items, vendor_id, customer_name, customer_mobile, comment }),
        });
        const data = await res.json();
        if (data.success) {
          cart = []; renderCart(); resetSaleForm();
          await refreshHeldBadge();
          showToast('Order held! Resume it from Held Orders.', 'success');
        } else {
          alert('Failed to hold order: ' + (data.message || 'Unknown error'));
        }
      }
    } catch (e) {
      try { await doHoldOffline(); showToast('Connection lost! Order held offline.', 'warning'); }
      catch(e2) { alert('Failed to hold order: ' + e2.message); }
    } finally {
      btn.disabled = false;
      btn.innerHTML = '<i class="fa fa-pause mr-1"></i> Hold Order';
      if (mBtn) { mBtn.disabled = false; mBtn.innerHTML = '<i class="fa fa-pause"></i>'; }
    }
  }

  async function recordVendorCredit() {
    const vendor_id = document.getElementById('vendor_id').value;
    if (!vendor_id) return;

    const amountInput = document.getElementById('record-credit-amount');
    const amount       = Number(amountInput.value);
    const description  = (document.getElementById('record-credit-description').value || '').trim() || null;
    const statusEl      = document.getElementById('record-credit-status');

    if (!amount || amount <= 0) {
      statusEl.className = 'small text-danger';
      statusEl.textContent = 'Enter a valid amount.';
      return;
    }

    const payload = { vendor_id, amount, description };
    const btn = document.getElementById('record-credit-save-btn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin mr-1"></i> Saving…';
    statusEl.className = 'small text-muted';
    statusEl.textContent = '';

    const doQueueOffline = async () => {
      await idbCreditAdd(payload);
      statusEl.className = 'small text-warning';
      showToast('Credit queued offline — will sync when internet returns.', 'warning');
      closeRecordCreditModal();
    };

    try {
      if (!navigator.onLine) {
        await doQueueOffline();
      } else {
        const res = await fetch('{{ route('mobile.creditAmount') }}', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
          body: JSON.stringify(payload),
        });
        const data = await res.json();
        if (data.success) {
          showToast('Credit recorded for vendor.', 'success');
          $('#vendor_id').trigger('change');
          closeRecordCreditModal();
        } else {
          statusEl.className = 'small text-danger';
          statusEl.textContent = data.message || 'Failed to record credit.';
        }
      }
    } catch (e) {
      try { await doQueueOffline(); }
      catch (e2) {
        statusEl.className = 'small text-danger';
        statusEl.textContent = 'Failed to record credit: ' + e2.message;
      }
    } finally {
      btn.disabled = false;
      btn.innerHTML = '<i class="fa fa-check-square-o mr-1"></i> Save';
    }
  }

  function closeRecordCreditModal() {
    $('#record-credit-modal').modal('hide');
    document.getElementById('record-credit-amount').value = '';
    document.getElementById('record-credit-description').value = '';
    document.getElementById('record-credit-status').textContent = '';
  }

  async function refreshHeldBadge() {
    let count = 0;
    try { count += (await idbHeldGetAll()).length; } catch(e) {}
    if (navigator.onLine) {
      try {
        const res  = await fetch('{{ route('mobile.pos.held') }}', { credentials: 'same-origin' });
        const data = await res.json();
        count += (data.orders || []).length;
      } catch(e) {}
    }
    const badge = document.getElementById('held-badge');
    badge.textContent   = count;
    badge.style.display = count > 0 ? '' : 'none';
  }

  async function openHeldOrdersModal() {
    $('#held-orders-modal').modal('show');
    const body = document.getElementById('held-orders-body');
    body.innerHTML = '<div class="text-center py-4 text-muted"><i class="fa fa-spinner fa-spin mr-1"></i> Loading…</div>';

    try {
      let orders = [];

      const localOrders = await idbHeldGetAll();
      localOrders.forEach(o => {
        const items = o.cart_items || [];
        const total = items.reduce((s, i) => s + Math.max(0, (Number(i.price) - Number(i.discount || 0))), 0);
        const heldAt = o.held_at ? new Date(o.held_at).toLocaleString('en-GB', { day:'2-digit', month:'short', year:'numeric', hour:'2-digit', minute:'2-digit' }) : '—';
        orders.push({
          _key: 'local:' + o.id, _src: 'local', is_offline: true,
          held_at: heldAt, item_count: items.length, total: total.toFixed(2),
          customer: o.customer_name || 'Walk-in', comment: o.comment,
          cart_items: o.cart_items, vendor_id: o.vendor_id,
          customer_name: o.customer_name, customer_mobile: o.customer_mobile,
        });
      });

      if (navigator.onLine) {
        const res  = await fetch('{{ route('mobile.pos.held') }}', { credentials: 'same-origin' });
        const text = await res.text();
        try {
          const data = JSON.parse(text);
          if (res.ok && data.success) {
            (data.orders || []).forEach(o => { orders.push({ ...o, _key: 'server:' + o.id, _src: 'server', is_offline: false }); });
          } else if (!orders.length) {
            body.innerHTML = '<div class="text-center py-4 text-danger">Error ' + res.status + ': ' + (JSON.parse(text).message || 'Unknown') + '</div>';
            return;
          }
        } catch(e) {
          if (!orders.length) {
            body.innerHTML = '<div class="text-center py-4 text-danger"><strong>Server error:</strong><pre class="text-left mt-2 small" style="max-height:200px;overflow:auto;">' + text.substring(0, 1000) + '</pre></div>';
            return;
          }
        }
      }

      renderHeldOrders(orders);
    } catch (e) {
      body.innerHTML = '<div class="text-center py-4 text-danger">Error: ' + e.message + '</div>';
    }
  }

  function renderHeldOrders(orders) {
    const body = document.getElementById('held-orders-body');
    _heldOrdersMap = {};
    orders.forEach(o => { _heldOrdersMap[o._key] = o; });

    if (!orders.length) {
      body.innerHTML = '<div class="text-center py-5 text-muted"><i class="fa fa-inbox fa-2x mb-2 d-block"></i> No held orders.</div>';
      return;
    }

    let html = '<div class="table-responsive"><table class="table table-hover mb-0" style="font-size:.95rem;">';
    html += '<thead style="background:#f1f3f5;"><tr><th>Time Held</th><th>Customer</th><th class="text-center">Items</th><th>Total</th><th>Comment</th><th></th></tr></thead><tbody>';
    orders.forEach(o => {
      const offlinePill = o.is_offline ? ' <span class="badge badge-warning" style="font-size:.68rem;vertical-align:middle;">Offline</span>' : '';
      html += `<tr>
        <td class="small align-middle">${o.held_at}${offlinePill}</td>
        <td class="align-middle font-weight-bold">${o.customer}</td>
        <td class="text-center align-middle">${o.item_count}</td>
        <td class="align-middle font-weight-bold text-success">Rs. ${o.total}</td>
        <td class="small text-muted align-middle">${o.comment || '—'}</td>
        <td class="align-middle text-nowrap">
          <button class="btn btn-sm btn-success font-weight-bold mr-1" onclick="resumeOrder('${o._key}')"><i class="fa fa-play mr-1"></i>Resume</button>
          <button class="btn btn-sm btn-outline-danger" onclick="deleteHeldOrder('${o._key}', false)"><i class="fa fa-trash"></i></button>
        </td>
      </tr>`;
    });
    html += '</tbody></table></div>';
    body.innerHTML = html;
  }

  async function resumeOrder(key) {
    const order = _heldOrdersMap[key];
    if (!order) return;
    if (cart.length && !confirm('This will replace your current cart with the held order. Continue?')) return;

    cart = order.cart_items.map(i => {
      const known = window.unitData[i.imei];
      return {
        id: i.mobile_unit_id, imei: i.imei, mobile_name: i.mobile_name,
        storage: known ? known.storage : '', pta_status: known ? known.pta_status : '',
        price: Number(i.price), discount: Number(i.discount || 0),
      };
    });
    cart.forEach(i => removeUnitFromDropdown(i.imei));
    renderCart();

    if (order.vendor_id) {
      try { $('#vendor_id').val(order.vendor_id).trigger('change'); } catch(e) {}
    } else {
      try { $('#vendor_id').val(null).trigger('change'); } catch(e) {}
      if (order.customer_name)   document.getElementById('customer_name').value   = order.customer_name;
      if (order.customer_mobile) document.getElementById('customer_mobile').value = order.customer_mobile;
    }
    if (order.comment) document.getElementById('sale_comment').value = order.comment;

    await deleteHeldOrder(key, true);
    $('#held-orders-modal').modal('hide');
    showToast('Order resumed!', 'success');
  }

  async function deleteHeldOrder(key, silent) {
    if (!silent && !confirm('Delete this held order?')) return;
    const [src, rawId] = key.split(':');
    if (src === 'local') {
      try { await idbHeldDelete(Number(rawId)); } catch(e) {}
    } else {
      try {
        await fetch('{{ url('/mobile/pos/hold') }}/' + rawId, {
          method: 'DELETE', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, credentials: 'same-origin',
        });
      } catch(e) {}
    }
    await refreshHeldBadge();
    if (!silent) await openHeldOrdersModal();
  }

  // =====================================================================
  // RETURNS
  // =====================================================================
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
        showToast('Return processed — Rs. ' + data.refund_amount + ' refunded.', 'success');
        setTimeout(() => window.location.reload(), 1200);
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

  // =====================================================================
  // CHECKOUT
  // =====================================================================
  async function checkoutSale() {
    if (!cart.length) return alert('Cart is empty!');

    const payload = buildPayload();
    if (!validatePayload(payload)) return;

    if (!navigator.onLine) {
      try { await saveOffline(payload); } catch(e) { alert('Failed to save offline: ' + e.message); }
      return;
    }

    const btn = document.getElementById('checkout-btn');
    document.getElementById('loading-overlay').style.display = 'flex';
    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin mr-1"></i> Processing…';

    try {
      const res = await fetch('{{ route('mobile.pos.checkout') }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify(payload)
      });
      const ct = res.headers.get('content-type') || '';
      let data;
      if (ct.includes('application/json')) { data = await res.json(); }
      else { const text = await res.text(); throw new Error('Server did not return JSON: ' + text.substring(0, 400)); }

      if (data.success) {
        window.open('{{ url('/mobile/pos/invoice') }}/' + data.invoice_number, '_blank');
        setTimeout(() => window.location.reload(), 700);
      } else {
        alert('Error: ' + (data.message || 'Sale failed.'));
        btn.disabled = false;
        btn.innerHTML = '<i class="fa fa-check-circle mr-1"></i> Checkout & Print Invoice';
        document.getElementById('loading-overlay').style.display = 'none';
      }
    } catch (err) {
      document.getElementById('loading-overlay').style.display = 'none';
      btn.disabled = false;
      btn.innerHTML = '<i class="fa fa-check-circle mr-1"></i> Checkout & Print Invoice';
      if (!navigator.onLine || err.message === 'Failed to fetch' || err.name === 'TypeError') {
        try {
          await saveOffline(payload);
          showToast('Connection lost! Sale saved offline — will sync when internet returns.', 'danger');
        } catch(e2) { alert('Checkout failed and offline save also failed: ' + e2.message); }
      } else {
        alert('Unexpected error: ' + err.message);
      }
    }
  }
</script>
@endsection
