<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \App\Models\Ecommerce\Product;
use \App\Models\Ecommerce\ProductCategory;
use App\Models\Page;
use Auth;
use Illuminate\Support\Facades\Cache;
class CatalogueController extends Controller
{
    public function home()
    {
        $page = new Page;
        $page->name = 'MCD - Product Catalogue';
        $product_wphotos = [17074,17412,14017,17312,2656,3148];
        $newly_added = [2066,2115,2164,2656,3148,17412,14017];
        
        $previously_ordered = Product::whereIn('id',$product_wphotos)->take(15)->get();
        $newly_added = Product::whereIn('id',$newly_added)->orderBy('id','desc')->take(15)->get();

        $categories = ProductCategory::orderBy('name')->take(1)->get();
        foreach($categories as $c){
            $link = 'public/storage/product-images/['.$c.']/';   
        }
        logger('sss');
       
        $categories = ProductCategory::with('products.photos')->with('products', function($query) {
            $query->whereHas('photos', function($q) {
                $q->where('description', 'small.png');
            })->take(5);
        })->orderBy('description')->get();
        
        return view('catalogue.home',compact('categories', 'page', 'previously_ordered', 'newly_added'));
    }

    public function category_products($category_id)
    {
        $page = new Page;
        $page->name = 'MCD - Product Catalogue Category';

        $category = ProductCategory::find($category_id);
        $products = Product::whereHas('photos')->where('category_id', $category_id)->paginate(20);

        return view('catalogue.category', compact('products', 'category', 'page'));
    }

    public function load_products()
    {     
        $d = '';
        $handle = fopen(storage_path('app\public\mcd_prods.csv'), "r");
        for ($i = 0; $row = fgetcsv($handle ); ++$i) {
            //$d.=$row[1]."<br>";
            $category = ProductCategory::where('name',$row[7])->first();
            if(empty($category))
                $cat_id = 0;
            else
                $cat_id = $category->id;

            $slug = \App\Models\Page::convert_to_slug($row[3]);
            $product = Product::create([
                'code' => $row[2],
                'category_id' => $cat_id,
                'name' => $row[3],
                'slug' => $slug,
                'short_description' => '',
                'description' => '',
                'brand' => '',
                'reorder_point' => 0,
                'currency' => 'PHP',
                'price' => 0,
                'size' => '',
                'weight' => '',
                'status' => 'PUBLISHED',
                'is_featured' => 0,
                'uom' => $row[6],
                'meta_title' => '',
                'meta_keyword' => '',
                'meta_description' => '',
                'zoom_image' => '',
                'created_by' => '1',
                'critical_qty' => 0
            ]);
        }
        fclose($handle);   
        return $d;
        
    }

    public function search(Request $request)
    {
        $query = $request->input('query');
        $category_id = $request->input('category_id');
        $category_name = $request->input('category_name');
        
        if ($category_id) {
            $products = Product::whereHas('photos')->where('category_id', $category_id)
            ->where(function ($q) use ($query) {
                return $q->where('name', 'like', '%' . $query . '%')
                ->orWhere('description', 'like', '%' . $query . '%')
                ->orWhere('code', 'like', '%' . $query . '%');
            })
            ->paginate(20)
            ->setPath('');
        }
        else {
            $products = Product::whereHas('photos')->where('name', 'like', '%' . $query . '%')
                    ->orWhere('description', 'like', '%' . $query . '%')
                    ->orWhere('code', 'like', '%' . $query . '%')
                    ->paginate(20)
                    ->setPath('');
        }
        
        $pagination = $products->appends( array('$query' => $request->input('query')) );

        return view('catalogue.search')->with([
            'products' => $products, 
            'query' => $query,
            'category_name' => $category_name,
            'category_id' => $category_id
        ]);
    }
}
