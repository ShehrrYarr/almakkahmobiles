@extends('user_navbar')
@section('content')

<style>
    .card { border-radius: 12px; }

    .photo-stack {
        position: relative;
        width: 64px;
        height: 64px;
        margin: 6px 10px 6px 2px;
        cursor: pointer;
    }
    .photo-stack img {
        width: 64px;
        height: 64px;
        object-fit: cover;
        border-radius: 6px;
        border: 2px solid #fff;
        box-shadow: 0 1px 3px rgba(0,0,0,.25);
    }
    .photo-stack .stack-back {
        position: absolute;
        top: 0; left: 0;
        z-index: 1;
    }
    .photo-stack .stack-back.back-2 { transform: translate(6px, 6px); z-index: 0; opacity: .7; }
    .photo-stack .stack-back.back-1 { transform: translate(3px, 3px); z-index: 1; opacity: .85; }
    .photo-stack .stack-front { position: relative; z-index: 2; }
    .photo-stack:hover .stack-front { filter: brightness(0.92); }
    .photo-stack .stack-count {
        position: absolute;
        bottom: -6px; right: -6px;
        z-index: 3;
        background: #556ee6;
        color: #fff;
        font-size: 11px;
        font-weight: 700;
        line-height: 1;
        padding: 4px 6px;
        border-radius: 10px;
        border: 2px solid #fff;
    }

    #unitGalleryModal .modal-body { padding-bottom: 10px; }
    .gallery-main-wrap { position: relative; display: flex; align-items: center; justify-content: center; }
    .gallery-main-wrap img {
        max-width: 100%;
        max-height: 65vh;
        border-radius: 8px;
        object-fit: contain;
    }
    .gallery-nav-btn {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        background: rgba(0,0,0,.45);
        color: #fff;
        border: none;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        font-size: 18px;
        line-height: 1;
        cursor: pointer;
        z-index: 2;
    }
    .gallery-nav-btn:hover { background: rgba(0,0,0,.7); }
    .gallery-prev { left: 8px; }
    .gallery-next { right: 8px; }
    .gallery-counter { text-align: center; margin-top: 8px; color: #6c757d; font-size: .9rem; }
    .gallery-thumbs { display: flex; flex-wrap: wrap; justify-content: center; gap: 6px; margin-top: 10px; }
    .gallery-thumbs img {
        width: 52px; height: 52px; object-fit: cover; border-radius: 5px;
        cursor: pointer; border: 2px solid transparent; opacity: .7;
    }
    .gallery-thumbs img.active { border-color: #556ee6; opacity: 1; }
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

            <div class="ml-1">
                <form method="GET" action="{{ route('mobile.purchase.report') }}" class="mb-3 d-flex align-items-center flex-wrap" style="gap:10px;">
                    <div>
                        <input type="date" class="form-control" name="start_date" value="{{ $start }}" style="max-width: 180px;">
                    </div>
                    <span>to</span>
                    <div>
                        <input type="date" class="form-control" name="end_date" value="{{ $end }}" style="max-width: 180px;">
                    </div>
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('mobile.purchase.report') }}" class="btn btn-secondary">Reset</a>
                </form>
            </div>

            <div class="row ml-1 mb-2">
                <div class="col-12 col-md-4">
                    <h5>Total Purchase Cost: Rs. {{ number_format($totalPurchaseAmount, 2) }}</h5>
                </div>
                <div class="col-12 col-md-4">
                    <h5>Total Selling Value: Rs. {{ number_format($totalSellingAmount, 2) }}</h5>
                </div>
            </div>

            <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-12 latest-update-tracking mt-1">
                <div class="card">
                    <div class="card-header latest-update-heading d-flex justify-content-between">
                        <h4 class="latest-update-heading-title text-bold-500">Mobile Purchase Report</h4>
                    </div>
                    <div class="table-responsive">
                        <table id="mobilePurchaseReportTable" class="table table-striped table-bordered zero-configuration">
                            <thead>
                                <tr>
                                    <th>Purchase Date</th>
                                    <th>Photos</th>
                                    <th>Name</th>
                                    <th>IMEI</th>
                                    <th>IMEI 2</th>
                                    <th>Storage</th>
                                    <th>PTA</th>
                                    <th>Box</th>
                                    <th>Purchase Price</th>
                                    <th>Selling Price</th>
                                    <th>Bought From</th>
                                    <th>Seller Phone</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($units as $unit)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($unit->purchase_date)->format('d M Y') }}</td>
                                    <td>
                                        @if($unit->images->isNotEmpty())
                                        @php
                                        $urls = $unit->images->map(fn($img) => asset('storage/' . $img->path));
                                        @endphp
                                        <div class="photo-stack" data-images='@json($urls)' onclick="openUnitGallery(this, '{{ $unit->imei }}')">
                                            @if($urls->count() > 2)
                                            <img src="{{ $urls[0] }}" class="stack-back back-2" alt="">
                                            @endif
                                            @if($urls->count() > 1)
                                            <img src="{{ $urls[0] }}" class="stack-back back-1" alt="">
                                            @endif
                                            <img src="{{ $urls[0] }}" class="stack-front" alt="Unit Photo">
                                            @if($urls->count() > 1)
                                            <span class="stack-count">+{{ $urls->count() }}</span>
                                            @endif
                                        </div>
                                        @else
                                        <span class="text-muted">No photos</span>
                                        @endif
                                    </td>
                                    <td>{{ $unit->name }}</td>
                                    <td>{{ $unit->imei }}</td>
                                    <td>{{ $unit->imei2 ?: '-' }}</td>
                                    <td>{{ $unit->storage ?: '-' }}</td>
                                    <td>{{ $unit->pta_status }}</td>
                                    <td>{{ $unit->has_box ? 'Yes' : 'No' }}</td>
                                    <td>Rs. {{ number_format($unit->purchase_price, 2) }}</td>
                                    <td>Rs. {{ number_format($unit->selling_price, 2) }}</td>
                                    <td>{{ $unit->seller_name }}</td>
                                    <td>{{ $unit->seller_phone ?: '-' }}</td>
                                    <td>
                                        @if($unit->status === 'sold')
                                        <span class="badge badge-secondary">Sold</span>
                                        @else
                                        <span class="badge badge-success">In Stock</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="12" class="text-center">No purchases found.</td>
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

