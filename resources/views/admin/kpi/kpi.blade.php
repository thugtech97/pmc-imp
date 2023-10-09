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

@section('pagecss')
    <style>
        #cgauge table {margin: auto !important;}
        #cgauge_main table {margin: auto !important;}
    </style>
    <link href="{{ asset('lib/ion-rangeslider/css/ion.rangeSlider.min.css') }}" rel="stylesheet">
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">

      google.charts.load('current', {'packages':['gauge']});
      google.charts.setOnLoadCallback(drawGauge);

      google.charts.setOnLoadCallback(drawGaugeMain);

      function drawGauge() {

        var data = google.visualization.arrayToDataTable([
       ['Label', 'Value'],['MCD', {{$gauge_total}}]         
        ]);

        var options = {          
          width: 800, height: 240,
          redFrom: 450, redTo: 500,
          yellowFrom: 400, yellowTo: 450,
          minorTicks: 3,
          max: 500
        };

        var chart = new google.visualization.Gauge(document.getElementById('cgauge_main'));

        chart.draw(data, options);

      }

      function drawGaugeMain() {

        var data = google.visualization.arrayToDataTable([
       ['Label', 'Value'],
          {!!$gauge!!}
        ]);

        var options = {
          width: 800, height: 240,
          redFrom: 130, redTo: 150,
          yellowFrom: 100, yellowTo: 130,
          minorTicks: 1,
          max: 150
        };

        var chart = new google.visualization.Gauge(document.getElementById('cgauge'));

        chart.draw(data, options);

      }


      google.charts.load('current', {'packages':['corechart']});
      google.charts.setOnLoadCallback(drawStep);

      function drawStep() {
        var data = google.visualization.arrayToDataTable([
          ['Month',  'Viewed', 'Completed'],
          {!!$step!!}
        ]);

        var options = {
          title: 'Monthly Response Time Average',
          vAxis: {title: 'Total Days'},
          isStacked: true
        };

        var chart = new google.visualization.SteppedAreaChart(document.getElementById('cstep'));

        chart.draw(data, options);
      }



      google.charts.load("current", {packages:["corechart"]});
      google.charts.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = google.visualization.arrayToDataTable([
          ['Remarks', 'Value'],
          ['On-time',{{$ontym_total}}],
          ['Delayed',{{$gauge_total}}],
        ]);

        var options = {
          width: 600, height: 300,
          title: '',
          is3D: false,
          legend: 'none',
          pieSliceText: 'label',
        };

        var chart = new google.visualization.PieChart(document.getElementById('ontime'));
        chart.draw(data, options);
      }
      
    </script>
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
                <h4 class="mg-b-0 tx-spacing--1">KPI Users</h4>
            </div>
        </div>

        <div class="row row-sm">

            <!-- Start Filters -->
            <div class="col-md-12">

                <div class="filter-buttons">
                    <div class="d-md-flex bd-highlight">
                        <div class="bd-highlight mg-r-10 mg-t-10">
                            
                        </div>

                        <div class="ml-auto bd-highlight mg-t-10 mg-r-10">
                            <form class="form-inline" id="searchForm">
                                <div class="search-form">
                                    <input name="max" type="number" id="max" class="form-control"  placeholder="Max" value="{{$max}}">
                                </div>
                                <div class="search-form mg-r-10">
                                    <input name="startdate" value="@if(isset($_GET['startdate'])){{$_GET['startdate']}}@endif" type="date" id="startdate" class="form-control" >
                                    <button class="btn filter" id="btnSearch"><i data-feather="search"></i></button>
                                </div>
                                <div class="search-form mg-r-10">
                                    <input name="enddate" type="date" id="enddate" class="form-control"  value="@if(isset($_GET['enddate'])){{$_GET['enddate']}}@endif">
                                    <button class="btn filter" id="btnSearch"><i data-feather="search"></i></button>
                                </div>
                                <input type="submit" class="btn btn-success btn-sm mg-b-5 mt-lg-0 mt-md-0 mt-sm-0 mt-1" value="Filter">
                            </form>
                        </div>
                        <div class="mg-t-10">
                          
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Filters -->


            <!-- Start Pages -->
            <div class="col-md-12 mg-t-40">
                <div class="row">

                    <div class="col-md-6 text-center">
                        <h4>Performance Gauge <br> <small>Total transactions not completed within {{$max}} days</small></h4>
                        <div id="cgauge_main" class="text-center"></div>
                    </div>
                    <div class="col-md-6 text-center">
                        <h4>Delayed vs Ontime <br></h4>
                        <div id="ontime" class="text-center"></div>
                    </div>
                    
                    <div class="col-md-12 text-center">
                        <h4>User Performance Gauge</h4>
                        <div id="cgauge" class="text-center"></div>
                    </div>

                    <div class="col-md-12">                        
                        <div id="cstep"></div>
                    </div>
                    
                </div>
                
                
            </div>
            <!-- End Pages -->

           
            <!-- End Navigation -->

        </div>
    </div>

@endsection

@section('pagejs')
    
   

@endsection

@section('customjs')
@endsection
