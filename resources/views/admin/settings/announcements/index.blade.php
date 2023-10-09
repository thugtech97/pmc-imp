@extends('admin.layouts.app')

@section('pagetitle')
    Manage Customer
@endsection

@section('pagecss')
    <link href="{{ asset('lib/ion-rangeslider/css/ion.rangeSlider.min.css') }}" rel="stylesheet">
    <style>
        .row-selected {
            background-color: #92b7da !important;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css">
@endsection

@section('content')
    <div class="container pd-x-0">
        <div class="d-sm-flex align-items-center justify-content-between mg-b-20 mg-lg-b-25 mg-xl-b-30">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1 mg-b-5">
                        <li class="breadcrumb-item" aria-current="page"><a href="{{route('dashboard')}}">CMS</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Announcements</li>
                    </ol>
                </nav>
                <h4 class="mg-b-0 tx-spacing--1">Manage Announcements</h4>
            </div>
        </div>

        <div class="row row-sm">

            <!-- Start Filters -->
            <div class="col-md-12">
                <div class="filter-buttons">
                    <div class="d-md-flex bd-highlight">
                        <div class="bd-highlight mg-r-10 mg-t-10">
                            <div class="dropdown d-inline mg-r-5">
                                <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    {{__('common.filters')}}
                                </button>
                                <div class="dropdown-menu">
                                    <form id="filterForm" class="pd-20">
                                        <div class="form-group">
                                            <label for="exampleDropdownFormEmail1">{{__('common.sort_by')}}</label>
                                            <div class="custom-control custom-radio">
                                                <input type="radio" id="orderBy1" name="orderBy" class="custom-control-input" value="updated_at" @if ($filter->orderBy == 'updated_at') checked @endif>
                                                <label class="custom-control-label" for="orderBy1">{{__('common.date_modified')}}</label>
                                            </div>
                                            <div class="custom-control custom-radio">
                                                <input type="radio" id="orderBy2" name="orderBy" class="custom-control-input" value="name" @if ($filter->orderBy == 'name') checked @endif>
                                                <label class="custom-control-label" for="orderBy2">{{__('common.title')}}</label>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="exampleDropdownFormEmail1">{{__('common.sort_order')}}</label>
                                            <div class="custom-control custom-radio">
                                                <input type="radio" id="sortByAsc" name="sortBy" class="custom-control-input" value="asc" @if ($filter->sortBy == 'asc') checked @endif>
                                                <label class="custom-control-label" for="sortByAsc">{{__('common.ascending')}}</label>
                                            </div>

                                            <div class="custom-control custom-radio">
                                                <input type="radio" id="sortByDesc" name="sortBy" class="custom-control-input" value="desc"  @if ($filter->sortBy == 'desc') checked @endif>
                                                <label class="custom-control-label" for="sortByDesc">{{__('common.descending')}}</label>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" id="showDeleted" name="showDeleted" class="custom-control-input" @if ($filter->showDeleted) checked @endif>
                                                <label class="custom-control-label" for="showDeleted">{{__('common.show_deleted')}}</label>
                                            </div>
                                        </div>
                                        <div class="form-group mg-b-40">
                                            <label class="d-block">{{__('common.item_displayed')}}</label>
                                            <input id="displaySize" type="text" class="js-range-slider" name="perPage" value="{{ $filter->perPage }}"/>
                                        </div>
                                        <button id="filter" type="button" class="btn btn-sm btn-primary">{{__('common.apply_filters')}}</button>
                                    </form>
                                </div>
                            </div>
                            @if(auth()->user()->has_access_to_route('pages.change.status') || auth()->user()->has_access_to_route('pages.delete'))
                                <div class="list-search d-inline">
                                    <div class="dropdown d-inline mg-r-10">
                                        <button class="btn btn-light btn-sm dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            Actions
                                        </button>

                                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                            @if(auth()->user()->has_access_to_route('pages.change.status'))
                                                <a class="dropdown-item" href="javascript:void(0)" onclick="change_status('PUBLISHED')">{{__('common.publish')}}</a>
                                                <a class="dropdown-item" href="javascript:void(0)" onclick="change_status('PRIVATE')">{{__('common.private')}}</a>
                                            @endif
                                            @if(auth()->user()->has_access_to_route('pages.delete'))
                                                <a class="dropdown-item tx-danger" href="javascript:void(0)" onclick="delete_page()">{{__('common.delete')}}</a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="ml-auto bd-highlight mg-t-10 mg-r-10">
                            <form class="form-inline" id="searchForm">
                                <div class="search-form mg-r-10">
                                    <input name="search" type="search" id="search" class="form-control"  placeholder="Search by Title" value="{{ $filter->search }}">
                                    <button class="btn filter" id="btnSearch"><i data-feather="search"></i></button>
                                </div>
                            </form>
                        </div>
                        <div class="mg-t-10">
                            @if(auth()->user()->has_access_to_route('pages.create'))
                                <a class="btn btn-primary btn-sm mg-b-20" href="{{ route('announcements.create') }}">{{__('standard.announcements.create')}}</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Filters -->


            <!-- Start Pages -->
            <div class="col-md-12">
                <div class="table-list mg-b-10">
                    <div class="table-responsive-lg">
                        <table class="table mg-b-0 table-light table-hover" style="width:100%;word-wrap: break-word;min-width:700px">
                            <thead>
                            <tr>
                                <th style="width: 10%;">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="checkbox_all">
                                        <label class="custom-control-label" for="checkbox_all"></label>
                                    </div>
                                </th>
                                <th style="width: 40%;overflow: hidden;">Name</th>
                                <th style="width: 10%;">Content</th>
                                <th style="width: 10%;">Schedule</th>
                                <th style="width: 10%;">Location</th>
                                <th style="width: 10%;">Status</th>
                                <th style="width: 10%;">Last Date Modified</th>
                                <th style="width: 10%;">Options</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($announcements as $page)
                                <tr id="row{{$page->id}}" class="row_cb">
                                    <th>
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input cb" id="cb{{$page->id}}">
                                            <label class="custom-control-label" for="cb{{$page->id}}"></label>
                                        </div>
                                    </th>
                                    <td style="overflow: hidden;text-overflow: ellipsis;" title="{{$page->name}}">
                                        <strong title="{{$page->name}}"> {{$page->name}}</strong>
                                    </td>
                                    <td>{{ $page->content }}</td>
                                    <td>{{ $page->schedule }}</td>
                                    <td>{{ $page->location }}</td>
                                    <td>{!! ucfirst(strtolower($page->status)) !!}</td>
                                    <td><span class="text-nowrap">{{ Setting::date_for_listing($page->updated_at) }}</span></td>
                                    <td>
                                        <nav class="nav table-options justify-content-end flex-nowrap">
                                            @if(auth()->user()->has_access_to_route('pages.show'))
                                                <a id="emailAnnouncement" class="nav-link" href="#" title="Email Announcement"><i data-feather="mail"></i></a>
                                            @endif
                                            @if(auth()->user()->has_access_to_route('pages.edit'))
                                                <a class="nav-link" href="{{route('announcements.edit',$page->id)}}" title="Edit Page"><i data-feather="edit"></i></a>
                                            @endif
                                            @if ((auth()->user()->has_access_to_route('pages.change.status') || (auth()->user()->has_access_to_route('pages.delete'))))
                                                <a class="nav-link" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i data-feather="settings"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-right">
                                                    @if(auth()->user()->has_access_to_route('pages.change.status'))
                                                        @if($page->status=='disabled')
                                                            <a class="dropdown-item" href="javascript:void(0);" onclick="post_form('{{route('announcements.change.status')}}','active',{{$page->id}})"> Activate</a>
                                                        @else
                                                            <a class="dropdown-item" href="javascript:void(0);" onclick="post_form('{{route('announcements.change.status')}}','disabled',{{$page->id}})"> Disable</a>
                                                        @endif
                                                    @endif
                                                    @if(auth()->user()->has_access_to_route('pages.delete'))
                                                        <button type="button" class="dropdown-item" data-target="#prompt-delete" data-toggle="modal" data-animation="effect-scale" data-id="{{ $page->id }}" data-name="{{ $page->name }}">Delete</button>
                                                        <form id="pageForm{{ $page->id }}" method="POST" action="{{ route('announcements.destroy', $page->id) }}">
                                                            @csrf
                                                            @method('DELETE')
                                                        </form>
                                                    @endif
                                                </div>
                                            @endif
                                        </nav>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" style="text-align: center;"> <p class="text-danger">No announcements found.</p></td>
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
                    @if ($announcements->firstItem() == null)
                        <p class="tx-gray-400 tx-12 d-inline">{{__('common.showing_zero_items')}}</p>
                    @else
                        <p class="tx-gray-400 tx-12 d-inline">Showing {{ $announcements->firstItem() }} to {{ $announcements->lastItem() }} of {{ $announcements->total() }} items</p>
                    @endif
                </div>
            </div>
            <div class="col-md-6">
                <div class="text-md-right float-md-right mg-t-5">
                    <div>
                        {{ $announcements->appends((array) $filter)->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form action="" id="posting_form" style="display:none;" method="post">
        @csrf
        <input type="text" id="pages" name="pages">
        <input type="text" id="status" name="status">
    </form>

    <div class="modal effect-scale" id="prompt-delete" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">{{__('common.delete_confirmation_title')}}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>{{__('common.delete_confirmation')}}</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-danger" id="btnDelete">Yes, Delete</button>
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal effect-scale" id="prompt-multiple-delete" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">{{__('common.delete_mutiple_confirmation_title')}}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    {{__('common.delete_mutiple_confirmation')}}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-danger" id="btnDeleteMultiple">Yes, Delete</button>
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal effect-scale" id="prompt-update-status" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">{{__('common.update_confirmation_title')}}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    You are about to <span id="pageStatus"></span> this item. Do you want to continue?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-danger" id="btnUpdateStatus">Yes, Update</button>
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal effect-scale" id="prompt-no-selected" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">{{__('common.no_selected_title')}}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>{{__('common.no_selected')}}</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('pagejs')
    <script src="{{ asset('lib/bselect/dist/js/bootstrap-select.js') }}"></script>
    <script src="{{ asset('lib/bselect/dist/js/i18n/defaults-en_US.js') }}"></script>
    <script src="{{ asset('lib/ion-rangeslider/js/ion.rangeSlider.min.js') }}"></script>

    <script>
        let listingUrl = "{{ route('pages.index') }}";
        let advanceListingUrl = "{{ route('pages.index.advance-search') }}";
        let searchType = "{{ $searchType }}";
    </script>
    <script src="{{ asset('js/listing.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>
@endsection

@section('customjs')
    <script>
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr("content")
                }
            });

            $('#emailAnnouncement').click(function() {
                swal({
                    title: 'Are you sure?',
                    text: "This will send email to all users",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#2ecc71',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, send them an email!'
                },
                function(isConfirm) {
                    if (isConfirm) {
                        $.ajax({
                            type: 'POST',
                            url: "{{ route('announcements.mail') }}",
                            success: function(response) {
                                if (response.data.success) {
                                    swal(
                                        'Good job!',
                                        'You clicked the button!',
                                        'success'
                                    );
                                }
                            }
                        });
                    } 
                    else {                    
                        swal.close();                   
                    }
                });
            });
        });

        function post_form(url,status,pages){

            $('#posting_form').attr('action',url);
            $('#pages').val(pages);
            $('#status').val(status);
            $('#posting_form').submit();

        }

        /*** handles the changing of status of multiple pages ***/

        let selected_pages = '';
        let new_status = '';
        function change_status(status) {
            new_status = status;
            var counter = 0;
            selected_pages = '';
            $(".cb:checked").each(function () {
                counter++;
                fid = $(this).attr('id');
                selected_pages += fid.substring(2, fid.length) + '|';
            });

            if (parseInt(counter) < 1) {
                $('#prompt-no-selected').modal('show');
                return false;
            } else {
                if (parseInt(counter) > 1) { // ask for confirmation when multiple pages was selected
                    status = (status == 'PUBLISHED') ? 'PUBLISH' : status;
                    $('#pageStatus').html(status)
                    $('#prompt-update-status').modal('show');
                } else {
                    post_form('{{route('pages.change.status')}}', status, selected_pages);
                }
            }

        }

        $('#btnUpdateStatus').on('click', function() {
            post_form('{{route('pages.change.status')}}', new_status, selected_pages);
        });

        function delete_page(){
            var counter = 0;
            $(".cb:checked").each(function(){
                counter++;
                fid = $(this).attr('id');
                selected_pages += fid.substring(2, fid.length)+'|';
            });

            if(parseInt(counter) < 1){
                $('#prompt-no-selected').modal('show');
                return false;
            }
            else{
                $('#prompt-multiple-delete').modal('show');
            }
        }

        $('#btnDeleteMultiple').on('click', function() {
            post_form('{{route('pages.delete')}}','',selected_pages);
        });

        function check_date(feld){
            if($('#search_datestart').val() && $('#search_dateend').val()){
                if($('#search_datestart').val() > $('#search_dateend').val()){
                    alert('Date Start should not be later than Date End!');
                    $('#'+feld).val('');
                    return false;
                }
            }
            else{
                return true;
            }
        }

        let pageId;
        $('#prompt-delete').on('show.bs.modal', function (e) {
            //get data-id attribute of the clicked element
            let album = e.relatedTarget;
            pageId = $(album).data('id');
            let pageName = $(album).data('name');

            $('#pageName').html(pageName);
        });

        $('#btnDelete').on('click', function() {
            $('#pageForm'+pageId).submit();
        });

    </script>
@endsection
