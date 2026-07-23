<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        @font-face {
            font-family: 'Lora';
            src: url('{{ public_path('fonts/lora/Lora-Regular.ttf') }}') format('truetype');
            font-weight: normal;
            font-style: normal;
        }
        @font-face {
            font-family: 'Lora';
            src: url('{{ public_path('fonts/lora/Lora-Bold.ttf') }}') format('truetype');
            font-weight: bold;
            font-style: normal;
        }

        * { box-sizing: border-box; }
        body {
            margin: 0mm !important;
            font-family: 'Lora', serif;
            font-size: 9px;
            color: #000;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        .bordered th,
        .bordered td {
            border: 1px solid #000;
            padding: 3px 4px;
            vertical-align: middle;
        }
        .no-border td { border: 0; padding: 1px 2px; }
        .center { text-align: center; }
        .right { text-align: right; }
        .bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }

        /* ---- Company header ---- */
        .company-title { font-size: 11px; font-weight: bold; line-height: 1.25; }
        .form-title { font-size: 10px; font-weight: bold; color: #1f3864; }
        .form-no { font-size: 9px; color: #1f3864; }
        .effective { font-size: 8px; font-style: italic; color: #1f3864; }
        .logo { width: 55px; height: auto; }

        /* ---- Header meta / purpose ---- */
        .meta-label { font-weight: bold; }
        .chk { font-family: 'DejaVu Sans', sans-serif; }

        /* ---- Item table ---- */
        .items th { background-color: #f2f2f2; font-size: 8px; text-align: center; }
        .items td { font-size: 8px; }
        .col-code { text-align: center; }

        /* ---- Status ---- */
        .status-label {
            background-color: #28a745;
            color: #fff;
            border-radius: 10px;
            padding: 2px 8px;
            font-size: 9px;
        }

        /* ---- Signatories ---- */
        .sign-line { border-top: 1px solid #000; width: 80%; margin-top: 26px; }
        .sign-name { font-weight: bold; }
        .sign-role { font-size: 8px; }

        /* ---- Legend / priority ---- */
        .legend { font-size: 8px; }
        .priority th, .priority td { border: 1px solid #000; padding: 2px; font-size: 8px; }
        .nothing-follows { text-align: center; font-size: 8px; padding: 4px 0; letter-spacing: 1px; }
    </style>
</head>
<body>
@php
    $selectedUpdateTypes = array_filter(array_map('trim', explode(',', $InventoryRequestData->update_type ?? '')));
    $isUpdate = $InventoryRequestData->type === 'update';
    $chk = function ($condition) {
        return $condition ? '[ X ]' : '[&nbsp;&nbsp;&nbsp;]';
    };
    $updateBoxes = ['Stock type update', 'Min-max update', 'Merge code', 'Obsolete', 'Bin location update', 'Others'];
@endphp

{{-- =================== COMPANY HEADER =================== --}}
<table class="bordered">
    <tr>
        <td style="width: 15%;" class="center">
            <img src="{{ public_path('img/pmc-logo.png') }}" alt="Logo" class="logo">
        </td>
        <td class="center">
            <div class="company-title">
                PHILSAGA MINING CORPORATION<br>
                MINDANAO MINERAL PROCESSING &amp; REFINING CORPORATION
            </div>
            <div style="font-size: 9px;">MATERIAL CONTROL DEPARTMENT</div>
            <div class="form-title">INVENTORY MAINTENANCE FORM</div>
            <div class="form-no">MCD-FORM-007 REV02</div>
            <div class="effective">Effective Date: May 10, 2023</div>
        </td>
        <td style="width: 15%;" class="center">
            <span class="status-label">{{ $InventoryRequestData->status }}</span>
        </td>
    </tr>
</table>

{{-- =================== META + PURPOSE =================== --}}
<table class="bordered" style="border-top: 0;">
    <tr>
        {{-- Left: Date Prepared / Department / Section / Division --}}
        <td style="width: 45%; vertical-align: top;">
            <table class="no-border">
                <tr>
                    <td style="width: 35%;"><span class="meta-label">IMF No.</span></td>
                    <td class="uppercase">{{ $InventoryRequestData->items[0]['imf_no'] ?? $InventoryRequestData->id }}</td>
                </tr>
                <tr>
                    <td><span class="meta-label">Date Prepared</span></td>
                    <td>{{ \Carbon\Carbon::parse($InventoryRequestData->created_at)->format('F d, Y') }}</td>
                </tr>
                <tr>
                    <td><span class="meta-label">Department</span></td>
                    <td class="uppercase">{{ $InventoryRequestData->department }}</td>
                </tr>
                <tr>
                    <td><span class="meta-label">Section</span></td>
                    <td class="uppercase">{{ $InventoryRequestData->section ?? '' }}</td>
                </tr>
                <tr>
                    <td><span class="meta-label">Division</span></td>
                    <td class="uppercase">{{ $InventoryRequestData->division ?? '' }}</td>
                </tr>
            </table>
        </td>
        {{-- Right: Purpose --}}
        <td style="vertical-align: top;">
            <div class="bold" style="margin-bottom: 3px;">Purpose</div>
            <div style="margin-bottom: 3px;">
                <span class="chk">{!! $chk(!$isUpdate) !!}</span>
                <span class="bold">New Stock item</span> - For registration in Classic Inventory System
            </div>
            <div>
                <span class="chk">{!! $chk($isUpdate) !!}</span>
                <span class="bold">Update</span> - For information / action in Classic Inventory System
            </div>
            <table class="no-border" style="margin-top: 2px;">
                @foreach (array_chunk($updateBoxes, 3) as $rowBoxes)
                    <tr>
                        @foreach ($rowBoxes as $box)
                            <td style="width: 33%;">
                                <span class="chk">{!! $chk(in_array($box, $selectedUpdateTypes)) !!}</span> {{ $box }}
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </table>
        </td>
    </tr>
</table>

{{-- =================== ITEMS =================== --}}
@if ($isUpdate)
    {{-- Update: old value / new value comparison --}}
    @php
        $old = $oldItems[0] ?? null;
        $new = $items[0] ?? null;
        $rows = [
            ['Item Description', 'item_description'],
            ['Brand', 'brand'],
            ['OEM ID', 'OEM_ID'],
            ['UoM', 'UoM'],
            ['Usage Rate Qty', 'usage_rate_qty'],
            ['Usage Frequency', 'usage_frequency'],
            ['Min Qty', 'min_qty'],
            ['Max Qty', 'max_qty'],
            ['Purpose', 'purpose'],
        ];
    @endphp
    <table class="bordered items" style="border-top: 0;">
        <thead>
            <tr>
                <th style="width: 20%;">Field</th>
                <th style="width: 40%;">Old Value</th>
                <th>New Value</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as [$label, $field])
                @php
                    $oldVal = $old ? $old->{$field} : '';
                    $newVal = $new ? $new->{$field} : '';
                    // Fall back to current value when nothing changed for this field.
                    $displayOld = ($old && $oldVal !== '' && $oldVal !== null) ? $oldVal : '';
                @endphp
                <tr>
                    <th style="text-align: left;">{{ $label }}</th>
                    <td>{{ $displayOld }}</td>
                    <td>{{ $newVal }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@else
    {{-- New stock: C1–C11 columnar layout --}}
    <table class="bordered items" style="border-top: 0;">
        <thead>
            <tr>
                <th style="width: 4%;">Item No.</th>
                <th style="width: 8%;">Stock Code</th>
                <th style="width: 22%;">Item Description<br>(Generic to Specific)</th>
                <th style="width: 9%;">Brand</th>
                <th style="width: 9%;">OEM ID</th>
                <th style="width: 5%;">UoM</th>
                <th style="width: 6%;">Usage Rate Qty</th>
                <th style="width: 8%;">Usage Frequency</th>
                <th>Item to be used for / Application / Purpose / Remarks</th>
                <th style="width: 5%;">Min Qty</th>
                <th style="width: 5%;">Max Qty</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($items as $index => $item)
                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td class="col-code">{{ ($item->stock_code && $item->stock_code !== 'null') ? $item->stock_code : '' }}</td>
                    <td class="uppercase">{{ $item->item_description }}</td>
                    <td class="uppercase">{{ $item->brand }}</td>
                    <td>{{ $item->OEM_ID }}</td>
                    <td class="center">{{ $item->UoM }}</td>
                    <td class="center">{{ $item->usage_rate_qty }}</td>
                    <td class="center">{{ $item->usage_frequency }}</td>
                    <td>{{ $item->purpose }}</td>
                    <td class="center">{{ $item->min_qty }}</td>
                    <td class="center">{{ $item->max_qty }}</td>
                </tr>
            @empty
                <tr><td colspan="11" class="center">No items.</td></tr>
            @endforelse
            <tr>
                <td colspan="11" class="nothing-follows">
                    ----------------------------------------------NOTHING FOLLOWS----------------------------------------------
                </td>
            </tr>
        </tbody>
    </table>
@endif

{{-- =================== SIGNATORIES =================== --}}
<table class="no-border" style="margin-top: 10px;">
    <tr>
        <td style="width: 50%;">Requested and Noted by:</td>
        <td style="width: 50%;">Reviewed and Acted by:</td>
    </tr>
    <tr>
        <td>
            <div class="sign-line"></div>
            <div class="sign-name uppercase">{{ $InventoryRequestData->user->name ?? '' }}</div>
            <div class="sign-role">End-User</div>
        </td>
        <td>
            <div class="sign-line"></div>
            <div class="sign-name uppercase">{!! $InventoryRequestData->planner_approved_by ? e($InventoryRequestData->planner_approved_by) : '&nbsp;' !!}</div>
            <div class="sign-role">MCD Planning Team</div>
        </td>
    </tr>
    <tr>
        <td>
            <div class="sign-line"></div>
            <div class="sign-name uppercase">{!! $InventoryRequestData->approved_by ? e($InventoryRequestData->approved_by) : '&nbsp;' !!}</div>
            <div class="sign-role">Department Head</div>
        </td>
        <td>
            <div class="sign-line"></div>
            <div class="sign-name uppercase">{!! $InventoryRequestData->approver_approved_by ? e($InventoryRequestData->approver_approved_by) : '&nbsp;' !!}</div>
            <div class="sign-role">Manager</div>
        </td>
    </tr>
</table>

{{-- =================== LEGEND + PRIORITY =================== --}}
<table style="margin-top: 12px;">
    <tr>
        {{-- Legend --}}
        <td style="width: 40%; vertical-align: top; padding-right: 8px;">
            <div class="bold" style="font-size: 9px; margin-bottom: 2px;">Legend: <span style="font-weight: normal;">End-users Guide</span></div>
            <ul class="legend" style="margin: 0; padding-left: 12px;">
                <li><b>Column 1, 3-9</b> — For New Stock Item Registration Only (usage rate based on estimated actual consumption).</li>
                <li><b>Column 1-6, 10-11</b> — For Min-Max Update Only.</li>
                <li><b>Column 1-6, 9</b> — For Merge Code, Stock type Update, Obsolete.</li>
                <li><b>Column 1-9</b> — For Usage Rate Update Only.</li>
                <li><b>Column 1-11</b> — Others (Choose Column to fill in).</li>
                <li>Bin Location Update (for MCD only).</li>
            </ul>
        </td>
        {{-- Priority --}}
        <td style="width: 60%; vertical-align: top;">
            <div class="bold" style="font-size: 10px; margin-bottom: 3px;">
                PRIORITY NO.: <span style="color: red;">{{ $InventoryRequestData->priority }}</span>
            </div>
            <table class="priority">
                <thead>
                    <tr>
                        <th style="width: 18%;">LABEL</th>
                        <th style="width: 22%;">EXISTING</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td rowspan="4">Priority 1</td><td rowspan="4">Very Urgent</td><td>Can stop operation of the end-user</td></tr>
                    <tr><td>If date needed is earlier than normal DLT</td></tr>
                    <tr><td>For legal compliances (regulatory requirements)</td></tr>
                    <tr><td>Safety and Medical emergency related requirements</td></tr>

                    <tr><td rowspan="3">Priority 2</td><td rowspan="3">High Priority but not urgent</td><td>Long lead time items (regularly ordered)</td></tr>
                    <tr><td>Can stop operation of the end-user</td></tr>
                    <tr><td>Safety and Medical related requirements but not urgent</td></tr>

                    <tr><td rowspan="6">Priority 3</td><td rowspan="6">Needed but not priority</td><td>SR Items</td></tr>
                    <tr><td>Project related items (projects are normally planned, thus not urgent)</td></tr>
                    <tr><td>Confirming POs</td></tr>
                    <tr><td>PMS (based on annual PMS Program)</td></tr>
                    <tr><td>CAPEX items (based on CAPEX Budget)</td></tr>
                    <tr><td>Annual Activities requirements (Detective and preventive maintenance)</td></tr>
                </tbody>
            </table>
        </td>
    </tr>
</table>

</body>
</html>
