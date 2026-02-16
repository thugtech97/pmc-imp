<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Revised MRS Notification</title>
</head>
<body style="margin:0; padding:0; background-color:#f4f6f9; font-family: Arial, Helvetica, sans-serif;">

    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f6f9; padding:40px 0;">
        <tr>
            <td align="center">

                <!-- Main Container -->
                <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 4px 10px rgba(0,0,0,0.05);">

                    <!-- Header -->
                    <tr>
                        <td style="background-color:#1f2937; padding:20px 30px; text-align:left;">
                            <h2 style="margin:0; color:#ffffff; font-size:20px; letter-spacing:0.5px;">
                                Revised MRS Notification
                            </h2>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding:30px; color:#374151; font-size:14px; line-height:1.6;">

                            <p style="margin-top:0;">
                                Dear MCD,
                            </p>

                            <p>
                                Please be informed that a 
                                <strong style="color:#111827;">Revised MRS</strong> 
                                has been successfully created in the system.
                            </p>

                            <!-- Info Box -->
                            <table width="100%" cellpadding="0" cellspacing="0" 
                                   style="margin:20px 0; background:#f9fafb; border:1px solid #e5e7eb; border-radius:6px;">
                                <tr>
                                    <td style="padding:15px;">

                                        <p style="margin:5px 0;">
                                            <strong>MRS No:</strong> 
                                            {{ $sales->order_number ?? 'N/A' }}
                                        </p>

                                        <p style="margin:5px 0;">
                                            <strong>Updated By:</strong> 
                                            {{ auth()->user()->name ?? 'System' }}
                                        </p>

                                        <p style="margin:5px 0;">
                                            <strong>Status:</strong> 
                                            <span style="color:#2563eb; font-weight:bold;">
                                                {{ $sales->status }}
                                            </span>
                                        </p>

                                        <p style="margin:5px 0;">
                                            <strong>Date Revised:</strong> 
                                            {{ now()->format('Y-m-d h:i:s A') }}
                                        </p>

                                    </td>
                                </tr>
                            </table>

                            <p>
                                Kindly review the revised details in the system and proceed with the necessary actions accordingly.
                            </p>

                            <p style="margin-bottom:0;">
                                Thank you.
                            </p>

                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background:#f3f4f6; padding:15px 30px; text-align:center; font-size:12px; color:#6b7280;">
                            © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                        </td>
                    </tr>

                </table>
                <!-- End Container -->

            </td>
        </tr>
    </table>

</body>
</html>
