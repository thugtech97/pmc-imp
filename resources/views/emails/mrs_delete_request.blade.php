<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MRS Deletion Request</title>
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

        .header {
            background: linear-gradient(135deg, #1a365d 0%, #2b6cb0 100%);
            padding: 36px 40px;
            text-align: center;
        }

        .header h1 {
            color: #ffffff;
            font-size: 22px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .header p {
            color: #bee3f8;
            font-size: 13px;
            margin-top: 6px;
        }

        .badge-warning {
            display: inline-block;
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffc107;
            border-radius: 20px;
            padding: 4px 14px;
            font-size: 12px;
            font-weight: 600;
            margin-top: 12px;
            letter-spacing: 0.4px;
        }

        .body {
            padding: 36px 40px;
        }

        .greeting {
            font-size: 15px;
            color: #4a5568;
            margin-bottom: 16px;
        }

        .message-body {
            background: #f7fafc;
            border-left: 4px solid #2b6cb0;
            border-radius: 0 8px 8px 0;
            padding: 16px 20px;
            font-size: 14px;
            color: #2d3748;
            margin-bottom: 28px;
            white-space: pre-line;
        }

        .section-title {
            font-size: 13px;
            font-weight: 700;
            color: #718096;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 12px;
        }

        .mrs-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 28px;
            font-size: 14px;
        }

        .mrs-table thead tr {
            background: #2b6cb0;
            color: #ffffff;
        }

        .mrs-table thead th {
            padding: 10px 14px;
            text-align: left;
            font-weight: 600;
            font-size: 12px;
            letter-spacing: 0.4px;
        }

        .mrs-table tbody tr {
            border-bottom: 1px solid #e2e8f0;
        }

        .mrs-table tbody tr:last-child {
            border-bottom: none;
        }

        .mrs-table tbody tr:nth-child(even) {
            background: #f7fafc;
        }

        .mrs-table tbody td {
            padding: 10px 14px;
            color: #2d3748;
        }

        .mrs-badge {
            display: inline-block;
            background: #ebf8ff;
            color: #2b6cb0;
            border: 1px solid #bee3f8;
            border-radius: 4px;
            padding: 2px 10px;
            font-size: 12px;
            font-weight: 600;
        }

        .info-box {
            background: #fff5f5;
            border: 1px solid #fed7d7;
            border-radius: 8px;
            padding: 14px 18px;
            font-size: 13px;
            color: #c53030;
            margin-bottom: 28px;
        }

        .info-box strong {
            display: block;
            margin-bottom: 4px;
        }

        .sender-box {
            background: #f0fff4;
            border: 1px solid #c6f6d5;
            border-radius: 8px;
            padding: 14px 18px;
            font-size: 14px;
            color: #276749;
            margin-bottom: 28px;
        }

        .divider {
            border: none;
            border-top: 1px solid #e2e8f0;
            margin: 24px 0;
        }

        .footer {
            background: #f7fafc;
            padding: 20px 40px;
            text-align: center;
            font-size: 12px;
            color: #a0aec0;
            border-top: 1px solid #e2e8f0;
        }

        .footer strong {
            color: #718096;
        }
    </style>
</head>
<body>
    <div class="wrapper">

        <!-- Header -->
        <div class="header">
            <h1>MRS Deletion Request</h1>
            <p>Material Requisition Slip System</p>
            <span class="badge-warning">⚠ Action Required</span>
        </div>

        <!-- Body -->
        <div class="body">

            <p class="greeting">
                Hello, <br><br>
                A deletion request has been submitted for the MRS records listed below.
                Please review and take the necessary action.
            </p>

            <!-- Custom Message -->
            <div class="section-title">Message from Requestor</div>
            <div class="message-body">{{ $emailBody }}</div>

            <!-- Sender Info -->
            <div class="sender-box">
                <strong>📋 Requested by: {{ $senderName }}</strong>
                Submitted on: {{ now()->format('F j, Y \a\t g:i A') }}
            </div>

            <!-- Warning -->
            <div class="info-box">
                <strong>⚠ Important Notice</strong>
                The following MRS records have been flagged for deletion. This action is irreversible once approved.
            </div>

            <!-- MRS List Table -->
            <div class="section-title">Selected MRS Records ({{ count($selectedMrs) }})</div>
            <table class="mrs-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>MRS Number</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($selectedMrs as $index => $mrs)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td><span class="mrs-badge">{{ $mrs }}</span></td>
                            <td>Pending Deletion</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <hr class="divider">

            <p style="font-size: 13px; color: #718096;">
                This is an automated notification from the MRS system. Please do not reply to this email directly.
                Contact your system administrator for any concerns.
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <strong>MRS Request System</strong><br>
            This email was generated automatically. &copy; {{ date('Y') }}
        </div>

    </div>
</body>
</html>