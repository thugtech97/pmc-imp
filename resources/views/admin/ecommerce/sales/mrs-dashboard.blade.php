@extends('admin.layouts.app')

@section('pagecss')
    <style>
        .dash-tile {
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            background: #fff;
            height: 100%;
            transition: box-shadow .15s ease, transform .15s ease;
        }
        .dash-tile.clickable-card { cursor: pointer; }
        .dash-tile.clickable-card:hover {
            box-shadow: 0 4px 14px rgba(15, 23, 42, .12);
            transform: translateY(-2px);
        }
        .dash-tile .tile-body { padding: 16px 18px; }
        .dash-tile .tile-label {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
            color: #64748b;
            line-height: 1.4;
            margin-bottom: 6px;
            min-height: 31px;
        }
        .dash-tile .tile-value {
            font-size: 1.9rem;
            font-weight: 800;
            line-height: 1;
            color: #0f172a;
        }
        .dash-tile .tile-accent {
            height: 3px;
            border-radius: 6px 6px 0 0;
            background: #cbd5e1;
        }
        .tile-accent-primary { background: #3b7ddd !important; }
        .tile-accent-warning { background: #f0932b !important; }
        .tile-accent-danger  { background: #dc3545 !important; }
        .tile-accent-success { background: #198754 !important; }
        .tile-accent-muted   { background: #94a3b8 !important; }

        .chart-card {
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            background: #fff;
            height: 100%;
        }
        .chart-card .chart-head {
            padding: 12px 16px;
            border-bottom: 1px solid #eef2f7;
        }
        .chart-card .chart-title {
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
            color: #334155;
            margin: 0;
        }
        .chart-card .chart-sub {
            font-size: 11px;
            color: #94a3b8;
            margin: 2px 0 0;
        }
        .chart-canvas-wrap {
            padding: 14px 16px;
            height: 280px;
            position: relative;
        }
        .module-tabs .nav-link {
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .03em;
            text-transform: uppercase;
            color: #64748b;
            border: 1px solid transparent;
        }
        .module-tabs .nav-link.active {
            color: #0168fa;
            border-color: #d9e2ec #d9e2ec #fff;
        }
        .module-tabs .nav-link .count-pill {
            display: inline-block;
            margin-left: 5px;
            padding: 1px 7px;
            border-radius: 10px;
            background: #e2e8f0;
            color: #334155;
            font-size: 10px;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid">

        <div class="d-flex flex-column mb-3">
            <h4 class="mg-b-0 tx-spacing--1"><i data-feather="bar-chart-2"></i> MCD Dashboard</h4>
            <p class="text-muted small mb-0">
                Live counts across MRS, IMF, PA-DP and PA-SR. Click any tile to list the records behind it.
            </p>
        </div>

        {{-- ---------------------------------------------------------------
             Module tabs — each pane holds that module's tiles.
             --------------------------------------------------------------- --}}
        <ul class="nav nav-tabs module-tabs mb-3" role="tablist">
            @foreach ($modules as $key => $module)
                <li class="nav-item">
                    <a class="nav-link {{ $loop->first ? 'active' : '' }}"
                       data-toggle="tab"
                       href="#pane-{{ \Illuminate\Support\Str::slug($key) }}"
                       role="tab">
                        {{ $module['label'] }}
                        <span class="count-pill">{{ number_format($module['tiles']['total']['count']) }}</span>
                    </a>
                </li>
            @endforeach
        </ul>

        <div class="tab-content mb-4">
            @foreach ($modules as $key => $module)
                <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                     id="pane-{{ \Illuminate\Support\Str::slug($key) }}" role="tabpanel">
                    <div class="row row-sm">
                        @foreach ($module['tiles'] as $segKey => $tile)
                            @php
                                // Accent by meaning, so the eye lands on trouble first.
                                $accent = 'tile-accent-muted';
                                if ($segKey === 'total') {
                                    $accent = 'tile-accent-primary';
                                } elseif (in_array($segKey, ['on_hold', 'returned', 'for_planner'], true)) {
                                    $accent = 'tile-accent-warning';
                                } elseif (in_array($segKey, ['overdue_receiving', 'stale_canvass', 'rejected'], true)) {
                                    $accent = 'tile-accent-danger';
                                } elseif (in_array($segKey, ['approved', 'in_canvass'], true)) {
                                    $accent = 'tile-accent-success';
                                }
                            @endphp
                            <div class="col-6 col-md-4 col-xl-3 mb-3">
                                <div class="dash-tile clickable-card"
                                     data-module="{{ $key }}"
                                     data-type="{{ $segKey }}"
                                     title="{{ $tile['label'] }} — click to list these records">
                                    <div class="tile-accent {{ $accent }}"></div>
                                    <div class="tile-body">
                                        <div class="tile-label">{{ $tile['label'] }}</div>
                                        <div class="tile-value">{{ number_format($tile['count']) }}</div>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        @if ($key === 'MRS')
                            <div class="col-6 col-md-4 col-xl-3 mb-3">
                                <div class="dash-tile" title="Share of assigned MRS that have sat unreceived for more than 2 days.">
                                    <div class="tile-accent tile-accent-danger"></div>
                                    <div class="tile-body">
                                        <div class="tile-label">Assigned but stale (share)</div>
                                        <div class="tile-value">{{ $percentageOverdue }}%</div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        {{-- ---------------------------------------------------------------
             Charts
             --------------------------------------------------------------- --}}
        <div class="row row-sm mb-3">
            <div class="col-lg-5 mb-3">
                <div class="chart-card">
                    <div class="chart-head">
                        <p class="chart-title">MRS by pipeline stage</p>
                        <p class="chart-sub">Where every MRS currently sits</p>
                    </div>
                    <div class="chart-canvas-wrap"><canvas id="pipelineChart"></canvas></div>
                </div>
            </div>
            <div class="col-lg-7 mb-3">
                <div class="chart-card">
                    <div class="chart-head">
                        <p class="chart-title">Volume trend — last 12 months</p>
                        <p class="chart-sub">Records created per month, by module</p>
                    </div>
                    <div class="chart-canvas-wrap"><canvas id="trendChart"></canvas></div>
                </div>
            </div>
        </div>

        <div class="row row-sm mb-3">
            <div class="col-lg-6 mb-3">
                <div class="chart-card">
                    <div class="chart-head">
                        <p class="chart-title">Top 10 MRS stages in detail</p>
                        <p class="chart-sub">Same data as the pipeline chart, broken out</p>
                    </div>
                    <div class="chart-canvas-wrap"><canvas id="stageChart"></canvas></div>
                </div>
            </div>
            <div class="col-lg-6 mb-3">
                <div class="chart-card">
                    <div class="chart-head">
                        <p class="chart-title">Busiest departments</p>
                        <p class="chart-sub">MRS raised, top 8</p>
                    </div>
                    <div class="chart-canvas-wrap"><canvas id="departmentChart"></canvas></div>
                </div>
            </div>
        </div>

        <div class="row row-sm mb-4">
            <div class="col-lg-6 mb-3">
                <div class="chart-card">
                    <div class="chart-head">
                        <p class="chart-title">Ageing — MRS out for canvass</p>
                        <p class="chart-sub">Days since the canvasser received it (15+ is the red zone)</p>
                    </div>
                    <div class="chart-canvas-wrap"><canvas id="ageingChart"></canvas></div>
                </div>
            </div>
            <div class="col-lg-6 mb-3">
                <div class="chart-card">
                    <div class="chart-head">
                        <p class="chart-title">Records by module</p>
                        <p class="chart-sub">Total volume held in each module</p>
                    </div>
                    <div class="chart-canvas-wrap"><canvas id="moduleTotalsChart"></canvas></div>
                </div>
            </div>
        </div>

        {{-- Drill-down modal --}}
        <div class="modal fade" id="mrsRecordsModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title fw-bold mb-0" id="mrsRecordsModalLabel">Records</h5>
                            <small class="text-muted" id="mrsRecordsMeta"></small>
                        </div>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body p-0">
                        <div id="mrsLoader" class="text-center py-4" style="display:none;">
                            <div class="spinner-border" role="status"></div>
                            <div class="mt-2 small text-muted">Loading records…</div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm mb-0" style="font-size:12px;">
                                <thead class="thead-light">
                                    <tr>
                                        <th id="colReference">Reference</th>
                                        <th id="colContext">Department</th>
                                        <th id="colRequestedBy">Requested by</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                    </tr>
                                </thead>
                                <tbody id="mrsRecordsTableBody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection

@section('pagejs')
    <script src="{{ asset('lib/chart.js/Chart.bundle.min.js') }}"></script>

    <script>
        // Chart.js 2.7.3 and Bootstrap 4 — both are what this app actually ships.
        // The previous version of this page called the Bootstrap 5 API
        // (new bootstrap.Tooltip / new bootstrap.Modal), which threw on load and
        // took the card click handlers and feather.replace() down with it.
        (function () {
            var PALETTE = ['#3b7ddd', '#0dcaf0', '#6f42c1', '#198754', '#f0932b', '#dc3545', '#94a3b8', '#20c997'];

            var pipeline    = @json($pipelineChart);
            var stage       = @json($stageChart);
            var trend       = @json($trendChart);
            var department  = @json($departmentChart);
            var ageing      = @json($ageingChart);
            var moduleTotal = @json($moduleTotalsChart);

            function hasData(series) {
                for (var i = 0; i < series.length; i++) {
                    if (series[i] > 0) { return true; }
                }
                return false;
            }

            // Chart.js draws nothing for an all-zero dataset, which looks broken.
            // Say so instead.
            function emptyNotice(canvas) {
                var wrap = canvas.parentNode;
                wrap.innerHTML = '<div class="d-flex align-items-center justify-content-center h-100 text-muted"'
                    + ' style="font-size:12px;">No data for this period yet.</div>';
            }

            function build(id, config, series) {
                var canvas = document.getElementById(id);
                if (!canvas) { return; }
                if (!hasData(series)) { emptyNotice(canvas); return; }
                new Chart(canvas.getContext('2d'), config);
            }

            var barScales = {
                yAxes: [{ ticks: { beginAtZero: true, precision: 0 }, gridLines: { color: '#eef2f7' } }],
                xAxes: [{ gridLines: { display: false } }]
            };
            var horizontalScales = {
                xAxes: [{ ticks: { beginAtZero: true, precision: 0 }, gridLines: { color: '#eef2f7' } }],
                yAxes: [{ gridLines: { display: false } }]
            };

            build('pipelineChart', {
                type: 'doughnut',
                data: {
                    labels: pipeline.labels,
                    datasets: [{ data: pipeline.data, backgroundColor: PALETTE, borderWidth: 1, borderColor: '#fff' }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    legend: { position: 'right', labels: { boxWidth: 12, fontSize: 11 } }
                }
            }, pipeline.data);

            build('trendChart', {
                type: 'line',
                data: {
                    labels: trend.labels,
                    datasets: [
                        { label: 'MRS', data: trend.data, borderColor: '#3b7ddd', backgroundColor: 'rgba(59,125,221,.10)', fill: true, lineTension: .3, pointRadius: 2 },
                        { label: 'IMF', data: trend.imf,  borderColor: '#6f42c1', backgroundColor: 'transparent', fill: false, lineTension: .3, pointRadius: 2 },
                        { label: 'PA',  data: trend.pa,   borderColor: '#f0932b', backgroundColor: 'transparent', fill: false, lineTension: .3, pointRadius: 2 }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    legend: { position: 'top', labels: { boxWidth: 12, fontSize: 11 } },
                    scales: barScales
                }
            }, trend.data.concat(trend.imf, trend.pa));

            build('stageChart', {
                type: 'horizontalBar',
                data: {
                    labels: stage.labels,
                    datasets: [{ data: stage.data, backgroundColor: '#3b7ddd', barPercentage: .7 }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    legend: { display: false },
                    scales: horizontalScales
                }
            }, stage.data);

            build('departmentChart', {
                type: 'horizontalBar',
                data: {
                    labels: department.labels,
                    datasets: [{ data: department.data, backgroundColor: '#20c997', barPercentage: .7 }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    legend: { display: false },
                    scales: horizontalScales
                }
            }, department.data);

            build('ageingChart', {
                type: 'bar',
                data: {
                    labels: ageing.labels,
                    datasets: [{
                        data: ageing.data,
                        backgroundColor: ['#198754', '#0dcaf0', '#f0932b', '#dc3545'],
                        barPercentage: .6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    legend: { display: false },
                    scales: barScales
                }
            }, ageing.data);

            build('moduleTotalsChart', {
                type: 'bar',
                data: {
                    labels: moduleTotal.labels,
                    datasets: [{ data: moduleTotal.data, backgroundColor: PALETTE, barPercentage: .6 }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    legend: { display: false },
                    scales: barScales
                }
            }, moduleTotal.data);
        })();

        $(function () {
            feather.replace();

            $('[title]').tooltip({ placement: 'top' });

            // Column headers differ per module.
            var HEADERS = {
                'MRS':   { reference: 'MRS #',  context: 'Department', requestedBy: 'Requested by' },
                'IMF':   { reference: 'IMF #',  context: 'Department', requestedBy: 'Type' },
                'PA-DP': { reference: 'PA #',   context: 'MRS #',      requestedBy: 'Planner' },
                'PA-SR': { reference: 'PA #',   context: 'MRS #',      requestedBy: 'Planner' }
            };

            function esc(value) {
                return $('<div>').text(value === null || value === undefined ? '' : value).html();
            }

            $('.clickable-card').on('click', function () {
                var module = $(this).data('module');
                var type   = $(this).data('type');
                var label  = $(this).find('.tile-label').text().trim();

                $('#mrsRecordsModalLabel').text(module + ' — ' + label);
                $('#mrsRecordsMeta').text('');
                $('#mrsRecordsTableBody').empty();
                $('#mrsLoader').show();
                $('#mrsRecordsModal').modal('show');

                var head = HEADERS[module] || HEADERS.MRS;
                $('#colReference').text(head.reference);
                $('#colContext').text(head.context);
                $('#colRequestedBy').text(head.requestedBy);

                $.getJSON("{{ route('dashboard.mrs-records') }}", { module: module, type: type })
                    .done(function (payload) {
                        $('#mrsLoader').hide();

                        var rows = payload.records || [];
                        if (!rows.length) {
                            $('#mrsRecordsTableBody').html(
                                '<tr><td colspan="5" class="text-center text-muted py-3">No records found.</td></tr>');
                            return;
                        }

                        if (payload.total > payload.shown) {
                            $('#mrsRecordsMeta').text(
                                'Showing the ' + payload.shown + ' most recent of ' + payload.total + ' records.');
                        } else {
                            $('#mrsRecordsMeta').text(payload.total + ' record(s).');
                        }

                        // Build once, then insert — the old version concatenated
                        // innerHTML inside the loop, re-parsing the table each row.
                        var html = '';
                        for (var i = 0; i < rows.length; i++) {
                            html += '<tr>'
                                + '<td><strong>' + esc(rows[i].reference) + '</strong></td>'
                                + '<td>' + esc(rows[i].context) + '</td>'
                                + '<td>' + esc(rows[i].requested_by) + '</td>'
                                + '<td>' + esc(rows[i].status) + '</td>'
                                + '<td>' + esc(rows[i].created_at) + '</td>'
                                + '</tr>';
                        }
                        $('#mrsRecordsTableBody').html(html);
                    })
                    .fail(function () {
                        $('#mrsLoader').hide();
                        $('#mrsRecordsTableBody').html(
                            '<tr><td colspan="5" class="text-center text-danger py-3">Could not load these records.</td></tr>');
                    });
            });
        });
    </script>
@endsection
