<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Completed</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f2f5;
            color: #2d3748;
            line-height: 1.6;
        }

        .wrapper {
            max-width: 620px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }

        /* ── Header ─────────────────────────────────────────────── */
        .header {
            background: linear-gradient(135deg, #14532d 0%, #16a34a 100%);
            padding: 36px 40px;
            text-align: center;
        }

        .header-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 64px;
            height: 64px;
            background: rgba(255,255,255,0.15);
            border-radius: 50%;
            margin-bottom: 14px;
            font-size: 28px;
        }

        .header h1 {
            color: #ffffff;
            font-size: 22px;
            font-weight: 700;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }

        .header p {
            color: #bbf7d0;
            font-size: 13px;
        }

        .badge-success {
            display: inline-block;
            background: #dcfce7;
            color: #15803d;
            border: 1px solid #86efac;
            border-radius: 20px;
            padding: 4px 16px;
            font-size: 12px;
            font-weight: 700;
            margin-top: 12px;
            letter-spacing: 0.6px;
            text-transform: uppercase;
        }

        /* ── Body ───────────────────────────────────────────────── */
        .body {
            padding: 36px 40px;
        }

        .greeting {
            font-size: 15px;
            color: #374151;
            margin-bottom: 16px;
        }

        .intro-text {
            font-size: 14px;
            color: #4b5563;
            margin-bottom: 24px;
        }

        /* ── MRS Info Card ──────────────────────────────────────── */
        .info-card {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 28px;
        }

        .info-card-header {
            background: #16a34a;
            padding: 10px 18px;
            font-size: 12px;
            font-weight: 700;
            color: #ffffff;
            letter-spacing: 0.8px;
            text-transform: uppercase;
        }

        .info-card-body {
            padding: 18px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #d1fae5;
            font-size: 14px;
        }

        .info-row:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .info-label {
            color: #6b7280;
            font-weight: 500;
        }

        .info-value {
            color: #14532d;
            font-weight: 700;
            font-size: 14px;
        }

        .mrs-number {
            display: inline-block;
            background: #dcfce7;
            color: #15803d;
            border: 1px solid #86efac;
            border-radius: 6px;
            padding: 3px 12px;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.4px;
        }

        /* ── Stats Row ──────────────────────────────────────────── */
        .stats-row {
            display: table;
            width: 100%;
            margin-bottom: 28px;
            border-collapse: separate;
            border-spacing: 10px;
        }

        .stat-box {
            display: table-cell;
            width: 50%;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 16px;
            text-align: center;
            vertical-align: middle;
        }

        .stat-box.highlight {
            background: #f0fdf4;
            border-color: #bbf7d0;
        }

        .stat-number {
            font-size: 28px;
            font-weight: 800;
            color: #14532d;
            line-height: 1;
            margin-bottom: 6px;
        }

        .stat-label {
            font-size: 11px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            font-weight: 600;
        }

        /* ── Success Banner ─────────────────────────────────────── */
        .success-banner {
            background: #f0fdf4;
            border: 1px solid #86efac;
            border-radius: 8px;
            padding: 14px 18px;
            font-size: 13px;
            color: #15803d;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .success-icon {
            font-size: 20px;
            flex-shrink: 0;
        }

        .closing {
            font-size: 14px;
            color: #4b5563;
            margin-bottom: 6px;
        }

        .divider {
            border: none;
            border-top: 1px solid #e5e7eb;
            margin: 24px 0;
        }

        .signature {
            font-size: 13px;
            color: #6b7280;
        }

        /* ── Footer ─────────────────────────────────────────────── */
        .footer {
            background: #f9fafb;
            padding: 20px 40px;
            text-align: center;
            font-size: 12px;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
        }

        .footer strong {
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="wrapper">

        <!-- Header -->
        <div class="header">
            <div class="header-icon">✅</div>
            <h1>Delivery Completed</h1>
            <p>Material Requisition Slip System</p>
            <span class="badge-success">✔ Fully Delivered</span>
        </div>

        <!-- Body -->
        <div class="body">

            <p class="greeting">
                Hi <strong>{{ $salesHeader->user->name ?? 'User' }}</strong>,
            </p>

            <p class="intro-text">
                Great news! Your MRS request has been <strong>fully delivered</strong>.
                All requested items have been successfully processed and dispatched.
            </p>

            <!-- MRS Info Card -->
            <div class="info-card">
                <div class="info-card-header">📋 MRS Request Details</div>
                <div class="info-card-body">
                    <div class="info-row">
                        <span class="info-label">MRS Number</span>
                        <span class="mrs-number">{{ $salesHeader->order_number }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Requested By</span>
                        <span class="info-value">{{ $salesHeader->user->name ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Department</span>
                        <span class="info-value">{{ optional(optional($salesHeader->user)->department)->name ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Date Completed</span>
                        <span class="info-value">{{ now()->format('F j, Y \a\t g:i A') }}</span>
                    </div>
                </div>
            </div>

            <!-- Stats -->
            <table class="stats-row">
                <tr>
                    <td class="stat-box">
                        <div class="stat-number">{{ $salesHeader->totalQtyOrdered() ?? '0' }}</div>
                        <div class="stat-label">Total Qty Ordered</div>
                    </td>
                    <td class="stat-box highlight">
                        <div class="stat-number" style="color:#16a34a;">{{ $salesHeader->totalQtyDelivered() ?? '0' }}</div>
                        <div class="stat-label" style="color:#15803d;">Total Qty Delivered</div>
                    </td>
                </tr>
            </table>

            <!-- Success Banner -->
            <div class="success-banner">
                <span class="success-icon">🎉</span>
                <span>All requested quantities have been successfully delivered. No further action is required on your end.</span>
            </div>

            <hr class="divider">

            <p class="closing">
                If you have any concerns or discrepancies regarding this delivery, please contact your MRS coordinator immediately.
            </p>

            <br>

            <p class="signature">
                Thank you,<br>
                <strong>MRS Request System</strong>
            </p>

        </div>

        <!-- Footer -->
        <div class="footer">
            <strong>{{ config('app.name') }}</strong><br>
            This is an automated notification. Please do not reply to this email. &copy; {{ date('Y') }}
        </div>

    </div>
</body>
</html>