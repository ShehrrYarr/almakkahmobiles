@extends('user_navbar')
@section('content')

{{-- Add Shop Modal --}}
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Shop</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <form method="post" action="{{ route('shops.store') }}">
                    @csrf
                    <div class="mb-1">
                        <label class="form-label">Shop Name</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-1">
                        <label class="form-label">URL Slug (optional — auto-generated from name if left blank)</label>
                        <input type="text" class="form-control" name="slug" placeholder="e.g. karachi-branch" pattern="[A-Za-z0-9_\-]+">
                    </div>
                    <div class="form-actions mt-2">
                        <button type="button" class="btn btn-warning mr-1" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Edit Shop Modal --}}
<div class="modal fade" id="exampleModal1" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Shop</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <form method="post" action="{{ route('shops.update') }}">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="id" id="id">
                    <div class="mb-1">
                        <label class="form-label">Shop Name</label>
                        <input type="text" class="form-control" id="vname" name="name" required>
                    </div>
                    <div class="mb-1">
                        <label class="form-label">Active</label>
                        <select class="form-control" id="v_is_active" name="is_active">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                    <div class="form-actions mt-2">
                        <button type="button" class="btn btn-warning mr-1" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .card { border-radius: 12px; }
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

            <button type="button" class="btn btn-primary ml-1 mb-1" data-toggle="modal" data-target="#exampleModal">
                <i class="bi bi-plus"></i> Add Shop
            </button>

            <div class="col-12 mt-1">
                <div class="card">
                    <div class="card-header">
                        <h4 class="text-bold-500">Mobile Shops</h4>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered zero-configuration">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Login URL</th>
                                    <th>Salesmen</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($shops as $shop)
                                @php $loginUrl = url('/shop/'.$shop->slug.'/login'); @endphp
                                <tr>
                                    <td>{{ $shop->name }}</td>
                                    <td>
                                        <a href="{{ $loginUrl }}" target="_blank">{{ $loginUrl }}</a>
                                    </td>
                                    <td>{{ $shop->users_count }}</td>
                                    <td>{{ $shop->is_active ? 'Active' : 'Inactive' }}</td>
                                    <td>
                                        <a href="{{ route('shops.enter', $shop->id) }}" class="btn btn-sm btn-success mr-1">
                                            <i class="fa fa-sign-in mr-1"></i> Enter
                                        </a>
                                        <a href="#" onclick="edit({{ $shop->id }})" data-toggle="modal" data-target="#exampleModal1">
                                            <i class="feather icon-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center">No shops yet. Click "Add Shop" to create one.</td>
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

<script>
    function edit(id) {
        $.ajax({
            type: 'GET',
            url: '/shops/' + id + '/edit',
            success: function (data) {
                var s = data.result;
                $('#id').val(s.id);
                $('#vname').val(s.name);
                $('#v_is_active').val(s.is_active ? '1' : '0');
            },
            error: function (err) { console.log('Error loading shop:', err); }
        });
    }
</script>

@endsection
