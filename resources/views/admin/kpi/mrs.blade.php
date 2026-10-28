
@extends('admin.layouts.app')

@section('pagetitle')
    User Management
@endsection
@section('pagecss')

    <link href="{{ asset('lib/ionicons/css/ionicons.min.css') }}" rel="stylesheet">
    <link href="{{ asset('lib/cryptofont/css/cryptofont.min.css') }}" rel="stylesheet">
@endsection
@section('content')
    <div class="container pd-x-0">
        <div class="d-sm-flex align-items-center justify-content-between mg-b-20 mg-lg-b-25 mg-xl-b-30">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1 mg-b-5">
                        <li class="breadcrumb-item" aria-current="page">KPI</li>
                        <li class="breadcrumb-item active" aria-current="page">Users</li>
                    </ol>
                </nav>
                <h4 class="mg-b-0 tx-spacing--1">MRS-KPI</h4>
            </div>
        </div>
        <div class="row row-xs">
            <div class="col-12">
                <div class="card card-body">
                    <div class="d-md-flex align-items-center justify-content-between">
                        <div class="media align-sm-items-center">
                            <div class="tx-40 tx-lg-60 lh-0 tx-orange"><i class="cf cf-dash"></i></div>
                            <div class="media-body mg-l-15">
                                <h6 class="tx-12 tx-lg-14 tx-semibold tx-uppercase tx-spacing-1 mg-b-5">Performance score this month (Feb 2023) <span class="tx-normal tx-color-03">(KPI: 85% Completed on time)</span></h6>
                                <div class="d-flex align-items-baseline">
                                    <h2 class="tx-20 tx-lg-28 tx-normal tx-rubik tx-spacing--2 lh-2 mg-b-0">48 <small>MRS</small></h2>
                                    <h6 class="tx-11 tx-lg-16 tx-normal tx-rubik tx-success mg-l-5 lh-2 mg-b-0">90%(48/53)</h6>
                                </div>
                            </div><!-- media-body -->
                        </div><!-- media -->
                        <div class="d-flex flex-column flex-sm-row mg-t-20 mg-md-t-0">
                            <button class="btn btn-sm btn-white btn-uppercase pd-x-15"><i data-feather="download" class="mg-r-5"></i> Export CSV</button>
                            <button class="btn btn-sm btn-white btn-uppercase pd-x-15 mg-t-5 mg-sm-t-0 mg-sm-l-5"><i data-feather="share-2" class="mg-r-5"></i> Share</button>
                            <button class="btn btn-sm btn-white btn-uppercase pd-x-15 mg-t-5 mg-sm-t-0  mg-sm-l-5"><i data-feather="eye" class="mg-r-5"></i> Watch</button>
                        </div>
                    </div>
                </div>
            </div><!-- col -->
            <div class="col-lg-9 mg-t-10">
                <div class="card card-crypto">
                    <div class="card-header pd-y-8 d-sm-flex align-items-center justify-content-between">
                        <nav class="nav nav-line">
                            <a href="" class="nav-link">Hourly</a>
                            <a href="" class="nav-link">Daily</a>
                            <a href="" class="nav-link">Weekly</a>
                            <a href="" class="nav-link active">Monthly</a>
                            <a href="" class="nav-link">Yearly</a>
                        
                        </nav>
                        <div class="tx-12 tx-color-03 align-items-center d-none d-sm-flex">
                            <a href="" class="link-01 tx-spacing-1 tx-rubik">07/01/2022 <i class="icon ion-ios-arrow-down"></i></a>
                            <span class="mg-x-10">to</span>
                            <a href="" class="link-01 tx-spacing-1 tx-rubik">01/31/2023 <i class="icon ion-ios-arrow-down"></i></a>
                        </div>
                    </div><!-- card-header -->
                    <div class="card-body pd-10 pd-sm-20">
                        <div class="chart-eleven">
                            <div id="flotChart1" class="flot-chart"></div>
                        </div><!-- chart-eleven -->
                    </div><!-- card-body -->
                    <div class="card-footer pd-20">
                        <div class="row row-sm">
                            <div class="col-6 col-sm-4 col-md">
                                <h6 class="tx-uppercase tx-11 tx-spacing-1 tx-color-03 mg-b-10">Avg Completion</h6>
                                <h5 class="tx-normal tx-rubik mg-b-0 mg-r-5 lh-1">73</h5>
                            </div><!-- col -->
                            <div class="col-6 col-sm-4 col-md">
                                <h6 class="tx-uppercase tx-11 tx-spacing-1 tx-color-03 mg-b-10">Avg Processing</h6>
                                <h5 class="tx-normal tx-rubik mg-b-0 mg-r-5 lh-1">36</h5>
                            </div><!-- col -->
                            <div class="col-6 col-sm-4 col-md mg-t-20 mg-sm-t-0">
                                <h6 class="tx-uppercase tx-11 tx-spacing-1 tx-color-03 mg-b-10">Avg Approval</h6>
                                <h5 class="tx-normal tx-rubik mg-b-0 mg-r-5 lh-1">25 </h5>
                            </div><!-- col -->
                            <div class="col-6 col-sm-4 col-md-3 col-xl mg-t-20 mg-md-t-0">
                                <h6 class="tx-uppercase tx-11 tx-spacing-1 tx-color-03 mg-b-10"><span class="d-none d-sm-inline">All time </span>High</h6>
                                <h5 class="tx-normal tx-rubik mg-b-0 mg-r-5 lh-1">126</h5>
                            </div><!-- col -->
                            <div class="col-6 col-sm-4 col-md mg-t-20 mg-md-t-0">
                                <h6 class="tx-uppercase tx-11 tx-spacing-1 tx-color-03 mg-b-10">Performance Score</h6>
                                <h5 class="tx-normal tx-rubik mg-b-0 mg-r-5 lh-1"><small class="tx-danger"><i class="icon ion-md-arrow-down"></i></small> 3.4%</h5>
                            </div><!-- col -->
                        </div><!-- row -->
                    </div><!-- card-footer -->
                </div><!-- card -->
            </div><!-- col -->
            <div class="col-lg-3 mg-t-10">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mg-b-0">MRS Status for the last 30 days</h6>
                    </div><!-- card-header -->
                    <div class="card-body pd-lg-25">
                        <div class="chart-seven"><canvas id="chartDonut"></canvas></div>
                    </div><!-- card-body -->
                    <div class="card-footer pd-20">
                        <div class="row">
                            <div class="col-6">
                                <p class="tx-10 tx-uppercase tx-medium tx-color-03 tx-spacing-1 tx-nowrap mg-b-5">Unfulfill</p>
                                <div class="d-flex align-items-center">
                                    <div class="wd-10 ht-10 rounded-circle bg-pink mg-r-5"></div>
                                    <h5 class="tx-normal tx-rubik mg-b-0">22 <small class="tx-color-04">MRS</small></h5>
                                </div>
                            </div><!-- col -->
                            <div class="col-6">
                                <p class="tx-10 tx-uppercase tx-medium tx-color-03 tx-spacing-1 mg-b-5">Completed</p>
                                <div class="d-flex align-items-center">
                                    <div class="wd-10 ht-10 rounded-circle bg-primary mg-r-5"></div>
                                    <h5 class="tx-normal tx-rubik mg-b-0">15 <small class="tx-color-04">MRS</small></h5>
                                </div>
                            </div><!-- col -->
                            <div class="col-6 mg-t-20">
                                <p class="tx-10 tx-uppercase tx-medium tx-color-03 tx-spacing-1 mg-b-5">Partial</p>
                                <div class="d-flex align-items-center">
                                    <div class="wd-10 ht-10 rounded-circle bg-teal mg-r-5"></div>
                                    <h5 class="tx-normal tx-rubik mg-b-0">32 <small class="tx-color-04">MRS</small></h5>
                                </div>
                            </div><!-- col -->
                            <div class="col-6 mg-t-20">
                                <p class="tx-10 tx-uppercase tx-medium tx-color-03 tx-spacing-1 mg-b-5">On Hold</p>
                                <div class="d-flex align-items-center">
                                    <div class="wd-10 ht-10 rounded-circle bg-orange mg-r-5"></div>
                                    <h5 class="tx-normal tx-rubik mg-b-0">28 <small class="tx-color-04">MRS</small></h5>
                                </div>
                            </div><!-- col -->
                        </div><!-- row -->
                    </div><!-- card-footer -->
                </div><!-- card -->
            </div><!-- col -->
            <div class="col-12 mg-t-10">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mg-b-0">Performance Score per User (As of Feb 2, 2023)</h6>
                    </div><!-- card-header -->
                    <div class="card-body pd-0">
                        <div class="row no-gutters">
                            <div class="col col-sm-6 col-lg">
                                <div class="crypto">
                                    <div class="media mg-b-10">
                                        <div class="crypto-icon bg-success">
                                            <i class="cf cf-dash"></i>
                                        </div><!-- crypto-icon -->
                                        <div class="media-body pd-l-8">
                                            <h6 class="tx-11 tx-spacing-1 tx-uppercase tx-semibold mg-b-5">ASPERA <span class="tx-color-03 tx-normal">KRYSTALLE</span></h6>
                                            <div class="d-flex align-items-baseline tx-rubik">
                                                <h5 class="tx-20 mg-b-0">6</h5>
                                                <p class="mg-b-0 tx-11 tx-success mg-l-3"><i class="icon ion-md-arrow-up"></i> 2%</p>
                                            </div>
                                        </div><!-- media-body -->
                                    </div><!-- media -->

                                    <div class="chart-twelve">
                                        <div id="flotChart2" class="flot-chart"></div>
                                    </div><!-- chart-twelve -->

                                    <div class="pos-absolute b-5 l-20 tx-medium">
                                        <label class="tx-9 tx-uppercase tx-sans tx-color-03">
                                            <a href="" class="link-01 tx-semibold">6</a> Completed MRS
                                        </label>
                                        <label class="tx-9 tx-uppercase tx-sans tx-color-03 mg-l-10">
                                            <a href="" class="link-01 tx-semibold">11</a> Pending
                                        </label>
                                    </div>
                                </div><!-- crypto -->
                            </div>
                            <div class="col col-sm-6 col-lg bd-t bd-sm-t-0 bd-sm-l">
                                <div class="crypto">
                                    <div class="media mg-b-10">
                                        <div class="crypto-icon bg-success">
                                            <i class="cf cf-dash"></i>
                                        </div>
                                        <div class="media-body pd-l-8">
                                            <h6 class="tx-11 tx-spacing-1 tx-uppercase tx-semibold mg-b-5">RODRIGO<span class="tx-color-03 tx-normal"> ZOSIMO</span></h6>
                                            <div class="d-flex align-items-baseline tx-rubik">
                                                <h5 class="tx-20 mg-b-0">11</h5>
                                                <p class="mg-b-0 tx-11 tx-success mg-l-3"><i class="icon ion-md-arrow-up"></i> 4.34%</p>
                                            </div>
                                        </div><!-- media-body -->
                                    </div><!-- media -->

                                    <div class="chart-twelve">
                                        <div id="flotChart3" class="flot-chart"></div>
                                    </div><!-- chart-twelve -->

                                    <div class="pos-absolute b-5 l-20 tx-medium">
                                        <label class="tx-9 tx-uppercase tx-sans tx-color-03">
                                            <a href="" class="link-01 tx-semibold">11</a> Completed MRS
                                        </label>
                                        <label class="tx-9 tx-uppercase tx-sans tx-color-03 mg-l-10">
                                            <a href="" class="link-01 tx-semibold">7</a> Pending
                                        </label>
                                    </div>
                                </div><!-- crypto -->
                            </div>
                            <div class="col col-sm-6 col-lg bd-t bd-lg-t-0 bd-lg-l">
                                <div class="crypto">
                                    <div class="media mg-b-10">
                                        <div class="crypto-icon bg-danger">
                                            <i class="cf cf-dash"></i>
                                        </div><!-- crypto-icon -->
                                        <div class="media-body pd-l-8">
                                            <h6 class="tx-11 tx-spacing-1 tx-uppercase tx-semibold mg-b-5">FUSINGAN <span class="tx-color-03 tx-normal">KATHERINE</span></h6>
                                            <div class="d-flex align-items-baseline tx-rubik">
                                                <h5 class="tx-20 mg-b-0">8</h5>
                                                <p class="mg-b-0 tx-11 tx-danger mg-l-3"><i class="icon ion-md-arrow-down"></i> 2.17%</p>
                                            </div>
                                        </div><!-- media-body -->
                                    </div><!-- media -->

                                    <div class="chart-twelve">
                                        <div id="flotChart4" class="flot-chart"></div>
                                    </div><!-- chart-twelve -->

                                    <div class="pos-absolute b-5 l-20 tx-medium">
                                        <label class="tx-9 tx-uppercase tx-sans tx-color-03">
                                            <a href="" class="link-01 tx-semibold">8</a> Completed MRS
                                        </label>
                                        <label class="tx-9 tx-uppercase tx-sans tx-color-03 mg-l-10">
                                            <a href="" class="link-01 tx-semibold">16</a> Pending
                                        </label>
                                    </div>
                                </div><!-- crypto -->
                            </div>
                            <div class="col col-sm-6 col-lg bd-t bd-lg-t-0 bd-sm-l">
                                <div class="crypto">
                                    <div class="media mg-b-10">
                                        <div class="crypto-icon bg-warning">
                                            <i class="cf cf-dash"></i>
                                        </div><!-- crypto-icon -->
                                        <div class="media-body pd-l-8">
                                            <h6 class="tx-11 tx-spacing-1 tx-uppercase tx-semibold mg-b-5">TOYOGON <span class="tx-color-03 tx-normal">ERICKA</span></h6>
                                            <div class="d-flex align-items-baseline tx-rubik">
                                                <h5 class="tx-20 mg-b-0">23</h5>
                                                <p class="mg-b-0 tx-11 tx-danger mg-l-3"><i class="icon ion-md-arrow-down"></i> 0.80%</p>
                                            </div>
                                        </div><!-- media-body -->
                                    </div><!-- media -->

                                    <div class="chart-twelve">
                                        <div id="flotChart5" class="flot-chart"></div>
                                    </div><!-- chart-twelve -->

                                    <div class="pos-absolute b-5 l-20 tx-medium">
                                        <label class="tx-9 tx-uppercase tx-sans tx-color-03">
                                            <a href="" class="link-01 tx-semibold">23</a> Completed MRS
                                        </label>
                                        <label class="tx-9 tx-uppercase tx-sans tx-color-03 mg-l-10">
                                            <a href="" class="link-01 tx-semibold">6</a> Pending
                                        </label>
                                    </div>
                                </div><!-- crypto -->
                            </div>
                        </div><!-- row -->
                    </div><!-- card-body -->
                </div><!-- card -->
            </div><!-- col -->
            <div class="col-3 mg-t-10">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <h6 class="lh-5 mg-b-0">ASPERA</h6>
                        <nav class="nav nav-row-xs align-items-center">
                            <a href="" class="nav-link">1D</a>
                            <a href="" class="nav-link active">1W</a>
                            <a href="" class="nav-link">1M</a>
                            <a href="" class="nav-link">1Y</a>
                        </nav>
                    </div><!-- card-header -->
                    <div class="card-body pd-0 pos-relative">
                        <div class="pos-absolute t-20 l-20">
                            <p class="tx-uppercase tx-11 tx-spacing-1 tx-color-03 tx-medium mg-b-0">KPI: 18 On time MRS</p>
                            <div class="d-flex align-items-baseline">
                                <h2 class="tx-normal tx-rubik tx-spacing--2 mg-b-0"><small class="tx-color-03">40</small> </h2>
                                <span class="tx-rubik tx-success mg-l-5"><i class="icon ion-md-arrow-up"></i> 20 (2.45%)</span>
                            </div>
                        </div>
                        <div class="chart-fourteen">
                            <div id="flotChart6" class="flot-chart flotChart6"></div>
                        </div><!-- chart-fourteen -->
                        <ul class="list-group list-group-flush mg-t-15">
                            <li class="list-group-item d-flex align-items-center">
                                <div class="crypto-icon crypto-icon-sm bg-orange">
                                    <i class="cf cf-eth"></i>
                                </div>
                                <div class="mg-l-15">
                                    <h6 class="lh-5 mg-b-0">Jan 2023</h6>
                                    <span class="d-block tx-color-03 tx-uppercase tx-11 tx-medium">20/40 MRS</span>
                                </div>
                                <div class="mg-l-auto text-right">
                                    <p class="mg-b-0 tx-rubik">11%</p>
                                    <span class="d-block tx-color-03 tx-uppercase tx-11 tx-rubik text-danger">-5%</span>
                                </div>
                            </li>
                            <li class="list-group-item d-flex align-items-center">
                                <div class="crypto-icon crypto-icon-sm bg-secondary">
                                    <i class="cf cf-eth"></i>
                                </div>
                                <div class="mg-l-15">
                                    <h6 class="lh-5 mg-b-0">Dec 2022</h6>
                                    <span class="d-block tx-color-03 tx-uppercase tx-11 tx-medium">16/23 MRS</span>
                                </div>
                                <div class="mg-l-auto text-right">
                                    <p class="mg-b-0 tx-rubik text-danger">-11%</p>
                                    <span class="d-block tx-color-03 tx-uppercase tx-11 tx-rubik text-danger">-24%</span>
                                </div>
                            </li>
                            <li class="list-group-item d-flex align-items-center">
                                <div class="crypto-icon crypto-icon-sm bg-litecoin">
                                    <i class="cf cf-eth"></i>
                                </div>
                                <div class="mg-l-15">
                                    <h6 class="lh-5 mg-b-0">Nov 2022</h6>
                                    <span class="d-block tx-color-03 tx-uppercase tx-11 tx-medium">22/32 MRS</span>
                                </div>
                                <div class="mg-l-auto text-right">
                                    <p class="mg-b-0 tx-rubik">+22%</p>
                                    <span class="d-block tx-color-03 tx-uppercase tx-11 tx-rubik">+5%</span>
                                </div>
                            </li>
                            <li class="list-group-item d-flex align-items-center">
                                <div class="crypto-icon crypto-icon-sm bg-success">
                                    <i class="cf cf-eth"></i>
                                </div>
                                <div class="mg-l-15">
                                    <h6 class="lh-5 mg-b-0">Oct 2022</h6>
                                    <span class="d-block tx-color-03 tx-uppercase tx-11 tx-medium">14/23 MRS</span>
                                </div>
                                <div class="mg-l-auto text-right">
                                    <p class="mg-b-0 tx-rubik text-danger">-22%</p>
                                    <span class="d-block tx-color-03 tx-uppercase tx-11 tx-rubik text-danger">-33%</span>
                                </div>
                            </li>
                            <li class="list-group-item d-flex align-items-center">
                                <div class="crypto-icon crypto-icon-sm bg-primary">
                                    <i class="cf cf-eth"></i>
                                </div>
                                <div class="mg-l-15">
                                    <h6 class="lh-5 mg-b-0">Sep 2022</h6>
                                    <span class="d-block tx-color-03 tx-uppercase tx-11 tx-medium">21/48 MRS</span>
                                </div>
                                <div class="mg-l-auto text-right">
                                    <p class="mg-b-0 tx-rubik">+16%</p>
                                    <span class="d-block tx-color-03 tx-uppercase tx-11 tx-rubik">+23%</span>
                                </div>
                            </li>
                        </ul>
                    </div><!-- card-body -->
                </div><!-- card -->
            </div>
            <div class="col-3 mg-t-10">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <h6 class="lh-5 mg-b-0">RODRIGO</h6>
                        <nav class="nav nav-row-xs align-items-center">
                            <a href="" class="nav-link">1D</a>
                            <a href="" class="nav-link active">1W</a>
                            <a href="" class="nav-link">1M</a>
                            <a href="" class="nav-link">1Y</a> 
                        </nav>
                    </div><!-- card-header -->
                    <div class="card-body pd-0 pos-relative">
                        <div class="pos-absolute t-20 l-20">
                            <p class="tx-uppercase tx-11 tx-spacing-1 tx-color-03 tx-medium mg-b-0">KPI: 18 On time MRS</p>
                            <div class="d-flex align-items-baseline">
                                <h2 class="tx-normal tx-rubik tx-spacing--2 mg-b-0"><small class="tx-color-03">33</small> </h2>
                                <span class="tx-rubik tx-success mg-l-5 text-danger"><i class="icon ion-md-arrow-down text-danger"></i> 16 (2.45%)</span>
                            </div>
                        </div>
                        <div class="chart-fourteen">
                            <div id="flotChart6a1" class="flot-chart flotChart6"></div>
                        </div><!-- chart-fourteen -->
                        <ul class="list-group list-group-flush mg-t-15">
                            <li class="list-group-item d-flex align-items-center">
                                <div class="crypto-icon crypto-icon-sm bg-orange">
                                    <i class="cf cf-eth"></i>
                                </div>
                                <div class="mg-l-15">
                                    <h6 class="lh-5 mg-b-0">Jan 2023</h6>
                                    <span class="d-block tx-color-03 tx-uppercase tx-11 tx-medium">16/33 MRS</span>
                                </div>
                                <div class="mg-l-auto text-right">
                                    <p class="mg-b-0 tx-rubik">13%</p>
                                    <span class="d-block tx-color-03 tx-uppercase tx-11 tx-rubik text-danger">-8%</span>
                                </div>
                            </li>
                            <li class="list-group-item d-flex align-items-center">
                                <div class="crypto-icon crypto-icon-sm bg-secondary">
                                    <i class="cf cf-eth"></i>
                                </div>
                                <div class="mg-l-15">
                                    <h6 class="lh-5 mg-b-0">Dec 2022</h6>
                                    <span class="d-block tx-color-03 tx-uppercase tx-11 tx-medium">19/21 MRS</span>
                                </div>
                                <div class="mg-l-auto text-right">
                                    <p class="mg-b-0 tx-rubik text-danger">-6%</p>
                                    <span class="d-block tx-color-03 tx-uppercase tx-11 tx-rubik text-danger">-3%</span>
                                </div>
                            </li>
                            <li class="list-group-item d-flex align-items-center">
                                <div class="crypto-icon crypto-icon-sm bg-litecoin">
                                    <i class="cf cf-eth"></i>
                                </div>
                                <div class="mg-l-15">
                                    <h6 class="lh-5 mg-b-0">Nov 2022</h6>
                                    <span class="d-block tx-color-03 tx-uppercase tx-11 tx-medium">22/32 MRS</span>
                                </div>
                                <div class="mg-l-auto text-right">
                                    <p class="mg-b-0 tx-rubik">+21%</p>
                                    <span class="d-block tx-color-03 tx-uppercase tx-11 tx-rubik">+7%</span>
                                </div>
                            </li>
                            <li class="list-group-item d-flex align-items-center">
                                <div class="crypto-icon crypto-icon-sm bg-success">
                                    <i class="cf cf-eth"></i>
                                </div>
                                <div class="mg-l-15">
                                    <h6 class="lh-5 mg-b-0">Oct 2022</h6>
                                    <span class="d-block tx-color-03 tx-uppercase tx-11 tx-medium">16/23 MRS</span>
                                </div>
                                <div class="mg-l-auto text-right">
                                    <p class="mg-b-0 tx-rubik text-danger">-12%</p>
                                    <span class="d-block tx-color-03 tx-uppercase tx-11 tx-rubik text-danger">-24%</span>
                                </div>
                            </li>
                            <li class="list-group-item d-flex align-items-center">
                                <div class="crypto-icon crypto-icon-sm bg-primary">
                                    <i class="cf cf-eth"></i>
                                </div>
                                <div class="mg-l-15">
                                    <h6 class="lh-5 mg-b-0">Sep 2022</h6>
                                    <span class="d-block tx-color-03 tx-uppercase tx-11 tx-medium">21/49 MRS</span>
                                </div>
                                <div class="mg-l-auto text-right">
                                    <p class="mg-b-0 tx-rubik">+15%</p>
                                    <span class="d-block tx-color-03 tx-uppercase tx-11 tx-rubik">+24%</span>
                                </div>
                            </li>
                        </ul>
                    </div><!-- card-body -->
                </div><!-- card -->
            </div>
            <div class="col-3 mg-t-10">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <h6 class="lh-5 mg-b-0">FUSINGAN</h6>
                        <nav class="nav nav-row-xs align-items-center">
                            <a href="" class="nav-link">1D</a>
                            <a href="" class="nav-link active">1W</a>
                            <a href="" class="nav-link">1M</a>
                            <a href="" class="nav-link">1Y</a>
                        </nav>
                    </div><!-- card-header -->
                    <div class="card-body pd-0 pos-relative">
                        <div class="pos-absolute t-20 l-20">
                            <p class="tx-uppercase tx-11 tx-spacing-1 tx-color-03 tx-medium mg-b-0">KPI: 18 On time MRS</p>
                            <div class="d-flex align-items-baseline">
                                <h2 class="tx-normal tx-rubik tx-spacing--2 mg-b-0"><small class="tx-color-03">56</small> </h2>
                                <span class="tx-rubik tx-success mg-l-5"><i class="icon ion-md-arrow-up"></i> 35 (194%)</span>
                            </div>
                        </div>
                        <div class="chart-fourteen">
                            <div id="flotChart6a2" class="flot-chart flotChart6"></div>
                        </div><!-- chart-fourteen -->
                        <ul class="list-group list-group-flush mg-t-15">
                            <li class="list-group-item d-flex align-items-center">
                                <div class="crypto-icon crypto-icon-sm bg-orange">
                                    <i class="cf cf-eth"></i>
                                </div>
                                <div class="mg-l-15">
                                    <h6 class="lh-5 mg-b-0">Jan 2023</h6>
                                    <span class="d-block tx-color-03 tx-uppercase tx-11 tx-medium">35/56 MRS</span>
                                </div>
                                <div class="mg-l-auto text-right">
                                    <p class="mg-b-0 tx-rubik">12%</p>
                                    <span class="d-block tx-color-03 tx-uppercase tx-11 tx-rubik text-danger">-15%</span>
                                </div>
                            </li>
                            <li class="list-group-item d-flex align-items-center">
                                <div class="crypto-icon crypto-icon-sm bg-secondary">
                                    <i class="cf cf-eth"></i>
                                </div>
                                <div class="mg-l-15">
                                    <h6 class="lh-5 mg-b-0">Dec 2022</h6>
                                    <span class="d-block tx-color-03 tx-uppercase tx-11 tx-medium">32/46 MRS</span>
                                </div>
                                <div class="mg-l-auto text-right">
                                    <p class="mg-b-0 tx-rubik text-danger">-6%</p>
                                    <span class="d-block tx-color-03 tx-uppercase tx-11 tx-rubik text-danger">-4%</span>
                                </div>
                            </li>
                            <li class="list-group-item d-flex align-items-center">
                                <div class="crypto-icon crypto-icon-sm bg-litecoin">
                                    <i class="cf cf-eth"></i>
                                </div>
                                <div class="mg-l-15">
                                    <h6 class="lh-5 mg-b-0">Nov 2022</h6>
                                    <span class="d-block tx-color-03 tx-uppercase tx-11 tx-medium">21/33 MRS</span>
                                </div>
                                <div class="mg-l-auto text-right">
                                    <p class="mg-b-0 tx-rubik">+16%</p>
                                    <span class="d-block tx-color-03 tx-uppercase tx-11 tx-rubik">+5%</span>
                                </div>
                            </li>
                            <li class="list-group-item d-flex align-items-center">
                                <div class="crypto-icon crypto-icon-sm bg-success">
                                    <i class="cf cf-eth"></i>
                                </div>
                                <div class="mg-l-15">
                                    <h6 class="lh-5 mg-b-0">Oct 2022</h6>
                                    <span class="d-block tx-color-03 tx-uppercase tx-11 tx-medium">28/35 MRS</span>
                                </div>
                                <div class="mg-l-auto text-right">
                                    <p class="mg-b-0 tx-rubik text-danger">-19%</p>
                                    <span class="d-block tx-color-03 tx-uppercase tx-11 tx-rubik text-danger">-31%</span>
                                </div>
                            </li>
                            <li class="list-group-item d-flex align-items-center">
                                <div class="crypto-icon crypto-icon-sm bg-primary">
                                    <i class="cf cf-eth"></i>
                                </div>
                                <div class="mg-l-15">
                                    <h6 class="lh-5 mg-b-0">Sep 2022</h6>
                                    <span class="d-block tx-color-03 tx-uppercase tx-11 tx-medium">29/48 MRS</span>
                                </div>
                                <div class="mg-l-auto text-right">
                                    <p class="mg-b-0 tx-rubik">+18%</p>
                                    <span class="d-block tx-color-03 tx-uppercase tx-11 tx-rubik">+26%</span>
                                </div>
                            </li>
                        </ul>
                    </div><!-- card-body -->
                </div><!-- card -->
            </div>
            <div class="col-3 mg-t-10">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <h6 class="lh-5 mg-b-0">TOYOGON</h6>
                        <nav class="nav nav-row-xs align-items-center">
                            <a href="" class="nav-link">1D</a>
                            <a href="" class="nav-link active">1W</a>
                            <a href="" class="nav-link">1M</a>
                            <a href="" class="nav-link">1Y</a>
                        </nav>
                    </div><!-- card-header -->
                    <div class="card-body pd-0 pos-relative">
                        <div class="pos-absolute t-20 l-20">
                            <p class="tx-uppercase tx-11 tx-spacing-1 tx-color-03 tx-medium mg-b-0">KPI: 18 On time MRS</p>
                            <div class="d-flex align-items-baseline">
                                <h2 class="tx-normal tx-rubik tx-spacing--2 mg-b-0"><small class="tx-color-03">41</small> </h2>
                                <span class="tx-rubik tx-success mg-l-5"><i class="icon ion-md-arrow-up"></i> 21 (11%)</span>
                            </div>
                        </div>
                        <div class="chart-fourteen">
                            <div id="flotChart6a3" class="flot-chart flotChart6"></div>
                        </div><!-- chart-fourteen -->
                        <ul class="list-group list-group-flush mg-t-15">
                            <li class="list-group-item d-flex align-items-center">
                                <div class="crypto-icon crypto-icon-sm bg-orange">
                                    <i class="cf cf-eth"></i>
                                </div>
                                <div class="mg-l-15">
                                    <h6 class="lh-5 mg-b-0">Jan 2023</h6>
                                    <span class="d-block tx-color-03 tx-uppercase tx-11 tx-medium">21/36 MRS</span>
                                </div>
                                <div class="mg-l-auto text-right">
                                    <p class="mg-b-0 tx-rubik">12%</p>
                                    <span class="d-block tx-color-03 tx-uppercase tx-11 tx-rubik text-danger">-6%</span>
                                </div>
                            </li>
                            <li class="list-group-item d-flex align-items-center">
                                <div class="crypto-icon crypto-icon-sm bg-secondary">
                                    <i class="cf cf-eth"></i>
                                </div>
                                <div class="mg-l-15">
                                    <h6 class="lh-5 mg-b-0">Dec 2022</h6>
                                    <span class="d-block tx-color-03 tx-uppercase tx-11 tx-medium">17/24 MRS</span>
                                </div>
                                <div class="mg-l-auto text-right">
                                    <p class="mg-b-0 tx-rubik text-danger">-16%</p>
                                    <span class="d-block tx-color-03 tx-uppercase tx-11 tx-rubik text-danger">-24%</span>
                                </div>
                            </li>
                            <li class="list-group-item d-flex align-items-center">
                                <div class="crypto-icon crypto-icon-sm bg-litecoin">
                                    <i class="cf cf-eth"></i>
                                </div>
                                <div class="mg-l-15">
                                    <h6 class="lh-5 mg-b-0">Nov 2022</h6>
                                    <span class="d-block tx-color-03 tx-uppercase tx-11 tx-medium">26/36 MRS</span>
                                </div>
                                <div class="mg-l-auto text-right">
                                    <p class="mg-b-0 tx-rubik">+26%</p>
                                    <span class="d-block tx-color-03 tx-uppercase tx-11 tx-rubik">+5%</span>
                                </div>
                            </li>
                            <li class="list-group-item d-flex align-items-center">
                                <div class="crypto-icon crypto-icon-sm bg-success">
                                    <i class="cf cf-eth"></i>
                                </div>
                                <div class="mg-l-15">
                                    <h6 class="lh-5 mg-b-0">Oct 2022</h6>
                                    <span class="d-block tx-color-03 tx-uppercase tx-11 tx-medium">13/24 MRS</span>
                                </div>
                                <div class="mg-l-auto text-right">
                                    <p class="mg-b-0 tx-rubik text-danger">-26%</p>
                                    <span class="d-block tx-color-03 tx-uppercase tx-11 tx-rubik text-danger">-32%</span>
                                </div>
                            </li>
                            <li class="list-group-item d-flex align-items-center">
                                <div class="crypto-icon crypto-icon-sm bg-primary">
                                    <i class="cf cf-eth"></i>
                                </div>
                                <div class="mg-l-15">
                                    <h6 class="lh-5 mg-b-0">Sep 2022</h6>
                                    <span class="d-block tx-color-03 tx-uppercase tx-11 tx-medium">21/49 MRS</span>
                                </div>
                                <div class="mg-l-auto text-right">
                                    <p class="mg-b-0 tx-rubik">+16%</p>
                                    <span class="d-block tx-color-03 tx-uppercase tx-11 tx-rubik">+8%</span>
                                </div>
                            </li>
                        </ul>
                    </div><!-- card-body -->
                </div><!-- card -->
            </div>
            
        </div>
    </div>

