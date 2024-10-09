<?php

namespace App\Http\Controllers\Ecommerce;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Storage;
use Auth;
use Spatie\Activitylog\Models\Activity;
use App\Constants\Status;
use App\Helpers\ListingHelper;
use App\Http\Requests\ProductRequest;
use App\Models\Ecommerce\{
    ProductCategory, ProductPhoto, ProductTag, Product, InventoryRequest, InventoryLog, InventoryRequestItems
};
use App\Models\{
    Permission, Page
};
use PhpOffice\PhpSpreadsheet\IOFactory;

class ProductController extends Controller
{
    private $searchFields = ['name'];
    private $advanceSearchFields = ['category_id', 'name', 'brand', 'short_description', 'description', 'status', 'price1', 'price2', 'user_id', 'updated_at1', 'updated_at2'];
    private $model_directory = "App\Models\Ecommerce\Product";

    public function __construct () {}

    public function index(Request $request)
    {
        $query = Product::where('status', '!=', 'UNEDITABLE')->orderBy('updated_at', 'DESC');

        // FILTER DROPDOWN SEARCH
        if ($request && $request->productCode == 1) 
        {
            $query->whereNotNull('code');
        } 
        elseif ($request && $request->productCode == 2) 
        {
            $query->whereNull('code');
        }

        // SEARCH BY FIELDS
        $query->with('category');

        if ($request->has('search')) 
        {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'LIKE', "%$searchTerm%")
                ->orWhere('code', 'LIKE', "%$searchTerm%")
                ->orWhere('price', 'LIKE', "%$searchTerm%")
                ->orWhere('status', 'LIKE', "%$searchTerm%")
                ->orWhere('updated_at', 'LIKE', "%$searchTerm%")
                ->orWhereHas('category', function($query) use ($searchTerm) {
                    $query->where('name', 'LIKE', "%$searchTerm%");
                });
            });
        }
        
        $query->whereRaw('NOT EXISTS (
            SELECT 1 FROM products AS p2
            WHERE p2.code = products.code AND p2.updated_at > products.updated_at
        )');
        
        $products = $query->paginate(10);
        $helper = new ListingHelper;
        $listing = $helper->required_condition('status', '!=', 'UNEDITABLE');
        $filter = [];
        $searchType = 'simple_search';
        $advanceSearchData = $listing->get_search_data($this->advanceSearchFields);
        $uniqueProductByCategory = $listing->get_unique_item_by_column(Product::class, 'category_id');
        $uniqueProductByBrand = $listing->get_unique_item_by_column(Product::class, 'brand');
        $uniqueProductByUser = $listing->get_unique_item_by_column(Product::class, 'created_by');

        return view('admin.ecommerce.products.index', compact('products', 'filter', 'searchType','uniqueProductByCategory','uniqueProductByBrand','uniqueProductByUser','advanceSearchData'));
    }

    /*
    public function index()
    {
        $helper = new ListingHelper;
        $listing = $helper->required_condition('status', '!=', 'UNEDITABLE');
        $products = $listing->simple_search(Product::class, $this->searchFields);

        // Simple search init data
        $filter = $listing->get_filter($this->searchFields);
        $searchType = 'simple_search';

        $advanceSearchData = $listing->get_search_data($this->advanceSearchFields);
        $uniqueProductByCategory = $listing->get_unique_item_by_column(Product::class, 'category_id');
        $uniqueProductByBrand = $listing->get_unique_item_by_column(Product::class, 'brand');
        $uniqueProductByUser = $listing->get_unique_item_by_column(Product::class, 'created_by');

        return view('admin.ecommerce.products.index',compact('products', 'filter', 'searchType','uniqueProductByCategory','uniqueProductByBrand','uniqueProductByUser','advanceSearchData'));
    }
    */

    public function exportCsv(Request $request)
    {
        $fileName = 'inventory.csv';
        $products = Product::with('category')->get();

        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $columns = array('Category', 'Product ID', 'Product Description', 'Inventory');

        $callback = function() use($products, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($products as $product) {
                $row['Category'] = $product->category->name;
                $row['Product ID'] = $product->id;
                $row['Product Description'] = $product->description;
                $row['Inventory'] = $product->inventory;

                fputcsv($file, array($row['Category'], $row['Product ID'], $row['Product Description'], $row['Inventory']));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function importCsv(Request $request)
    {
        if ($request->hasFile("upload_file")) {
            $stored = $this->upload_file_to_storage('csv/', $request->file('upload_file'));
            $file = fopen(storage_path('app\public\csv\\' . $stored["name"]), "r");

            while(($getData = fgetcsv($file, 10000, ",")) !== FALSE) {
                $product = Product::find(intval($getData[1]));

                if ($product) {
                    if ($product->inventory != intval($getData[3])) {
                        $inventory_log = InventoryLog::create([
                            "product_id" => $product->id,
                            "old_value" => $product->inventory,
                            "new_value" => $getData[3],
                            "file" => $stored["url"]
                        ]);
                    }

                    $product->inventory = intval($getData[3]);
                    $product->update();
                }
            }
            
            fclose($file);

            return redirect()->back()->with('success', 'Successfully updated inventory!');
        }
    }

    public function advance_index(Request $request)
    {
        $customConditions = [
            [
                'field' => 'status',
                'operator' => '!=',
                'value' => 'UNEDITABLE',
                'apply_to_deleted_data' => true
            ]
        ];

        $equalQueryFields = ['category_id', 'status', 'created_by','brand'];

        $listing = new ListingHelper( 'desc', 10, 'updated_at', $customConditions);
        $products = $listing->advance_search($this->model_directory, $this->advanceSearchFields, $equalQueryFields);

        $filter = $listing->get_filter($this->searchFields);

        $advanceSearchData = $listing->get_search_data($this->advanceSearchFields);
        $uniqueProductByCategory = $listing->get_unique_item_by_column($this->model_directory, 'category_id');
        $uniqueProductByBrand = $listing->get_unique_item_by_column($this->model_directory, 'brand');
        $uniqueProductByUser = $listing->get_unique_item_by_column($this->model_directory, 'created_by');

        $searchType = 'advance_search';

        return view('admin.ecommerce.products.index',compact('products', 'filter', 'searchType','uniqueProductByCategory','uniqueProductByBrand','uniqueProductByUser','advanceSearchData'));
    }

    public function create()
    {
        $categories = ProductCategory::orderBy('name','asc')->get();

        return view('admin.ecommerce.products.create', compact('categories'));
    }

    public function store(ProductRequest $request)
    {
        $zoom_image = '';

        $slug = Page::convert_to_slug($request->name);

        $product = Product::create([
            'code' => $request->code,
            'category_id' => $request->category,
            'name' => $request->name,
            'slug' => $slug,
            // 'short_description' => $request->short_description,
            // 'description' => $request->long_description,
            'brand' => $request->brand,
            'reorder_point' => $request->reorder_point ?? 0.00,
            'currency' => 'PHP',
            'price' => $request->price,
            'size' => $request->size,
            'weight' => $request->weight,
            'status' => ($request->has('status') ? 'PUBLISHED' : 'PRIVATE'),
            'is_featured' => $request->has('is_featured'),
            'uom' => $request->uom,
            'meta_title' => $request->seo_title,
            'meta_keyword' => $request->seo_keywords,
            'meta_description' => $request->seo_description,
            'zoom_image' => $zoom_image,
            'created_by' => Auth::id(),
            'critical_qty' => $request->critical_qty
        ]);

        if($request->hasFile('zoom_image'))
        {
            $newFile = $this->upload_file_to_storage('zoom_image/'.$product->id, $request->file('zoom_image'));
            $zoom_image = $newFile['url'];
        }

        $this->tags($product->id, $request->tags);

        $newPhotos = $this->set_order(request('photos'));
        $productPhotos = $this->move_product_to_official_folder($product->id, $newPhotos);

        $this->delete_temporary_product_folder();

        foreach ($productPhotos as $key => $photo) {
            ProductPhoto::create([
                'product_id' => $product->id,
                'name' => (empty($photo['name']) ? '' : $photo['name']),
                'description' => '',
                'path' => $photo['image_path'],
                'status' => 'PUBLISHED',
                'is_primary' => ($key == $request->is_primary) ? 1 : 0,
                'created_by' => Auth::id()
            ]);
        }

        return redirect()->route('products.index')->with('success', __('standard.products.product.create_success'));
    }

    public function tags($id,$tags)
    {
        foreach(explode(',',$tags) as $tag)
        {
            ProductTag::create([
                'product_id' => $id,
                'tag' => $tag,
                'created_by' => Auth::id()
            ]);
        }
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
        $product = Product::findOrFail($id);
        $categories = ProductCategory::orderBy('name','asc')->get();

        return view('admin.ecommerce.products.edit',compact('product','categories'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(ProductRequest $request, $id)
    {
        $product = Product::findOrFail($id);

        $zoom_image = $product->zoom_image;
        if (isset($request->delete_zoom)) {
            $zoom_image = '';
        }

        if($request->hasFile('zoom_image'))
        {
            $newFile = $this->upload_file_to_storage('zoom_image/'.$id, $request->file('zoom_image'));
            $zoom_image = $newFile['url'];
        }

        if($product->name == $request->name){
            $slug = $product->slug;
        }
        else{
            $slug = \App\Models\Page::convert_to_slug($request->name);
        }

        $product->update([
            'code' => $request->code,
            'category_id' => $request->category,
            'name' => $request->name,
            'brand' => $request->brand,
            'slug' => $slug,
            // 'short_description' => $request->short_description,
            // 'description' => $request->long_description,
            'currency' => 'PHP',
            'price' => $request->price,
            //'reorder_point' => $request->reorder_point,
            'size' => $request->size,
            'weight' => $request->weight,
            'status' => ($request->has('status') ? 'PUBLISHED' : 'PRIVATE'),
            'is_featured' => $request->has('is_featured'),
            'uom' => $request->uom,
            'zoom_image' => $zoom_image,
            'meta_title' => $request->seo_title,
            'meta_keyword' => $request->seo_keywords,
            'meta_description' => $request->seo_description,
            'created_by' => Auth::id(),
            'oem' => $request->oem,
            'stock_type' => $request->stock_type,
            'inv_code' => $request->inv_code,
            'usage_rate_qty' => $request->usage_rate_qty,
            'on_hand' => $request->on_hand,
            'min_qty' => $request->min_qty,
            'max_qty' => $request->max_qty,
            //'critical_qty' => $request->critical_qty
        ]);

        $this->update_tags($product->id,$request->tags);

        $photos = $this->set_order(request('photos'));

        $this->update_photos($this->get_product_photos($photos));

        $this->remove_photos_from_product(request('remove_photos'));

        $newPhotos = $this->get_new_photos($photos);

        $newPhotos = $this->move_product_to_official_folder($product->id, $newPhotos);

        foreach ($newPhotos as $key => $photo) {
            ProductPhoto::create([
                'product_id' => $product->id,
                'name' => (empty($photo['name']) ? '' : $photo['name']),
                'description' => '',
                'path' => $photo['image_path'],
                'status' => 'PUBLISHED',
                'is_primary' => ($key == $request->is_primary) ? 1 : 0,
                'created_by' => Auth::id()
            ]);
        }

        // Update the inventory items, to sync data.
        $items = InventoryRequestItems::where('product_id', $product->id);
                
        $items->update([
            "stock_code" => $request->code,
            "item_description" => $request->name,
            "UoM" => $request->uom,
        ]);

        return redirect()->route('products.edit', $product->id)->with('success', __('standard.products.product.update_success'));
    }

    public function update_photos($photos)
    {
        foreach ($photos as $photo) {
            if ($photo) {
                $photo['name'] = ($photo['name']) ? $photo['name'] : '';
                ProductPhoto::find($photo['id'])->update($photo);
            }
        }
    }

    public function update_tags($id,$tags)
    {
        $delete = ProductTag::where('product_id',$id)->forceDelete();

        if($delete){
            foreach(explode(',',$tags) as $tag){
                ProductTag::create([
                    'product_id' => $id,
                    'tag' => $tag,
                    'created_by' => Auth::id()
                ]);
            }
        }

    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function get_slug(Request $request)
    {
        return Page::convert_to_slug($request->url, $request->parentPage);
    }

    public function restore($id)
    {
        Product::withTrashed()->find($id)->update(['created_by' => Auth::id() ]);
        Product::whereId($id)->restore();

        return back()->with('success', __('standard.products.product.restore_product_success'));
    }

    public function change_status($id,$status)
    {
        Product::where('id',$id)->update([
            'status' => $status,
            'created_by' => Auth::id()
        ]);

        return back()->with('success', __('standard.products.product.update_status_success', ['STATUS' => $status]));
    }

    public function single_delete(Request $request)
    {
        $product = Product::findOrFail($request->products);
        $product->update([ 'created_by' => Auth::id() ]);
        $product->delete();

        return back()->with('success', __('standard.products.product.single_delete_success'));

    }

    public function multiple_change_status(Request $request)
    {
        $products = explode("|", $request->products);

        foreach ($products as $product) {
            $publish = Product::where('status', '!=', $request->status)->whereId($product)->update([
                'status'  => $request->status,
                'created_by' => Auth::id()
            ]);
        }

        return back()->with('success',  __('standard.products.product.change_status_success', ['STATUS' => $request->status]));
    }

    public function multiple_delete(Request $request)
    {
        $products = explode("|",$request->products);

        foreach($products as $product){
            Product::whereId($product)->update(['created_by' => Auth::id() ]);
            Product::whereId($product)->delete();
        }

        return back()->with('success', __('standard.products.product.multiple_delete_success'));
    }

    // save files to temporary folder
    public function upload(Request $request)
    {
        if ($request->hasFile('banner')) {

            $newFile = $this->upload_file_to_temporary_storage($request->file('banner'));

            return response()->json([
                'status' => 'success',
                'image_url' => $newFile['url'],
                'image_name' => $newFile['name'],
                'image_path' => $newFile['path'],
            ]);
        }

        return response()->json([
            'status' => 'failed',
            'image_url' => '',
            'image_name' => ''
        ]);
    }

    public function get_product_photos($photos)
    {
        return array_filter($photos, function ($photo) {
            return isset($photo['id']);
        });
    }

    public function get_new_photos($photos)
    {
        return array_filter($photos, function ($photo) {
            return !isset($photo['id']);
        });
    }

    public function remove_photos_from_product($photos)
    {
        ProductPhoto::find($photos ?? [])->each(function ($photo, $key) {
            $imagePath = $this->get_banner_path_in_storage($photo->image_path);
            Storage::disk('public')->delete($imagePath);
            $photo->update(['user_id' => auth()->id()]);
            $photo->delete();

        });
    }

    public function upload_file_to_temporary_storage($file)
    {
        $temporaryFolder = 'temporary_products'.auth()->id();
        $fileName = $file->getClientOriginalName();
        if (Storage::disk('public')->exists($temporaryFolder.'/'.$fileName)) {
            $fileName = $this->make_unique_file_name($temporaryFolder, $fileName);
        }

        $path = Storage::disk('public')->putFileAs($temporaryFolder, $file, $fileName);
        $url = Storage::disk('public')->url($path);

        return [
            'path' => $temporaryFolder.'/'.$fileName,
            'name' => $fileName,
            'url' => $url
        ];
    }
//

// move uploaded product files to official product folder
    public function delete_temporary_product_folder()
    {
        $temporaryFolder = 'temporary_products'.auth()->id();
        Storage::disk('public')->deleteDirectory($temporaryFolder);
    }

    public function set_order($products = [])
    {
        $products = $products ?? [];

        $count = 1;
        foreach($products as $key => $product) {
            $products[$key]['order'] = $count;
            $count += 1;
        }

        return $products;
    }

    public function move_product_to_official_folder($productId, $products)
    {
        foreach ($products as $key => $product) {
            $temporaryPath = $this->get_banner_path_in_storage($products[$key]['image_path']);
            $fileName = $this->get_banner_file_name($products[$key]['image_path']);
            $bannerFolder = '';

            $products[$key]['image_path'] = $this->move_to_products_folder($productId, $temporaryPath, $bannerFolder.$fileName);
        }

        return $products;
    }

    public function get_banner_path_in_storage($path)
    {
        $paths = explode('storage/', $path);

        if (count($paths) == 1) {
            return '';
        }

        return explode('storage/', $path)[1];
    }

    public function get_banner_file_name($path)
    {
        $temporaryFolder = 'temporary_products'.auth()->id();
        return explode($temporaryFolder, $path)[1];
    }

    public function move_to_products_folder($id,$temporaryPath, $fileName)
    {
        $folder = 'products/'.$id;
        if (Storage::disk('public')->exists($folder.$fileName)) {
            $fileName = $this->make_unique_file_name($folder, $fileName);
        }

        $newPath = $folder.$fileName;
        Storage::disk('public')->move($temporaryPath, $newPath);
        return $id.$fileName;
    }

    public function make_unique_file_name($folder, $fileName)
    {
        $fileNames = explode(".", $fileName);
        $count = 2;
        $newFilename = $fileNames[0].' ('.$count.').'.$fileNames[1];
        while(Storage::disk('public')->exists($folder.'/'.$newFilename)) {
            $count += 1;
            $newFilename = $fileNames[0].' ('.$count.').'.$fileNames[1];
        }

        return $newFilename;
    }

    public function upload_file_to_storage($folder, $file, $key = '') {

        $fileName = $file->getClientOriginalName();
        if (Storage::disk('public')->exists($folder.'/'.$fileName)) {
            $fileNames = explode(".", $fileName);
            $count = 2;
            $newFilename = $fileNames[0].' ('.$count.').'.$fileNames[1];
            while(Storage::disk('public')->exists($folder.'/'.$newFilename)) {
                $count += 1;
                $newFilename = $fileNames[0].' ('.$count.').'.$fileNames[1];
            }

            $fileName = $newFilename;
        }

        $path = Storage::disk('public')->putFileAs($folder, $file, $fileName);
        $url = Storage::disk('public')->url($path);
        $returnArr = [
            'name' => $fileName,
            'url' => $url
        ];

        if ($key == '') {
            return $returnArr;
        } else {
            return $returnArr[$key];
        }
    }

    public function history($slug)
    {
        $logs = Activity::where('log_name', 'products')->simplePaginate(10);

        return view('admin.ecommerce.products.history.index', compact('logs'));
    }

    public function inventoryLogs()
    {
        $logs = InventoryLog::with('product')->paginate(10);
        
        return view('admin.ecommerce.products.inventory_history', compact('logs'));
    }

    public function product_search(Request $request)
    {
        $product = Product::select(
                'products.*', 
                'iri.id as item_id',
                'iri.imf_no',
                'iri.brand',
                'iri.OEM_ID',
                'iri.min_qty',
                'iri.max_qty',
                'iri.usage_rate_qty',
                'iri.usage_frequency',
                'iri.purpose'
            )
            ->where("code", $request->code)
            ->leftJoin('inventory_requests_items as iri', 'iri.stock_code', 'code')
            ->first();
       
        if (!empty($product)) 
        {    
            $items = InventoryRequestItems::select(
                    'inventory_requests_items.*',
                    'inventory_requests.status as imf_status',
                    'inventory_requests.type as type',
                )
                ->where('stock_code', $product->code)
                ->leftJoin('inventory_requests', 'inventory_requests.id', 'inventory_requests_items.imf_no')
                ->where('inventory_requests.type', 'update')
                ->get();

            $hasApproved = false;
          
            if (!$items->isEmpty())
            {
                foreach ($items as $item) {
                    if ($item->imf_status !== Status::APPROVED_MCD && $item->imf_status !== Status::CANCELLED) 
                    {
                        $hasApproved = false;
                        break;
                    } else {
                        $hasApproved = true;
                    }
                }
            }

            if (count($items) === 0 || $hasApproved)
            {
                $filenameWithoutExtension = pathinfo($product->item_id, PATHINFO_FILENAME);
                $directoryPath = 'public/inventory_items/' . $product->imf_no;

                $matchingFiles = collect(Storage::files($directoryPath))->filter(function ($file) use ($filenameWithoutExtension) {
                    return pathinfo($file, PATHINFO_FILENAME) === $filenameWithoutExtension;
                });

                $product->attachments = $matchingFiles->isNotEmpty() ? Storage::url($matchingFiles->first()) : null;

                $response = [
                    'status' => 'success',
                    'data' => $product,
                    'hasApproved' => $hasApproved,
                    'valid' => 1
                ];
                return response()->json($response);
            } else {
                $response = [
                    'status' => 'success',
                    'hasApproved' => $hasApproved,
                    'valid' => 0
                ];

                return response()->json($response);
            }
        } else {
            $response = [
                'status' => 'error',
                'data' => null,
            ];
            return response()->json($response);
        }
    }
    
    public function upload_stocks(Request $request){
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $extension = $file->getClientOriginalExtension();

            if ($extension === 'xlsx' || $extension === 'xls') {
                try {
                    $spreadsheet = IOFactory::load($file);
                    $worksheet = $spreadsheet->getActiveSheet();
                    $rows = $worksheet->toArray();
                    //$headers = ["Class", "Stock Type", "Inv Code", "Stock Code", "Stock Description", "OEM ID", "UOM", "Average Monthly UR(as of March)", "On Hand Qty As On (OH)", "On-Order Qty Posted (OO)"];
                    $headers = ["Stock Code", "Stock Description", "OEM ID", "UOM", "Stock Type", "Inv Code", "Average Unit Price", "Average Monthly UR", "On Hand Qty As On (OH)", "MIN", "MAX"];
                    $fileHeaders = array_slice(array_map('strtoupper', array_map('trim', $rows[4])), 0, 11);

                    // Remove the month from "Average Monthly UR (as of ...)" in the file headers
                    $fileHeaders = array_map(function ($header) {
                        return preg_replace('/\(as of [A-Za-z]+\)/i', '', $header);
                    }, $fileHeaders);

                    // Compare headers
                    if ($fileHeaders !== array_map('strtoupper', $headers)) {
                        return back()->with('error', "Headers not valid!");
                    }

                    
                    $stop = false;
                    for ($i = 5; !$stop; $i++) {
                        if (isset($rows[$i])){
                            $code = trim($rows[$i][0]); // Trim leading and trailing spaces
                            if($code !== ""){
                                $product = Product::where('code', $code)->first();
                                if ($product) {
                                    $product->update([
                                        'code' => $code,
                                        'description' => $rows[$i][1],
                                        'oem' => $rows[$i][2],
                                        'uom' => $rows[$i][3],
                                        'name' => $rows[$i][1],
                                        'stock_type' => $rows[$i][4],
                                        'inv_code' => $rows[$i][5],
                                        'usage_rate_qty' => (int)$rows[$i][7],
                                        'on_hand' => (int)$rows[$i][8],
                                        'min_qty' => (int)$rows[$i][9],
                                        'max_qty' => (int)$rows[$i][10],
                                        'price' => number_format((float)str_replace(',', '', $rows[$i][6]), 4, '.', ''),
                                    ]);
                                } else {
                                    Product::create([
                                        'code' => $code,
                                        'category_id' => 29,
                                        'description' => $rows[$i][1],
                                        'oem' => $rows[$i][2],
                                        'uom' => $rows[$i][3],
                                        'name' => $rows[$i][1],
                                        'stock_type' => $rows[$i][4],
                                        'inv_code' => $rows[$i][5],
                                        'usage_rate_qty' => (int)$rows[$i][7],
                                        'on_hand' => (int)$rows[$i][8],
                                        'min_qty' => (int)$rows[$i][9],
                                        'max_qty' => (int)$rows[$i][10],
                                        'price' => number_format((float)str_replace(',', '', $rows[$i][6]), 4, '.', ''), 
                                        'slug' => 'new-product',
                                        'status' => 'PUBLISHED',
                                        'created_by' => Auth::id()
                                    ]);
                                }
                            }else{
                                $stop = true;
                                break;
                            }
                        }else{
                            $stop = true;
                            break;
                        }
                    }
                    return back()->with('success', "Inventory updated!");
                } catch (\Exception $e) {
                    return back()->with('error', "Error reading the Excel file: " . $e->getMessage());
                }
            } else {
                return back()->with('error', "Invalid file format. Only .xls .xlsx files are allowed.");
            }
        }
    
        return back()->with('error', "No file uploaded.");
    }
    

}
