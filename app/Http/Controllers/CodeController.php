<?php

namespace App\Http\Controllers;

use App\Models\CostCode;
use App\Models\JobCode;
use Illuminate\Http\Request;

class CodeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\CostCode  $costCode
     * @return \Illuminate\Http\Response
     */
    public function show(CostCode $costCode)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\CostCode  $costCode
     * @return \Illuminate\Http\Response
     */
    public function edit(CostCode $costCode)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\CostCode  $costCode
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, CostCode $costCode)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CostCode  $costCode
     * @return \Illuminate\Http\Response
     */
    public function destroy(CostCode $costCode)
    {
        //
    }

    public function fetch_codes(Request $request)
    {
        switch ($request->type) {
            case 'CC':
                $costcodes = CostCode::all();
                return response()->json($costcodes);
            case 'JC':
                $jobcodes = JobCode::all();
                return response()->json($jobcodes);
            default:
                return response()->json(['error' => 'Invalid type'], 400);
        }
    }
}
