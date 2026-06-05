@extends('user_navbar')
@section('content')

<style>
    .payment-method-card {
        border: 2px solid #dee2e6;
        border-radius: 10px;
        padding: 12px 18px;
        cursor: pointer;
        transition: all .2s;
        background: #fff;
        user-select: none;
    }
    .payment-method-card:hover { border-color: #0d6efd; background: #f0f6ff; }
    .payment-method-card.active { border-color: #0d6efd; background: #e8f0fe; }
    .payment-method-card input[type="radio"] { display: none; }

    .cart-item-remove {
        background: none;
        border: none;
        color: #dc3545;
        font-size: 1.1em;
        cursor: pointer;
        padding: 2px 6px;
        border-radius: 4px;
        transition: background .15s;
    }
    .cart-item-remove:hover { background: #fde8ea; }

    .cart-input {
        width: 72px;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        padding: 4px 6px;
        text-align: center;
        font-size: .92em;
    }
    .cart-input:focus { outline: none; border-color: #0d6efd; }

    #cart-badge {
        font-size: .75em;
        vertical-align: middle;
    }

    @media (max-width: 991px) {
        .pos-sticky { position: static !important; }
    }
</style>

<div class="app-content content">
    <div class="content-overlay"></div>
    <div class="content-wrapper">
        <div class="content-header row"></div>
        <div class="content-body">

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

            {{-- Page Title --}}
            <div class="d-flex align-items-center justify-content-between mb-2">
                <h3 class="mb-0 font-weight-bold">
                    <i class="fa fa-shopping-cart text-primary mr-2"></i> Point of Sale
                </h3>
            </div>

            <div class="row">
                {{-- ===== LEFT COLUMN (compact) ===== --}}
                <div class="col-lg-4">

                    {{-- Customer / Vendor --}}
                    <div class="card shadow-sm mb-2">
                        <div class="card-header py-1 bg-white border-bottom">
                            <span class="small font-weight-bold"><i class="fa fa-user text-secondary mr-1"></i> Customer / Vendor</span>
                        </div>
                        <div class="card-body p-2">
                            <form method="POST" action="{{ route('sales.store') }}" id="sale-meta-form">
                                @csrf
                                <select name="vendor_id" id="vendor_id" class="form-control form-control-sm mb-1">
                                    <option value="">Walk-in Customer</option>
                                    @foreach($vendors as $vendor)
                                    <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                                    @endforeach
                                </select>
                                <input type="text" name="customer_name" id="customer_name" class="form-control form-control-sm mb-1" placeholder="Customer name (optional)">
                                <div id="customer_mobile_row" style="display:none;">
                                    <input type="text" name="customer_mobile" id="customer_mobile" class="form-control form-control-sm mb-1" placeholder="Mobile: 923XXXXXXXXX">
                                </div>
                                <textarea id="sale_comment" name="comment" rows="1" class="form-control form-control-sm" placeholder="Comment (optional)"></textarea>
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
                        <div class="card-header py-1 bg-white border-bottom">
                            <span class="small font-weight-bold"><i class="fa fa-barcode text-secondary mr-1"></i> Add Items</span>
                        </div>
                        <div class="card-body p-2">
                            <div class="input-group input-group-sm mb-1">
                                <input type="text" id="barcode_search" class="form-control" placeholder="Scan barcode…" autocomplete="off">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-warning btn-sm font-weight-bold" onclick="scanBarcode()">
                                        <i class="fa fa-search"></i> Scan
                                    </button>
                                </div>
                            </div>
                            <div class="input-group input-group-sm">
                                <select id="manual_batch_select" class="form-control">
                                    <option value="">Select batch manually…</option>
                                    @foreach($batches as $batch)
                                    <option value="{{ $batch->barcode }}">
                                        {{ $batch->barcode }} — {{ $batch->accessory->name }} ({{ $batch->qty_remaining }} left){{ $batch->accessory->description ? ' — '.$batch->accessory->description : '' }}
                                    </option>
                                    @endforeach
                                </select>
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-secondary btn-sm font-weight-bold" onclick="addSelectedBatch()">
                                        <i class="fa fa-plus"></i> Add
                                    </button>
                                </div>
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
                <div class="col-lg-8">
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
                                    <table class="table table-sm table-hover mb-0" id="sale-cart-table">
                                        <thead style="background:#f8f9fa;">
                                            <tr>
                                                <th>Item</th>
                                                <th class="text-center">Qty</th>
                                                <th class="text-center">Price</th>
                                                <th class="text-center">Disc</th>
                                                <th class="text-center">Total</th>
                                                <th></th>
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

                        {{-- Payment --}}
                        <div class="card shadow-sm mb-2">
                            <div class="card-header py-2 bg-white border-bottom">
                                <h6 class="mb-0 font-weight-bold"><i class="fa fa-credit-card text-secondary mr-1"></i> Payment</h6>
                            </div>
                            <div class="card-body" id="payment-section">
                                <div class="d-flex gap-2 mb-3" style="gap:10px;">
                                    <label class="payment-method-card active flex-fill text-center" id="label-counter">
                                        <input type="radio" name="payment_method" value="counter" checked>
                                        <div><i class="fa fa-money fa-lg text-success mb-1"></i></div>
                                        <div class="font-weight-bold small">Counter (Cash)</div>
                                    </label>
                                    <label class="payment-method-card flex-fill text-center" id="label-bank">
                                        <input type="radio" name="payment_method" value="bank">
                                        <div><i class="fa fa-university fa-lg text-primary mb-1"></i></div>
                                        <div class="font-weight-bold small">Bank Transfer</div>
                                    </label>
                                </div>

                                <div id="bank-select-wrap" style="display:none;" class="mb-2">
                                    <label class="small font-weight-bold text-muted mb-1">Select Bank</label>
                                    <select id="bank_id" class="form-control">
                                        <option value="">Select Bank</option>
                                        @foreach($banks as $bank)
                                        <option value="{{ $bank->id }}">{{ $bank->name }}{{ $bank->account_no ? ' — '.$bank->account_no : '' }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div id="bank-ref-wrap" style="display:none;" class="mb-2">
                                    <label class="small font-weight-bold text-muted mb-1">Reference / Slip #</label>
                                    <input type="text" id="bank_reference" class="form-control" placeholder="Optional">
                                </div>
                            </div>
                        </div>

                        {{-- Checkout --}}
                        <button class="btn btn-primary btn-block font-weight-bold py-2" id="checkout-btn" onclick="checkoutSale()" style="font-size:1.05em;">
                            <i class="fa fa-check-circle mr-1"></i> Checkout &amp; Print Invoice
                        </button>

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
  // --- INIT ---
  $(document).ready(function () {
    $('#manual_batch_select').select2({ placeholder: "Select a Batch", allowClear: true, width: '100%' });
    $('#vendor_id').select2({ placeholder: "Select a vendor", allowClear: true, width: '100%' });

    // Payment method card toggle
    document.querySelectorAll('.payment-method-card').forEach(card => {
      card.addEventListener('click', function () {
        document.querySelectorAll('.payment-method-card').forEach(c => c.classList.remove('active'));
        this.classList.add('active');
        const isBank = this.querySelector('input[type="radio"]').value === 'bank';
        document.getElementById('bank-select-wrap').style.display = isBank ? '' : 'none';
        document.getElementById('bank-ref-wrap').style.display   = isBank ? '' : 'none';
      });
    });

    // Vendor extra fields + balance fetch
    $('#vendor_id').on('change', function () {
      const vendorId = $(this).val();
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
          .catch(() => { balanceInput.value = 'Error'; });
      } else {
        extraFields.style.display = 'none';
        mobileRow.style.display   = '';
        balanceInput.value = '';
      }
    });
    $('#vendor_id').trigger('change');
  });

  // Enter triggers scan
  document.getElementById('barcode_search').addEventListener('keydown', function (e) {
    if (e.key === 'Enter') { e.preventDefault(); scanBarcode(); }
  });

  // Mobile normalization
  document.getElementById('customer_mobile').addEventListener('input', function () {
    this.value = this.value.replace(/\D/g, '');
    if (!this.value.startsWith('923')) this.value = '923' + this.value.replace(/^923*/, '');
    if (this.value.length > 12) this.value = this.value.slice(0, 12);
  });

  // --- CART ---
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
        <td class="small font-weight-bold">${item.accessory}<div class="text-muted font-weight-normal" style="font-size:.8em;">${item.barcode}</div></td>
        <td class="text-center"><input class="cart-input" type="number" value="${qty}" min="1" onchange="updateQuantity(${i}, this.value)"></td>
        <td class="text-center"><input class="cart-input" type="number" value="${unitPrice.toFixed(2)}" min="0" step="0.01" onchange="updatePrice(${i}, this.value)"></td>
        <td class="text-center"><input class="cart-input" type="number" value="${unitDisc.toFixed(2)}" min="0" step="0.01" onchange="updateDiscount(${i}, this.value)"></td>
        <td class="text-center font-weight-bold small">${lineTotal.toFixed(2)}</td>
        <td class="text-center"><button type="button" class="cart-item-remove" onclick="removeCartItem(${i})"><i class="fa fa-trash"></i></button></td>
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

  // --- CHECKOUT ---
  function checkoutSale() {
    if (!cart.length) return alert('Cart is empty!');

    document.getElementById('loading-overlay').style.display = 'flex';
    const btn = document.getElementById('checkout-btn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin mr-1"></i> Processing…';

    const vendor_id       = document.getElementById('vendor_id').value || null;
    const customer_name   = document.getElementById('customer_name').value || null;
    const customer_mobile = document.getElementById('customer_mobile')?.value || '';
    const comment         = (document.getElementById('sale_comment').value || '').trim() || null;

    const netTotal = cart.reduce((t, it) => {
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
        if (method === 'bank' && !bank_id) {
          alert('Please select a bank for the bank payment.');
          btn.disabled = false; btn.innerHTML = '<i class="fa fa-check-circle mr-1"></i> Checkout & Print Invoice';
          document.getElementById('loading-overlay').style.display = 'none';
          return;
        }
        payments.push({ method: method === 'bank' ? 'bank' : 'counter', bank_id: method === 'bank' ? Number(bank_id) : null, amount: Number(raw_pay_amount), reference_no: method === 'bank' ? (reference_no || null) : null });
      }
    } else {
      if (method === 'bank' && !bank_id) {
        alert('Please select a bank for the bank payment.');
        btn.disabled = false; btn.innerHTML = '<i class="fa fa-check-circle mr-1"></i> Checkout & Print Invoice';
        document.getElementById('loading-overlay').style.display = 'none';
        return;
      }
      payments.push({ method: method === 'bank' ? 'bank' : 'counter', bank_id: method === 'bank' ? Number(bank_id) : null, amount: Number(netTotal), reference_no: method === 'bank' ? (reference_no || null) : null });
    }

    const payload = {
      vendor_id, customer_name, customer_mobile, comment,
      pay_amount: vendor_id ? Number(raw_pay_amount) : Number(netTotal),
      payment_method: method,
      bank_id: method === 'bank' ? (bank_id ? Number(bank_id) : null) : null,
      reference_no: method === 'bank' ? (reference_no || null) : null,
      payments,
      items: cart.map(i => ({ barcode: i.barcode, qty: Number(i.qty), price: Number(i.price), discount: Number(i.discount || 0) }))
    };

    fetch('/pos/checkout', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
      body: JSON.stringify(payload)
    })
    .then(async res => {
      const ct = res.headers.get('content-type') || '';
      if (ct.includes('application/json')) return res.json();
      const text = await res.text();
      throw new Error('Server did not return JSON: ' + text.substring(0, 400));
    })
    .then(data => {
      if (data.success) {
        window.open('/pos/invoice/' + data.invoice_number, '_blank');
        setTimeout(() => window.location.reload(), 700);
      } else {
        alert('Error: ' + (data.message || 'Sale failed.'));
        btn.disabled = false; btn.innerHTML = '<i class="fa fa-check-circle mr-1"></i> Checkout & Print Invoice';
        document.getElementById('loading-overlay').style.display = 'none';
      }
    })
    .catch(err => {
      alert('Unexpected error: ' + err.message);
      btn.disabled = false; btn.innerHTML = '<i class="fa fa-check-circle mr-1"></i> Checkout & Print Invoice';
      document.getElementById('loading-overlay').style.display = 'none';
    });
  }
</script>
@endsection
