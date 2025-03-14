@extends('admin.layouts.app')

@section('pagecss')
    <link href="{{ asset('lib/ion-rangeslider/css/ion.rangeSlider.min.css') }}" rel="stylesheet">
    <style>
        .row-selected {
            background-color: #92b7da !important;
        }
        #loadingSpinner {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            justify-content: center;
            align-items: center;
            z-index: 9999;
            font-size: 15px;
            width: 1em;
            height: 1em;
            border-radius: 50%;
            text-indent: -9999em;
            animation: mulShdSpin 1.1s infinite ease;
            transform: translateZ(0);
        }
        @keyframes mulShdSpin {
            0%,
            100% {
                box-shadow: 0em -2.6em 0em 0em #ffffff, 1.8em -1.8em 0 0em rgba(255,255,255, 0.2), 2.5em 0em 0 0em rgba(255,255,255, 0.2), 1.75em 1.75em 0 0em rgba(255,255,255, 0.2), 0em 2.5em 0 0em rgba(255,255,255, 0.2), -1.8em 1.8em 0 0em rgba(255,255,255, 0.2), -2.6em 0em 0 0em rgba(255,255,255, 0.5), -1.8em -1.8em 0 0em rgba(255,255,255, 0.7);
            }
            12.5% {
                box-shadow: 0em -2.6em 0em 0em rgba(255,255,255, 0.7), 1.8em -1.8em 0 0em #ffffff, 2.5em 0em 0 0em rgba(255,255,255, 0.2), 1.75em 1.75em 0 0em rgba(255,255,255, 0.2), 0em 2.5em 0 0em rgba(255,255,255, 0.2), -1.8em 1.8em 0 0em rgba(255,255,255, 0.2), -2.6em 0em 0 0em rgba(255,255,255, 0.2), -1.8em -1.8em 0 0em rgba(255,255,255, 0.5);
            }
            25% {
                box-shadow: 0em -2.6em 0em 0em rgba(255,255,255, 0.5), 1.8em -1.8em 0 0em rgba(255,255,255, 0.7), 2.5em 0em 0 0em #ffffff, 1.75em 1.75em 0 0em rgba(255,255,255, 0.2), 0em 2.5em 0 0em rgba(255,255,255, 0.2), -1.8em 1.8em 0 0em rgba(255,255,255, 0.2), -2.6em 0em 0 0em rgba(255,255,255, 0.2), -1.8em -1.8em 0 0em rgba(255,255,255, 0.2);
            }
            37.5% {
                box-shadow: 0em -2.6em 0em 0em rgba(255,255,255, 0.2), 1.8em -1.8em 0 0em rgba(255,255,255, 0.5), 2.5em 0em 0 0em rgba(255,255,255, 0.7), 1.75em 1.75em 0 0em #ffffff, 0em 2.5em 0 0em rgba(255,255,255, 0.2), -1.8em 1.8em 0 0em rgba(255,255,255, 0.2), -2.6em 0em 0 0em rgba(255,255,255, 0.2), -1.8em -1.8em 0 0em rgba(255,255,255, 0.2);
            }
            50% {
                box-shadow: 0em -2.6em 0em 0em rgba(255,255,255, 0.2), 1.8em -1.8em 0 0em rgba(255,255,255, 0.2), 2.5em 0em 0 0em rgba(255,255,255, 0.5), 1.75em 1.75em 0 0em rgba(255,255,255, 0.7), 0em 2.5em 0 0em #ffffff, -1.8em 1.8em 0 0em rgba(255,255,255, 0.2), -2.6em 0em 0 0em rgba(255,255,255, 0.2), -1.8em -1.8em 0 0em rgba(255,255,255, 0.2);
            }
            62.5% {
                box-shadow: 0em -2.6em 0em 0em rgba(255,255,255, 0.2), 1.8em -1.8em 0 0em rgba(255,255,255, 0.2), 2.5em 0em 0 0em rgba(255,255,255, 0.2), 1.75em 1.75em 0 0em rgba(255,255,255, 0.5), 0em 2.5em 0 0em rgba(255,255,255, 0.7), -1.8em 1.8em 0 0em #ffffff, -2.6em 0em 0 0em rgba(255,255,255, 0.2), -1.8em -1.8em 0 0em rgba(255,255,255, 0.2);
            }
            75% {
                box-shadow: 0em -2.6em 0em 0em rgba(255,255,255, 0.2), 1.8em -1.8em 0 0em rgba(255,255,255, 0.2), 2.5em 0em 0 0em rgba(255,255,255, 0.2), 1.75em 1.75em 0 0em rgba(255,255,255, 0.2), 0em 2.5em 0 0em rgba(255,255,255, 0.5), -1.8em 1.8em 0 0em rgba(255,255,255, 0.7), -2.6em 0em 0 0em #ffffff, -1.8em -1.8em 0 0em rgba(255,255,255, 0.2);
            }
            87.5% {
                box-shadow: 0em -2.6em 0em 0em rgba(255,255,255, 0.2), 1.8em -1.8em 0 0em rgba(255,255,255, 0.2), 2.5em 0em 0 0em rgba(255,255,255, 0.2), 1.75em 1.75em 0 0em rgba(255,255,255, 0.2), 0em 2.5em 0 0em rgba(255,255,255, 0.2), -1.8em 1.8em 0 0em rgba(255,255,255, 0.5), -2.6em 0em 0 0em rgba(255,255,255, 0.7), -1.8em -1.8em 0 0em #ffffff;
            }
        }
        body.search-active::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100vh; 
            background-color: rgba(0, 0, 0, 0.5); 
            z-index: 999; 
        }
    </style>
