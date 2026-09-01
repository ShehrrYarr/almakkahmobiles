@extends('user_navbar')
@section('content')

<style>
    .card { border-radius: .65rem; box-shadow: 0 6px 18px rgba(0,0,0,.06); }
    .card-header { background: #f7f9fc; font-weight: 600; }
    .buyback-result {
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 10px 12px;
        margin-bottom: 6px;
        cursor: pointer;
    }
    .buyback-result:hover { background: #f7f9fc; border-color: #556ee6; }
    .buyback-result .imei { font-weight: 600; }
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

            @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div class="card mb-3">
                <div class="card-header">Find Sold Mobile</div>
                <div class="card-body">
                    <input type="text" class="form-control" id="buyback-search" placeholder="Search by Sale # or IMEI...">
                    <div id="buyback-search-results" class="mt-2"></div>
                </div>
            </div>

            <form method="POST" action="{{ route('mobile.buyback.store') }}" id="buyback-form" style="display:none;">
                @csrf
                <input type="hidden" name="mobile_unit_id" id="bb-unit-id">

                <div class="card mb-3">
                    <div class="card-header">Selected Mobile</div>
                    <div class="card-body">
                        <div id="bb-origin-info" class="text-muted small mb-2"></div>
                        <div class="row">
                            <div class="col-md-4 mb-2">
                                <label class="form-label">Name</label>
                                <input type="text" class="form-control" id="bb-name" readonly>
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="form-label">IMEI</label>
                                <input type="text" class="form-control" id="bb-imei" readonly>
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="form-label">Storage / Color</label>
                                <input type="text" class="form-control" id="bb-storage" readonly>
                            </div>

                            <div class="col-md-4 mb-2">
                                <label class="form-label">Battery Percentage</label>
                                <input type="text" class="form-control" name="battery" id="bb-battery" placeholder="e.g. 88%">
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="form-label">Battery Cycle</label>
                                <input type="number" min="0" class="form-control" name="battery_cycle" id="bb-battery-cycle">
                            </div>
                            <div class="col-md-4 mb-2 d-flex align-items-center">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" name="has_box" value="1" id="bb-has-box">
                                    <label class="form-check-label" for="bb-has-box">Box Included</label>
                                </div>
                            </div>

                            <div class="col-md-6 mb-2">
                                <label class="form-label">Buyback (Cost) Price</label>
                                <input type="number" step="0.01" min="0" class="form-control" name="buyback_price" id="bb-buyback-price" required>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label">New Selling Price</label>
                                <input type="number" step="0.01" min="0" class="form-control" name="new_selling_price" id="bb-new-selling-price" required>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header">Sold Back By</div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Name</label>
                                <input type="text" class="form-control" name="seller_name" required>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label">CNIC</label>
                                <input type="text" class="form-control" name="seller_cnic"
                                    maxlength="13" pattern="\d{13}" inputmode="numeric" title="CNIC must be exactly 13 digits">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Phone Number</label>
                                <input type="text" class="form-control" name="seller_phone">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Address</label>
                                <input type="text" class="form-control" name="seller_address">
                            </div>
                            <div class="col-12 mb-2">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" name="seller_description" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header">Payment</div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Payment Method</label>
                                <select class="form-control" name="payment_method" id="bb-payment-method" required>
                                    <option value="counter">Counter (Cash)</option>
                                    <option value="bank">Bank</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-2" id="bb-bank-wrapper" style="display:none;">
                                <label class="form-label">Bank</label>
                                <select class="form-control" name="mobile_bank_id" id="bb-bank-id">
                                    <option value="">Select bank</option>
                                    @foreach($banks as $bank)
                                    <option value="{{ $bank->id }}">{{ $bank->name }} ({{ $bank->account_no }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-right mb-3">
                    <button type="submit" class="btn btn-success"><i class="fa fa-check-square-o mr-1"></i> Confirm Buyback</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    var searchInput = document.getElementById('buyback-search');
    var resultsEl = document.getElementById('buyback-search-results');
    var formEl = document.getElementById('buyback-form');
    var searchTimer = null;

    searchInput.addEventListener('input', function () {
        clearTimeout(searchTimer);
        var q = searchInput.value.trim();
        if (!q) { resultsEl.innerHTML = ''; return; }
        searchTimer = setTimeout(function () { runSearch(q); }, 300);
    });

    function runSearch(q) {
        fetch('{{ route('mobile.buyback.search') }}?q=' + encodeURIComponent(q), { headers: { 'Accept': 'application/json' } })
            .then(function (res) { return res.json(); })
            .then(function (json) {
                resultsEl.innerHTML = '';
                if (!json.success || !json.results.length) {
                    resultsEl.innerHTML = '<div class="text-muted small">No matching sold mobile found.</div>';
                    return;
                }
                json.results.forEach(function (r) {
                    var div = document.createElement('div');
                    div.className = 'buyback-result';
                    div.innerHTML =
                        '<div class="imei">' + r.name + ' — IMEI ' + r.imei + '</div>' +
                        '<div class="small text-muted">' +
                        (r.sale_id ? 'Sale #' + r.sale_id + ' · ' : '') +
                        (r.sale_date ? r.sale_date + ' · ' : '') +
                        (r.customer_name ? 'Sold to ' + r.customer_name + ' · ' : '') +
                        (r.sold_price ? 'Rs. ' + Number(r.sold_price).toLocaleString() : '') +
                        '</div>';
                    div.addEventListener('click', function () { selectResult(r); });
                    resultsEl.appendChild(div);
                });
            })
            .catch(function () {
                resultsEl.innerHTML = '<div class="text-danger small">Search failed — check your connection.</div>';
            });
    }

    function selectResult(r) {
        document.getElementById('bb-unit-id').value = r.unit_id;
        document.getElementById('bb-name').value = r.name;
        document.getElementById('bb-imei').value = r.imei + (r.imei2 ? ' / ' + r.imei2 : '');
        document.getElementById('bb-storage').value = [r.storage, r.color, r.pta_status].filter(Boolean).join(' · ');
        document.getElementById('bb-battery').value = r.battery || '';
        document.getElementById('bb-battery-cycle').value = r.battery_cycle || '';
        document.getElementById('bb-has-box').checked = !!r.has_box;

        var origin = [];
        if (r.sale_id) origin.push('Sale #' + r.sale_id);
        if (r.sale_date) origin.push('sold ' + r.sale_date);
        if (r.customer_name) origin.push('to ' + r.customer_name);
        if (r.sold_price) origin.push('for Rs. ' + Number(r.sold_price).toLocaleString());
        document.getElementById('bb-origin-info').textContent = origin.length ? 'Originally: ' + origin.join(', ') : '';

        resultsEl.innerHTML = '';
        searchInput.value = r.imei;
        formEl.style.display = '';
        formEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    document.getElementById('bb-payment-method').addEventListener('change', function () {
        var isBank = this.value === 'bank';
        document.getElementById('bb-bank-wrapper').style.display = isBank ? '' : 'none';
        document.getElementById('bb-bank-id').required = isBank;
    });
</script>

@endsection
