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
        $fileName = 'inventory-' . now()->format('Ymd-His') . '.csv';   // e.g. inventory-20261115-143005.csv

        // One row per stock code — the latest-updated product for that code, same rule
        // as the stock list. Stocks without a code can't be identified, so they're left out.
        $products = Product::whereNotNull('code')
            ->where('code', '<>', '')
            ->whereRaw('NOT EXISTS (
                SELECT 1 FROM products AS p2
                WHERE p2.code = products.code AND p2.updated_at > products.updated_at
                  AND p2.deleted_at IS NULL
            )')
            ->orderBy('code')
            ->orderBy('id', 'desc')
            ->get(['id', 'code', 'description', 'inventory'])
            ->unique('code');   // tie on updated_at — keep the newest id

        // Built in memory (a few MB) rather than streamed, so Content-Length is sent and
        // the page's download progress bar can show a real percentage.
        $file = fopen('php://temp', 'w+');
        fputcsv($file, array('Stock Code', 'Product Description', 'Inventory'));
        foreach ($products as $product) {
            fputcsv($file, array($product->code, $product->description, $product->inventory));
        }
        rewind($file);
        $csv = stream_get_contents($file);
        fclose($file);

        return response($csv, 200, array(
            "Content-Type"        => "text/csv; charset=UTF-8",
            "Content-Length"      => strlen($csv),
            "Content-Disposition" => "attachment; filename=\"$fileName\"",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0",
        ));
    }

    /**
     * Update Inventory from the "Download Inventory Template" CSV
     * (Stock Code, Product Description, Inventory). Matches by stock code (latest
     * product for the code, same as the export), only touches rows whose value
     * changed, and logs each change to inventory_logs with the uploaded file.
     */
    public function importCsv(Request $request)
    {
        $request->validate([
            'upload_file' => 'required|file|mimes:csv,txt|max:20480',
        ], [
            'upload_file.required' => 'Please choose the inventory CSV to upload.',
            'upload_file.mimes'    => 'The file must be a .csv — use "Download Inventory Template", edit the Inventory column, and save it as CSV.',
        ]);

        // The stock list uploads via AJAX (for its progress bar): flash the result and hand
        // back where to go, so the summary still shows on the reloaded page.
        $finish = function (array $flash) use ($request) {
            if ($request->ajax()) {
                foreach ($flash as $key => $value) {
                    session()->flash($key, $value);
                }
                return response()->json(['redirect' => url()->previous()]);
            }
            $redirect = back();
            foreach ($flash as $key => $value) {
                $redirect = $redirect->with($key, $value);
            }
            return $redirect;
        };

        $upload = $request->file('upload_file');
        $handle = fopen($upload->getRealPath(), 'r');
        if (!$handle) {
            return $finish(['error' => 'Could not read the uploaded file.']);
        }

        // Skip the BOM Excel's "CSV UTF-8" adds — left in, it glues onto the first
        // header and stops fgetcsv from seeing its quotes.
        if (fread($handle, 3) !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        // Locate the columns by header name so a re-ordered file still works.
        $header = fgetcsv($handle, 10000, ',');
        $header = array_map(function ($h) {
            return strtolower(trim((string) $h));
        }, $header ?: []);
        $codeCol = array_search('stock code', $header, true);
        $qtyCol  = array_search('inventory', $header, true);

        if ($codeCol === false || $qtyCol === false) {
            fclose($handle);
            return $finish(['error' => 'Wrong file format — the first row must have the "Stock Code" and "Inventory" columns. Download the inventory template and use that.']);
        }

        $rows = [];         // code => new inventory (a repeated code: last row wins)
        $invalid = [];      // "row N (code): value"
        $duplicates = [];
        $line = 1;
        while (($data = fgetcsv($handle, 10000, ',')) !== false) {
            $line++;
            $code = trim((string) ($data[$codeCol] ?? ''));
            $raw  = str_replace(',', '', trim((string) ($data[$qtyCol] ?? '')));

            if ($code === '' && $raw === '') {
                continue;   // blank line
            }
            if ($code === '' || !preg_match('/^\d+(\.0+)?$/', $raw)) {
                $invalid[] = "row {$line}" . ($code !== '' ? " ({$code})" : '') . ': "' . ($data[$qtyCol] ?? '') . '"';
                continue;
            }
            if (array_key_exists($code, $rows)) {
                $duplicates[$code] = true;
            }
            $rows[$code] = (int) $raw;
        }
        fclose($handle);

        if (empty($rows) && empty($invalid)) {
            return $finish(['error' => 'The file has no stock rows to update.']);
        }

        // Latest product per code — chunked, SQL Server caps a query at 2100 parameters.
        $products = [];
        foreach (array_chunk(array_keys($rows), 1000) as $chunk) {
            $found = Product::whereIn('code', array_map('strval', $chunk))
                ->orderBy('updated_at')->orderBy('id')
                ->get();
            foreach ($found as $product) {
                $products[(string) $product->code] = $product;   // ascending, so the newest wins
            }
        }

        // Keep a copy of the file for the inventory history log.
        $stored = $this->upload_file_to_storage('csv', $upload);

        $updated = 0;
        $unchanged = 0;
        $notFound = [];

        \DB::transaction(function () use ($rows, $products, $stored, &$updated, &$unchanged, &$notFound) {
            foreach ($rows as $code => $qty) {
                $product = $products[(string) $code] ?? null;
                if (!$product) {
                    $notFound[] = $code;
                    continue;
                }
                if ((int) $product->inventory === $qty) {
                    $unchanged++;
                    continue;
                }

                InventoryLog::create([
                    'product_id' => $product->id,
                    'old_value'  => $product->inventory,
                    'new_value'  => $qty,
                    'file'       => $stored['url'],
                ]);

                $product->inventory = $qty;
                $product->save();
                $updated++;
            }
        });

        return $finish([
            'success'          => "Inventory upload done: {$updated} updated, {$unchanged} unchanged.",
            'inventory_import' => [
                'file'       => $upload->getClientOriginalName(),
                'updated'    => $updated,
                'unchanged'  => $unchanged,
                'not_found'  => $notFound,
                'invalid'    => $invalid,
                'duplicates' => array_keys($duplicates),
            ],
        ]);
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
            'last_po_ref' => $request->last_po_ref,
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

    /**
     * Stock-code lookup for an Update IMF row: the item's current values to
     * pre-fill the row, or why it can't be added.
     *
     * status=error           no product with that code
     * status=success valid=0 already on another open Update IMF (open_imf)
     * status=success valid=1 data = the current values
     */
    public function product_search(Request $request)
    {
        $current = InventoryRequestItems::currentValuesFor($request->code);

        if (!$current) {
            return response()->json(['status' => 'error', 'data' => null]);
        }

        $openImf = InventoryRequestItems::openUpdateImfFor($request->code, $request->input('except_imf'));

        if ($openImf) {
            return response()->json(['status' => 'success', 'valid' => 0, 'open_imf' => \App\Models\Ecommerce\InventoryRequest::displayNoFor($openImf)]);
        }

        return response()->json(['status' => 'success', 'valid' => 1, 'data' => $current['values']]);
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
                    $headers = ["Stock Code", "Stock Description", "OEM ID", "UOM", "Stock Type", "Inv Code", "Last PO Ref", "Average Unit Price", "Average Monthly UR", "On Hand Qty As On (OH)", "MIN", "MAX"];
                    $fileHeaders = array_slice(array_map('strtoupper', array_map('trim', $rows[4])), 0, 12);

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
                                        'last_po_ref' => $rows[$i][6],
                                        'usage_rate_qty' => (int)$rows[$i][8],
                                        'on_hand' => (int)$rows[$i][9],
                                        'min_qty' => (int)$rows[$i][10],
                                        'max_qty' => (int)$rows[$i][11],
                                        'price' => number_format((float)str_replace(',', '', $rows[$i][7]), 4, '.', ''),
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
                                        'last_po_ref' => $rows[$i][6],
                                        'usage_rate_qty' => (int)$rows[$i][8],
                                        'on_hand' => (int)$rows[$i][9],
                                        'min_qty' => (int)$rows[$i][10],
                                        'max_qty' => (int)$rows[$i][11],
                                        'price' => number_format((float)str_replace(',', '', $rows[$i][7]), 4, '.', ''), 
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

    public function product_lookup(Request $request) {
        $name = $request->name;

        if (!$name) {
            return response()->json([], 200);
        }

        $products = Product::where('name', 'LIKE', '%' . $name . '%')
            ->orWhere('code', 'LIKE', '%' . $name . '%')
            ->limit(30)
            ->get();

        return response()->json($products, 200);
    }

}
