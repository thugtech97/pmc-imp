@extends('admin.layouts.app')

@section('content')
    <div class="container-fluid">

        <div class="d-flex flex-column mb-4">
            <h2 class="fw-bold mb-1"><i data-feather="bar-chart-2"></i> MCD Dashboard</h2>
            <p class="text-muted small mb-0">
                Click on any card to view the detailed list of MRS records for that category.
                Hover over the cards to see additional information.
            </p>
        </div>


        <!-- CARD STYLE OVERRIDES -->
        <style>
            .dashboard-number {
                font-size: 2.8rem;
                /* Bigger numbers */
                font-weight: 800;
            }

            .dashboard-title {
                font-size: 1rem;
                /* slightly larger titles */
                font-weight: 600;
            }

            .dashboard-icon {
                width: 38px !important;
                height: 38px !important;
            }
        </style>

        <!-- FIRST ROW -->
        <div class="row g-3">

            <div class="col-md-3">
                <div class="card shadow-sm dashboard-card h-100 clickable-card" data-type="total" data-bs-toggle="tooltip"
                    title="Total number of all MRS records you've submitted.">
                    <div class="card-body text-center py-4">
                        <i data-feather="file-text" class="mb-2 text-muted dashboard-icon"></i>
                        <h6 class="dashboard-title text-muted">Total MRS</h6>
                        <div class="dashboard-number text-dark">{{ $totalSales }}</div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm dashboard-card h-100 clickable-card" data-type="posted" data-bs-toggle="tooltip"
                    title="MRS newly submitted and forwarded to WFS.">
                    <div class="card-body text-center py-4">
                        <i data-feather="send" class="mb-2 text-muted dashboard-icon"></i>
                        <h6 class="dashboard-title text-muted">Newly Submitted MRS to WFS</h6>
                        <div class="dashboard-number text-dark">{{ $postedCount }}</div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm dashboard-card h-100 clickable-card" data-type="in-progress"
                    data-bs-toggle="tooltip" title="MRS currently being processed by WFS.">
                    <div class="card-body text-center py-4">
                        <i data-feather="loader" class="mb-2 text-muted dashboard-icon"></i>
                        <h6 class="dashboard-title text-muted">In-Progress (WFS)</h6>
                        <div class="dashboard-number text-dark">{{ $inProgressCount }}</div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm dashboard-card h-100 clickable-card" data-type="overdue" data-bs-toggle="tooltip"
                    title="In-Progress MRS older than 2 days (possible delay).">
                    <div class="card-body text-center py-4">
                        <i data-feather="alert-triangle" class="mb-2 text-muted dashboard-icon"></i>
                        <h6 class="dashboard-title text-muted">Overdue (2+ days)</h6>
                        <div class="dashboard-number text-danger">{{ $inProgressOverdue }}</div>
                    </div>
                </div>
            </div>

        </div>

        <!-- SECOND ROW -->
        <div class="row g-3 mt-2">

            <div class="col-md-3">
                <div class="card shadow-sm dashboard-card h-100 clickable-card" data-type="approved_not_received"
                    data-bs-toggle="tooltip" title="Fully approved MRS that canvassers have not yet received.">
                    <div class="card-body text-center py-4">
                        <i data-feather="clock" class="mb-2 text-muted dashboard-icon"></i>
                        <h6 class="dashboard-title text-muted">Approved but Not Received (Canvassers)</h6>
                        <div class="dashboard-number text-dark">{{ $approvedNotReceived }}</div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm dashboard-card h-100 clickable-card" data-type="approved_no_canvasser"
                    data-bs-toggle="tooltip" title="MRS that are approved but not yet assigned to any canvasser.">
                    <div class="card-body text-center py-4">
                        <i data-feather="user-check" class="mb-2 text-muted dashboard-icon"></i>
                        <h6 class="dashboard-title text-muted">Approved but No Canvasser Assigned</h6>
                        <div class="dashboard-number text-dark">{{ $approvedNullReceived }}</div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card shadow-sm dashboard-card h-100" data-bs-toggle="tooltip"
                    title="Percentage of in-progress MRS that are considered overdue.">
                    <div class="card-body text-center py-5">
                        <i data-feather="percent" class="mb-3 text-muted dashboard-icon"></i>
                        <h4 class="fw-bold text-muted">Overdue Percentage</h4>
                        <div class="display-2 fw-bold text-dark">{{ $percentageOverdue }}%</div>
                    </div>
                </div>
            </div>

        </div>

        <!-- MRS Records Modal -->
        <div class="modal fade" id="mrsRecordsModal" tabindex="-1" aria-labelledby="mrsRecordsModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold" id="mrsRecordsModalLabel">MRS Records</h5>
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
                            <table class="table table-bordered mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Order Number</th>
                                        <th>Customer Name</th>
                                        <th>Requested By</th>
                                    </tr>
                                </thead>
                                <tbody id="mrsRecordsTableBody">
                                    <!-- Records will be injected here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>


    </div>
@endsection

@section('pagejs')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Bootstrap tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Load feather icons
            feather.replace();

            // Click handler for dashboard cards
            document.querySelectorAll('.clickable-card').forEach(card => {
                card.addEventListener('click', function() {
                    let type = this.getAttribute('data-type');

                    // Show modal
                    let modal = new bootstrap.Modal(document.getElementById('mrsRecordsModal'));
                    modal.show();

                    // Build route dynamically using Laravel route name
                    let url = "{{ route('dashboard.mrs-records') }}" + "?type=" + type;
                    // Show loader
                    document.getElementById('mrsLoader').style.display = 'block';

                    // Clear old table rows
                    document.getElementById('mrsRecordsTableBody').innerHTML = '';

                    fetch(url)
                        .then(response => response.json())
                        .then(data => {

                            // Hide loader after fetch
                            document.getElementById('mrsLoader').style.display = 'none';

                            let tbody = document.getElementById('mrsRecordsTableBody');

                            if (data.length === 0) {
                                tbody.innerHTML =
                                    '<tr><td colspan="3" class="text-center">No records found</td></tr>';
                                return;
                            }

                            data.forEach(record => {
                                let row = `
                                    <tr>
                                        <td>${record.order_number}</td>
                                        <td>${record.customer_name}</td>
                                        <td>${record.requested_by}</td>
                                    </tr>`;
                                tbody.innerHTML += row;
                            });
                        })
                        .catch(err => {
                            console.error(err);
                            document.getElementById('mrsLoader').style.display = 'none';
                        });

                });
            });
        });
    </script>
@endsection
