@extends('user_navbar')
@section('content')

{{-- Modal --}}
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Add Mobile Vendor</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form class="form" id="storeMobile" action="{{ route('mobile.storeVendor') }}" method="post"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="form-body">
                        <div class="mb-1">
                            <label for="name" class="form-label">Vendor Name</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="mb-1">
                            <label for="office_address" class="form-label">Office Address</label>
                            <input type="text" class="form-control" name="office_address">
                        </div>
                        <div class="mb-1">
                            <label for="city" class="form-label">City</label>
                            <input type="text" class="form-control" name="city">
                        </div>
                        <div class="mb-1">
                            <label for="mobile_no" class="form-label">Mobile #</label>
                            <input type="number" id="mobileNo" class="form-control" name="mobile_no" required>
                        </div>
                        <div class="mb-1">
                            <label for="CNIC" class="form-label">CNIC</label>
                            <input type="text" class="form-control" name="CNIC">
                        </div>
                        <div class="mb-1">
                            <label for="picture" class="form-label">Picture</label>
                            <input type="file" class="form-control" name="picture" id="picture" accept="image/*"
                                capture="environment">
                        </div>
                        <div class="mb-1">
                            <label class="form-label">Preview</label>
                            <div>
                                <img id="preview" src="#" alt="Image Preview"
                                    style="display:none; max-width: 100%; height: auto;" />
                            </div>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn btn-warning mr-1" data-dismiss="modal">
                            <i class="feather icon-x"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-check-square-o"></i> Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
{{-- End Modal --}}

{{-- Edit Modal --}}
<div class="modal fade" id="exampleModal1" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Edit Mobile Vendor</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form class="form" id="editmobile" action="{{ route('mobile.updateVendor') }}" method="post"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="form-body">
                        <div class="mb-1">
                            <label for="name" class="form-label">Vendor Name</label>
                            <input class="form-control" type="hidden" name="id" id="id" value="Update">
                            <input type="text" class="form-control" id="vname" name="name" required>
                        </div>
                        <div class="mb-1">
                            <label for="office_address" class="form-label">Office Address</label>
                            <input type="text" class="form-control" id="voffice_address" name="office_address">
                        </div>
                        <div class="mb-1">
                            <label for="city" class="form-label">City</label>
                            <input type="text" class="form-control" id="vcity" name="city">
                        </div>
                        <div class="mb-1">
                            <label for="mobile_no" class="form-label">Mobile #</label>
                            <input type="text" class="form-control" id="vmobile_no" name="mobile_no" required>
                        </div>
                        <div class="mb-1">
                            <label for="CNIC" class="form-label">CNIC</label>
                            <input type="text" class="form-control" id="vCNIC" name="CNIC">
                        </div>
                        <div class="mb-1">
                            <label for="vendor_picture" class="form-label">Vendor Picture</label><br>
                            <img id="edit-picture-preview" src="" alt="Vendor Picture" width="100" class="mb-2"
                                onerror="this.src='{{ asset('images/placeholder.png') }}'; this.alt='N/A';">
                            <input type="file" class="form-control" name="picture" id="edit_vendor_picture"
                                accept="image/*">
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn btn-warning mr-1" data-dismiss="modal">
                            <i class="feather icon-x"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-check-square-o"></i> Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
{{-- End Edit Modal --}}

<style>
    .card { border-radius: 12px; }
</style>

<div class="app-content content">
    <div class="content-overlay"></div>
    <div class="content-wrapper">
        <div class="content-header row"></div>
        <div class="content-body">
            @if (session('success'))
            <div class="alert alert-success" id="successMessage">{{ session('success') }}</div>
            @endif
            @if (session('danger'))
            <div class="alert alert-danger" id="dangerMessage" style="color: red;">{{ session('danger') }}</div>
            @endif

            <button type="button" class="btn btn-primary ml-1" data-toggle="modal" data-target="#exampleModal">
                <i class="bi bi-plus"></i> Add Mobile Vendor
            </button>

            <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-12 latest-update-tracking mt-1">
                <div class="card">
                    <div class="card-header latest-update-heading d-flex justify-content-between">
                        <h4 class="latest-update-heading-title text-bold-500">Available Mobile Vendors</h4>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered zero-configuration">
                            <thead>
                                <tr>
                                    <th>Created At</th>
                                    <th>Accounts</th>
                                    <th>Picture</th>
                                    <th>Vendor Name</th>
                                    <th>Office Address</th>
                                    <th>City</th>
                                    <th>Mobile Number</th>
                                    <th>CNIC</th>
                                    <th>Action</th>
                                    <th>Created by</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($vendors as $key)
                                <tr>
                                    <td>{{ $key->created_at }}</td>
                                    <td>
                                        <a href="{{ route('mobile.showAccounts', $key->id) }}" class="btn btn-sm btn-primary">
                                            <i class="fa fa-book"></i>
                                        </a>
                                    </td>
                                    <td>
                                        @if($key->picture)
                                        <a href="{{ asset('storage/' . $key->picture) }}" target="_blank">
                                            <img src="{{ asset('storage/' . $key->picture) }}" alt="Vendor Picture"
                                                width="100" style="cursor: zoom-in;">
                                        </a>
                                        @else
                                        N/A
                                        @endif
                                    </td>
                                    <td>{{ $key->name }}</td>
                                    <td>{{ $key->office_address }}</td>
                                    <td>{{ $key->city }}</td>
                                    <td>{{ $key->mobile_no }}</td>
                                    <td>{{ $key->CNIC }}</td>
                                    <td>
                                        <a href="" onclick="edit({{ $key->id }})" data-toggle="modal"
                                            data-target="#exampleModal1">
                                            <i class="feather icon-edit"></i></a>
                                    </td>
                                    <td>{{ $key->creator->name ?? 'N/A' }}</td>
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

<script>
    document.getElementById('mobileNo').addEventListener('input', function (e) {
        this.value = this.value.replace(/\D/g, '');
        if (!this.value.startsWith('923')) {
            this.value = '923' + this.value.replace(/^923*/, '');
        }
        if (this.value.length > 12) {
            this.value = this.value.slice(0, 12);
        }
    });

    function edit(id) {
        $.ajax({
            type: "GET",
            url: '/mobile/editvendor/' + id,
            success: function (data) {
                $("#editmobile").trigger("reset");
                $('#id').val(data.result.id);
                $('#vname').val(data.result.name);
                $('#vmobile_no').val(data.result.mobile_no);
                $('#vcity').val(data.result.city);
                $('#voffice_address').val(data.result.office_address);
                $('#vCNIC').val(data.result.CNIC);

                if (data.result.picture) {
                    $('#edit-picture-preview').attr('src', '/storage/' + data.result.picture);
                } else {
                    $('#edit-picture-preview').attr('src', '{{ asset("images/placeholder.png") }}');
                    $('#edit-picture-preview').attr('alt', 'N/A');
                }
            },
            error: function (error) { console.log('Error:', error); }
        });
    }

    $('#edit_vendor_picture').on('change', function () {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) { $('#edit-picture-preview').attr('src', e.target.result); };
            reader.readAsDataURL(file);
        }
    });

    document.getElementById("picture").addEventListener("change", function (event) {
        const input = event.target;
        const preview = document.getElementById("preview");
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function (e) {
                preview.src = e.target.result;
                preview.style.display = "block";
            };
            reader.readAsDataURL(input.files[0]);
        } else {
            preview.src = "#";
            preview.style.display = "none";
        }
    });
</script>

@endsection
