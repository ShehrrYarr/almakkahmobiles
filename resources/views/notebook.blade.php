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
    #notebook-tabs {
        border-bottom: 1px solid #eee;
    }
    .notebook-tab {
        background: #f1f3f5;
        border-radius: 8px 8px 0 0;
        padding: 6px 10px;
        gap: 6px;
    }
    .notebook-tab.active {
        background: #556ee6;
    }
    .notebook-tab.active .notebook-tab-link {
        color: #fff;
        font-weight: 600;
    }
    .notebook-tab-link {
        color: #495057;
        text-decoration: none;
        white-space: nowrap;
    }
    .notebook-tab-rename, .notebook-tab-delete {
        cursor: pointer;
        color: #868e96;
        font-size: .85em;
    }
    .notebook-tab.active .notebook-tab-rename,
    .notebook-tab.active .notebook-tab-delete {
        color: #e9ecef;
    }
    .notebook-tab-rename:hover, .notebook-tab-delete:hover {
        color: #212529;
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
                    @if($activeId)
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
                    @endif
                </div>

                @if($notebooks->isNotEmpty())
                <div class="px-3 pt-2 d-flex align-items-end flex-wrap" style="gap:6px;" id="notebook-tabs">
                    @foreach($notebooks as $nb)
                    <div class="d-flex align-items-center notebook-tab {{ $nb->id == $activeId ? 'active' : '' }}">
                        <a href="{{ route('notebook.index', $nb->id) }}" class="notebook-tab-link">{{ $nb->name }}</a>
                        @if(auth()->user()->isAdmin())
                        <i class="fa fa-pencil notebook-tab-rename" data-id="{{ $nb->id }}" data-name="{{ $nb->name }}" title="Rename notebook"></i>
                        <i class="fa fa-times notebook-tab-delete" data-id="{{ $nb->id }}" title="Delete notebook"></i>
                        @endif
                    </div>
                    @endforeach
                    @if(auth()->user()->isAdmin())
                    <button type="button" class="btn btn-sm btn-light" id="notebook-add-tab" title="Add Notebook" style="border-radius:8px 8px 0 0;">
                        <i class="fa fa-plus"></i>
                    </button>
                    @endif
                </div>
                @endif

                <div class="card-body">
                    @if($notebooks->isEmpty())
                    <div class="text-center text-muted py-5">
                        <p>No notebooks yet.</p>
                        @if(auth()->user()->isAdmin())
                        <button type="button" class="btn btn-primary" id="notebook-add-tab-empty">
                            <i class="fa fa-plus mr-1"></i> Create Notebook
                        </button>
                        @else
                        <p class="small">Ask an admin to create one.</p>
                        @endif
                    </div>
                    @else
                    <p class="text-muted small mb-2">Shared by everyone &mdash; anything typed here is visible to all users after they refresh the page. Right-click a cell for more options (insert/delete rows &amp; columns). Select a cell and press <strong>Ctrl+B</strong> to bold it.</p>
                    <div id="notebook-sheet"></div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>

{{-- Add Notebook Modal --}}
<div class="modal fade" id="addNotebookModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Notebook</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="mb-1">
                    <label class="form-label">Notebook Name</label>
                    <input type="text" class="form-control" id="add-notebook-name" maxlength="255">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-warning" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="add-notebook-submit">Save</button>
            </div>
        </div>
    </div>
</div>

{{-- Rename Notebook Modal --}}
<div class="modal fade" id="renameNotebookModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Rename Notebook</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="rename-notebook-id">
                <div class="mb-1">
                    <label class="form-label">Notebook Name</label>
                    <input type="text" class="form-control" id="rename-notebook-name" maxlength="255">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-warning" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="rename-notebook-submit">Save</button>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        var csrf = '{{ csrf_token() }}';

        function showAddModal() { $('#add-notebook-name').val(''); $('#addNotebookModal').modal('show'); }
        var addBtn = document.getElementById('notebook-add-tab');
        var addBtnEmpty = document.getElementById('notebook-add-tab-empty');
        if (addBtn) addBtn.addEventListener('click', showAddModal);
        if (addBtnEmpty) addBtnEmpty.addEventListener('click', showAddModal);

        var addSubmitBtn = document.getElementById('add-notebook-submit');
        if (addSubmitBtn) {
            addSubmitBtn.addEventListener('click', function () {
                var name = document.getElementById('add-notebook-name').value.trim();
                if (!name) { alert('Please enter a name.'); return; }
                fetch('{{ route('notebook.store') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                    body: JSON.stringify({ name: name }),
                })
                .then(function (res) { return res.json(); })
                .then(function (json) {
                    if (json.success) {
                        window.location = '{{ url('/notebook') }}/' + json.id;
                    } else {
                        alert(json.message || 'Failed to create notebook.');
                    }
                })
                .catch(function () { alert('Failed to create notebook — check your connection.'); });
            });
        }

        document.querySelectorAll('.notebook-tab-rename').forEach(function (el) {
            el.addEventListener('click', function () {
                document.getElementById('rename-notebook-id').value = el.dataset.id;
                document.getElementById('rename-notebook-name').value = el.dataset.name;
                $('#renameNotebookModal').modal('show');
            });
        });

        var renameSubmitBtn = document.getElementById('rename-notebook-submit');
        if (renameSubmitBtn) {
            renameSubmitBtn.addEventListener('click', function () {
                var id = document.getElementById('rename-notebook-id').value;
                var name = document.getElementById('rename-notebook-name').value.trim();
                if (!name) { alert('Please enter a name.'); return; }
                fetch('{{ url('/notebook') }}/' + id + '/rename', {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                    body: JSON.stringify({ name: name }),
                })
                .then(function (res) { return res.json(); })
                .then(function (json) {
                    if (json.success) {
                        window.location.reload();
                    } else {
                        alert(json.message || 'Failed to rename notebook.');
                    }
                })
                .catch(function () { alert('Failed to rename notebook — check your connection.'); });
            });
        }

        document.querySelectorAll('.notebook-tab-delete').forEach(function (el) {
            el.addEventListener('click', function () {
                if (!confirm('Delete this notebook? This cannot be undone.')) return;
                var id = el.dataset.id;
                fetch('{{ url('/notebook') }}/' + id, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                })
                .then(function (res) { return res.json(); })
                .then(function (json) {
                    if (json.success) {
                        window.location = '{{ url('/notebook') }}';
                    } else {
                        alert(json.message || 'Failed to delete notebook.');
                    }
                })
                .catch(function () { alert('Failed to delete notebook — check your connection.'); });
            });
        });
    })();

    @if($activeId)
    document.addEventListener('DOMContentLoaded', function () {
        var initialValues = @json($values);
        var initialStyle = @json($style && count($style) ? $style : (object) []);
        var initialColumns = @json($columns);
        var initialRows = @json($rows);
        var csrf = '{{ csrf_token() }}';
        var saveUrl = '{{ route('notebook.save', $activeId) }}';
        var statusEl = document.getElementById('notebook-status');
        var sheetEl = document.getElementById('notebook-sheet');

        var minRows = 20;
        var minCols = 10;

        function colLetter(x) {
            var s = '', n = x + 1;
            while (n > 0) {
                var rem = (n - 1) % 26;
                s = String.fromCharCode(65 + rem) + s;
                n = Math.floor((n - 1) / 26);
            }
            return s;
        }
        function cellName(x, y) { return colLetter(x) + (y + 1); }

        var lastSelection = null; // [x1, y1, x2, y2]

        var options = {
            data: (initialValues && initialValues.length) ? initialValues : undefined,
            style: initialStyle,
            minDimensions: [minCols, minRows],
            tableOverflow: true,
            tableWidth: '100%',
            tableHeight: '70vh',
            allowInsertRow: true,
            allowInsertColumn: true,
            allowDeleteRow: true,
            allowDeleteColumn: true,
            columnSorting: false,
            rowResize: true,
            onchange: function () { scheduleSave(); },
            oninsertrow: function () { scheduleSave(); },
            oninsertcolumn: function () { scheduleSave(); },
            ondeleterow: function () { scheduleSave(); },
            ondeletecolumn: function () { scheduleSave(); },
            onresizerow: function () { scheduleSave(); },
            onresizecolumn: function () { scheduleSave(); },
            onselection: function (instance, x1, y1, x2, y2) {
                lastSelection = [x1, y1, x2, y2];
            },
        };

        // jspreadsheet-ce's init crashes if `columns`/`rows` are present in
        // the options object at all with an undefined value (unlike `data`,
        // which tolerates it) — so only set these keys when there's real
        // saved sizing to restore.
        if (initialColumns && initialColumns.length) { options.columns = initialColumns; }
        if (initialRows && initialRows.length) { options.rows = initialRows; }

        var sheet = jspreadsheet(sheetEl, options);

        function toggleBoldOnSelection() {
            if (!lastSelection) return;
            var minX = Math.min(lastSelection[0], lastSelection[2]);
            var maxX = Math.max(lastSelection[0], lastSelection[2]);
            var minY = Math.min(lastSelection[1], lastSelection[3]);
            var maxY = Math.max(lastSelection[1], lastSelection[3]);

            var anchorStyle = sheet.getStyle(cellName(minX, minY)) || '';
            var isBold = /font-weight\s*:\s*bold/.test(anchorStyle);

            for (var y = minY; y <= maxY; y++) {
                for (var x = minX; x <= maxX; x++) {
                    sheet.setStyle(cellName(x, y), 'font-weight', isBold ? 'normal' : 'bold');
                }
            }
            scheduleSave();
        }

        sheetEl.addEventListener('keydown', function (e) {
            var key = e.key ? e.key.toLowerCase() : '';
            if ((e.ctrlKey || e.metaKey) && key === 'b') {
                e.preventDefault();
                e.stopPropagation();
                toggleBoldOnSelection();
            }
        }, true);

        var saveTimer = null;
        function scheduleSave() {
            statusEl.className = 'saving';
            statusEl.textContent = 'Saving...';
            clearTimeout(saveTimer);
            saveTimer = setTimeout(doSave, 800);
        }

        function doSave() {
            var grid = sheet.getData();
            var style = sheet.getStyle();
            var columns = (sheet.options.columns || []).map(function (c) { return { width: c.width }; });
            var rows = (sheet.options.rows || []).map(function (r) { return { height: r.height }; });
            fetch(saveUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ data: grid, style: style, columns: columns, rows: rows }),
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
    @endif
</script>

@endsection
