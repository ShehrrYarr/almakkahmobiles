@extends('user_navbar')
@section('content')

<div class="app-content content">
    <div class="content-overlay"></div>
    <div class="content-wrapper">
        <div class="content-header row">
        </div>
        <div class="content-body">

            <div class="content-header row">
            </div>


            {{-- Image Banner --}}
            <div class="mb-2">
                <img src="{{ asset('images/banner.jpg') }}" alt=" Banner" class="img-fluid shadow rounded"
                    style="width: 100%; max-height: 250px; object-fit: cover;">
            </div>

            <!-- Grouped multiple cards for statistics starts here -->
            <div class="row grouped-multiple-statistics-card">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-6 col-xl-3 col-sm-6 col-12">
                                    <div
                                        class="d-flex align-items-start mb-sm-1 mb-xl-0 border-right-blue-grey border-right-lighten-5">
                                        <span class="card-icon primary d-flex justify-content-center mr-3">
                                            <a href="/accessories"> <i
                                                    class="icon p-1 fa fa-mobile customize-icon font-large-5 p-1"></i></a>
                                        </span>
                                        <div class="stats-amount mr-3">
                                            <h3 class="heading-text text-bold-600">{{$totalAccessoryCount}}</h3>
                                            <p class="sub-heading">Available Accessories</p>
                                        </div>
                                        <!-- <span class="inc-dec-percentage">
                                                                        <small class="success"><i class="fa fa-long-arrow-up"></i> 5.2%</small>
                                                                    </span> -->
                                    </div>
                                </div>

                                <div class="col-lg-6 col-xl-3 col-sm-6 col-12">
                                    <div class="d-flex align-items-start border-right-blue-grey border-right-lighten-5">
                                        <span class="card-icon success d-flex justify-content-center mr-3">
                                            <a href="/sales/all"> <i
                                                    class="icon p-1 fa fa-mobile customize-icon font-large-5 p-1"></i></a>
                                        </span>
                                        <div class="stats-amount mr-3">
                                            <h3 class="heading-text text-bold-600">{{$totalSoldAccessories}}</h3>
                                            <p class="sub-heading">Sold Accessories</p>
                                        </div>
                                        <!-- <span class="inc-dec-percentage">
                                                                        <small class="success"><i class="fa fa-long-arrow-up"></i> 10.0%</small>
                                                                    </span> -->
                                    </div>
                                </div>
                                <div class="col-lg-6 col-xl-3 col-sm-6 col-12">
                                    <div class="d-flex align-items-start border-right-blue-grey border-right-lighten-5">
                                        <span class="card-icon success d-flex justify-content-center mr-3">
                                            <a href="/sales/pending"> <i
                                                    class="icon p-1 fa fa-cart-plus customize-icon font-large-5 p-1"></i></a>
                                        </span>
                                        <div class="stats-amount mr-3">
                                            <h3 class="heading-text text-bold-600">{{$totalPendingSalesCount}}</h3>
                                            <p class="sub-heading">Pending Sales</p>
                                        </div>
                                        <!-- <span class="inc-dec-percentage">
                                                                        <small class="success"><i class="fa fa-long-arrow-up"></i> 10.0%</small>
                                                                    </span> -->
                                    </div>
                                </div>
                                <div class="col-lg-6 col-xl-3 col-sm-6 col-12">
                                    <div class="d-flex align-items-start border-right-blue-grey border-right-lighten-5">
                                        <span class="card-icon success d-flex justify-content-center mr-3">
                                            <a href="/sales/approved"> <i
                                                    class="icon p-1 fa fa-cart-plus customize-icon font-large-5 p-1"></i></a>
                                        </span>
                                        <div class="stats-amount mr-3">
                                            <h3 class="heading-text text-bold-600">{{$totalApprovedSalesCount}}</h3>
                                            <p class="sub-heading">Approved Sales</p>
                                        </div>
                                        <!-- <span class="inc-dec-percentage">
                                                                        <small class="success"><i class="fa fa-long-arrow-up"></i> 10.0%</small>
                                                                    </span> -->
                                    </div>
                                </div>



                            </div>
                        </div>
                    </div>
                </div>
            </div>



            @if($lowStockAccessories->count())
            <div class="card border-0 shadow-sm mt-2 mb-2" id="lowStockBox">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap" style="background:#fff3cd; border-bottom:2px solid #ffc107; gap:10px;">
                    <div class="d-flex align-items-center flex-wrap" style="gap:10px;">
                        <h5 class="mb-0 font-weight-bold text-dark">
                            <i class="fas fa-exclamation-triangle text-warning mr-1"></i>
                            Low Stock Reminder
                            <span class="badge badge-danger ml-1">{{ $lowStockAccessories->count() }}</span>
                        </h5>
                        <small id="lowStockFilterBadge" class="text-muted font-italic"></small>
                    </div>

                    <div class="d-flex align-items-center flex-wrap" style="gap:6px;">
                        {{-- Company chips --}}
                        @if(isset($lowStockCompanies) && $lowStockCompanies->count())
                        <span class="text-muted small font-weight-bold">Company:</span>
                        @foreach($lowStockCompanies as $c)
                        <button type="button" class="btn btn-sm btn-outline-warning chip chip-company"
                            data-type="company" data-id="{{ $c['id'] }}"
                            style="border-radius:999px; font-size:0.78em; padding:2px 10px;">
                            {{ $c['name'] }} <span class="badge badge-warning text-dark ml-1">{{ $c['count'] }}</span>
                        </button>
                        @endforeach
                        @endif

                        {{-- Group chips --}}
                        @if(isset($lowStockGroups) && $lowStockGroups->count())
                        <span class="text-muted small font-weight-bold ml-1">Group:</span>
                        @foreach($lowStockGroups as $g)
                        <button type="button" class="btn btn-sm btn-outline-secondary chip chip-group"
                            data-type="group" data-id="{{ $g['id'] }}"
                            style="border-radius:999px; font-size:0.78em; padding:2px 10px;">
                            {{ $g['name'] }} <span class="badge badge-secondary ml-1">{{ $g['count'] }}</span>
                        </button>
                        @endforeach
                        @endif

                        <button type="button" id="clearLowStockFilter"
                            class="btn btn-sm btn-outline-secondary"
                            style="border-radius:999px; display:none;">
                            <i class="fa fa-times"></i> Clear
                        </button>

                        <button id="toggleStockBtn" class="btn btn-sm btn-dark ml-1">
                            <i class="fa fa-expand mr-1"></i> Expand
                        </button>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div style="overflow:hidden; transition:max-height 0.4s ease;" id="lowStockTableWrapper">
                        <table class="table table-hover table-bordered mb-0">
                            <thead style="background:#f8f9fa;">
                                <tr>
                                    <th class="text-center" style="width:50px;">#</th>
                                    <th>Accessory</th>
                                    <th>Company</th>
                                    <th>Group</th>
                                    <th class="text-center">Min Qty</th>
                                    <th class="text-center">In Stock</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody id="low-stock-tbody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
            @else
            <div class="alert alert-success d-flex align-items-center mt-2 mb-2" role="alert">
                <i class="fa fa-check-circle mr-2" style="font-size:1.2em;"></i>
                <span>All accessories are above their minimum quantity.</span>
            </div>
            @endif

            @if(auth()->user()->isAdmin())
            <div class="row grouped-multiple-statistics-card">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-6 col-xl-3 col-sm-6 col-12">
                                    <div
                                        class="d-flex align-items-start mb-sm-1 mb-xl-0 border-right-blue-grey border-right-lighten-5">

                                        <div class="stats-amount mr-3">
                                            <h3 class="heading-text text-bold-600">Rs. {{
                                                number_format($totalAccessoryAmount) }}
                                            </h3>
                                            <p class="sub-heading">Total Accessory Cost</p>
                                        </div>
                                        <!-- <span class="inc-dec-percentage">
                                                                                        <small class="success"><i class="fa fa-long-arrow-up"></i> 5.2%</small>
                                                                                    </span> -->
                                    </div>
                                </div>
                                <div class="col-lg-6 col-xl-3 col-sm-6 col-12">
                                    <div
                                        class="d-flex align-items-start mb-sm-1 mb-xl-0 border-right-blue-grey border-right-lighten-5">

                                        <div class="stats-amount mr-3">
                                            <h3 class="heading-text text-bold-600">
                                                Rs.{{number_format($totalSoldAmount)}}</h3>
                                            <p class="sub-heading">Total Sold Accessory</p>
                                        </div>
                                        <!-- <span class="inc-dec-percentage">
                                                                                        <small class="danger"><i class="fa fa-long-arrow-down"></i> 2.0%</small>
                                                                                    </span> -->
                                    </div>
                                </div>

                                <div class="col-lg-6 col-xl-3 col-sm-6 col-12">
                                    <div
                                        class="d-flex align-items-start mb-sm-1 mb-xl-0 border-right-blue-grey border-right-lighten-5">

                                        <div class="stats-amount mr-3">
                                            <h3 class="heading-text text-bold-600">
                                                Rs.{{ number_format($totalReceivable) }}</h3>
                                            <p class="sub-heading">Total Receivable</p>
                                        </div>
                                        <!-- <span class="inc-dec-percentage">
                                                                                        <small class="success"><i class="fa fa-long-arrow-up"></i> 5.2%</small>
                                                                                    </span> -->
                                    </div>
                                </div>
                                <div class="col-lg-6 col-xl-3 col-sm-6 col-12">
                                    <div
                                        class="d-flex align-items-start mb-sm-1 mb-xl-0 border-right-blue-grey border-right-lighten-5">

                                        <div class="stats-amount mr-3">
                                            <h3 class="heading-text text-bold-600">
                                                Rs.{{ number_format($todayTotalDebit) }}</h3>
                                            <p class="sub-heading">Today's Total Debit</p>
                                        </div>
                                        <!-- <span class="inc-dec-percentage">
                                                                                        <small class="success"><i class="fa fa-long-arrow-up"></i> 5.2%</small>
                                                                                    </span> -->
                                    </div>
                                </div>
                                <div class="col-lg-6 col-xl-3 col-sm-6 col-12">
                                    <div
                                        class="d-flex align-items-start mb-sm-1 mb-xl-0 border-right-blue-grey border-right-lighten-5">

                                        <div class="stats-amount mr-3">
                                            <h3 class="heading-text text-bold-600">
                                                Rs.{{ number_format($todayTotalCredit) }}</h3>
                                            <p class="sub-heading">Today's Total Credit</p>
                                        </div>
                                        <!-- <span class="inc-dec-percentage">
                                                                                        <small class="success"><i class="fa fa-long-arrow-up"></i> 5.2%</small>
                                                                                    </span> -->
                                    </div>
                                </div>
                                <div class="col-lg-6 col-xl-3 col-sm-6 col-12">
                                    <div
                                        class="d-flex align-items-start mb-sm-1 mb-xl-0 border-right-blue-grey border-right-lighten-5">

                                        <div class="stats-amount mr-3">
                                            <h3 class="heading-text text-bold-600">
                                                Rs.{{ number_format($totalPendingSales) }}</h3>
                                            <p class="sub-heading">Total Pending Sales</p>
                                        </div>
                                        <!-- <span class="inc-dec-percentage">
                                                                                        <small class="success"><i class="fa fa-long-arrow-up"></i> 5.2%</small>
                                                                                    </span> -->
                                    </div>
                                </div>


                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row grouped-multiple-statistics-card">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">




                                <div class="col-lg-6 col-xl-3 col-sm-6 col-12">
                                    <div
                                        class="d-flex align-items-start mb-sm-1 mb-xl-0 border-right-blue-grey border-right-lighten-5">

                                        <div class="stats-amount mr-3">
                                            <h3 class="heading-text text-bold-600">
                                                Rs.{{ number_format($totalApprovedSales) }}</h3>
                                            <p class="sub-heading">Total Approved Sales</p>
                                        </div>
                                        <!-- <span class="inc-dec-percentage">
                                                                                                                        <small class="success"><i class="fa fa-long-arrow-up"></i> 5.2%</small>
                                                                                                                    </span> -->
                                    </div>
                                </div>


                            </div>
                        </div>
                    </div>
                </div>
            </div>








            @endif

            {{-- Credit Entries Table (admin only) --}}
            @if(auth()->user()->isAdmin())
            <div class="row mt-1">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="card-title mb-0">
                                Today's Credit Entries
                                <span class="badge badge-success ml-1">{{ $allCreditEntries->count() }}</span>
                            </h4>
                            <span class="text-bold-600 text-success" style="font-size:1.1em;">
                                Total: Rs. {{ number_format($allCreditEntries->sum('Credit')) }}
                            </span>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered mb-0">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Vendor</th>
                                        <th>Description</th>
                                        <th>Amount (CR)</th>
                                        <th>Added By</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($allCreditEntries as $entry)
                                    <tr>
                                        <td>{{ $entry->created_at->format('d M Y, h:i A') }}</td>
                                        <td>{{ optional($entry->vendor)->name ?? '—' }}</td>
                                        <td>{{ $entry->description ?? '—' }}</td>
                                        <td class="text-success text-bold-600">Rs. {{ number_format($entry->Credit) }}</td>
                                        <td>{{ optional($entry->creator)->name ?? '—' }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-2">No credit entries found.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                                @if($allCreditEntries->count())
                                <tfoot>
                                    <tr>
                                        <th colspan="3" class="text-right">Total</th>
                                        <th class="text-success">Rs. {{ number_format($allCreditEntries->sum('Credit')) }}</th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            @endif





        </div>
    </div>
</div>
</div>

{{-- <script>
    let lowStockAccessories = @json($lowStockAccessories);
    let showingAll = false;
    
    function renderLowStockTable(showAll = false) {
    let tbody = document.getElementById('low-stock-tbody');
    tbody.innerHTML = ''; // Clear
    
    let dataToShow = showAll ? lowStockAccessories : lowStockAccessories.slice(0, 5);
    
    dataToShow.forEach(item => {
    let row = document.createElement('tr');
    row.innerHTML = `
    <td>${item.name}</td>
    <td>${item.min_qty}</td>
    <td class="low-stock-count">${item.stock}</td>
    <td class="low-stock-status">Restock Needed!</td>
    `;
    tbody.appendChild(row);
    });
    }
    
    document.addEventListener('DOMContentLoaded', function () {
    renderLowStockTable();
    
    let btn = document.getElementById('toggleStockBtn');
    let wrapper = document.getElementById('lowStockTableWrapper');
    
    // Initial wrapper max-height for collapsed state
    let rowHeight = 38; // Estimate row height (px). Adjust if needed for your design.
    let collapsedHeight = rowHeight * 5 + 40; // 5 rows + header
    let expandedHeight = rowHeight * (lowStockAccessories.length) + 40; // all rows + header
    
    wrapper.style.maxHeight = collapsedHeight + 'px';
    
    if (!btn) return;
    
    btn.addEventListener('click', function () {
    showingAll = !showingAll;
    renderLowStockTable(showingAll);
    
    // Animate the height
    if (showingAll) {
    wrapper.style.maxHeight = expandedHeight + 'px';
    btn.textContent = 'Minimize';
    } else {
    wrapper.style.maxHeight = collapsedHeight + 'px';
    btn.textContent = 'Maximize';
    }
    });
    
    // Hide the button if 5 or fewer
    if (lowStockAccessories.length <= 5) { btn.style.display='none' ; } });


</script> --}}

<script>
    // Data from controller
  const LOW_STOCK = @json($lowStockAccessories); // [{id,name,stock,min_qty,company_id,company,group_id,group}]
  let showingAll = false;
  let activeFilter = null; // { type: 'company'|'group', id: number|null }

  const tbody   = document.getElementById('low-stock-tbody');
  const wrapper = document.getElementById('lowStockTableWrapper');
  const toggleBtn = document.getElementById('toggleStockBtn');
  const clearBtn  = document.getElementById('clearLowStockFilter');
  const filterBadge = document.getElementById('lowStockFilterBadge');

  function applyFilter(data) {
    if (!activeFilter) return data;
    if (activeFilter.type === 'company') {
      return data.filter(x => String(x.company_id) === String(activeFilter.id));
    }
    if (activeFilter.type === 'group') {
      return data.filter(x => String(x.group_id) === String(activeFilter.id));
    }
    return data;
  }

  function renderLowStockTable(showAll = false) {
    tbody.innerHTML = '';

    let data = applyFilter(LOW_STOCK);
    let dataToShow = showAll ? data : data.slice(0, 5);

    dataToShow.forEach((item, index) => {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td class="text-center text-muted">${index + 1}</td>
        <td><strong>${item.name}</strong></td>
        <td>${item.company || '-'}</td>
        <td>${item.group || '-'}</td>
        <td class="text-center">${item.min_qty}</td>
        <td class="text-center"><span class="badge badge-danger" style="font-size:0.95em;">${item.stock}</span></td>
        <td class="text-center"><span class="badge badge-warning text-dark" style="font-size:0.85em;">Restock Needed</span></td>
      `;
      tbody.appendChild(tr);
    });

    // Empty state
    if (dataToShow.length === 0) {
      const tr = document.createElement('tr');
      tr.innerHTML = `<td colspan="6" style="text-align:center; padding:10px;">No items match this filter.</td>`;
      tbody.appendChild(tr);
    }

    // Filter badge + clear button visibility
    if (activeFilter) {
      clearBtn.style.display = '';
      const label = activeFilter.type === 'company' ? 'Company' : 'Group';
      const name = (data[0]?.[activeFilter.type] ?? '').toString();
      filterBadge.textContent = `(${label}: ${name})`;
    } else {
      clearBtn.style.display = 'none';
      filterBadge.textContent = '';
    }
  }

  // Expand/Collapse animation
  document.addEventListener('DOMContentLoaded', () => {
    renderLowStockTable(false);

    const rowHeight = 42; // tweak if needed
    const collapsedHeight = rowHeight * 5 + 44; // 5 rows + header
    // dynamic expanded height based on filtered set
    function recomputeExpandedHeight() {
      const count = applyFilter(LOW_STOCK).length || 1;
      return rowHeight * count + 44;
    }

    wrapper.style.maxHeight = collapsedHeight + 'px';

    toggleBtn.addEventListener('click', () => {
      showingAll = !showingAll;
      renderLowStockTable(showingAll);
      wrapper.style.maxHeight = (showingAll ? recomputeExpandedHeight() : collapsedHeight) + 'px';
      toggleBtn.innerHTML = showingAll ? '<i class="fa fa-compress mr-1"></i> Collapse' : '<i class="fa fa-expand mr-1"></i> Expand';
    });

    // Chip clicks
    document.querySelectorAll('.chip').forEach(chip => {
      chip.addEventListener('click', () => {
        activeFilter = { type: chip.dataset.type, id: chip.dataset.id };
        showingAll = true; // auto-expand when filtering
        renderLowStockTable(true);
        wrapper.style.maxHeight = recomputeExpandedHeight() + 'px';
        toggleBtn.innerHTML = '<i class="fa fa-compress mr-1"></i> Collapse';
      });
    });

    // Clear filter
    clearBtn.addEventListener('click', () => {
      activeFilter = null;
      showingAll = false;
      renderLowStockTable(false);
      wrapper.style.maxHeight = collapsedHeight + 'px';
      toggleBtn.innerHTML = '<i class="fa fa-expand mr-1"></i> Expand';
    });

    // Hide Expand button if <= 5 items initially
    if (LOW_STOCK.length <= 5) { toggleBtn.style.display = 'none'; }
  });
</script>




@endsection