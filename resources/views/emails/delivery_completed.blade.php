<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Delivery Completed</title>
</head>
<body style="margin:0; padding:0; background:#f4f6f9; font-family:Arial, Helvetica, sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" style="padding:40px 0;">
<tr>
<td align="center">

<table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:8px; overflow:hidden;">

    <!-- Header -->
    <tr>
        <td style="background:#16a34a; padding:20px 30px;">
            <h2 style="margin:0; color:#ffffff;">
                Delivery Completed
            </h2>
        </td>
    </tr>

    <!-- Body -->
    <tr>
        <td style="padding:30px; color:#374151; font-size:14px; line-height:1.6;">

            <p>Hi {{ $salesHeader->user->name ?? 'User' }},</p>

            <p>
                Good news! Your MRS request has been fully delivered.
            </p>

            <table width="100%" cellpadding="0" cellspacing="0"
                   style="margin:20px 0; background:#f9fafb; border:1px solid #e5e7eb; border-radius:6px;">
                <tr>
                    <td style="padding:15px;">
                        <p style="margin:5px 0;">
                            <strong>MRS No:</strong>
                            {{ $salesHeader->order_number }}
                        </p>
                        <p style="margin:5px 0;">
                            <strong>Total Quantity Ordered:</strong>
                            {{ $salesHeader->totalQtyOrdered() ?? '0' }}
                        </p>
                        <p style="margin:5px 0;">
                            <strong>Total Quantity Delivered:</strong>
                            {{ $salesHeader->totalQtyDelivered() ?? '0' }}
                        </p>
                    </td>
                </tr>
            </table>

            <p>
                All requested quantities have been successfully delivered.
            </p>

            <p>
                Thank you.
            </p>

        </td>
    </tr>

    <!-- Footer -->
    <tr>
        <td style="background:#f3f4f6; padding:15px 30px; text-align:center; font-size:12px; color:#6b7280;">
            © {{ date('Y') }} {{ config('app.name') }}
        </td>
    </tr>

</table>

</td>
</tr>
</table>

</body>
</html>
