@extends('user_navbar')
@section('content')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jsuites@4/dist/jsuites.css" type="text/css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jspreadsheet-ce@4/dist/jspreadsheet.css" type="text/css" />
<script src="https://cdn.jsdelivr.net/npm/jsuites@4/dist/jsuites.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspreadsheet-ce@4/dist/index.js"></script>

<style>
    #notebook-status {
        font-size: .85em;
        color: #6c757d;
    }
    #notebook-status.saving {
        color: #b45309;
    }
    #notebook-status.saved {
        color: #15803d;
    }
    #notebook-status.error {
        color: #dc3545;
    }
</style>

<div class="app-content content">
    <div class="content-overlay"></div>
    <div class="content-wrapper">
        <div class="content-header row">
        </div>
        <div class="content-body">

            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex align-items-center justify-content-between flex-wrap" style="gap:10px;">
                    <h4 class="mb-0 font-weight-bold"><i class="fa fa-book text-secondary mr-1"></i> Notebook</h4>
                    <div class="d-flex align-items-center" style="gap:10px;">
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="notebook-add-row">
                            <i class="fa fa-plus mr-1"></i> Add Row
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="notebook-add-col">
                            <i class="fa fa-plus mr-1"></i> Add Column
                        </button>
                        <span id="notebook-status">
                            @if($updatedAt)
                                Last saved {{ $updatedAt->format('d M Y, H:i') }}@if($updatedBy) by {{ $updatedBy }} @endif
                            @else
                                Not saved yet
                            @endif
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-2">Shared by everyone &mdash; anything typed here is visible to all users after they refresh the page. Right-click a cell for more options (insert/delete rows &amp; columns).</p>
                    <div id="notebook-sheet"></div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var initialData = @json($data);
        var csrf = '{{ csrf_token() }}';
        var saveUrl = '{{ route('notebook.save') }}';
        var statusEl = document.getElementById('notebook-status');

        var minRows = 20;
        var minCols = 10;

        var options = {
            data: (initialData && initialData.length) ? initialData : undefined,
            minDimensions: [minCols, minRows],
            tableOverflow: true,
            tableWidth: '100%',
            tableHeight: '70vh',
            allowInsertRow: true,
            allowInsertColumn: true,
            allowDeleteRow: true,
            allowDeleteColumn: true,
            columnSorting: false,
            onchange: function () { scheduleSave(); },
            oninsertrow: function () { scheduleSave(); },
            oninsertcolumn: function () { scheduleSave(); },
            ondeleterow: function () { scheduleSave(); },
            ondeletecolumn: function () { scheduleSave(); },
        };

        var sheet = jspreadsheet(document.getElementById('notebook-sheet'), options);

        var saveTimer = null;
        function scheduleSave() {
            statusEl.className = 'saving';
            statusEl.textContent = 'Saving...';
            clearTimeout(saveTimer);
            saveTimer = setTimeout(doSave, 800);
        }

        function doSave() {
            var grid = sheet.getData();
            fetch(saveUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ data: grid }),
            })
            .then(function (res) { return res.json(); })
            .then(function (json) {
                statusEl.className = 'saved';
                statusEl.textContent = 'Saved ' + json.updated_at + ' by ' + json.updated_by;
            })
            .catch(function () {
                statusEl.className = 'error';
                statusEl.textContent = 'Failed to save — check your connection and try again.';
            });
        }

        document.getElementById('notebook-add-row').addEventListener('click', function () {
            sheet.insertRow();
            scheduleSave();
        });
        document.getElementById('notebook-add-col').addEventListener('click', function () {
            sheet.insertColumn();
            scheduleSave();
        });
    });
</script>

@endsection
