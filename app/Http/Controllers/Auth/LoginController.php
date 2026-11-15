<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

use Auth;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    protected function redirectTo()
    {
        // Matched by role name, never role_id — the ids differ per environment.
        $user = auth()->user();

        // The IMF module is all the Planning Supervisor has, so land them on it.
        if($user->has_role('Planning Supervisor')){
            return route('imf.requests');
        }

        // Admin lands on the MCD dashboard — the CMS dashboard is hidden from the sidebar.
        if($user->has_role('Admin', 'MCD Planner', 'MCD Verifier', 'MCD Approver')){
            return route('sales.dashboard');
        }
        if($user->has_role('Purchasing Officer')){
            return route('pa.index');
        }
        if($user->has_role('Purchaser')){
            return route('purchaser.index');
        }
        if($user->has_role('Warehouse In-charge')){
            return route('warehouse_mrs.index');
        }

        // Department users and any role with no panel landing page of its own.
        // Spelled out rather than left to fall through as null, which is what
        // sent the Planning Supervisor to the storefront.
        return route('home');
    }

    protected function loggedOut()
    {   
        Auth::logout();
        return redirect()->route('panel.login');
    }
}
