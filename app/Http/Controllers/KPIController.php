<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \App\Models\Kpi;
use Auth;
use DB;
use App\Models\Page;

class KPIController extends Controller
{
    public function user() 
    {
        $page = new Page;
        $page->name = 'MCD';
        $cond = "";
        if(isset($_GET['startdate'])){
            $cond.= " and Created>='".$_GET['startdate']."' and Created<='".$_GET['enddate']."'";
        }

        $max = 9;
        if(isset($_GET['max'])){
            $max = $_GET['max'];
        }


        $data = DB::select("select [User],count(trans) as completed from kpi where Completed_days>".$max." ".$cond." group by [User] order by [User]");

        $monthly = DB::select("select format(Created,'MMM') as mo,format(Created,'MM'), avg(Viewed_days) as viewed, avg(Completed_days) as completed from kpi
            group by format(Created,'MMM'),format(Created,'MM') order by format(Created,'MM'),format(Created,'MMM')");

        $ontime = DB::select("select [User],count(trans) as completed from kpi where Completed_days<=".$max." ".$cond." group by [User] order by [User]");
        
        return view('admin.kpi.user',compact('data','monthly', 'max', 'ontime'));

    }

    public function mrs() 
    {
        $page = new Page;
        $page->name = 'MCD';
        
        
        return view('admin.kpi.mrs',compact('page'));

    }

    
}
