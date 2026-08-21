@extends('user_navbar')
@section('content')

{{-- Add Modal --}}
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Add Mobile</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form class="form" id="storeMobileForm" action="{{ route('mobile.store') }}" method="post">
                    @csrf
                    <div class="form-body">
                        <div class="mb-1">
                            <label class="form-label">Mobile Name</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="mb-1">
                            <label class="form-label">Company</label>
                            <select class="form-control" name="mobile_company_id" required>
                                <option value="">Select Company</option>
                                @foreach ($companies as $company)
                                <option value="{{ $company->id }}">{{ $company->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-1">
                            <label class="form-label">Condition</label>
                            <select class="form-control" name="mobile_group_id" required>
                                <option value="">Select Condition</option>
                                @foreach ($groups as $group)
                                <option value="{{ $group->id }}">{{ $group->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-1">
                            <label class="form-label">Description (Optional)</label>
                            <input type="text" class="form-control" name="description">
                        </div>
                        <div class="mb-1">
                            <label class="form-label">Low Stock Threshold (Optional)</label>
                            <input type="number" class="form-control" name="min_qty" min="0">
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
{{-- End Add Modal --}}

{{-- Edit Modal --}}
<div class="modal fade" id="exampleModal1" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Edit Mobile</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form class="form" id="editMobileForm" action="{{ route('mobile.update') }}" method="post">
                    @csrf
                    @method('PUT')
                    <div class="form-body">
                        <input type="hidden" name="id" id="id">
                        <div class="mb-1">
                            <label class="form-label">Mobile Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-1">
                            <label class="form-label">Company</label>
                            <select class="form-control" id="mobile_company_id" name="mobile_company_id" required>
                                @foreach ($companies as $company)
                                <option value="{{ $company->id }}">{{ $company->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-1">
                            <label class="form-label">Condition</label>
                            <select class="form-control" id="mobile_group_id" name="mobile_group_id" required>
                                @foreach ($groups as $group)
                                <option value="{{ $group->id }}">{{ $group->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-1">
                            <label class="form-label">Description</label>
                            <input type="text" class="form-control" id="description" name="description">
                        </div>
                        <div class="mb-1">
                            <label class="form-label">Low Stock Threshold</label>
                            <input type="number" class="form-control" id="min_qty" name="min_qty" min="0">
                        </div>
                        <div class="mb-1">
                            <label class="form-label">Edit Password</label>
                            <input type="password" class="form-control" name="password" required>
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
                <i class="bi bi-plus"></i> Add Mobile
            </button>
            <a href="{{ route('mobile.purchase.create') }}" class="btn btn-success ml-1">
                <i class="fa fa-shopping-cart mr-1"></i> Purchase Mobiles
            </a>

            <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-12 latest-update-tracking mt-1">
                <div class="card">
                    <div class="card-header latest-update-heading d-flex justify-content-between">
                        <h4 class="latest-update-heading-title text-bold-500">Mobiles</h4>
                    </div>
                    <div class="table-responsive">
                        <table id="mobileTable" class="table table-striped table-bordered zero-configuration">
                            <thead>
                                <tr>
                                    <th>Created At</th>
                                    <th>Created By</th>
                                    <th>Name</th>
                                    <th>Company</th>
                                    <th>Condition</th>
                                    <th>In Stock</th>
                                    <th>Sold</th>
                                    <th>Description</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($mobiles as $mobile)
                                <tr>
                                    <td>{{ $mobile->created_at }}</td>
                                    <td>{{ $mobile->user->name ?? 'N/A' }}</td>
                                    <td>{{ $mobile->name }}</td>
                                    <td>{{ $mobile->company->name ?? '-' }}</td>
                                    <td>{{ $mobile->group->name ?? '-' }}</td>
                                    <td><strong>{{ $mobile->units->where('status', 'in_stock')->count() }}</strong></td>
                                    <td>{{ $mobile->units->where('status', 'sold')->count() }}</td>
                                    <td>{{ $mobile->description }}</td>
                                    <td>
                                        <a href="" onclick="edit({{ $mobile->id }})" data-toggle="modal"
                                            data-target="#exampleModal1">
                                            <i class="feather icon-edit"></i></a>
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

<script>
    function edit(value) {
        $.ajax({
            type: "GET",
            url: '/mobile/editmobile/' + value,
            success: function (data) {
                $("#editMobileForm").trigger("reset");
                $('#id').val(data.result.id);
                $('#name').val(data.result.name);
                $('#mobile_company_id').val(data.result.mobile_company_id);
                $('#mobile_group_id').val(data.result.mobile_group_id);
                $('#description').val(data.result.description);
                $('#min_qty').val(data.result.min_qty);
            },
            error: function (error) { console.log('Error:', error); }
        });
    }
</script>

@endsection
