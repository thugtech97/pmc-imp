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
        if(auth()->user()->role_id == 1){
            return route('dashboard');
        }
        if(auth()->user()->role_id == 4){
            return route('sales-transaction.index');
        }
        if(auth()->user()->role_id == 5){
            return route('pa.index');
        }

        if(auth()->user()->role_id == 7){
            return route('imf.requests');
        }

        if(auth()->user()->role_id == 8){
            return route('imf.requests');
        }
        if(auth()->user()->role_id == 9){
            return route('purchaser.index');
        }
        if(auth()->user()->role_id == 10){
            return route('warehouse_mrs.index');
        }
    }

    protected function loggedOut()
    {   
        Auth::logout();
        return redirect()->route('panel.login');
    }
}