@endsection

@section('pagejs')
    
    <script src="{{ asset('lib/jquery.flot/jquery.flot.js') }}"></script>
    <script src="{{ asset('lib/jquery.flot/jquery.flot.stack.js') }}"></script>
    <script src="{{ asset('lib/jquery.flot/jquery.flot.resize.js') }}"></script>
    <script src="{{ asset('lib/flot.curvedlines/curvedLines.js') }}"></script>
    <script src="{{ asset('lib/peity/jquery.peity.min.js') }}"></script>
    <script src="{{ asset('lib/chart.js/Chart.bundle.min.js') }}"></script>

    <script src="{{ asset('js/dashforge.js') }}"></script>
    <script src="{{ asset('js/dashforge.sampledata.js') }}"></script>
    <script src="{{ asset('js/dashboard-two.js') }}"></script>

    <script>
      $(function(){
        'use strict'

        $.plot('#flotChart1', [{
            data: df3,
            color: '#e1e5ed',
            lines: {
              lineWidth: 1
            }
          },{
            data: df3,
            color: '#69b2f8',
            lines: {
              lineWidth: 1
            }
          },{
            data: df3,
            color: '#0168fa'
          }], {
                series: {
            stack: 0,
                    shadowSize: 0,
            lines: {
              show: true,
              lineWidth: 1.7,
              fill: true,
              fillColor: { colors: [ { opacity: 0 }, { opacity: 0.2 } ] }
            }
                },
          grid: {
            borderWidth: 0,
            labelMargin: 5,
            hoverable: true
          },
                yaxis: {
            show: true,
            color: 'rgba(72, 94, 144, .1)',
            min: 0,
            max: 160,
            font: {
              size: 10,
              color: '#8392a5'
            }
          },
                xaxis: {
            show: true,
            color: 'rgba(72, 94, 144, .1)',
            ticks: [[0, 'Jun 2022'], [20, 'Jul 2022'], [40, 'Aug 2022'], [60, 'Sep 2022'], [80, 'Oct 2022'], [100, 'Nov 2022'], [120, 'Dec 2022'], [140, 'Jan 2023']],
            font: {
              size: 10,
              family: 'Arial, sans-serif',
              color: '#8392a5'
            },
            reserveSpace: false
          }
            });

        function flotOption(min, max) {
          return {
            series: {
              stack: 0,
              shadowSize: 0,
              lines: {
                show: true,
                lineWidth: 1.5,
                fill: true,
                fillColor: { colors: [ { opacity: 0 }, { opacity: 0.2 } ] }
              }
            },
            grid: { borderWidth: 0 },
            yaxis: { show: false },
            xaxis: {
              show: false,
              min: min,
              max: max
            }
          }
        }

        // Ethereum
        $.plot('#flotChart2', [{
          data: df3,
          color: '#c0ccda',
          lines: { lineWidth: 1 }
        },{
          data: df3,
          color: '#a0aabc'
        }], flotOption(0,50));

        // Bitcoin Cash
        $.plot('#flotChart3', [{
          data: df3,
          color: '#b8eace',
          lines: { lineWidth: 1 }
        },{
          data: df3,
          color: '#58cd8b'
        }], flotOption(20,70));

        // Litecoin
        $.plot('#flotChart4', [{
          data: df3,
          color: '#c0ccdf',
          lines: { lineWidth: 1 }
        },{
          data: df3,
          color: '#6e8ab6'
        }], flotOption(90,140));

        // Dash
        $.plot('#flotChart5', [{
          data: df3,
          color: '#b1d0fd',
          lines: { lineWidth: 1 }
        },{
          data: df3,
          color: '#4c94fb'
        }], flotOption(80,130));



        // Markets
        $.plot('#flotChart6', [{
            data: df1,
            color: '#00cccc',
            lines: {
              lineWidth: 1.7,
              fill: true,
              fillColor: { colors: [ { opacity: 0 }, { opacity: 0.4 } ] }
            }
          },{
            data: df2,
            color: '#e1e5ed',
            lines: {
              lineWidth: 1,
              fill: true,
              fillColor: { colors: [ { opacity: 0 }, { opacity: 0.2 } ] }
            }
          }], {
                series: {
                    shadowSize: 0,
            lines: {
              show: true,
            }
                },
          grid: {
            borderWidth: 0,
            labelMargin: 10,
            aboveData: true
          },
                yaxis: {
            show: false,
            max: 150
          },
                xaxis: {
            show: true,
            tickColor: 'rgba(72,94,144, 0.07)',
            ticks: [[25,'Sep 22'],[50,'Oct 22'],[75,'Nov 22'],[100,'Dec 22'],[125,'Jan 23']],
            //min: 35,
            //max: 125
          }
            });






        $.plot('#flotChart6a3', [{
            data: df1,
            color: '#00cccc',
            lines: {
              lineWidth: 1.7,
              fill: true,
              fillColor: { colors: [ { opacity: 0 }, { opacity: 0.4 } ] }
            }
          },{
            data: df2,
            color: '#e1e5ed',
            lines: {
              lineWidth: 1,
              fill: true,
              fillColor: { colors: [ { opacity: 0 }, { opacity: 0.2 } ] }
            }
          }], {
                series: {
                    shadowSize: 0,
            lines: {
              show: true,
            }
                },
          grid: {
            borderWidth: 0,
            labelMargin: 10,
            aboveData: true
          },
                yaxis: {
            show: false,
            max: 150
          },
                xaxis: {
            show: true,
            tickColor: 'rgba(72,94,144, 0.07)',
            ticks: [[25,'Sep 22'],[50,'Oct 22'],[75,'Nov 22'],[100,'Dec 22'],[125,'Jan 23']],
            //min: 35,
            //max: 125
          }
            });







$.plot('#flotChart6a2', [{
            data: df1,
            color: '#00cccc',
            lines: {
              lineWidth: 1.7,
              fill: true,
              fillColor: { colors: [ { opacity: 0 }, { opacity: 0.4 } ] }
            }
          },{
            data: df2,
            color: '#e1e5ed',
            lines: {
              lineWidth: 1,
              fill: true,
              fillColor: { colors: [ { opacity: 0 }, { opacity: 0.2 } ] }
            }
          }], {
                series: {
                    shadowSize: 0,
            lines: {
              show: true,
            }
                },
          grid: {
            borderWidth: 0,
            labelMargin: 10,
            aboveData: true
          },
                yaxis: {
            show: false,
            max: 150
          },
                xaxis: {
            show: true,
            tickColor: 'rgba(72,94,144, 0.07)',
            ticks: [[25,'Sep 22'],[50,'Oct 22'],[75,'Nov 22'],[100,'Dec 22'],[125,'Jan 23']],
            //min: 35,
            //max: 125
          }
            });





$.plot('#flotChart6a1', [{
            data: df1,
            color: '#00cccc',
            lines: {
              lineWidth: 1.7,
              fill: true,
              fillColor: { colors: [ { opacity: 0 }, { opacity: 0.4 } ] }
            }
          },{
            data: df2,
            color: '#F7C5B5',
            lines: {
              lineWidth: 1,
              fill: true,
              fillColor: { colors: [ { opacity: 0 }, { opacity: 0.2 } ] }
            }
          }], {
                series: {
                    shadowSize: 0,
            lines: {
              show: true,
            }
                },
          grid: {
            borderWidth: 0,
            labelMargin: 10,
            aboveData: true
          },
                yaxis: {
            show: false,
            max: 150
          },
                xaxis: {
            show: true,
            tickColor: 'rgba(72,94,144, 0.07)',
            ticks: [[25,'Sep 22'],[50,'Oct 22'],[75,'Nov 22'],[100,'Dec 22'],[125,'Jan 23']],
            //min: 35,
            //max: 125
          }
            });







        /** PIE CHART **/
        var datapie = {
          labels: ['Unfulfill', 'Completed', 'Partial', 'On-hold'],
          datasets: [{
            data: [25,15,32,28],
            backgroundColor: ['#66a4fb', '#4cebb5','#fec85e','#ff7c8f']
          }]
        };

        var optionpie = {
          maintainAspectRatio: false,
          responsive: true,
          legend: {
            display: false,
          },
          animation: {
            animateScale: true,
            animateRotate: true
          }
        };

        // For a pie chart
        var ctx2 = document.getElementById('chartDonut');
        var myDonutChart = new Chart(ctx2, {
          type: 'doughnut',
          data: datapie,
          options: optionpie
        });



        window.darkMode = function(){
          $('.btn-white').addClass('btn-dark').removeClass('btn-white');

          myDonutChart.options.elements.arc.borderColor = '#141c2b';
          myDonutChart.update();
        }

        window.lightMode = function() {
          $('.btn-dark').addClass('btn-white').removeClass('btn-dark');

          myDonutChart.options.elements.arc.borderColor = '#fff';
          myDonutChart.update();
        }

        var hasMode = Cookies.get('df-mode');
        if(hasMode === 'dark') {
          darkMode();
        } else {
          lightMode();
        }

      })
    </script>
@endsection

@section('customjs')
@endsection
