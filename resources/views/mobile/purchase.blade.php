@extends('user_navbar')
@section('content')

<style>
    .card { border-radius: .65rem; box-shadow: 0 6px 18px rgba(0,0,0,.06); }
    .card-header { background: #f7f9fc; font-weight: 600; }
    .thumb-preview { width: 70px; height: 70px; object-fit: cover; border-radius: 6px; border: 1px solid #ddd; margin-right: 6px; margin-top: 6px; }
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

            <form method="POST" action="{{ route('mobile.purchase.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="card mb-3">
                    <div class="card-header">Bought From</div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Name</label>
                                <input type="text" class="form-control" name="seller_name" value="{{ old('seller_name') }}" required>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label">CNIC</label>
                                <input type="text" class="form-control" name="seller_cnic" value="{{ old('seller_cnic') }}">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Phone Number</label>
                                <input type="text" class="form-control" name="seller_phone" value="{{ old('seller_phone') }}">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Address</label>
                                <input type="text" class="form-control" name="seller_address" value="{{ old('seller_address') }}">
                            </div>
                            <div class="col-12 mb-2">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" name="seller_description" rows="2">{{ old('seller_description') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header">Mobile</div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Mobile Name</label>
                                <input type="text" class="form-control" name="name" value="{{ old('name') }}" required>
                            </div>
                            <div class="col-md-3 mb-2">
                                <label class="form-label">IMEI</label>
                                <input type="text" class="form-control" name="imei" value="{{ old('imei') }}" required>
                            </div>
                            <div class="col-md-3 mb-2">
                                <label class="form-label">IMEI 2 (Optional)</label>
                                <input type="text" class="form-control" name="imei2" value="{{ old('imei2') }}">
                            </div>

                            <div class="col-md-4 mb-2">
                                <label class="form-label">Storage</label>
                                <input type="text" class="form-control" name="storage" placeholder="e.g. 128GB" value="{{ old('storage') }}">
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="form-label">Battery Percentage (Optional)</label>
                                <input type="text" class="form-control" name="battery" placeholder="e.g. 92%" value="{{ old('battery') }}">
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="form-label">Battery Cycle (Optional)</label>
                                <input type="number" min="0" class="form-control" name="battery_cycle" value="{{ old('battery_cycle') }}">
                            </div>

                            <div class="col-md-6 mb-2">
                                <label class="form-label">PTA Status</label>
                                <select class="form-control" name="pta_status" required>
                                    <option value="PTA" @selected(old('pta_status') === 'PTA')>PTA</option>
                                    <option value="Non PTA" @selected(old('pta_status') === 'Non PTA')>Non-PTA</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-2 d-flex align-items-center">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" name="has_box" value="1" id="has_box" @checked(old('has_box'))>
                                    <label class="form-check-label" for="has_box">Box Included</label>
                                </div>
                            </div>

                            <div class="col-md-6 mb-2">
                                <label class="form-label">Purchase Price</label>
                                <input type="number" step="0.01" min="0" class="form-control" name="purchase_price" value="{{ old('purchase_price') }}" required>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Selling Price</label>
                                <input type="number" step="0.01" min="0" class="form-control" name="selling_price" value="{{ old('selling_price') }}" required>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Purchase Date</label>
                                <input type="date" class="form-control" name="purchase_date" value="{{ old('purchase_date', now()->toDateString()) }}" required>
                            </div>

                            <div class="col-12 mb-2">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" name="description" rows="2">{{ old('description') }}</textarea>
                            </div>

                            <div class="col-12 mb-2">
                                <label class="form-label">Images (up to 5)</label>
                                <input type="file" class="form-control" id="images" name="images[]" accept="image/*" multiple>
                                <div id="images_preview" class="mt-2"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-right mb-3">
                    <button type="submit" class="btn btn-success"><i class="fa fa-check-square-o mr-1"></i> Save Purchase</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.getElementById('images').addEventListener('change', function (e) {
        const preview = document.getElementById('images_preview');
        preview.innerHTML = '';
        const files = Array.from(e.target.files || []).slice(0, 5);
        if (e.target.files.length > 5) alert('Only the first 5 images will be used.');
        files.forEach(f => {
            const img = document.createElement('img');
            img.src = URL.createObjectURL(f);
            img.className = 'thumb-preview';
            preview.appendChild(img);
        });
    });
</script>

@endsection
