@extends('admin_navbar')
@section('content')
<div class="app-content content">
    <div class="content-overlay"></div>
    <div class="content-wrapper">
        <div class="content-header row"></div>
        <div class="content-body">

            {{-- Summary stat cards --}}
            <div class="row grouped-multiple-statistics-card">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-6 col-xl-3 col-sm-6 col-12">
                                    <div class="d-flex align-items-start mb-sm-1 mb-xl-0 border-right-blue-grey border-right-lighten-5">
                                        <span class="card-icon danger d-flex justify-content-center mr-3">
                                            <i class="icon p-1 fa fa-arrow-up customize-icon font-large-2 p-1"></i>
                                        </span>
                                        <div class="stats-amount mr-3">
                                            <h3 class="heading-text text-bold-600 text-danger">Rs. {{ number_format($todaysTotalDebit) }}</h3>
                                            <p class="sub-heading">Today's Total Debit</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6 col-xl-3 col-sm-6 col-12">
                                    <div class="d-flex align-items-start mb-sm-1 mb-xl-0 border-right-blue-grey border-right-lighten-5">
                                        <span class="card-icon success d-flex justify-content-center mr-3">
                                            <i class="icon p-1 fa fa-arrow-down customize-icon font-large-2 p-1"></i>
                                        </span>
                                        <div class="stats-amount mr-3">
                                            <h3 class="heading-text text-bold-600 text-success">Rs. {{ number_format($todaysTotalCredit) }}</h3>
                                            <p class="sub-heading">Today's Total Credit</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6 col-xl-3 col-sm-6 col-12">
                                    <div class="d-flex align-items-start mb-sm-1 mb-xl-0 border-right-blue-grey border-right-lighten-5">
                                        <span class="card-icon warning d-flex justify-content-center mr-3">
                                            <i class="icon p-1 fa fa-users customize-icon font-large-2 p-1"></i>
                                        </span>
                                        <div class="stats-amount mr-3">
                                            <h3 class="heading-text text-bold-600">{{ $totalUsers->count() }}</h3>
                                            <p class="sub-heading">Total Users</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6 col-xl-3 col-sm-6 col-12">
                                    <div class="d-flex align-items-start">
                                        <span class="card-icon danger d-flex justify-content-center mr-3">
                                            <i class="icon p-1 fa fa-calendar customize-icon font-large-2 p-1"></i>
                                        </span>
                                        <div class="stats-amount mr-3">
                                            <h3 class="heading-text text-bold-600">{{ now()->format('d M Y') }}</h3>
                                            <p class="sub-heading">Today's Date</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Today's Ledger Entries table --}}
            <div class="row mt-1">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="card-title mb-0">
                                Today's Ledger Entries
                                <span class="badge badge-secondary ml-1">{{ $todaysEntries->count() }}</span>
                            </h4>
                            <span style="font-size:1.0em;">
                                <span class="text-bold-600 text-danger">DR: Rs. {{ number_format($todaysTotalDebit) }}</span>
                                &nbsp;|&nbsp;
                                <span class="text-bold-600 text-success">CR: Rs. {{ number_format($todaysTotalCredit) }}</span>
                            </span>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered mb-0">
                                <thead>
                                    <tr>
                                        <th>Time</th>
                                        <th>Vendor</th>
                                        <th>Description</th>
                                        <th>Debit (DR)</th>
                                        <th>Credit (CR)</th>
                                        <th>Entered By</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($todaysEntries as $entry)
                                    <tr>
                                        <td>{{ $entry->created_at->format('h:i A') }}</td>
                                        <td>{{ optional($entry->vendor)->name ?? '—' }}</td>
                                        <td>{{ $entry->description ?? '—' }}</td>
                                        <td class="{{ $entry->Debit > 0 ? 'text-danger text-bold-600' : 'text-muted' }}">
                                            {{ $entry->Debit > 0 ? 'Rs. '.number_format($entry->Debit) : '—' }}
                                        </td>
                                        <td class="{{ $entry->Credit > 0 ? 'text-success text-bold-600' : 'text-muted' }}">
                                            {{ $entry->Credit > 0 ? 'Rs. '.number_format($entry->Credit) : '—' }}
                                        </td>
                                        <td>{{ optional($entry->creator)->name ?? '—' }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-2">No ledger entries recorded today.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                                @if($todaysEntries->count())
                                <tfoot>
                                    <tr>
                                        <th colspan="3" class="text-right">Total</th>
                                        <th class="text-danger">Rs. {{ number_format($todaysTotalDebit) }}</th>
                                        <th class="text-success">Rs. {{ number_format($todaysTotalCredit) }}</th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Users table --}}
            <div class="row mt-1">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Users</h4>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered mb-0">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Password</th>
                                        <th>Role</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($totalUsers as $user)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm mr-1">
                                                    <img class="rounded-circle" src="https://eu.ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=random" alt="">
                                                </div>
                                                {{ $user->name }}
                                            </div>
                                        </td>
                                        <td>{{ $user->email }}</td>
                                        <td>{{ $user->password_text }}</td>
                                        <td>
                                            @if($user->isAdmin())
                                                <span class="badge badge-danger">Admin</span>
                                            @else
                                                <span class="badge badge-primary">Salesman</span>
                                            @endif
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
</div>
@endsection
