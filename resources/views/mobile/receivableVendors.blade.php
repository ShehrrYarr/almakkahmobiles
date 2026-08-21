@extends('user_navbar')
@section('content')

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

            <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-12 latest-update-tracking mt-1">
                <div class="card">
                    <div class="card-header latest-update-heading d-flex justify-content-between">
                        <h4 class="latest-update-heading-title text-bold-500">Receivable Mobile Vendors</h4>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered zero-configuration">
                            <thead>
                                <tr>
                                    <th>Created At</th>
                                    <th>Owed Amount</th>
                                    <th>WhatsApp Send</th>
                                    <th>Accounts</th>
                                    <th>Picture</th>
                                    <th>Vendor Name</th>
                                    <th>Office Address</th>
                                    <th>City</th>
                                    <th>Mobile Number</th>
                                    <th>CNIC</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($vendorsOwingDetails as $key)
                                <tr>
                                    <td>{{ $key->created_at }}</td>
                                    <td>{{ $key->amount_owed }}</td>
                                    @php
                                        $mobile = preg_replace('/\D+/', '', $key->mobile_no ?? '');
                                        if (str_starts_with($mobile, '0')) {
                                            $mobile = '92' . substr($mobile, 1);
                                        }
                                        if (strlen($mobile) === 10 && str_starts_with($mobile, '3')) {
                                            $mobile = '92' . $mobile;
                                        }
                                        $amount = number_format((float) $key->amount_owed, 0);
                                        $msg = "Assalam-o-Alaikum {$key->name}, please pay your pending amount Rs {$amount}. Thanks - Almakkah Mobiles";
                                        $waUrl = "https://wa.me/{$mobile}?text=" . urlencode($msg);
                                    @endphp
                                    <td>
                                        @if(!empty($mobile) && strlen($mobile) >= 11)
                                        <a href="{{ $waUrl }}" target="_blank" class="btn btn-sm btn-success" title="Send WhatsApp">
                                            <i class="fa fa-whatsapp"></i>
                                        </a>
                                        @else
                                        <span class="text-danger">No valid #</span>
                                        @endif
                                    </td>
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

@endsection
