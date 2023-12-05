@extends('theme.main')

@section('pagecss')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css">
@endsection
@section('content')
    <div class="container content-wrap">
        <div class="row">
            <div class="col-md-12">
                <a href="{{ route('new-stock.index') }}" class="btn btn-primary mb-3">Back</a>

                <table>
                    <tr>
                        <td><strong>Department:</strong> {{ $request->department }}</td>
                    </tr>
                    <tr>
                        <td><strong>Section:</strong> {{ $request->section }}</td>
                    </tr>
                    <tr>
                        <td><strong>Division:</strong> {{ $request->division }}</td>

                    </tr>
                    <tr>

                        <td><strong>UoM:</strong> {{ $request->UoM }}</td>
                    </tr>
                    <tr>
                        <td><strong>Usage Rate Quantity:</strong> {{ $request->usage_rate_qty }}</td>
                    </tr>
                    <tr>
                        <td><strong>Usage Frequency:</strong> {{ $request->usage_frequency }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <table class="table">
                    <thead>
                        <th>Stock Code:</th>
                        <th>Item Description:</th>
                        <th>Purpose:</th>
                        <th>Min Quantity:</th>
                        <th>Brand:</th>
                        <th>Max Quantity:</th>
                        <th>OEM ID:</th>
                        <th>Status:</th>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                            <tr>
                                <td>{{ $item->stock_code }}</td>
                                <td class="text-uppercase">{{ $item->item_description }}</td>
                                <td>{{ $item->purpose}}</td>
                                <td>{{ $item->min_qty }}</td>
                                <td>{{ strtoupper($item->brand) }}</td>
                                <td>{{ $item->max_qty }}</td>
                                <td>{{ $item->OEM_ID }}</td>
                                <td>{{ $request->status }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" style="text-align: center;">No requests found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
