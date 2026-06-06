@extends('user_navbar')
@section('content')

<style>
    /* ── POS light-blue gradient scheme ── */
    :root {
        --lb-dark:   #4a90c4;
        --lb-mid:    #7ab8e0;
        --lb-light:  #b8d9f2;
        --lb-xlight: #e8f4fc;
        --lb-text:   #fff;
    }

    /* ── Card headers ── */
    .card > .card-header {
        background: linear-gradient(135deg, var(--lb-dark) 0%, var(--lb-mid) 55%, var(--lb-light) 100%) !important;
        color: var(--lb-text) !important;
        border-bottom: none !important;
    }
    .card > .card-header span,
    .card > .card-header h6,
    .card > .card-header h5,
    .card > .card-header i { color: var(--lb-text) !important; }

    /* ── Payment method cards ── */
    .payment-method-card {
        border: 2px solid var(--lb-light);
        border-radius: 10px;
        padding: 12px 18px;
        cursor: pointer;
        transition: all .2s;
        background: #fff;
        user-select: none;
    }
    .payment-method-card:hover  {
        border-color: var(--lb-mid);
        background: var(--lb-xlight);
    }
    .payment-method-card.active {
        border-color: var(--lb-dark);
        background: linear-gradient(135deg, var(--lb-xlight), #fff);
        box-shadow: inset 0 1px 4px rgba(74,144,196,.15);
    }
    .payment-method-card input[type="radio"] { display: none; }

    /* ── Checkout button — gradient green ── */
    #checkout-btn {
        background: linear-gradient(135deg, #1a7a3a 0%, #2ecc6a 100%) !important;
        border: none !important;
        color: #fff !important;
        text-shadow: 0 1px 2px rgba(0,0,0,.2);
    }
    #checkout-btn:hover { filter: brightness(1.08); }

    /* ── Scan button ── */
    .btn-warning {
        background: linear-gradient(135deg, var(--lb-dark) 0%, var(--lb-light) 100%) !important;
        border: none !important;
        color: #fff !important;
    }
    .btn-warning:hover { filter: brightness(1.08); }

    /* ── Add / secondary buttons ── */
    .btn-secondary {
        background: linear-gradient(135deg, var(--lb-dark) 0%, var(--lb-mid) 100%) !important;
        border: none !important;
        color: #fff !important;
    }
    .btn-secondary:hover { filter: brightness(1.08); }

    /* ── Sync button ── */
    #sync-now-btn {
        background: linear-gradient(135deg, var(--lb-dark), var(--lb-light)) !important;
        border: none !important;
        color: #fff !important;
    }

    /* ── Cart badge ── */
    #cart-badge {
        background: linear-gradient(135deg, var(--lb-dark), var(--lb-mid)) !important;
        color: #fff !important;
        font-size: .75em;
        vertical-align: middle;
    }

    /* ── Cart inputs ── */
    .cart-item-remove {
        background: none; border: none; color: #dc3545;
        font-size: 1.1em; cursor: pointer;
        padding: 2px 6px; border-radius: 4px; transition: background .15s;
    }
    .cart-item-remove:hover { background: #fde8ea; }

    .cart-input {
        width: 72px; border: 1px solid var(--lb-light);
        border-radius: 6px; padding: 4px 6px;
        text-align: center; font-size: .92em;
    }
    .cart-input:focus { outline: none; border-color: var(--lb-dark); }

    /* ── Page title ── */
    .fa-shopping-cart.text-primary { color: var(--lb-dark) !important; }

    /* ── Card body / footer light silver ── */
    .card { background: #e8eaed !important; border-radius: 14px !important; overflow: hidden; }
    .card .card-body,
    .card .card-footer { background: #e8eaed !important; }
    .card .table,
    .card .table thead { background: #e8eaed !important; }
    .card .table thead tr { background: #d8dadd !important; }

    @media (max-width: 991px) {
        .pos-sticky { position: static !important; }
    }
</style>

<div class="app-content content">
    <div class="content-overlay"></div>
    <div class="content-wrapper">
        <div class="content-header row"></div>
        <div class="content-body">

            {{-- Offline Banner --}}
            <div id="offline-banner" style="display:none;position:fixed;top:56px;left:0;right:0;z-index:9990;background:#fd7e14;color:#fff;text-align:center;padding:7px 16px;font-weight:bold;font-size:.9em;box-shadow:0 2px 8px rgba(0,0,0,.15);">
                <i class="fa fa-exclamation-triangle mr-1"></i> You are offline — sales are saved locally and will sync automatically when internet returns
                <span id="offline-count" class="badge badge-light text-dark ml-2" style="display:none;"></span>
            </div>

            {{-- Sync Banner --}}
            <div id="sync-banner" style="display:none;position:fixed;top:56px;left:0;right:0;z-index:9991;background:#0d6efd;color:#fff;text-align:center;padding:7px 16px;font-weight:bold;font-size:.9em;">
                <i class="fa fa-refresh fa-spin mr-1"></i> Syncing offline sales to server…
            </div>

            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
            @endif
            @if(session('danger'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('danger') }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
            @endif

            {{-- Failed/Conflict sales panel --}}
            <div id="failed-sales-panel" style="display:none;" class="mb-2"></div>

            {{-- Page Title --}}
            <div class="d-flex align-items-center justify-content-between mb-2">
                <h3 class="mb-0 font-weight-bold">
                    <i class="fa fa-shopping-cart text-primary mr-2"></i> Point of Sale
                </h3>
            </div>

            <div class="row">
                {{-- ===== LEFT COLUMN (compact) ===== --}}
                <div class="col-lg-5">

                    {{-- Customer / Vendor --}}
                    <div class="card shadow-sm mb-2">
                        <div class="card-header py-2 bg-white border-bottom">
                            <span class="font-weight-bold" style="font-size:1rem;"><i class="fa fa-user text-secondary mr-1"></i> Customer / Vendor</span>
                        </div>
                        <div class="card-body p-2">
                            <form method="POST" action="{{ route('sales.store') }}" id="sale-meta-form">
                                @csrf
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
                            </form>
                        </div>
                    </div>

                    {{-- Vendor balance (shown when vendor selected) --}}
                    <div id="vendor-extra-fields" style="display:none;">
                        <div class="card shadow-sm mb-2 border-primary">
                            <div class="card-body p-2">
                                <div class="d-flex gap-2" style="gap:6px;">
                                    <input type="number" min="0" name="pay_amount" id="pay_amount" class="form-control form-control-sm" placeholder="Pay amount">
                                    <input type="text" id="vendor_balance" class="form-control form-control-sm font-weight-bold text-primary" placeholder="Balance" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Scan / Add --}}
                    <div class="card shadow-sm mb-2">
                        <div class="card-header py-2 bg-white border-bottom">
                            <span class="font-weight-bold" style="font-size:1rem;"><i class="fa fa-barcode text-secondary mr-1"></i> Add Items</span>
                        </div>
                        <div class="card-body p-2">
                            <div class="input-group mb-1">
                                <input type="text" id="barcode_search" class="form-control" placeholder="Scan barcode…" autocomplete="off">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-warning font-weight-bold" onclick="scanBarcode()">
                                        <i class="fa fa-search"></i> Scan
                                    </button>
                                </div>
                            </div>
                            <div class="mt-2">
                                <select id="manual_batch_select" class="form-control">
                                    <option value="">Select batch manually…</option>
                                    @foreach($batches as $batch)
                                    <option value="{{ $batch->barcode }}">
                                        {{ $batch->barcode }} — {{ $batch->accessory->name }} ({{ $batch->qty_remaining }} left){{ $batch->accessory->description ? ' — '.$batch->accessory->description : '' }}
                                    </option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-secondary font-weight-bold mt-2" onclick="addSelectedBatch()">
                                    <i class="fa fa-plus"></i> Add
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Preload batch data --}}
                    <script>
                        window.batchData = {};
                        @foreach($batches as $batch)
                        window.batchData["{{ $batch->barcode }}"] = {
                            id: {{ $batch->id }},
                            barcode: "{{ $batch->barcode }}",
                            accessory: "{{ addslashes($batch->accessory->name) }}",
                            qty_remaining: {{ $batch->qty_remaining }},
                            price: {{ $batch->selling_price }}
                        };
                        @endforeach
                    </script>

                </div>{{-- /left --}}

                {{-- ===== RIGHT COLUMN (cart) ===== --}}
                <div class="col-lg-7">
                    <div class="pos-sticky" style="position:sticky; top:80px;">

                        {{-- Cart --}}
                        <div class="card shadow-sm mb-2">
                            <div class="card-header py-2 bg-white border-bottom d-flex align-items-center justify-content-between">
                                <h6 class="mb-0 font-weight-bold">
                                    <i class="fa fa-shopping-cart text-secondary mr-1"></i> Cart
                                    <span id="cart-badge" class="badge badge-primary ml-1">0</span>
                                </h6>
                                <span class="font-weight-bold text-success">Rs. <span id="cart-total">0.00</span></span>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0" id="sale-cart-table" style="font-size:0.97rem;">
                                        <thead style="background:#f8f9fa;">
                                            <tr>
                                                <th style="padding:6px 8px;">Item</th>
                                                <th class="text-center" style="padding:6px 4px;">Qty</th>
                                                <th class="text-center" style="padding:6px 4px;">Price</th>
                                                <th class="text-center" style="padding:6px 4px;">Disc</th>
                                                <th class="text-center" style="padding:6px 4px;">Total</th>
                                                <th style="padding:6px 4px;"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr id="cart-empty-row">
                                                <td colspan="6" class="text-center text-muted py-3">
                                                    <i class="fa fa-inbox mr-1"></i> Cart is empty
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            @if(auth()->user()->isAdmin())
                            <div class="card-footer bg-white text-right py-2">
                                <span class="text-muted small">Grand Total</span>
                                <span class="font-weight-bold text-success ml-2" style="font-size:1.15em;">Rs. <span id="cart-total-footer">0.00</span></span>
                            </div>
                            @endif
                        </div>

                        {{-- Sync pending offline sales (visible only when online + pending exist) --}}
                        <button class="btn btn-warning btn-block font-weight-bold py-2 mb-2" id="sync-now-btn" onclick="syncOfflineSales()" style="display:none; font-size:1.05em;">
                            <i class="fa fa-refresh mr-1"></i> Sync Offline Sales
                            <span id="sync-count-badge" class="badge badge-dark ml-1"></span>
                        </button>

                        {{-- Checkout --}}
                        <div class="d-flex justify-content-end mb-2">
                            <button class="btn btn-primary font-weight-bold py-2" id="checkout-btn" onclick="checkoutSale()" style="font-size:1.05em; min-width:260px;">
                                <i class="fa fa-check-circle mr-1"></i> Checkout &amp; Print Invoice
                            </button>
                        </div>

                        {{-- Payment --}}
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

                    </div>{{-- /sticky --}}
                </div>{{-- /right --}}
            </div>{{-- /row --}}

            {{-- ===== Daily Sales ===== --}}
            <div class="card shadow-sm mt-3">
                <div class="card-header bg-white d-flex align-items-start justify-content-between flex-wrap" style="gap:12px;">
                    <h5 class="mb-0 font-weight-bold"><i class="fa fa-list-alt text-secondary mr-1"></i> Daily Sales</h5>
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
                                <th>Status</th>
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
                                                <span class="badge badge-primary">Bank</span>
                                                {{ $p->bank->name ?? 'Bank' }} — Rs. {{ number_format($p->amount, 2) }}
                                                @if(!empty($p->reference_no))<br><small class="text-muted">Ref: {{ $p->reference_no }}</small>@endif
                                            @else
                                                <span class="badge badge-secondary">Counter</span> Rs. {{ number_format($p->amount, 2) }}
                                            @endif
                                        </div>
                                        @endforeach
                                    @endif
                                </td>
                                <td>
                                    @foreach($sale->items as $item)
                                    <div class="small">{{ $item->batch->accessory->name ?? '-' }} ×{{ $item->quantity }} <span class="text-muted">({{ number_format($item->price_per_unit, 2) }})</span></div>
                                    @endforeach
                                </td>
                                <td class="small text-muted">{{ $sale->comment ?: '—' }}</td>
                                <td>
                                    @if($sale->status === 'approved')
                                        <span class="badge badge-success">Approved</span>
                                    @else
                                        <span class="badge badge-warning text-dark">Pending</span>
                                    @endif
                                </td>
                                <td>
                                    <a class="btn btn-sm btn-outline-primary" target="_blank" href="{{ route('sales.invoice', $sale->id) }}">
                                        <i class="fa fa-print mr-1"></i> Receipt
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if(isset($todaysRefunds))
                <div class="card-footer bg-white">
                    <div class="row">
                        <div class="col-md-4 mb-2 mb-md-0">
                            <div class="card shadow-sm h-100">
                                <div class="card-body py-2">
                                    <div class="text-muted small mb-1">Today's Refund Value</div>
                                    <h5 class="mb-0">Rs. {{ number_format($todaysRefunds['value_from_items'], 2) }}</h5>
                                    <small class="text-muted">{{ $todaysRefunds['returns'] }} return(s), {{ $todaysRefunds['lines'] }} line(s)</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-2 mb-md-0">
                            <div class="card shadow-sm h-100">
                                <div class="card-body py-2">
                                    <div class="text-muted small mb-1">Refunds Paid Out</div>
                                    <h5 class="mb-0">Rs. {{ number_format($todaysRefunds['paid_out_total'], 2) }}</h5>
                                    @if(!empty($todaysRefunds['paid_by_method']))
                                    <small class="text-muted">
                                        @foreach($todaysRefunds['paid_by_method'] as $method => $amt)
                                        {{ ucfirst($method) }}: Rs. {{ number_format($amt, 2) }}@if(!$loop->last), @endif
                                        @endforeach
                                    </small>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card shadow-sm h-100">
                                <div class="card-body py-2">
                                    <div class="text-muted small mb-1">Net Effect</div>
                                    <h5 class="mb-0">Rs. {{ number_format($todaysRefunds['net_effect'], 2) }}</h5>
                                    <small class="text-muted">Credit notes created but not paid out</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>

        </div>
    </div>
</div>

{{-- Processing overlay --}}
<div id="loading-overlay" style="display:none; position:fixed; inset:0; z-index:99999; background:rgba(255,255,255,.6); backdrop-filter:blur(6px); justify-content:center; align-items:center;">
    <div class="card shadow-lg px-5 py-4 text-center">
        <i class="fa fa-spinner fa-spin fa-2x text-primary mb-2"></i>
        <div class="font-weight-bold" style="font-size:1.1em;">Processing Sale…</div>
    </div>
</div>

<script>
  // =====================================================================
  // OFFLINE / IndexedDB helpers
  // =====================================================================
  const _IDB_NAME = 'amm_pos_offline';
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
      };
      req.onsuccess = e => { _idb = e.target.result; res(_idb); };
      req.onerror   = e => rej(e.target.error);
    });
  }

  function idbGetByStatus(status) {
    return idbOpen().then(db => new Promise((res, rej) => {
      const idx = db.transaction('sales', 'readonly').objectStore('sales').index('status');
      const r = idx.getAll(status);
      r.onsuccess = () => res(r.result);
      r.onerror   = () => rej(r.error);
    }));
  }

  function idbAdd(payload) {
    return idbOpen().then(db => new Promise((res, rej) => {
      const r = db.transaction('sales', 'readwrite').objectStore('sales').add({
        payload, queued_at: new Date().toISOString(), status: 'pending', error: null
      });
      r.onsuccess = () => res(r.result);
      r.onerror   = () => rej(r.error);
    }));
  }

  function idbSetStatus(id, status, error) {
    return idbOpen().then(db => new Promise((res, rej) => {
      const store = db.transaction('sales', 'readwrite').objectStore('sales');
      const get = store.get(id);
      get.onsuccess = () => {
        const rec = get.result;
        rec.status = status; rec.error = error || null;
        const put = store.put(rec);
        put.onsuccess = () => res();
        put.onerror   = () => rej(put.error);
      };
      get.onerror = () => rej(get.error);
    }));
  }

  function idbDelete(id) {
    return idbOpen().then(db => new Promise((res, rej) => {
      const r = db.transaction('sales', 'readwrite').objectStore('sales').delete(id);
      r.onsuccess = () => res();
      r.onerror   = () => rej(r.error);
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
    } else {
      badge.style.display = 'none';
    }

    const syncBtn   = document.getElementById('sync-now-btn');
    const syncBadge = document.getElementById('sync-count-badge');
    if (!isOff && pending.length) {
      syncBadge.textContent = pending.length;
      syncBtn.style.display = '';
    } else {
      syncBtn.style.display = 'none';
    }

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
  async function syncOfflineSales() {
    const pending = await idbGetByStatus('pending');
    if (!pending.length) return;

    document.getElementById('sync-banner').style.display  = 'block';
    document.getElementById('sync-now-btn').style.display = 'none';

    // Fetch a fresh CSRF token before syncing
    let csrf = '{{ csrf_token() }}';
    try {
      const tr = await fetch('/api/pos/token', { credentials: 'same-origin' });
      const td = await tr.json();
      if (td.csrf) csrf = td.csrf;
    } catch(e) { /* use cached token */ }

    let synced = 0, failed = 0;
    for (const sale of pending) {
      try {
        const res  = await fetch('/pos/checkout', {
          method:  'POST',
          headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
          body:    JSON.stringify(sale.payload)
        });
        const data = await res.json();
        if (data.success) {
          await idbSetStatus(sale.id, 'synced', null);
          synced++;
        } else {
          await idbSetStatus(sale.id, 'failed', data.message || 'Server error');
          failed++;
        }
      } catch(e) {
        break; // Network dropped again — stop and retry later
      }
    }

    document.getElementById('sync-banner').style.display = 'none';

    if (synced > 0) {
      showToast('✓ ' + synced + ' sale(s) synced successfully!' + (failed ? ' ' + failed + ' failed — see conflicts above.' : ''), 'success');
      setTimeout(() => window.location.reload(), 1800);
    } else if (failed > 0) {
      showToast(failed + ' sale(s) failed to sync. See conflicts panel.', 'danger');
      updateOfflineUI();
    } else {
      updateOfflineUI();
    }
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

  function updateBatchDropdownQty() {
    const select = document.getElementById('manual_batch_select');
    if (!select) return;
    Array.from(select.options).forEach(opt => {
      if (!opt.value || !window.batchData[opt.value]) return;
      opt.text = opt.text.replace(/\(\d+ left\)/, '(' + window.batchData[opt.value].qty_remaining + ' left)');
    });
    try { $('#manual_batch_select').trigger('change'); } catch(e) {}
  }

  function buildPayload() {
    const vendor_id       = document.getElementById('vendor_id').value || null;
    const customer_name   = document.getElementById('customer_name').value || null;
    const customer_mobile = document.getElementById('customer_mobile')?.value || '';
    const comment         = (document.getElementById('sale_comment').value || '').trim() || null;
    const netTotal        = cart.reduce((t, it) => {
      const p = Number(it.price) || 0;
      const d = Math.min(Math.max(Number(it.discount) || 0, 0), p);
      return t + Math.max(p - d, 0) * (Number(it.qty) || 0);
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
      vendor_id, customer_name, customer_mobile, comment,
      pay_amount:     vendor_id ? Number(raw_pay_amount) : Number(netTotal),
      payment_method: method,
      bank_id:        method === 'bank' ? (bank_id ? Number(bank_id) : null) : null,
      reference_no:   method === 'bank' ? (reference_no || null) : null,
      payments,
      items: cart.map(i => ({ barcode: i.barcode, qty: Number(i.qty), price: Number(i.price), discount: Number(i.discount || 0) })),
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
    cart.forEach(item => {
      if (window.batchData[item.barcode]) {
        window.batchData[item.barcode].qty_remaining = Math.max(0, window.batchData[item.barcode].qty_remaining - Number(item.qty));
      }
    });
    updateBatchDropdownQty();
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
    $('#manual_batch_select').select2({ placeholder: "Select a Batch", allowClear: true, width: '100%' });
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
        fetch(`/api/vendor-balance/${vendorId}`)
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

    // IndexedDB init + offline UI
    idbOpen().then(() => updateOfflineUI());

    // Request persistent storage so browser won't evict offline queue
    if (navigator.storage && navigator.storage.persist) {
      navigator.storage.persist();
    }

    // Register service worker
    if ('serviceWorker' in navigator) {
      navigator.serviceWorker.register('/sw.js').catch(() => {});
    }
  });

  document.getElementById('barcode_search').addEventListener('keydown', function (e) {
    if (e.key === 'Enter') { e.preventDefault(); scanBarcode(); }
  });

  document.getElementById('customer_mobile').addEventListener('input', function () {
    this.value = this.value.replace(/\D/g, '');
    if (!this.value.startsWith('923')) this.value = '923' + this.value.replace(/^923*/, '');
    if (this.value.length > 12) this.value = this.value.slice(0, 12);
  });

  window.addEventListener('online',  () => { updateOfflineUI(); syncOfflineSales(); });
  window.addEventListener('offline', () => { updateOfflineUI(); });

  // =====================================================================
  // CART
  // =====================================================================
  let cart = [];

  function scanBarcode() {
    const code = document.getElementById('barcode_search').value.trim();
    if (!code) return alert('Enter or scan a barcode!');
    const batch = window.batchData[code];
    if (!batch) return alert('Barcode not found in available batches!');
    const qty = prompt('Quantity to add from batch ' + code + ' (Max: ' + batch.qty_remaining + '):', 1);
    if (!qty || isNaN(qty) || qty <= 0 || qty > batch.qty_remaining) return alert('Invalid quantity!');
    const existing = cart.find(i => i.barcode === batch.barcode);
    if (existing) { existing.qty = Number(existing.qty) + Number(qty); }
    else { cart.push({ barcode: batch.barcode, accessory: batch.accessory, qty: Number(qty), price: Number(batch.price), discount: 0 }); }
    renderCart();
    document.getElementById('barcode_search').value = '';
  }

  function addSelectedBatch() {
    const code = document.getElementById('manual_batch_select').value;
    if (!code) return alert('Select a batch to add!');
    const batch = window.batchData[code];
    if (!batch) return alert('Batch not found!');
    const qty = prompt('Quantity to add from batch ' + code + ' (Max: ' + batch.qty_remaining + '):', 1);
    if (!qty || isNaN(qty) || qty <= 0 || qty > batch.qty_remaining) return alert('Invalid quantity!');
    const existing = cart.find(i => i.barcode === batch.barcode);
    if (existing) { existing.qty = Number(existing.qty) + Number(qty); }
    else { cart.push({ barcode: batch.barcode, accessory: batch.accessory, qty: Number(qty), price: Number(batch.price), discount: 0 }); }
    renderCart();
  }

  function renderCart() {
    const tbody = document.querySelector('#sale-cart-table tbody');
    tbody.innerHTML = '';
    if (!cart.length) {
      tbody.innerHTML = '<tr id="cart-empty-row"><td colspan="6" class="text-center text-muted py-3"><i class="fa fa-inbox mr-1"></i> Cart is empty</td></tr>';
      document.getElementById('cart-total').textContent = '0.00';
      document.getElementById('cart-badge').textContent = '0';
      const footer = document.getElementById('cart-total-footer');
      if (footer) footer.textContent = '0.00';
      return;
    }
    cart.forEach((item, i) => {
      const unitPrice = Number(item.price) || 0;
      const unitDisc  = Math.min(Math.max(Number(item.discount) || 0, 0), unitPrice);
      item.discount   = unitDisc;
      const qty       = Number(item.qty) || 0;
      const lineTotal = Math.max(unitPrice - unitDisc, 0) * qty;
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td class="font-weight-bold" style="padding:5px 8px;font-size:0.97rem;">${item.accessory}<div class="text-muted font-weight-normal" style="font-size:.8em;">${item.barcode}</div></td>
        <td class="text-center" style="padding:5px 4px;"><input class="cart-input" type="number" value="${qty}" min="1" onchange="updateQuantity(${i}, this.value)"></td>
        <td class="text-center" style="padding:5px 4px;"><input class="cart-input" type="number" value="${unitPrice.toFixed(2)}" min="0" step="0.01" onchange="updatePrice(${i}, this.value)"></td>
        <td class="text-center" style="padding:5px 4px;"><input class="cart-input" type="number" value="${unitDisc.toFixed(2)}" min="0" step="0.01" onchange="updateDiscount(${i}, this.value)"></td>
        <td class="text-center font-weight-bold" style="padding:5px 4px;font-size:0.97rem;">${lineTotal.toFixed(2)}</td>
        <td class="text-center" style="padding:5px 4px;"><button type="button" class="cart-item-remove" onclick="removeCartItem(${i})"><i class="fa fa-trash"></i></button></td>
      `;
      tbody.appendChild(tr);
    });
    const grandTotal = cart.reduce((t, it) => {
      const p = Number(it.price) || 0;
      const d = Math.min(Math.max(Number(it.discount) || 0, 0), p);
      return t + Math.max(p - d, 0) * (Number(it.qty) || 0);
    }, 0);
    document.getElementById('cart-total').textContent = grandTotal.toFixed(2);
    document.getElementById('cart-badge').textContent  = cart.length;
    const footer = document.getElementById('cart-total-footer');
    if (footer) footer.textContent = grandTotal.toFixed(2);
  }

  function updateQuantity(i, v) { const q = Number(v); if (!isNaN(q) && q > 0) { cart[i].qty = q; renderCart(); } }
  function updatePrice(i, v)    { const p = Number(v); if (!isNaN(p) && p >= 0) { cart[i].price = p; if ((Number(cart[i].discount)||0) > p) cart[i].discount = p; renderCart(); } }
  function updateDiscount(i, v) { const d = Number(v); if (!isNaN(d) && d >= 0) { cart[i].discount = d; renderCart(); } }
  function removeCartItem(i)    { cart.splice(i, 1); renderCart(); }

  // =====================================================================
  // CHECKOUT (online + offline)
  // =====================================================================
  async function checkoutSale() {
    if (!cart.length) return alert('Cart is empty!');

    const payload = buildPayload();
    if (!validatePayload(payload)) return;

    // ---- OFFLINE PATH ----
    if (!navigator.onLine) {
      try { await saveOffline(payload); } catch(e) { alert('Failed to save offline: ' + e.message); }
      return;
    }

    // ---- ONLINE PATH ----
    const btn = document.getElementById('checkout-btn');
    document.getElementById('loading-overlay').style.display = 'flex';
    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin mr-1"></i> Processing…';

    try {
      const res = await fetch('/pos/checkout', {
        method:  'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body:    JSON.stringify(payload)
      });
      const ct = res.headers.get('content-type') || '';
      let data;
      if (ct.includes('application/json')) {
        data = await res.json();
      } else {
        const text = await res.text();
        throw new Error('Server did not return JSON: ' + text.substring(0, 400));
      }
      if (data.success) {
        window.open('/pos/invoice/' + data.invoice_number, '_blank');
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
      // If connection dropped during the request, save offline automatically
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
