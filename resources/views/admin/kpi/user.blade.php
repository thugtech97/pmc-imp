@php 
    $gauge = "";
    $gauge_total = 0;
    foreach($data as $user){        
        $gauge.="['".$user->User."', ".round($user->completed)."],";
        $gauge_total += round($user->completed);
    }
    $gauge = rtrim($gauge,",");


    $ontym = "";
    $ontym_total = 0;
    foreach($ontime as $user){        
        $ontym.="['".$user->User."', ".round($user->completed)."],";
        $ontym_total += round($user->completed);
    }
    $ontym = rtrim($ontym,",");



    $step = "";
    foreach($monthly as $month){        
        $step.="['".$month->mo."', ".round($month->viewed).", ".round($month->completed)."],";        
    }
    $step = rtrim($step,",");
@endphp
@extends('admin.layouts.app')

@section('pagetitle')
    User Management
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
                <h4 class="mg-b-0 tx-spacing--1">IMF-KPI</h4>
            </div>
        </div>
        <div class="row row-xs">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header d-sm-flex justify-content-between bd-b-0 pd-t-20 pd-b-0">
                        <div class="mg-b-10 mg-sm-b-0">
                            <h6 class="mg-b-5">IMF Response Average per Month</h6>
                            <p class="tx-12 tx-color-03 mg-b-0">as of 31st of Dec 2022</p>
                        </div>
                        <ul class="list-inline tx-uppercase tx-10 tx-medium tx-spacing-1 tx-color-03 mg-b-0">
                            <li class="list-inline-item">
                                <span class="d-inline-block wd-7 ht-7 bg-primary rounded-circle mg-r-5"></span>
                                Total Received<span class="d-none d-md-inline"> IMF</span>
                            </li>
                            <li class="list-inline-item mg-l-10">
                                <span class="d-inline-block wd-7 ht-7 bg-df-2 rounded-circle mg-r-5"></span>
                                Processing<span class="d-none d-md-inline"> days</span>
                            </li>
                            <li class="list-inline-item mg-l-10">
                                <span class="d-inline-block wd-7 ht-7 bg-gray-400 rounded-circle mg-r-5"></span>
                                Completed<span class="d-none d-md-inline"> days</span>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="chart-fifteen">
                            <div id="flotChart1" class="flot-chart"></div>
                        </div>
                    </div><!-- card-body -->
                    <div class="card-footer pd-y-15 pd-x-20">
                        <div class="row row-sm">
                            <div class="col-6 col-sm-4 col-md-3 col-lg">
                                <h4 class="tx-normal tx-rubik mg-b-10">327 <small style="font-size:9px;">IMF</small></h4>
                                <div class="progress ht-2 mg-b-10">
                                    <div class="progress-bar wd-100p bg-df-2" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <h6 class="tx-uppercase tx-spacing-1 tx-semibold tx-10 tx-color-02 mg-b-2">Received IMF Requests</h6>
                                <p class="tx-10 tx-color-03 mg-b-0"><span class="tx-medium tx-success">1.2% <i class="icon ion-md-arrow-down"></i></span> than previous year</p>
                            </div><!-- col -->
                            <div class="col-6 col-sm-4 col-md-3 col-lg">
                                <h4 class="tx-normal tx-rubik mg-b-10">36 <small style="font-size:9px;">IMF</small></h4>
                                <div class="progress ht-2 mg-b-10">
                                    <div class="progress-bar wd-85p bg-df-2" role="progressbar" aria-valuenow="36" aria-valuemin="0" aria-valuemax="50"></div>
                                </div>
                                <h6 class="tx-uppercase tx-spacing-1 tx-semibold tx-10 tx-color-02 mg-b-2">Avg IMF Request per Month </h6>
                                <p class="tx-10 tx-color-03 mg-b-0"><span class="tx-medium tx-danger">-0.6% <i class="icon ion-md-arrow-down"></i></span> than  previous year</p>
                            </div><!-- col -->
                            <div class="col-6 col-sm-4 col-md-3 col-lg mg-t-20 mg-sm-t-0">
                                <h4 class="tx-normal tx-rubik mg-b-10">14 <small style="font-size:9px;">days</small></h4>
                                <div class="progress ht-2 mg-b-10">
                                    <div class="progress-bar wd-25p bg-df-2" role="progressbar" aria-valuenow="14" aria-valuemin="0" aria-valuemax="20"></div>
                                </div>
                                <h6 class="tx-uppercase tx-spacing-1 tx-semibold tx-10 tx-color-02 mg-b-2">Avg MCD Processing Days</h6>
                                <p class="tx-10 tx-color-03 mg-b-0"><span class="tx-medium tx-success">0.3% <i class="icon ion-md-arrow-down"></i></span> than  previous year</p>
                            </div><!-- col -->
                            <div class="col-6 col-sm-4 col-md-3 col-lg mg-t-20 mg-md-t-0">
                                <h4 class="tx-normal tx-rubik mg-b-10">53 <small style="font-size:9px;">days</small></h4>
                                <div class="progress ht-2 mg-b-10">
                                    <div class="progress-bar wd-45p bg-df-2" role="progressbar" aria-valuenow="53" aria-valuemin="0" aria-valuemax="70"></div>
                                </div>
                                <h6 class="tx-uppercase tx-spacing-1 tx-semibold tx-10 tx-color-02 mg-b-2">Avg IMF Completion Days</h6>
                                <p class="tx-10 tx-color-03 mg-b-0"><span class="tx-medium tx-success">0.3% <i class="icon ion-md-arrow-down"></i></span> than  previous year</p>
                            </div><!-- col -->
                            <div class="col-6 col-sm-4 col-md-3 col-lg mg-t-20 mg-lg-t-0">
                                <h4 class="tx-normal tx-rubik mg-b-10">96 <small style="font-size:9px;">IMF</small></h4>
                                <div class="progress ht-2 mg-b-10">
                                    <div class="progress-bar wd-30p bg-df-2" role="progressbar" aria-valuenow="96" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <h6 class="tx-uppercase tx-spacing-1 tx-semibold tx-10 tx-color-02 mg-b-2">Not Completed in time</h6>
                                <p class="tx-10 tx-color-03 mg-b-0"><span class="tx-medium tx-success">0.3% <i class="icon ion-md-arrow-down"></i></span> than  previous year</p>
                            </div><!-- col -->
                        </div><!-- row -->
                    </div><!-- card-footer -->
                </div><!-- card -->
                <div class="row row-xs mg-t-10">
                    <div class="col-md-7">
                        <div class="card">
                            <div class="card-header pd-b-0 pd-t-20 bd-b-0">
                                <h6 class="mg-b-0">Processed IMF By User</h6>
                            </div><!-- card-header -->
                            <div class="card-body">
                                <div class="chart-seventeen"><canvas id="chartBar2"></canvas></div>
                            </div><!-- card-body -->
                        </div><!-- card -->
                    </div>
                    <div class="col-md-5 mg-t-10 mg-md-t-0">
                        <div class="card">
                            <div class="card-header pd-b-0 pd-t-20 bd-b-0">
                                <h6 class="mg-b-0">Request Fulfillment</h6>
                            </div><!-- card-header -->
                            <div class="card-body pd-y-10">
                                <div class="d-flex align-items-baseline tx-rubik">
                                    <h1 class="tx-40 lh-1 tx-normal tx-spacing--2 mg-b-5 mg-r-5">96</h1>
                                    <p class="tx-11 tx-color-03 mg-b-0"><span class="tx-medium tx-success">29% <i class="icon ion-md-arrow-up"></i></span></p>
                                </div>
                                <h6 class="tx-uppercase tx-spacing-1 tx-semibold tx-10 tx-color-02 mg-b-15">Performance Score</h6>

                                <div class="progress bg-transparent op-7 ht-10 mg-b-15">
                                    <div class="progress-bar bg-primary wd-10p" role="progressbar" aria-valuenow="8" aria-valuemin="0" aria-valuemax="100"></div>
                                    <div class="progress-bar bg-success wd-15p bd-l bd-white" role="progressbar" aria-valuenow="14" aria-valuemin="0" aria-valuemax="100"></div>
                                    <div class="progress-bar bg-warning wd-20p bd-l bd-white" role="progressbar" aria-valuenow="21" aria-valuemin="0" aria-valuemax="100"></div>
                                    <div class="progress-bar bg-pink wd-25p bd-l bd-white" role="progressbar" aria-valuenow="28" aria-valuemin="0" aria-valuemax="100"></div>
                                    <div class="progress-bar bg-teal wd-25p bd-l bd-white" role="progressbar" aria-valuenow="23" aria-valuemin="0" aria-valuemax="100"></div>
                                    <div class="progress-bar bg-purple wd-5p bd-l bd-white" role="progressbar" aria-valuenow="6" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>

                                <table class="table-dashboard-two">
                                    <tbody>
                                        <tr>
                                            <td><div class="wd-12 ht-12 rounded-circle bd bd-3 bd-primary"></div></td>
                                            <td class="tx-medium">Excellent</td>
                                            <td class="text-right">27</td>
                                            <td class="text-right">8%</td>
                                        </tr>
                                        <tr>
                                            <td><div class="wd-12 ht-12 rounded-circle bd bd-3 bd-success"></div></td>
                                            <td class="tx-medium">Very Good</td>
                                            <td class="text-right">46</td>
                                            <td class="text-right">14%</td>
                                        </tr>
                                        <tr>
                                            <td><div class="wd-12 ht-12 rounded-circle bd bd-3 bd-warning"></div></td>
                                            <td class="tx-medium">Good</td>
                                            <td class="text-right">68</td>
                                            <td class="text-right">21%</td>
                                        </tr>
                                        <tr>
                                            <td><div class="wd-12 ht-12 rounded-circle bd bd-3 bd-pink"></div></td>
                                            <td class="tx-medium">Fair</td>
                                            <td class="text-right">93</td>
                                            <td class="text-right">28%</td>
                                        </tr>
                                        <tr>
                                            <td><div class="wd-12 ht-12 rounded-circle bd bd-3 bd-teal"></div></td>
                                            <td class="tx-medium">Poor</td>
                                            <td class="text-right">74</td>
                                            <td class="text-right">23%</td>
                                        </tr>
                                        <tr>
                                            <td><div class="wd-12 ht-12 rounded-circle bd bd-3 bd-purple"></div></td>
                                            <td class="tx-medium">Very Poor</td>
                                            <td class="text-right">19</td>
                                            <td class="text-right">6%</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div><!-- card-body -->
                        </div><!-- card -->
                    </div>
                    <div class="col-md-6 mg-t-10" style="display:none;">
                        <div class="card">
                            <div class="card-header pd-b-0 pd-x-20 bd-b-0">
                                <div class="d-sm-flex align-items-center justify-content-between">
                                    <h6 class="mg-b-0">Recent Activities</h6>
                                    <p class="tx-12 tx-color-03 mg-b-0">Last activity: 2 hours ago</p>
                                </div>
                            </div><!-- card-header -->
                            <div class="card-body pd-20">
                                <ul class="activity tx-13">
                                    <li class="activity-item">
                                        <div class="activity-icon bg-primary-light tx-primary">
                                            <i data-feather="clock"></i>
                                        </div>
                                        <div class="activity-body">
                                            <p class="mg-b-2"><strong>Louise</strong> added a time entry to the ticket <a href="" class="link-02"><strong>Sales Revenue</strong></a></p>
                                            <small class="tx-color-03">2 hours ago</small>
                                        </div><!-- activity-body -->
                                    </li><!-- activity-item -->
                                    <li class="activity-item">
                                        <div class="activity-icon bg-success-light tx-success">
                                            <i data-feather="paperclip"></i>
                                        </div>
                                        <div class="activity-body">
                                            <p class="mg-b-2"><strong>Kevin</strong> added new attachment to the ticket <a href="" class="link-01"><strong>Software Bug Reporting</strong></a></p>
                                            <small class="tx-color-03">5 hours ago</small>
                                        </div><!-- activity-body -->
                                    </li><!-- activity-item -->
                                    <li class="activity-item">
                                        <div class="activity-icon bg-warning-light tx-orange">
                                            <i data-feather="share"></i>
                                        </div>
                                        <div class="activity-body">
                                            <p class="mg-b-2"><strong>Natalie</strong> reassigned ticket <a href="" class="link-02"><strong>Problem installing software</strong></a> to <strong>Katherine</strong></p>
                                            <small class="tx-color-03">8 hours ago</small>
                                        </div><!-- activity-body -->
                                    </li><!-- activity-item -->
                                    <li class="activity-item">
                                        <div class="activity-icon bg-pink-light tx-pink">
                                            <i data-feather="plus-circle"></i>
                                        </div>
                                        <div class="activity-body">
                                            <p class="mg-b-2"><strong>Katherine</strong> submitted new ticket <a href="" class="link-02"><strong>Payment Method</strong></a></p>
                                            <small class="tx-color-03">Yesterday</small>
                                        </div><!-- activity-body -->
                                    </li><!-- activity-item -->
                                    <li class="activity-item">
                                        <div class="activity-icon bg-indigo-light tx-indigo">
                                            <i data-feather="settings"></i>
                                        </div>
                                        <div class="activity-body">
                                            <p class="mg-b-2"><strong>Katherine</strong> changed settings to ticket category <a href="" class="link-02"><strong>Payment &amp; Invoice</strong></a></p>
                                            <small class="tx-color-03">2 days ago</small>
                                        </div><!-- activity-body -->
                                    </li><!-- activity-item -->
                                </ul><!-- activity -->
                            </div><!-- card-body -->
                        </div><!-- card -->
                    </div>
                    <div class="col-md-6 mg-t-10" style="display:none;">
                        <div class="card">
                            <div class="card-header pd-b-0 pd-x-20 bd-b-0">
                                <h6 class="mg-b-0">Agent Performance Points</h6>
                            </div><!-- card-header -->
                            <div class="card-body pd-t-25">
                                <div class="media">
                                    <div class="avatar"><img src="https://via.placeholder.com/500" class="rounded-circle" alt=""></div>
                                    <div class="media-body mg-l-15">
                                        <h6 class="tx-13 mg-b-2">Katherine Lumaad</h6>
                                        <p class="tx-color-03 tx-12 mg-b-10">Technical Support</p>
                                        <div class="progress ht-4 op-7 mg-b-5">
                                            <div class="progress-bar wd-85p" role="progressbar" aria-valuenow="85" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <div class="d-flex justify-content-between tx-12">
                                            <span class="tx-color-03">Executive Level</span>
                                            <strong class="tx-medium">12,312 points</strong>
                                        </div>
                                    </div><!-- media-body -->
                                </div><!-- media -->
                                <div class="media mg-t-25">
                                    <div class="avatar"><img src="https://via.placeholder.com/500" class="rounded-circle" alt=""></div>
                                    <div class="media-body mg-l-15">
                                        <h6 class="tx-13 mg-b-2">Adrian Monino</h6>
                                        <p class="tx-color-03 tx-12 mg-b-10">Sales Representative</p>
                                        <div class="progress ht-4 op-7 mg-b-5">
                                            <div class="progress-bar bg-success wd-60p" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <div class="d-flex justify-content-between tx-12">
                                            <span class="tx-color-03">Master Level</span>
                                            <strong class="tx-medium">10,044 points</strong>
                                        </div>
                                    </div><!-- media-body -->
                                </div><!-- media -->
                                <div class="media mg-t-25">
                                    <div class="avatar"><img src="https://via.placeholder.com/500" class="rounded-circle" alt=""></div>
                                    <div class="media-body mg-l-15">
                                        <h6 class="tx-13 mg-b-2">Rolando Paloso</h6>
                                        <p class="tx-color-03 tx-12 mg-b-10">Software Support</p>
                                        <div class="progress ht-4 op-7 mg-b-5">
                                            <div class="progress-bar bg-indigo wd-45p" role="progressbar" aria-valuenow="45" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <div class="d-flex justify-content-between tx-12">
                                            <span class="tx-color-03">Super Elite Level</span>
                                            <strong class="tx-medium">7,500 points</strong>
                                        </div>
                                    </div><!-- media-body -->
                                </div><!-- media -->
                                <div class="media mg-t-20">
                                    <div class="avatar"><img src="https://via.placeholder.com/500" class="rounded-circle" alt=""></div>
                                    <div class="media-body mg-l-15">
                                        <h6 class="tx-13 mg-b-2">Dyanne Rose Aceron</h6>
                                        <p class="tx-color-03 tx-12 mg-b-10">Sales Representative</p>
                                        <div class="progress ht-4 op-7 mg-b-5">
                                            <div class="progress-bar bg-pink wd-40p" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <div class="d-flex justify-content-between tx-12">
                                            <span class="tx-color-03">Elite Level</span>
                                            <strong class="tx-medium">6,870 points</strong>
                                        </div>
                                    </div><!-- media-body -->
                                </div><!-- media -->
                            </div><!-- card-body -->
                        </div><!-- card -->
                    </div>
                </div><!-- row -->
            </div><!-- col -->
            <div class="col-lg-4 mg-t-10 mg-lg-t-0">
                <div class="row row-xs">
                    <div class="col-12 col-md-6 col-lg-12">
                        <div class="card card-body">
                            <div class="media d-block d-sm-flex align-items-center">
                                <div class="d-inline-block pos-relative">
                                    <span class="peity-donut" data-peity='{ "fill": ["#F7B29C","#e5e9f2"], "height": 110, "width": 110, "innerRadius": 46 }'>70,30</span>

                                    <div class="pos-absolute a-0 d-flex flex-column align-items-center justify-content-center">
                                        <h3 class="tx-rubik tx-spacing--1 mg-b-0">71%</h3>
                                        <span class="tx-9 tx-semibold tx-sans tx-color-03 tx-uppercase">IMF</span>
                                    </div>
                                </div>
                                <div class="media-body mg-t-20 mg-sm-t-0 mg-sm-l-20">
                                    <h6 class="mg-b-5">Total IMF completed in time</h6>
                                    <p class="lh-4 tx-12 tx-color-03 mg-b-15">231/327 request completed</p>
                                    <h3 class="tx-spacing--1 mg-b-0">60 <small class="tx-13 tx-color-03">/ days: KPI </small></h3>
                                </div><!-- media-body -->
                            </div><!-- media -->
                        </div>
                    </div><!-- col -->
                    <div class="col-12 col-md-6 col-lg-12 mg-t-10 mg-md-t-0 mg-lg-t-10">
                        <div class="card card-body">
                            <div class="media d-block d-sm-flex align-items-center">
                                <div class="d-inline-block pos-relative">
                                    <span class="peity-donut" data-peity='{ "fill": ["#69b2f8","#e5e9f2"], "height": 110, "width": 110, "innerRadius": 46 }'>69,31</span>

                                    <div class="pos-absolute a-0 d-flex flex-column align-items-center justify-content-center">
                                        <h3 class="tx-rubik tx-spacing--1 mg-b-0">89%</h3>
                                        <span class="tx-9 tx-semibold tx-sans tx-color-03 tx-uppercase">Reached</span>
                                    </div>
                                </div>
                                <div class="media-body mg-t-20 mg-sm-t-0 mg-sm-l-20">
                                    <h6 class="mg-b-5">MCD Avg Processing Time</h6>
                                    <p class="lh-4 tx-12 tx-color-03 mg-b-15">Measure how quickly the MCD staff processed the request.</p>
                                    <h3 class="tx-spacing--1 mg-b-0">5<small class="tx-13 tx-color-03">/ Days: KPI</small></h3>
                                </div><!-- media-body -->
                            </div><!-- media -->
                        </div>
                    </div><!-- col -->
                    <div class="col-12 col-md-6 col-lg-12 mg-t-10">
                        <div class="card">
                            <div class="card-header pd-t-20 pd-b-0 bd-b-0 d-flex justify-content-between">
                                <h6 class="lh-5 mg-b-0">MCD Processing Trend</h6>
                                <a href="" class="tx-13 link-03">This Year <i class="icon ion-ios-arrow-down tx-12"></i></a>
                            </div><!-- card-header -->
                            <div class="card-body pd-0 pos-relative">
                                <div class="pos-absolute t-10 l-20 z-index-10">
                                    <div class="d-flex align-items-baseline">
                                        <h1 class="tx-normal tx-rubik mg-b-0 mg-r-5">291</h1>
                                        <p class="tx-11 tx-color-03 mg-b-0"><span class="tx-medium tx-success">0.3% <i class="icon ion-md-arrow-down"></i></span> than last year</p>
                                    </div>
                                    <p class="tx-12 tx-color-03 wd-60p">The total number of successfully processed IMF on time.</p>
                                </div>

                                <div class="chart-sixteen">
                                    <div id="flotChart2" class="flot-chart"></div>
                                </div>
                            </div><!-- card-body -->
                        </div><!-- card -->
                    </div><!-- col -->
                    <div class="col-12 col-md-6 col-lg-12 mg-t-10" style="display: none;">
                        <div class="card">
                            <div class="card-header pd-t-20 pd-b-0 bd-b-0">
                                <h6 class="lh-5 mg-b-5">MCD Staff Performance</h6>
                                <p class="tx-12 tx-color-03 mg-b-0">Measures the result of IMF users.</p>
                            </div><!-- card-header -->
                            <div class="card-body pd-0">
                                <div class="pd-t-10 pd-b-15 pd-x-20 d-flex align-items-baseline">
                                    <h1 class="tx-normal tx-rubik mg-b-0 mg-r-5">4.2</h1>
                                    <div class="tx-18">
                                        <i class="icon ion-md-star lh-0 tx-orange"></i>
                                        <i class="icon ion-md-star lh-0 tx-orange"></i>
                                        <i class="icon ion-md-star lh-0 tx-orange"></i>
                                        <i class="icon ion-md-star lh-0 tx-orange"></i>
                                        <i class="icon ion-md-star lh-0 tx-gray-300"></i>
                                    </div>
                                </div>
                                <div class="list-group list-group-flush tx-13">
                                    <div class="list-group-item pd-y-5 pd-x-20 d-flex align-items-center">
                                        <strong class="tx-12 tx-rubik">ASPERA, KRYSTALLE  PIAMONTE</strong>
                                        <div class="tx-16 mg-l-10">
                                            <i class="icon ion-md-star lh-0 tx-orange"></i>
                                            <i class="icon ion-md-star lh-0 tx-orange"></i>
                                            <i class="icon ion-md-star lh-0 tx-orange"></i>
                                            <i class="icon ion-md-star lh-0 tx-orange"></i>
                                            <i class="icon ion-md-star lh-0 tx-orange"></i>
                                        </div>
                                        <div class="mg-l-auto tx-rubik tx-color-02">4,230</div>
                                        <div class="mg-l-20 tx-rubik tx-color-03 wd-10p text-right">58%</div>
                                    </div>
                                    <div class="list-group-item pd-y-5 pd-x-20 d-flex align-items-center">
                                        <strong class="tx-12 tx-rubik">RODRIGO, ZOSIMO</strong>
                                        <div class="tx-16 mg-l-10">
                                            <i class="icon ion-md-star lh-0 tx-orange"></i>
                                            <i class="icon ion-md-star lh-0 tx-orange"></i>
                                            <i class="icon ion-md-star lh-0 tx-orange"></i>
                                            <i class="icon ion-md-star lh-0 tx-orange"></i>
                                            <i class="icon ion-md-star lh-0 tx-gray-300"></i>
                                        </div>
                                        <div class="mg-l-auto tx-rubik tx-color-02">1,416</div>
                                        <div class="mg-l-20 tx-rubik tx-color-03 wd-10p text-right">24%</div>
                                    </div>
                                    <div class="list-group-item pd-y-5 pd-x-20 d-flex align-items-center">
                                        <strong class="tx-12 tx-rubik">FUSINGAN, KATHERINE</strong>
                                        <div class="tx-16 mg-l-10">
                                            <i class="icon ion-md-star lh-0 tx-orange"></i>
                                            <i class="icon ion-md-star lh-0 tx-orange"></i>
                                            <i class="icon ion-md-star lh-0 tx-orange"></i>
                                            <i class="icon ion-md-star lh-0 tx-gray-300"></i>
                                            <i class="icon ion-md-star lh-0 tx-gray-300"></i>
                                        </div>
                                        <div class="mg-l-auto tx-rubik tx-color-02">980</div>
                                        <div class="mg-l-20 tx-rubik tx-color-03 wd-10p text-right">16%</div>
                                    </div>
                                    <div class="list-group-item pd-y-5 pd-x-20 d-flex align-items-center">
                                        <strong class="tx-12 tx-rubik">MADRAZO, ERIC</strong>
                                        <div class="tx-16 mg-l-10">
                                            <i class="icon ion-md-star lh-0 tx-orange"></i>
                                            <i class="icon ion-md-star lh-0 tx-orange"></i>
                                            <i class="icon ion-md-star lh-0 tx-gray-300"></i>
                                            <i class="icon ion-md-star lh-0 tx-gray-300"></i>
                                            <i class="icon ion-md-star lh-0 tx-gray-300"></i>
                                        </div>
                                        <div class="mg-l-auto tx-rubik tx-color-02">401</div>
                                        <div class="mg-l-20 tx-rubik tx-color-03 wd-10p text-right">8%</div>
                                    </div>
                                    <div class="list-group-item pd-y-5 pd-x-20 d-flex align-items-center bg-transparent">
                                        <strong class="tx-12 tx-rubik">TALAUGON, JAY</strong>
                                        <div class="tx-16 mg-l-10">
                                            <i class="icon ion-md-star lh-0 tx-orange"></i>
                                            <i class="icon ion-md-star lh-0 tx-gray-300"></i>
                                            <i class="icon ion-md-star lh-0 tx-gray-300"></i>
                                            <i class="icon ion-md-star lh-0 tx-gray-300"></i>
                                            <i class="icon ion-md-star lh-0 tx-gray-300"></i>
                                        </div>
                                        <div class="mg-l-auto tx-rubik tx-color-02">798</div>
                                        <div class="mg-l-20 tx-rubik tx-color-03 wd-10p text-right">12%</div>
                                    </div>
                                    <div class="list-group-item pd-y-5 pd-x-20 d-flex align-items-center bg-transparent">
                                        <strong class="tx-12 tx-rubik">TOYOGON, ERICKA</strong>
                                        <div class="tx-16 mg-l-10">
                                            <i class="icon ion-md-star lh-0 tx-orange"></i>
                                            <i class="icon ion-md-star lh-0 tx-gray-300"></i>
                                            <i class="icon ion-md-star lh-0 tx-gray-300"></i>
                                            <i class="icon ion-md-star lh-0 tx-gray-300"></i>
                                            <i class="icon ion-md-star lh-0 tx-gray-300"></i>
                                        </div>
                                        <div class="mg-l-auto tx-rubik tx-color-02">798</div>
                                        <div class="mg-l-20 tx-rubik tx-color-03 wd-10p text-right">12%</div>
                                    </div>
                                </div><!-- list-group -->
                            </div><!-- card-body -->
                        </div><!-- card -->
                    </div><!-- col -->
                    <div class="col-12 col-md-6 col-lg-12 mg-t-10">
                        <div class="card">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <h6 class="mg-b-0">Most Delayed IMF Request</h6>
                                <div class="d-flex tx-18">
                                    <a href="" class="link-03 lh-0"><i class="icon ion-md-refresh"></i></a>
                                    <a href="" class="link-03 lh-0 mg-l-10"><i class="icon ion-md-more"></i></a>
                                </div>
                            </div>
                            <ul class="list-group list-group-flush tx-13">
                                <li class="list-group-item d-flex pd-sm-x-20">
                                    <div class="avatar d-none d-sm-block"><span class="avatar-initial rounded-circle bg-indigo op-5"><i class="icon ion-md-return-left"></i></span></div>
                                    <div class="pd-sm-l-10">
                                        <p class="tx-medium mg-b-2">Update item description for #00910</p>
                                        <small class="tx-12 tx-color-03 mg-b-0">Oct 21, 2022, 1:00pm</small>
                                    </div>
                                    <div class="mg-l-auto text-right">
                                        <p class="tx-medium mg-b-2">87 days ago</p>
                                        <small class="tx-12 tx-success mg-b-0">On-going Approval</small>
                                    </div>
                                </li>
                                <li class="list-group-item d-flex pd-sm-x-20">
                                    <div class="avatar d-none d-sm-block"><span class="avatar-initial rounded-circle bg-orange op-5"><i class="icon ion-md-bus"></i></span></div>
                                    <div class="pd-sm-l-10">
                                        <p class="tx-medium mg-b-2">New Item Request #00962</p>
                                        <small class="tx-12 tx-color-03 mg-b-0">Oct 25, 2022, 11:40am</small>
                                    </div>
                                    <div class="mg-l-auto text-right">
                                        <p class="tx-medium mg-b-2">83 days ago</p>
                                        <small class="tx-12 tx-info mg-b-0">On-going Approval</small>
                                    </div>
                                </li>
                                <li class="list-group-item d-flex pd-sm-x-20">
                                    <div class="avatar d-none d-sm-block"><span class="avatar-initial rounded-circle bg-teal"><i class="icon ion-md-checkmark"></i></span></div>
                                    <div class="pd-sm-l-10">
                                        <p class="tx-medium mg-b-0">Add new variant for item #023328</p>
                                        <small class="tx-12 tx-color-03 mg-b-0">Nov 6, 2022, 10:30pm</small>
                                    </div>
                                    <div class="mg-l-auto text-right">
                                        <p class="tx-medium mg-b-0">76 days ago</p>
                                        <small class="tx-12 tx-success mg-b-0">Completed</small>
                                    </div>
                                </li>
                                <li class="list-group-item d-flex pd-sm-x-20">
                                    <div class="avatar d-none d-sm-block"><span class="avatar-initial rounded-circle bg-gray-400"><i class="icon ion-md-close"></i></span></div>
                                    <div class="pd-sm-l-10">
                                        <p class="tx-medium mg-b-0">New Item Request #087651</p>
                                        <small class="tx-12 tx-color-03 mg-b-0">Nov 19, 2022, 12:54pm</small>
                                    </div>
                                    <div class="mg-l-auto text-right">
                                        <p class="tx-medium mg-b-0">68 days ago</p>
                                        <small class="tx-12 tx-danger mg-b-0">Disapproved</small>
                                    </div>
                                </li>
                            </ul>
                            <div class="card-footer text-center tx-13">
                                <a href="" class="link-03">View All Transactions <i class="icon ion-md-arrow-down mg-l-5"></i></a>
                            </div><!-- card-footer -->
                        </div><!-- card -->
                    </div><!-- col -->
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
    <script src="{{ asset('js/dashboard-four.js') }}"></script>


@endsection

@section('customjs')
@endsection
