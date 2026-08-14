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
                    <p class="text-muted small mb-2">Shared by everyone &mdash; anything typed here is visible to all users after they refresh the page. Right-click a cell for more options (insert/delete rows &amp; columns). Select a cell and press <strong>Ctrl+B</strong> to bold it.</p>
                    <div id="notebook-sheet"></div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var initialValues = @json($values);
        var initialStyle = @json($style && count($style) ? $style : (object) []);
        var csrf = '{{ csrf_token() }}';
        var saveUrl = '{{ route('notebook.save') }}';
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
            onchange: function () { scheduleSave(); },
            oninsertrow: function () { scheduleSave(); },
            oninsertcolumn: function () { scheduleSave(); },
            ondeleterow: function () { scheduleSave(); },
            ondeletecolumn: function () { scheduleSave(); },
            onselection: function (instance, x1, y1, x2, y2) {
                lastSelection = [x1, y1, x2, y2];
            },
        };

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
            fetch(saveUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ data: grid, style: style }),
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
