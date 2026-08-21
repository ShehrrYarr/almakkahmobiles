@extends('user_navbar')
@section('content')

<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
    .card { border-radius: .65rem; box-shadow: 0 6px 18px rgba(0,0,0,.06); }
    .card-header { background: #f7f9fc; font-weight: 600; }
    .muted { color: #6b7280; font-size: .875rem; }
    .cursor-disabled { pointer-events: none; opacity: .6; }
    .overlay-blur {
        position: fixed; inset: 0; display: none; align-items: center; justify-content: center;
        backdrop-filter: blur(4px); background: rgba(255,255,255,.35); z-index: 9999;
    }
    .overlay-blur .box { background: #fff; padding: 16px 20px; border-radius: 10px; box-shadow: 0 12px 30px rgba(0,0,0,.15); font-weight: 600; }
    .action-bar { position: sticky; bottom: 0; z-index: 10; background: #fff; border-top: 1px solid #e5e7eb; padding: 12px 16px; box-shadow: 0 -4px 16px rgba(0,0,0,.04); }
    .action-bar .summary { font-weight: 600; }
    .table thead th { white-space: nowrap; }
    .thumb-preview { width: 48px; height: 48px; object-fit: cover; border-radius: 4px; border: 1px solid #ddd; margin-right: 4px; }
</style>

<div class="app-content content">
    <div class="content-overlay"></div>
    <div class="content-wrapper">
        <div class="content-header row"></div>

        <div class="container-fluid">
            <h3 class="mb-3">Purchase Mobiles</h3>

            {{-- 1) Vendor --}}
            <div class="card mb-3">
                <div class="card-body">
                    <label class="mb-1">Vendor</label>
                    <select id="vendor_id" class="form-control">
                        <option value="">Search Vendor</option>
                    </select>
                    <div class="muted mt-1">Choose vendor to enable mobile selection.</div>
                </div>
            </div>

            {{-- 2) Mobiles catalog --}}
            <div class="card mb-3">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <span>Mobiles</span>
                    <input id="searchBox" type="search" class="form-control form-control-sm"
                        placeholder="Search name, company, condition..." style="max-width: 320px;">
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Company</th>
                                    <th>Condition</th>
                                    <th style="width:110px;"></th>
                                </tr>
                            </thead>
                            <tbody id="mobilesBody" class="cursor-disabled">
                                @foreach ($mobiles as $m)
                                <tr data-mobile-id="{{ $m->id }}" data-name="{{ strtolower($m->name) }}"
                                    data-company="{{ strtolower(optional($m->company)->name) }}"
                                    data-group="{{ strtolower(optional($m->group)->name) }}">
                                    <td>{{ $m->name }}</td>
                                    <td>{{ optional($m->company)->name }}</td>
                                    <td>{{ optional($m->group)->name }}</td>
                                    <td><button class="btn btn-primary btn-sm selectMobile" disabled>Select</button></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="p-2 muted">Click <em>Select</em> to enter this phone's IMEI and details. Don't see the model you need? <a href="{{ route('mobile.index') }}">Add it to the Mobiles catalog</a> first.</div>
                </div>
            </div>

            {{-- 3) Selected units --}}
            <div class="card mb-0">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Selected Phones</span>
                    <div class="text-right">
                        <div class="muted">Items: <strong id="itemsCount">0</strong></div>
                        <div>Total (Purchase): <strong id="grandTotal">0.00</strong></div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-striped table-bordered mb-0" id="selectedTable">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>Mobile</th>
                                    <th>IMEI</th>
                                    <th>Storage</th>
                                    <th>PTA</th>
                                    <th>Purchase</th>
                                    <th>Selling</th>
                                    <th>Images</th>
                                    <th style="width:80px;"></th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="5" class="text-right">Grand Total</th>
                                    <th id="grandTotalFoot" colspan="2">0.00</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                {{-- 4) Sticky bottom action bar --}}
                <div class="action-bar d-flex flex-wrap align-items-center justify-content-between">
                    <div class="summary">
                        <span class="mr-3">Items: <span id="itemsCountBar">0</span></span>
                        <span>Grand Total: <span id="grandTotalBar">0.00</span></span>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="mr-2">
                            <label class="mb-1">Pay Amount (for all)</label>
                            <input type="number" id="payAmount" class="form-control" step="0.01" min="0" value="0" style="min-width: 220px;">
                        </div>
                        <button id="submitAllBtn" class="btn btn-success ml-2" disabled>Submit All</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal: enter details for a selected mobile --}}
        <div class="modal fade" id="unitModal" tabindex="-1" role="dialog" aria-labelledby="unitModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Phone — <span id="modalMobileName"></span></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group mb-2">
                            <label>IMEI</label>
                            <input type="text" class="form-control" id="m_imei" placeholder="15-digit IMEI">
                        </div>
                        <div class="form-row">
                            <div class="form-group col-6">
                                <label>Storage</label>
                                <input type="text" class="form-control" id="m_storage" placeholder="e.g. 128GB">
                            </div>
                            <div class="form-group col-6">
                                <label>PTA Status</label>
                                <select class="form-control" id="m_pta">
                                    <option value="PTA">PTA</option>
                                    <option value="Non PTA">Non PTA</option>
                                    <option value="JV">JV</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-6">
                                <label>Battery Health (Optional)</label>
                                <input type="text" class="form-control" id="m_battery" placeholder="e.g. 92%">
                            </div>
                            <div class="form-group col-6">
                                <label>Battery Cycle (Optional)</label>
                                <input type="number" class="form-control" id="m_battery_cycle" min="0">
                            </div>
                        </div>
                        <div class="form-group mb-2">
                            <label>Purchase Date</label>
                            <input type="date" class="form-control" id="m_purchase_date" value="{{ now()->toDateString() }}">
                        </div>
                        <div class="form-row">
                            <div class="form-group col-6">
                                <label>Purchase Price</label>
                                <input type="number" class="form-control" id="m_pprice" step="0.01" min="0">
                            </div>
                            <div class="form-group col-6">
                                <label>Selling Price</label>
                                <input type="number" class="form-control" id="m_sprice" step="0.01" min="0">
                            </div>
                        </div>
                        <div class="form-group mb-2">
                            <label>Description (Optional)</label>
                            <input type="text" class="form-control" id="m_description">
                        </div>
                        <div class="form-group mb-2">
                            <label>Images (up to 5)</label>
                            <input type="file" class="form-control" id="m_images" accept="image/*" multiple>
                            <div id="m_images_preview" class="mt-2"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button id="modalAddBtn" type="button" class="btn btn-primary">Add</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Blur overlay --}}
