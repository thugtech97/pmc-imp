<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \App\Models\ActivityLog;
use App\Models\Ecommerce\SalesHeader;
use App\Models\Department;
use Auth;
use Carbon\Carbon;
use DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
    	if(Auth::user()->role_id == '6'){
    		Auth::logout();
    		return back()->with('error','Restricted access');
    	}
		$past = Carbon::now()->subMonths(3)->format('Y-m-d');
		$logs = ActivityLog::where('log_by', auth()->id())->orderBy('id','desc')->paginate(15);
		$totalOpenOrders = SalesHeader::whereIn("status", ["PARTIAL", "APPROVED"])->count();
		$unservedOrders = SalesHeader::whereDate('created_at', '>=', Carbon::now()->subMonths(3)->format('Y-m-d'))
		->where("status", "=", "APPROVED")->count();
		$ordersToday = SalesHeader::whereDate('created_at', Carbon::today())->count();

		$depts = [];
		$departments = Department::select('name')->get()->toArray();
		foreach ($departments as $dept) {
			$depts[] = $dept['name'];
		}
		$depts = json_encode($depts);

		/*$ordersPerDepartment = SalesHeader::groupBy(function($date) {
			return Carbon::parse($date->created_at)->format('Y-m');
		});*/
		$ordersPerDepartment = Department::with('orders')->get();
		
		$data = array();
		$pending = array();
		$completed = array();
		foreach ($ordersPerDepartment as $dept) {
			$pending[] = $dept->orders->whereIn("status", ["POSTED", "posted", "APPROVED", "approved", "PARTIAL", "partial"])->count();
			$completed[] = $dept->orders->whereIn("status", ["COMPLETED", "completed"])->count();
		}
		
		$data[] = array(
			"label" => "Pending",
			"data" => $pending,
			"borderColor" =>  "rgb(255, 99, 132)",
			"backgroundColor" => "rgba(255, 99, 132, 0.5)"
		);

		$data[] = array(
			"label" => "Completed",
			"data" => $completed,
			"borderColor" =>  "rgb(54, 162, 235)",
			"backgroundColor" => "rgba(54, 162, 235, 0.5)"
		);

		$data = json_encode($data);
		
		return view('admin.dashboard.index',compact('logs', 'totalOpenOrders', 'unservedOrders', 'ordersToday', 'depts', 'data', 'past'));
    }
}
