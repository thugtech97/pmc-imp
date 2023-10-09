<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Announcement;
use App\Models\Ecommerce\Product;
use Facades\App\Helpers\ListingHelper;
use App\Models\User;
use App\Mail\AnnouncementEmail;
use Mail;

class AnnouncementController extends Controller
{
    private $searchFields = ['name'];

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $announcements = ListingHelper::simple_search(Announcement::class, $this->searchFields);
        $filter = ListingHelper::get_filter($this->searchFields);
        $searchType = 'simple_search';

        return view('admin.settings.announcements.index', compact(
            'announcements',
            'filter',
            'searchType'
        ));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $products = Product::all();

        return view('admin.settings.announcements.crud', compact('products'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $status = $request->schedule ? "disabled" : "active";
        $product = $request->location == "product" ? $request->product_id : null;

        $data = array(
            "name" => $request->name,
            "content" => $request->content,
            "schedule" => $request->schedule ?? date('Y-m-d'),
            "location" => $request->location,
            "product_id" => $product,
            "status" => $status
        );

        $announcement = Announcement::create($data);

        return redirect()->route('announcements.index')->with('success', 'New announcement has been created.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $products = Product::all();
        $edit = Announcement::find($id);

        return view('admin.settings.announcements.crud', compact('products', 'edit'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $status = $request->schedule ? "disabled" : "active";
        $product = $request->location == "product" ? $request->product_id : null;

        $data = array(
            "name" => $request->name,
            "content" => $request->content,
            "schedule" => $request->schedule ?? date('Y-m-d'),
            "location" => $request->location,
            "product_id" => $product,
            "status" => $status
        );

        $announcement = Announcement::find($id)->update($data);

        return redirect()->route('announcements.index')->with('success', 'Announcement with ID = ' . $id . ' has been updated.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Announcement::find($id)->delete();

        return redirect()->back()->with('success', 'Announcement has been deleted');
    }

    public function send(Request $request)
    {
        $users = User::all();

        Mail::to($users)->send(new AnnouncementEmail());

        return response()->json(['success'=>'Send email successfully.']);
    }

    public function change_status(Request $request)
    {
        $pages = explode("|", $request->pages);

        foreach ($pages as $page) {
            Announcement::where('status', '!=', $request->status)
            ->whereId($page)
            ->update([
                'status'  => $request->status
            ]);
        }

        return back()->with('success', __('standard.pages.status_success', ['STATUS' => $request->status]));
    }
}