<div class="overlay-blur" id="overlay">
    <div class="box"><div class="spinner-border mr-2" role="status" aria-hidden="true"></div>Storing… Please wait</div>
</div>

<script>
(function () {
  // --- Select2 (AJAX) for Vendor ---
  $('#vendor_id').select2({
    theme: 'bootstrap4', width: '100%', placeholder: 'Search vendor…', allowClear: true,
    ajax: {
      url: '{{ route('mobile.vendors.search') }}',
      dataType: 'json', delay: 200,
      data: params => ({ q: params.term || '' }),
      processResults: data => ({ results: data }),
      cache: true
    },
    minimumInputLength: 1
  });

  const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
  const mobBody = document.getElementById('mobilesBody');
  const searchBox = document.getElementById('searchBox');
  const overlay = document.getElementById('overlay');
  const selTable = document.querySelector('#selectedTable tbody');
  const submitBtn = document.getElementById('submitAllBtn');
  const itemsCount = document.getElementById('itemsCount');
  const gTotalTop = document.getElementById('grandTotal');
  const gTotalFoot = document.getElementById('grandTotalFoot');
  const itemsCountBar = document.getElementById('itemsCountBar');
  const gTotalBar = document.getElementById('grandTotalBar');
  const payAmount = document.getElementById('payAmount');

  const modalName = document.getElementById('modalMobileName');
  const m_imei = document.getElementById('m_imei');
  const m_storage = document.getElementById('m_storage');
  const m_pta = document.getElementById('m_pta');
  const m_battery = document.getElementById('m_battery');
  const m_battery_cycle = document.getElementById('m_battery_cycle');
  const m_date = document.getElementById('m_purchase_date');
  const m_pprice = document.getElementById('m_pprice');
  const m_sprice = document.getElementById('m_sprice');
  const m_desc = document.getElementById('m_description');
  const m_images = document.getElementById('m_images');
  const m_images_preview = document.getElementById('m_images_preview');
  const m_addBtn = document.getElementById('modalAddBtn');

  let cart = []; // { mobile_id, mobile_name, imei, storage, pta_status, battery, battery_cycle, purchase_date, purchase_price, selling_price, description, images: File[] }
  let current = { id: null, name: '' };

  function fmt(n){ return (Math.round((+n + Number.EPSILON) * 100)/100).toFixed(2); }

  function enableMobilesUI(enabled) {
    mobBody.classList.toggle('cursor-disabled', !enabled);
    document.querySelectorAll('.selectMobile').forEach(btn => btn.disabled = !enabled);
    recalc();
  }

  function recalc() {
    let total = 0;
    cart.forEach(i => total += Number(i.purchase_price || 0));
    const t = fmt(total);
    gTotalTop.textContent = t; gTotalFoot.textContent = t; gTotalBar.textContent = t;
    itemsCount.textContent = cart.length; itemsCountBar.textContent = cart.length;
    const vendorChosen = !!$('#vendor_id').val();
    submitBtn.disabled = !(vendorChosen && cart.length > 0);
  }

  function redraw() {
    selTable.innerHTML = '';
    cart.forEach((row, idx) => {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${idx+1}</td>
        <td>${row.mobile_name}</td>
        <td>${row.imei}</td>
        <td>${row.storage ?? ''}</td>
        <td>${row.pta_status}</td>
        <td>${fmt(row.purchase_price)}</td>
        <td>${fmt(row.selling_price)}</td>
        <td>${row.images.length} photo(s)</td>
        <td><button class="btn btn-sm btn-danger removeRow" data-index="${idx}">Remove</button></td>
      `;
      selTable.appendChild(tr);
    });
    recalc();
  }

  function required(val, label) {
    const raw = (val ?? '').toString().trim();
    if (raw === '') throw new Error(`${label} is required.`);
    return raw;
  }

  $('#vendor_id')
    .on('change', function () { enableMobilesUI(!!$(this).val()); })
    .on('select2:select', function () { enableMobilesUI(!!$(this).val()); })
    .on('select2:clear', function () { enableMobilesUI(false); });

  enableMobilesUI(!!$('#vendor_id').val());

  searchBox.addEventListener('input', () => {
    const q = searchBox.value.trim().toLowerCase();
    mobBody.querySelectorAll('tr').forEach(tr => {
      const hay = [tr.dataset.name, tr.dataset.company, tr.dataset.group].join(' ');
      tr.style.display = hay.includes(q) ? '' : 'none';
    });
  });

  mobBody.addEventListener('click', (e) => {
    if (!e.target.classList.contains('selectMobile')) return;
    if (!$('#vendor_id').val()) { alert('Select a vendor first.'); return; }

    const tr = e.target.closest('tr');
    current.id = +tr.getAttribute('data-mobile-id');
    current.name = tr.querySelector('td').textContent.trim();

    modalName.textContent = current.name;
    m_imei.value = ''; m_storage.value = ''; m_pta.value = 'PTA';
    m_battery.value = ''; m_battery_cycle.value = '';
    m_date.value = "{{ now()->toDateString() }}";
    m_pprice.value = ''; m_sprice.value = ''; m_desc.value = '';
    m_images.value = ''; m_images_preview.innerHTML = '';

    $('#unitModal').modal('show');
  });

  m_images.addEventListener('change', () => {
    m_images_preview.innerHTML = '';
    const files = Array.from(m_images.files || []).slice(0, 5);
    if (m_images.files.length > 5) alert('Only the first 5 images will be used.');
    files.forEach(f => {
      const url = URL.createObjectURL(f);
      const img = document.createElement('img');
      img.src = url; img.className = 'thumb-preview';
      m_images_preview.appendChild(img);
    });
  });

  m_addBtn.addEventListener('click', () => {
    try {
      const imei = required(m_imei.value, 'IMEI');
      if (cart.some(i => i.imei === imei)) throw new Error('That IMEI is already in this purchase list.');
      const pp = required(m_pprice.value, 'Purchase Price');
      const sp = required(m_sprice.value, 'Selling Price');
      const pdate = required(m_date.value, 'Purchase Date');

      cart.push({
        mobile_id: current.id,
        mobile_name: current.name,
        imei,
        storage: (m_storage.value || '').trim() || null,
        pta_status: m_pta.value,
        battery: (m_battery.value || '').trim() || null,
        battery_cycle: (m_battery_cycle.value || '').trim() || null,
        purchase_date: pdate,
        purchase_price: Number(pp),
        selling_price: Number(sp),
        description: (m_desc.value || '').trim() || null,
        images: Array.from(m_images.files || []).slice(0, 5),
      });

      $('#unitModal').modal('hide');
      redraw();
    } catch (err) {
      alert(err.message || 'Please fill all required fields correctly.');
    }
  });

  document.getElementById('selectedTable').addEventListener('click', (e) => {
    if (!e.target.classList.contains('removeRow')) return;
    cart.splice(+e.target.getAttribute('data-index'), 1);
    redraw();
  });

  submitBtn.addEventListener('click', async () => {
    const vendorId = $('#vendor_id').val();
    if (!vendorId || cart.length === 0) return;

    overlay.style.display = 'flex';
    submitBtn.disabled = true;

    try {
      const fd = new FormData();
      fd.append('vendor_id', vendorId);
      fd.append('pay_amount', payAmount.value || 0);
      cart.forEach((row, idx) => {
        fd.append(`items[${idx}][mobile_id]`, row.mobile_id);
        fd.append(`items[${idx}][imei]`, row.imei);
        if (row.storage) fd.append(`items[${idx}][storage]`, row.storage);
        fd.append(`items[${idx}][pta_status]`, row.pta_status);
        if (row.battery) fd.append(`items[${idx}][battery]`, row.battery);
        if (row.battery_cycle) fd.append(`items[${idx}][battery_cycle]`, row.battery_cycle);
        fd.append(`items[${idx}][purchase_date]`, row.purchase_date);
        fd.append(`items[${idx}][purchase_price]`, row.purchase_price);
        fd.append(`items[${idx}][selling_price]`, row.selling_price);
        if (row.description) fd.append(`items[${idx}][description]`, row.description);
        row.images.forEach(f => fd.append(`items[${idx}][images][]`, f));
      });

      const res = await fetch(`{{ route('mobile.purchase.store') }}`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        body: fd,
      });

      const data = await res.json().catch(() => ({}));

      if (res.status === 422) {
        const errs = data.errors || {};
        const msg = Object.keys(errs).length
          ? Object.keys(errs).map(k => `${k}: ${errs[k].join(', ')}`).join('\n')
          : (data.message || 'Validation failed.');
        throw new Error(msg);
      }
      if (!res.ok) throw new Error(data.message || `HTTP ${res.status}`);

      cart = [];
      payAmount.value = 0;
      redraw();
      $('#vendor_id').val(null).trigger('change');
      searchBox.value = '';

      alert('Mobiles purchased successfully.');
    } catch (err) {
      console.error(err);
      alert(err.message || 'Failed to store purchase.');
    } finally {
      overlay.style.display = 'none';
      submitBtn.disabled = false;
    }
  });
})();
</script>

@endsection