{{-- Unit Photo Gallery Modal --}}
<div class="modal fade" id="unitGalleryModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="unitGalleryTitle">Unit Photos</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="gallery-main-wrap">
                    <button type="button" class="gallery-nav-btn gallery-prev" id="galleryPrevBtn" onclick="galleryPrev()">&#10094;</button>
                    <img id="galleryMainImage" src="" alt="Unit Photo">
                    <button type="button" class="gallery-nav-btn gallery-next" id="galleryNextBtn" onclick="galleryNext()">&#10095;</button>
                </div>
                <div class="gallery-counter" id="galleryCounter"></div>
                <div class="gallery-thumbs" id="galleryThumbs"></div>
            </div>
        </div>
    </div>
</div>

<script>
    let _galleryImages = [];
    let _galleryIndex = 0;

    function openUnitGallery(el, imei) {
        _galleryImages = JSON.parse(el.getAttribute('data-images'));
        _galleryIndex = 0;
        document.getElementById('unitGalleryTitle').textContent = 'Unit Photos — IMEI ' + imei;
        renderGallery();
        $('#unitGalleryModal').modal('show');
    }

    function renderGallery() {
        document.getElementById('galleryMainImage').src = _galleryImages[_galleryIndex];
        document.getElementById('galleryCounter').textContent = (_galleryIndex + 1) + ' / ' + _galleryImages.length;

        const multi = _galleryImages.length > 1;
        document.getElementById('galleryPrevBtn').style.display = multi ? '' : 'none';
        document.getElementById('galleryNextBtn').style.display = multi ? '' : 'none';

        const thumbsEl = document.getElementById('galleryThumbs');
        if (!multi) { thumbsEl.innerHTML = ''; return; }
        thumbsEl.innerHTML = _galleryImages.map((src, i) =>
            `<img src="${src}" class="${i === _galleryIndex ? 'active' : ''}" onclick="galleryGoTo(${i})">`
        ).join('');
    }

    function galleryPrev() { _galleryIndex = (_galleryIndex - 1 + _galleryImages.length) % _galleryImages.length; renderGallery(); }
    function galleryNext() { _galleryIndex = (_galleryIndex + 1) % _galleryImages.length; renderGallery(); }
    function galleryGoTo(i) { _galleryIndex = i; renderGallery(); }

    document.addEventListener('keydown', function (e) {
        if (!$('#unitGalleryModal').hasClass('show')) return;
        if (e.key === 'ArrowLeft') galleryPrev();
        if (e.key === 'ArrowRight') galleryNext();
    });

    $(document).ready(function () {
        $('#mobilePurchaseReportTable').DataTable({ order: [[0, 'desc']] });
    });
</script>

@endsection
