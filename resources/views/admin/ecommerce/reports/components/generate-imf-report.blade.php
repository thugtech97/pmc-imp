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
        body {
            margin: 0mm !important;
            font-family: 'Lora', serif;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid black;
            padding: 3px;
            text-align: left;
        }
        th {
            font-size: 12px;
            background-color: #f2f2f2;
        }
        .text-align-center {
            text-align: center;
        }
        .text-bold {
            font-weight: bold
        }
        .item-style {
            font-size: 11px;
        }
        .header-style {
            font-size: 14px;
        }
        .mt {
            margin-top: 0.5px;
        }
        .logo-container {
            position: absolute;
            top: 0;
            right: 0;
        }
        .logo {
            width: 100px;
            height: auto;
        }

        .status-label-success {
            background-color: #0000FF;
            color: white;
            border-radius: 10px;
            padding: 3px 8px;
        }

        .status-label-danger {
            background-color: #FF0000;
            color: white;
            border-radius: 12px;
            padding: 3px 8px;
        }

    </style>
</head>
<body>  
    <table>
        <thead>
            <tr>
                <td colspan="2" rowspan="4" class="text-bold text-align-center header-style"><img src="{{ public_path('img/pmc-logo.png') }}" alt="Logo" class="logo"></td>
                <td colspan="3" class="text-bold header-style">PHILSAGA MINING CORPORATION</td>
                <td colspan="2" class="" style="font-size: 10px;">Warehouse @ Mill Site | Mine Site</td>
            </tr>
            <tr>
                <td colspan="3" class="text-bold header-style">Material Control Department</td>
                <td colspan="2" class="" style="font-size: 10px;">Phone: Local 2107 | 2134</td>
            </tr>
            <tr>
                <td colspan="3"></td>
                <td colspan="2" class="" style="font-size: 10px;">Fax: </td>
            </tr>
            <tr>
                <td colspan="3" class="text-bold text-align-center header-style"></td>
                <td colspan="2" class="" style="font-size: 10px;">Email: mcd@philsagamining.com</td>
            </tr>
            <tr>
                <td colspan="7" class="text-bold text-align-center header-style">IMF TRANSACTIONS</td>
            </tr>
            <tr>
                <td colspan="7" class="text-bold text-align-center header-style">{{ \Carbon\Carbon::parse($request->startdate)->format('F j, Y') }} to {{ \Carbon\Carbon::parse($request->enddate)->format('F j, Y') }}</td>
            </tr>
            <tr>
                <th class="text-align-center" width="12%">IMF Number</th>
                <th class="text-align-center" width="12%">Stock Code</th>
                <th class="text-align-center" width="12%">Department</th>
                <th class="text-align-center" width="18%">Date Prepared</th>
                <th class="text-align-center" width="18%">Date Submitted</th>
                <th class="text-align-center" width="10%">Type</th>
                <th class="text-align-center" width="18%">Status</th>
            </tr>                    
        </thead>
        <tbody class="item-style">   
            @forelse ($headers as $imf)
                <tr class="item-style">
                    <td class="text-align-center">{{ $imf->id }}</td>
                    <td class="text-align-center">{{ $imf->type == 'update' ? $imf->items[0]['stock_code'] : '--- N/A ---' }}</td>
                    <td class="text-align-center">{{ $imf->department }}</td>
                    <td class="text-align-center">{{ Carbon\Carbon::parse($imf->created_at)->format('F j, Y h:i A') }}</td>
                    <td class="text-align-center">{{ $imf->submitted_at ? Carbon\Carbon::parse($imf->submitted_at)->format('F j, Y h:i A') : 'N/A' }}</td>
                    <td class="text-align-center">{{ strtoupper($imf->type) }}</td>
                    <td class="text-align-center">{{ $imf->status }}</td>
                </tr>
            @empty
                <tr class="item-style">
                    <td colspan="7" class="text-align-center">No records found</td>
                </tr>
            @endforelse
            
        </tbody>
    </table>
    <table style="margin-top: 15px">
        <thead>
            <tr class="text-bold">
                <th class="text-align-center" colspan="2">Prepared by:</th>
                <th class="text-align-center">Reviewed by:</th>
            </tr>
        </thead>
        <tbody class="item-style">
            <tr>
                <td class="text-bold" width="10%">Name</td>
                <td>{{ strtoupper(\Auth::user()->name) }}</td>
                <td></td>
            </tr>
            <tr>
                <td class="text-bold" width="10%">Designation</td>
                <td>{{ \Auth::user()->role_name() }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>
</body>
</html>
