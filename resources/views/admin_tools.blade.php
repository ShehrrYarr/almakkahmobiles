@extends('user_navbar')
@section('content')

<div class="app-content content">
    <div class="content-overlay"></div>
    <div class="content-wrapper">
        <div class="content-header row"></div>
        <div class="content-body">

            <div class="d-flex align-items-center mb-3">
                <h3 class="mb-0 font-weight-bold">
                    <i class="feather icon-tool text-primary mr-2"></i> Admin Tools
                </h3>
            </div>

            @if(session('tool_success'))
            <div class="alert alert-success alert-dismissible fade show">
                <strong><i class="feather icon-check-circle mr-1"></i> {{ session('tool_success') }}</strong>
                @if(session('tool_output'))
                <pre class="mt-2 mb-0 p-2 bg-light rounded" style="font-size:.82rem;white-space:pre-wrap;max-height:260px;overflow-y:auto;">{{ session('tool_output') }}</pre>
                @endif
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
            @endif

            @if(session('tool_danger'))
            <div class="alert alert-danger alert-dismissible fade show">
                <strong><i class="feather icon-alert-circle mr-1"></i> {{ session('tool_danger') }}</strong>
                @if(session('tool_output'))
                <pre class="mt-2 mb-0 p-2 bg-light rounded" style="font-size:.82rem;white-space:pre-wrap;max-height:260px;overflow-y:auto;">{{ session('tool_output') }}</pre>
                @endif
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
            @endif

            <div class="row">

                {{-- Run Migrations --}}
                <div class="col-md-4 mb-3">
                    <div class="card shadow-sm h-100">
                        <div class="card-body d-flex flex-column align-items-center text-center p-4">
                            <div class="mb-3" style="font-size:2.5rem;color:#4a90c4;">
                                <i class="feather icon-database"></i>
                            </div>
                            <h5 class="font-weight-bold mb-1">Run Migrations</h5>
                            <p class="text-muted small mb-3">Runs <code>php artisan migrate --force</code> to apply any pending database migrations.</p>
                            <form method="POST" action="{{ route('admin.tools.migrate') }}" class="mt-auto w-100"
                                  onsubmit="return confirm('Run database migrations now?')">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-block font-weight-bold">
                                    <i class="feather icon-play mr-1"></i> Run Migrations
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Git Pull --}}
                <div class="col-md-4 mb-3">
                    <div class="card shadow-sm h-100">
                        <div class="card-body d-flex flex-column align-items-center text-center p-4">
                            <div class="mb-3" style="font-size:2.5rem;color:#28a745;">
                                <i class="feather icon-download-cloud"></i>
                            </div>
                            <h5 class="font-weight-bold mb-1">Pull from GitHub</h5>
                            <p class="text-muted small mb-3">Runs <code>git pull</code> to fetch and merge the latest code from the repository.</p>
                            <form method="POST" action="{{ route('admin.tools.pull') }}" class="mt-auto w-100"
                                  onsubmit="return confirm('Pull latest code from GitHub now?')">
                                @csrf
                                <button type="submit" class="btn btn-success btn-block font-weight-bold">
                                    <i class="feather icon-download-cloud mr-1"></i> Pull GitHub
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Optimize --}}
                <div class="col-md-4 mb-3">
                    <div class="card shadow-sm h-100">
                        <div class="card-body d-flex flex-column align-items-center text-center p-4">
                            <div class="mb-3" style="font-size:2.5rem;color:#fd7e14;">
                                <i class="feather icon-zap"></i>
                            </div>
                            <h5 class="font-weight-bold mb-1">Optimize</h5>
                            <p class="text-muted small mb-3">Runs <code>php artisan optimize</code> to cache routes, config, and views for better performance.</p>
                            <form method="POST" action="{{ route('admin.tools.optimize') }}" class="mt-auto w-100"
                                  onsubmit="return confirm('Run artisan optimize now?')">
                                @csrf
                                <button type="submit" class="btn btn-warning btn-block font-weight-bold" style="color:#fff;">
                                    <i class="feather icon-zap mr-1"></i> Optimize
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

@endsection
