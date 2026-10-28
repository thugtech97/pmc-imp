<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Revised MRS Notification</title>
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
            background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 100%);
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
            color: #bfdbfe;
            font-size: 13px;
        }

        .badge-revised {
            display: inline-block;
            background: #dbeafe;
            color: #1d4ed8;
            border: 1px solid #93c5fd;
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

        /* ── Info Card ──────────────────────────────────────────── */
        .info-card {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 28px;
        }

        .info-card-header {
            background: #2563eb;
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
            border-bottom: 1px solid #dbeafe;
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
            color: #1e3a5f;
            font-weight: 700;
            font-size: 14px;
        }

        .mrs-number {
            display: inline-block;
            background: #dbeafe;
            color: #1d4ed8;
            border: 1px solid #93c5fd;
            border-radius: 6px;
            padding: 3px 12px;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.4px;
        }

        .status-badge {
            display: inline-block;
            background: #dbeafe;
            color: #1d4ed8;
            border: 1px solid #93c5fd;
            border-radius: 6px;
            padding: 3px 12px;
            font-size: 12px;
            font-weight: 600;
        }

        /* ── Notice Banner ──────────────────────────────────────── */
        .notice-banner {
            background: #eff6ff;
            border: 1px solid #93c5fd;
            border-radius: 8px;
            padding: 14px 18px;
            font-size: 13px;
            color: #1d4ed8;
            margin-bottom: 24px;
        }

        .notice-banner strong {
            display: block;
            margin-bottom: 4px;
            font-size: 13px;
        }

        .divider {
            border: none;
            border-top: 1px solid #e5e7eb;
            margin: 24px 0;
        }

        .closing {
            font-size: 14px;
            color: #4b5563;
            margin-bottom: 6px;
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
            <div class="header-icon">🔄</div>
            <h1>Revised MRS Notification</h1>
            <p>Material Requisition Slip System</p>
            <span class="badge-revised">✎ MRS Revised</span>
        </div>

        <!-- Body -->
        <div class="body">

            <p class="greeting">
                Dear <strong>MCD</strong>,
            </p>

            <p class="intro-text">
                Please be informed that a <strong>Revised MRS</strong> has been successfully
                created in the system. Kindly review the updated details below and proceed
                with the necessary actions accordingly.
            </p>

            <!-- MRS Info Card -->
            <div class="info-card">
                <div class="info-card-header">📋 Revised MRS Details</div>
                <div class="info-card-body">

                    <div class="info-row">
                        <span class="info-label">MRS Number</span>
                        <span class="mrs-number">{{ $sales->order_number ?? 'N/A' }}</span>
                    </div>

                    <div class="info-row">
                        <span class="info-label">Updated By</span>
                        <span class="info-value">{{ auth()->user()->name ?? 'System' }}</span>
                    </div>

                    <div class="info-row">
                        <span class="info-label">Current Status</span>
                        <span class="status-badge">{{ $sales->status ?? 'N/A' }}</span>
                    </div>

                    <div class="info-row">
                        <span class="info-label">Date Revised</span>
                        <span class="info-value">{{ now()->format('F j, Y \a\t g:i A') }}</span>
                    </div>

                </div>
            </div>

            <!-- Notice Banner -->
            <div class="notice-banner">
                <strong>📌 Action Required</strong>
                Please log in to the system to review the revised MRS and proceed with
                the required approval or processing steps.
            </div>

            <hr class="divider">

            <p class="closing">
                If you have any questions or concerns regarding this revision, please
                coordinate with the requesting department immediately.
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