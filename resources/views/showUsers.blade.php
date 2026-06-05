@extends('user_navbar')
@section('content')

    {{-- Add User Modal --}}
    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add User</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('storeUser') }}" method="post">
                        @csrf
                        <div class="mb-1">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="mb-1">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="mb-1">
                            <label class="form-label">Password</label>
                            <input type="text" class="form-control" name="password" required>
                        </div>
                        <div class="mb-1">
                            <label class="form-label">Role</label>
                            <select name="role" id="add_role" class="form-control" onchange="toggleAddPermissions(this.value)">
                                <option value="salesman">Salesman</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>

                        <div id="add_permissions_section" class="mt-2 p-2 border rounded">
                            <label class="form-label font-weight-bold">Permissions</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="permissions[]" value="approve_sales" id="add_perm_approve">
                                <label class="form-check-label" for="add_perm_approve">Approve Sales</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="permissions[]" value="process_returns" id="add_perm_returns">
                                <label class="form-check-label" for="add_perm_returns">Process Returns / Refunds</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="permissions[]" value="manage_inventory" id="add_perm_inventory">
                                <label class="form-check-label" for="add_perm_inventory">Manage Inventory</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="permissions[]" value="view_vendor_accounts" id="add_perm_vendor">
                                <label class="form-check-label" for="add_perm_vendor">View Vendor Accounts &amp; Reports</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="permissions[]" value="delete_records" id="add_perm_delete">
                                <label class="form-check-label" for="add_perm_delete">Delete Records</label>
                            </div>
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
    {{-- End Add User Modal --}}


    {{-- Edit User Modal --}}
    <div class="modal fade" id="exampleModal1" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit User</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('updateUser') }}" method="post">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="id" id="id">

                        <div class="mb-1">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control" id="vname" name="name" required>
                        </div>
                        <div class="mb-1">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" id="vemail" name="email" required>
                        </div>
                        <div class="mb-1">
                            <label class="form-label">Password</label>
                            <input type="text" class="form-control" id="vpassword" name="password">
                        </div>
                        <div class="mb-1">
                            <label class="form-label">Active Status</label>
                            <select name="is_active" id="is_active" class="form-control">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                        <div class="mb-1">
                            <label class="form-label">Role</label>
                            <select name="role" id="edit_role" class="form-control" onchange="onEditRoleChange(this.value)">
                                <option value="salesman">Salesman</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>

                        {{-- Permissions panel — always visible --}}
                        <div class="mt-2 p-2 border rounded">
                            <p class="font-weight-bold mb-1" style="font-size:13px;">Permissions</p>

                            {{-- Shown for admin role --}}
                            <div id="edit_admin_notice" style="display:none;">
                                <small class="text-muted">Admin has full access to all features — no restrictions apply.</small>
                            </div>

                            {{-- Shown for salesman role --}}
                            <div id="edit_permissions_section">
                                <div class="form-check">
                                    <input class="form-check-input edit-perm" type="checkbox" name="permissions[]" value="approve_sales" id="edit_perm_approve">
                                    <label class="form-check-label" for="edit_perm_approve">Approve Sales</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input edit-perm" type="checkbox" name="permissions[]" value="process_returns" id="edit_perm_returns">
                                    <label class="form-check-label" for="edit_perm_returns">Process Returns / Refunds</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input edit-perm" type="checkbox" name="permissions[]" value="manage_inventory" id="edit_perm_inventory">
                                    <label class="form-check-label" for="edit_perm_inventory">Manage Inventory</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input edit-perm" type="checkbox" name="permissions[]" value="view_vendor_accounts" id="edit_perm_vendor">
                                    <label class="form-check-label" for="edit_perm_vendor">View Vendor Accounts &amp; Reports</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input edit-perm" type="checkbox" name="permissions[]" value="delete_records" id="edit_perm_delete">
                                    <label class="form-check-label" for="edit_perm_delete">Delete Records</label>
                                </div>
                            </div>
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
    {{-- End Edit User Modal --}}

    <style>
        .card { border-radius: 12px; }
        .badge-admin    { background-color: #e74c3c; color: #fff; padding: 3px 8px; border-radius: 4px; font-size: 12px; }
        .badge-salesman { background-color: #3498db; color: #fff; padding: 3px 8px; border-radius: 4px; font-size: 12px; }
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
                    <i class="bi bi-plus"></i> Add User
                </button>

                <div class="col-12 mt-1">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="text-bold-500">Available Users</h4>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>Created At</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Password</th>
                                        <th>Role</th>
                                        <th>Permissions</th>
                                        <th>Active</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($users as $user)
                                        <tr>
                                            <td>{{ $user->created_at->format('d M Y') }}</td>
                                            <td>{{ $user->name }}</td>
                                            <td>{{ $user->email }}</td>
                                            <td>{{ $user->password_text }}</td>
                                            <td>
                                                @if ($user->role === 'admin')
                                                    <span class="badge-admin">Admin</span>
                                                @else
                                                    <span class="badge-salesman">Salesman</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($user->role === 'admin')
                                                    <em class="text-muted">All</em>
                                                @elseif ($user->permissions->isEmpty())
                                                    <em class="text-muted">None</em>
                                                @else
                                                    @foreach ($user->permissions as $perm)
                                                        <span class="badge badge-light d-inline-block mb-1">
                                                            {{ str_replace('_', ' ', $perm->permission) }}
                                                        </span>
                                                    @endforeach
                                                @endif
                                            </td>
                                            <td>{{ $user->is_active ? 'Active' : 'Inactive' }}</td>
                                            <td>
                                                <a href="#" onclick="editUser({{ $user->id }})" data-toggle="modal" data-target="#exampleModal1">
                                                    <i class="feather icon-edit"></i>
                                                </a>
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
        function toggleAddPermissions(role) {
            document.getElementById('add_permissions_section').style.display = role === 'salesman' ? 'block' : 'none';
        }

        function onEditRoleChange(role) {
            if (role === 'admin') {
                document.getElementById('edit_admin_notice').style.display    = 'block';
                document.getElementById('edit_permissions_section').style.display = 'none';
            } else {
                document.getElementById('edit_admin_notice').style.display    = 'none';
                document.getElementById('edit_permissions_section').style.display = 'block';
            }
        }

        function editUser(id) {
            $.ajax({
                type: 'GET',
                url: '/edituser/' + id,
                success: function (data) {
                    var u = data.result;
                    $('#id').val(u.id);
                    $('#vname').val(u.name);
                    $('#vemail').val(u.email);
                    $('#vpassword').val(u.password_text);
                    $('#is_active').val(u.is_active == 1 ? '1' : '0');
                    $('#edit_role').val(u.role);

                    // Show the correct permissions panel based on role
                    onEditRoleChange(u.role);

                    // Reset checkboxes then tick the ones this user has
                    $('.edit-perm').prop('checked', false);
                    if (u.permission_list && u.permission_list.length) {
                        $.each(u.permission_list, function (i, perm) {
                            $('#edit_permissions_section input[value="' + perm + '"]').prop('checked', true);
                        });
                    }
                },
                error: function (err) {
                    console.log('Error loading user:', err);
                }
            });
        }

        // Init: add modal defaults to salesman — show permissions
        document.getElementById('add_permissions_section').style.display = 'block';
    </script>

@endsection
