<?php

namespace App\Http\Controllers\Ecommerce;

use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Facades\Input;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Http\Requests;

use Facades\App\Helpers\ListingHelper;
use App\Http\Requests\UserRequest;

use App\Mail\{AddNewUserMail, UpdatePasswordMail};

use App\Models\{
    Permission, Role, User, ActivityLog
};

use Illuminate\Support\Facades\Storage;
use App\Helpers\Setting;
use Auth;


class CustomerController extends Controller
{
    use SendsPasswordResetEmails;

    private $searchFields = ['name', 'is_active'];

    public function index()
    {   
        $listing = ListingHelper::required_condition('role_id', '=', 6);
        $listing->required_condition('is_active', '=', 1);
        $users = $listing->simple_search(User::class, $this->searchFields);

        // Simple search init data
        $filter = $listing->get_filter($this->searchFields);

        $searchType = 'simple_search';

        return view('admin.ecommerce.customers.index',compact('users','filter', 'searchType'));
    }


    public function deactivate(Request $request)
    {
    	$user = User::find($request->user_id);

        $user->update([
            'is_active' => 0,
            'user_id'   => Auth::id(),
        ]);

        $user->delete();

        return back()->with('success', __('standard.customers.status_success', ['status' => 'deactivated']));
    }

    public function activate(Request $request)
    {
        $user = User::withTrashed()->find($request->user_id)->update([
            'is_active' => 1,
            'user_id' => Auth::id() 
        ]);

        User::whereId($request->user_id)->restore();

        return back()->with('success', __('standard.customers.status_success', ['status' => 'activated']));
    }
}