@endsection

@section('content')
    <div class="container pd-x-0">
        
        <div id="loadingSpinner"></div>

        <div class="d-sm-flex align-items-center justify-content-between mg-b-20 mg-lg-b-25 mg-xl-b-30">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1 mg-b-5">
                        <li class="breadcrumb-item" aria-current="page"><a href="{{route('dashboard')}}">CMS</a></li>
                        <li class="breadcrumb-item active" aria-current="page"><a href="{{route('products.index')}}">Stocks</a></li>
                    </ol>
                </nav>
                <h4 class="mg-b-0 tx-spacing--1">STOCK LIST</h4>
            </div>
        </div>

        <div class="row row-sm">

            <!-- Start Filters -->
            <div class="col-md-12 mb-2">
                <div class="filter-buttons">
                    <div class="d-md-flex bd-highlight">
                        <div class="bd-highlight mg-r-10 mg-t-10">
                            <div class="dropdown d-inline mg-r-5">
                                <button class="btn btn-primary btn-sm dropdown-toggle px-4" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    {{__('common.filters')}}
                                </button>
                                <a class="btn btn-success btn-sm mx-2" href="javascript:void(0)" data-toggle="modal" data-target="#advanceSearchModal">Advance Search</a>
                                <div class="dropdown-menu">
                                    <form id="filterForm" class="pd-20" method="GET" action="{{  route('products.index')  }}">
                                    @csrf
                                        <div class="form-group">
                                            <label for="sortBy">Filter by:</label>
                                        
                                            <div class="custom-control custom-radio">
                                                <input type="radio" id="productCodeWith" name="productCode" class="custom-control-input" value="1">
                                                <label class="custom-control-label" for="productCodeWith">With Stock Code</label>
                                            </div>
                                        
                                            <div class="custom-control custom-radio">
                                                <input type="radio" id="productCodeWithout" name="productCode" class="custom-control-input" value="2">
                                                <label class="custom-control-label" for="productCodeWithout">Without Stock Code</label>
                                            </div>
                                        </div>
                                        
                                       
                                        <div class="row m-0 p-0">
                                            <div class="col-8 p-0">
                                                <input type="submit" value="Apply filter" class="btn btn-sm btn-primary">
                                            </div>
                                            <div class="col-4 p-0">
                                                <button id="clear-filter" type="button" class="btn btn-sm btn-secondary">Clear</button>
                                            </div>
                                        </div>

                                    </form>
                                </div>
                            </div>
                            @if (auth()->user()->has_access_to_route('product.multiple.change.status') || auth()->user()->has_access_to_route('products.multiple.delete'))
                                <div class="list-search d-inline">
                                    <div class="dropdown d-inline mg-r-10">
                                        <button class="btn btn-light btn-sm dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            Actions
                                        </button>
                                        <span data-href="{{ route('products.export') }}" id="export" class="btn btn-success btn-sm" onclick="exportTasks(event.target);">Download Inventory Template</span>
                                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                            @if (auth()->user()->has_access_to_route('product.multiple.change.status'))
                                                <a class="dropdown-item" href="javascript:void(0)" onclick="change_status('PUBLISHED')">{{__('common.publish')}}</a>
                                                <a class="dropdown-item" href="javascript:void(0)" onclick="change_status('PRIVATE')">{{__('common.private')}}</a>
                                            @endif

                                            @if (auth()->user()->has_access_to_route('products.multiple.delete'))
                                                <a class="dropdown-item tx-danger" href="javascript:void(0)" onclick="delete_category()">{{__('common.delete')}}</a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="ml-auto bd-highlight mg-t-10">
                            <form class="form-inline" id="searchForm">
                                <div class="search-form">
                                    <input name="search" type="search" id="search" class="form-control" style="width: 200px;" placeholder="Search">
                                    <button class="btn filter" type="button" id="btnSearch"><i data-feather="search"></i></button>
                                </div>
                                <div>
                                    <a class="btn btn-sm btn-secondary px-4 mb-1 ml-2" href="{{route('products.index')}}">Reset</a>
                                </div>
                            </form>
                        </div>
                        <div class="mg-t-10">
                            <!-- <a class="btn btn-primary btn-sm mg-b-20" href="{{ route('products.create') }}">{{__('standard.products.product.create')}}</a> -->
                            <a class="btn btn-primary btn-sm mg-b-20 ml-2" href="javascript:;" onclick="$('#uploadProductModal').modal('show');">Import</a>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Filters -->
            @if(Session::has('success'))
                <div class="col-12 alert alert-success alert-dismissible fade show" role="alert">
                    <strong>Success!</strong> Stock list has been updated.
                    <button type="button" class="btn-close" data-dismiss="alert" aria-label="Close" style="background: none; border: none; position: absolute; right: 1rem;">
                        <i class="fa fa-times"></i>
                    </button>
                </div>
            @endif

            @if(Session::has('error'))
                <div class="col-12 alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Error!</strong> {{ Session::get('error') }}
                    <button type="button" class="btn-close" data-dismiss="alert" aria-label="Close" style="background: none; border: none; position: absolute; right: 1rem;">
                        <i class="fa fa-times"></i>
                    </button>
                </div>
            @endif

            <!-- Start Pages -->
            <div class="col-md-12">
                <div class="table-list mg-b-10">
                    <div class="table-responsive-lg">
                        <table class="table mg-b-0 table-light table-hover"  style="table-layout: fixed;word-wrap: break-word;">
                            <thead>
                                <tr>
                                    <th style="width: 5%;">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="checkbox_all">
                                            <label class="custom-control-label" for="checkbox_all"></label>
                                        </div>
                                    </th>
                                    <th style="width: 10%;overflow: hidden;">Code</th>
                                    <th style="width: 20%;overflow: hidden;">Name</th>
                                    <th style="width: 15%;">Category</th>
                                    <th style="width: 10%;">Price</th>
                                    <th style="width: 10%;">Inventory</th>
                                    <th style="width: 10%;">Status</th>
                                    <th style="width: 10%;">Last Date Modified</th>
                                    <th style="width: 10%; text-align: center">Actions</th>
                                </tr>
                            </thead>
                            <tbody> 
                                @forelse($products as $product)
                                <tr id="row{{$product->id}}" class="row_cb">
                                    <th>
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input cb" id="cb{{ $product->id }}">
                                            <label class="custom-control-label" for="cb{{ $product->id }}"></label>
                                        </div>
                                    </th>
                                    <td>{{ $product->code }}</td>
                                    <td>
                                        <strong @if($product->trashed()) style="text-decoration:line-through;" @endif> {{ $product->name }}</strong>
                                    </td>
                                    <td>{{ $product->category->name }}</td>
                                    <td>{{ $product->currency }} {{ number_format($product->price,2) }}</td>
                                    <td>{{ $product->inventory < 0 ? 0 : $product->inventory }}</td>
                                    <td>{{ $product->status }}</td>
                                    <td>{{ Setting::date_for_listing($product->updated_at) }}</td>
                                    <td>
                                        @if($product->trashed())
                                            @if (auth()->user()->has_access_to_route('product.restore'))
                                                <nav class="nav table-options">
                                                    <a class="nav-link" href="{{route('product.restore',$product->id)}}" title="Restore this product"><i data-feather="rotate-ccw"></i></a>
                                                </nav>
                                            @endif
                                        @else
                                            <nav class="nav table-options">
                                                <a class="nav-link" target="_blank" href="{{ route('product.front.show', $product->slug) }}" title="View Product Profile"><i data-feather="eye"></i></a>
                                                <a class="nav-link" href="{{ route('products.edit',$product->id) }}" title="Edit Product"><i data-feather="edit"></i></a>
                                                <a class="nav-link" href="javascript:void(0)" onclick="delete_one_category({{$product->id}},'{{$product->name}}')" title="Delete Product"><i data-feather="trash"></i></a>
                                                <a class="nav-link" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i data-feather="settings"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-right">
                                                    @if($product->status == 'PUBLISHED')
                                                        <a class="dropdown-item" href="{{route('product.single-change-status',[$product->id,'PRIVATE'])}}" > Private</a>
                                                    @else
                                                        <a class="dropdown-item" href="{{route('product.single-change-status',[$product->id,'PUBLISHED'])}}"> Publish</a>
                                                    @endif
                                                    <a class="dropdown-item" href="{{ route('products.history', $product->slug) }}"> History</a>
                                                </div>
                                            </nav>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <th colspan="9" style="text-align: center;"> <p class="text-danger">No stocks found.</p></th>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- End Pages -->
            <div class="col-md-6">
                <div class="mg-t-5">
                    @if ($products->firstItem() == null)
                        <p class="tx-gray-400 tx-12 d-inline">{{__('common.showing_zero_items')}}</p>
                    @else
                        <p class="tx-gray-400 tx-12 d-inline">Showing {{ $products->firstItem() }} to {{ $products->lastItem() }} of {{ $products->total() }} items</p>
                    @endif
                </div>
            </div>
            <div class="col-md-6">
                <div class="text-md-right float-md-right mg-t-5">
                    <div>
                        {{ $products->appends((array) $filter)->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="uploadProductModal" class="modal fade" role="dialog">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <form id="upload_excel" method="POST" action="{{ route('products.upload') }}" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h4 class="modal-title">Update Inventory</h4>
                    </div>
                    <div class="modal-body">
                        @csrf
                        <input type="file" name="file" id="file" accept=".xls, .xlsx, .xlsb" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-dismiss="modal">Close</button>
                        <input type="submit" value="Upload" class="btn btn-success">
                    </div>
                </form>
            </div>
        </div>
    </div>
    <form action="" id="posting_form" style="display:none;" method="post">
        @csrf
        <input type="text" id="products" name="products">
        <input type="text" id="status" name="status">
    </form>

    @include('admin.ecommerce.modals')
    @include('admin.ecommerce.products.modal-advance-search')
    
@endsection

@section('pagejs')
    <script src="{{ asset('lib/bselect/dist/js/bootstrap-select.js') }}"></script>
    <script src="{{ asset('lib/bselect/dist/js/i18n/defaults-en_US.js') }}"></script>
    <script src="{{ asset('lib/ion-rangeslider/js/ion.rangeSlider.min.js') }}"></script>

    <script>
        let listingUrl = "{{ route('products.index') }}";
        let searchType = "{{ $searchType }}";
    </script>
    <script src="{{ asset('js/listing.js') }}"></script>
@endsection

@section('customjs')
    <script>
        function exportTasks(_this) {
            let _url = $(_this).data('href');
            window.location.href = _url;
        }

        function post_form(url,status,product){
            $('#posting_form').attr('action',url);
            $('#products').val(product);
            $('#status').val(status);
            $('#posting_form').submit();
        }

        /*** handles the changing of status of multiple pages ***/
        function change_status(status){
            var counter = 0;
            var selected_videos = '';
            $(".cb:checked").each(function(){
                counter++;
                fid = $(this).attr('id');
                selected_videos += fid.substring(2, fid.length)+'|';
            });
            if(parseInt(counter) < 1){
                $('#prompt-no-selected').modal('show');
                return false;
            }
            else{
                if(parseInt(counter)>1){ // ask for confirmation when multiple pages was selected
                    $('#productStatus').html(status)
                    $('#prompt-update-status').modal('show');

                    $('#btnUpdateStatus').on('click', function() {
                        post_form("{{route('product.multiple.change.status')}}",status,selected_videos);
                    });
                }
                else{
                    post_form("{{route('product.multiple.change.status')}}",status,selected_videos);
                }
            }
        }

        function delete_category(){
            var counter = 0;
            var selected_products = '';
            $(".cb:checked").each(function(){
                counter++;
                fid = $(this).attr('id');
                selected_products += fid.substring(2, fid.length)+'|';
            });

            if(parseInt(counter) < 1){
                $('#prompt-no-selected').modal('show');
                return false;
            }
            else{
                $('#prompt-multiple-delete').modal('show');
                $('#btnDeleteMultiple').on('click', function() {
                    post_form("{{route('products.multiple.delete')}}",'',selected_products);
                });
            }
        }

        function delete_one_category(id,product){
            $('#prompt-delete').modal('show');
            $('#btnDelete').on('click', function() {
                post_form("{{route('product.single.delete')}}",'',id);
            });
        }

        $('#clear-filter').on('click', function(evt) { 
            evt.preventDefault(); 
            
            var formData = $('#filterForm').serializeArray();
            
            $('#dropdownMenuButton').next('.dropdown-menu').toggleClass('show');

            if (formData.length < 2) return;
        
            location.reload(); 
        });

        $(function() {
            $('#btnSearch').prop('disabled', true);

            $('#search').on('input', function() {
                var inputValue = $(this).val().trim();
                var searchButton = $('#btnSearch');
                if (inputValue === '') 
                {
                    searchButton.prop('disabled', true);
                } else {
                    searchButton.prop('disabled', false);
                }
            });

            $('#search').keypress(function(event) {
                if (event.which === 13) 
                { 
                    var inputValue = $(this).val().trim();

                    if (inputValue !== '') 
                    {
                        $('body').addClass('search-active');
                        $('#loadingSpinner').show();
                    } else {
                        event.preventDefault();
                    }
                }
            });

            $('#btnSearch').click(function() {
                $('body').addClass('search-active');
                $('#loadingSpinner').show();
            });

            $('#upload_excel').on('submit', function() {
                $('body').addClass('search-active');
                $('#loadingSpinner').show();
            });
        });
    </script>
@endsection
