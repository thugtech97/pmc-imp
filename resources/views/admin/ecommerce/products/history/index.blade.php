@extends('admin.layouts.app')

@section('pagetitle')
    Product History
@endsection

@section('pagecss')
<link href="{{ asset('lib/ion-rangeslider/css/ion.rangeSlider.min.css') }}" rel="stylesheet">
@endsection

@section('content')
<div class="container pd-x-0">
    <div class="d-sm-flex align-items-center justify-content-between mg-b-20 mg-lg-b-25 mg-xl-b-30">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mg-b-5">
					<li class="breadcrumb-item" aria-current="page"><a href="{{route('dashboard')}}">CMS</a></li>
                    <li class="breadcrumb-item" aria-current="page"><a href="{{route('products.index')}}">Products</a></li>
					<li class="breadcrumb-item active" aria-current="page">History</li>
                </ol>
            </nav>
            <h4 class="mg-b-0 tx-spacing--1">Product History Logs</h4>
        </div>
    </div>

    <div class="row row-sm">
	    <div class="col-md-12">
			<div class="filter-buttons mg-b-10">
				<div class="d-md-flex bd-highlight">
					<div class="col-md-3" style="padding:0px !important;">
						<div class="bd-highlight mg-r-10 mg-t-10">
							<div class="dropdown d-inline mg-r-5">
								<button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
									Filters
								</button>
								<div class="dropdown-menu">
									<form id="filterForm" class="pd-20">
										<div class="form-group">
											<label for="exampleDropdownFormEmail1">Sort by</label>
											<div class="custom-control custom-radio">
												<input type="radio" id="orderBy1" name="orderBy" class="custom-control-input" value="activity_date">
												<label class="custom-control-label" for="orderBy1">Activity Date</label>
											</div>
											<div class="custom-control custom-radio">
												<input type="radio" id="orderBy2" name="orderBy" class="custom-control-input" value="db_table">
												<label class="custom-control-label" for="orderBy2">Module</label>
											</div>
										</div>
										<div class="form-group">
											<label for="exampleDropdownFormEmail1">Sort order</label>
											<div class="custom-control custom-radio">
												<input type="radio" id="sortByAsc" name="sortBy" class="custom-control-input" value="asc">
												<label class="custom-control-label" for="sortByAsc">Ascending</label>
											</div>

											<div class="custom-control custom-radio">
												<input type="radio" id="sortByDesc" name="sortBy" class="custom-control-input" value="desc">
												<label class="custom-control-label" for="sortByDesc">Descending</label>
											</div>
										</div>
										<div class="form-group mg-b-40">
											<label class="d-block">Items displayed</label>
											<input id="displaySize" type="text" class="js-range-slider" name="perPage" value=""/>
										</div>
										<button id="filter" type="button" class="btn btn-sm btn-primary">Apply filters</button>
									</form>
								</div>
							</div>
						</div>
					</div>
					<div class="ml-auto bd-highlight mg-t-10 mg-r-10">
						<form class="form-inline" id="searchForm">
							<div class="search-form mg-r-10">
								<input name="search" type="search" id="search" class="form-control" placeholder="Search" value="">
								<button class="btn filter" type="button" id="btnSearch"><i data-feather="search"></i></button>
							</div>
						</form>
					</div>
				</div>
			</div>
	    </div>
        <div class="col-md-12">
            <div class="mg-b-10">
                <div class="table-responsive-lg table-audit">
                    <table class="table mg-b-0 table-light table-hover" style="word-break: break-all;">
                        <thead>
                            <tr>
                                <th scope="col" class="wd-15p">Name</th>
								<th scope="col" class="wd-10p">Type</th>
								<th scope="col" class="wd-30p">Changes</th>
                                <th scope="col" class="wd-10p">Activity Date</th>
                            </tr>
                        </thead>
                        <tbody>
							@forelse($logs as $log)
								@if(isset($log->changes['old']))
								<tr>
									<td>
										<a href="{{ route('users.show', $log->causer_id) }}"><strong>{{ ucwords(optional($log->causer)->fullname) }}</strong><br><span class="badge badge-primary">{{ optional($log->causer)->userRole($log->log_by) }}</span></a>
									</td>
									<td>{{ $log->description }}</td>
                                    <td>
										@foreach(array_diff( json_decode($log->changes(), true)['old'] , json_decode($log->changes(), true)['attributes'] ) as $key => $change)
											{{ $key . ': ' . $change . ' => ' . $log->changes['attributes'][$key] }} <br />
										@endforeach
                                    </td>
									<td>{{ date('F j, Y', strtotime($log->created_at)) }}</td>
								</tr>
								@endif
							@empty
								<tr><td colspan="8"><center>Activity not found!</center></td></tr>
							@endforelse
                        </tbody>
                    </table>
                </div>
            </div>
		</div>
		<div class="col-md-6">
            <div class="mg-t-5">
            </div>
        </div>
        <div class="col-md-6">
            <div class="text-md-right float-md-right mg-t-5">
                <div>
					{{ $logs->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal effect-scale" id="modal-values" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-body" style="word-break: break-all;">
				<div class="divider-text">New Value</div>
				<div>
					<p id="new_value"></p>
				</div>
				<div class="divider-text">Old Value</div>
				<div>
					<p id="old_value"></p>
				</div>
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
        let listingUrl = "{{ route('audit-logs.index') }}";
        let searchType = "";
    </script>
    <script src="{{ asset('js/listing.js') }}"></script>
@endsection

@section('customjs')
    <script>
		$(document).on('click','.log_values', function(){
			$('#modal-values').show();
			$('#new_value').html($(this).data('new'));
			$('#old_value').html($(this).data('old'));
		});
    </script>
@endsection
