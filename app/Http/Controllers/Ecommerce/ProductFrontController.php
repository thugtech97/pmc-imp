<?php

namespace App\Http\Controllers\Ecommerce;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

use App\Models\Ecommerce\{
    ProductCategory, Product
};

use App\Models\Page;
use App\Models\Announcement;

use DB;

class ProductFrontController extends Controller
{
    public function show($slug)
    {
        $page = new Page;
        $page->name = 'Product Details';

        $product = Product::with('photos')->whereSlug($slug)->where('status', 'PUBLISHED')->first();
        $relatedProducts = Product::where('category_id',$product->category_id)->where('id','<>',$product->id)->where('status','PUBLISHED')->skip(0)->take(10)->get();

        if (empty($product)) {
            abort(404);
        }

        $announcements = Announcement::where([
            'location' => 'product',
            'status' => 'active',
            'product_id' => $product->id
        ])->get();

        return view('theme.pages.ecommerce.product-details', compact('announcements', 'product', 'relatedProducts', 'page'));
    }

    public function show_forsale(){

        $products = DB::table('products')->where('for_sale', '1')->where('status','PUBLISHED')->where('for_sale_web','1')->where('is_misc','0')->select('name')->distinct()->get();
        $miscs = DB::table('products')->where('for_sale', '1')->where('status','PUBLISHED')->where('for_sale_web','1')->where('is_misc','1')->select('name')->distinct()->get();

        $page = new Page();
        $page->name = 'Order';

        return view('theme.ecommerce.product.order',compact('products','page','miscs'));

    }


    public function list(Request $request)
    {
        $page = new Page();
        $page->name = 'Products';
        $pageLimit = 9;

        if($request->has('search')){
            $products = Product::with(['reviews', 'photos'])->whereStatus('PUBLISHED');

            if(!empty($request->searchtxt)){
                $searchtxt = $request->searchtxt;
                $products = $products->where(function($query) use ($searchtxt){
                        $query->where('name','like','%'.$searchtxt.'%')
                            ->orWhere('description','like','%'.$searchtxt.'%');
                        });
            }

            if(!empty($request->category)){
                $products = $products->whereIn('category_id',$request->category);
            }

            if(!empty($request->brand)){
                $products = $products->whereIn('brand',$request->brand);
            }

            if(!empty($request->price)){

                $priceConditions = '';
                foreach($request->price as $price){
                    $range = explode("-", $price);
                    $priceConditions.=' or (price>='.$range[0].' and price<='.$range[1].')';
                }
                $priceConditions = "(".ltrim($priceConditions," or").")";
                $products = $products->whereRaw($priceConditions);


            }

            if(!empty($request->sort)){
                if($request->sort == 'Price low to high'){
                    $products = $products->orderBy('price','asc');
                }
                elseif($request->sort == 'Price high to low'){
                    $products = $products->orderBy('price','desc');
                }
            }

            if(!empty($request->limit)){
                if($request->limit=='All')
                    $pageLimit = 100000000;
                else
                    $pageLimit = $request->limit;
            }
            $total_product = $products->count();
            $products = $products->orderBy('name','asc')->paginate($pageLimit);
        }
        else{
            $productQuery = Product::with('photos')->whereStatus('PUBLISHED');

            $total_product = $productQuery->count();
            $products = $productQuery->orderBy('name','asc')->paginate($pageLimit);
        }
        /* End Search function */

        $categories = ProductCategory::all();

        return view('theme.pages.ecommerce.product-list',compact('products','total_product','page','request','categories'));

    }

    public function product_list(Request $request, $slug)
    {
        $category = ProductCategory::where('slug',$slug)->first();
        $subcategories = [];
        array_push($subcategories,$category->id);

        $page = new Page();
        $page = $category;
        $pageLimit = 40;

        foreach($category->child_categories as $child){
            array_push($subcategories,$child->id);

            foreach($child->child_categories as $sub){
                array_push($subcategories,$sub->id);
            }
        }

        $products = Product::whereIn('category_id',$subcategories)->where('status','PUBLISHED');
        $categories = ProductCategory::all();

        $maxPrice = $products->max('price');
        $minPrice = 1;

        if($request->has('search')){

            if(!empty($request->rating)){
                $rating = $request->rating;
                $products->whereIn('id',function($query) use($rating){
                    $query->select('product_id')->from('ecommerce_product_review')
                    ->where('rating',$rating)
                    ->where('is_approved',1);
                });
            }

            if(!empty($request->sort)){
                if($request->sort == 'Price low to high'){
                    $products = $products->orderBy('price','asc');
                }
                elseif($request->sort == 'Price high to low'){
                    $products = $products->orderBy('price','desc');
                }
            }

            if(!empty($request->limit)){
                if($request->limit=='All')
                    $pageLimit = 100000000;
                else
                    $pageLimit = $request->limit;
            }

            if(!empty($request->price)){
                $price = explode(';',$request->price);
                $products = $products->whereBetween('price',[$price[0],$price[1]]);

                $productMaxPrice = $maxPrice;
                $maxPrice = $price[1];
                $minPrice = $price[0];

            }

            $total_product = $products->count();
            $products = $products->orderBy('updated_at','desc')->paginate($pageLimit);
        }
        else{
            $productMaxPrice = $maxPrice;
            $minPrice = $minPrice;
            $total_product = $products->count();
            $products = $products->orderBy('name','asc')->paginate($pageLimit);
        }

        return view('theme.pages.ecommerce.category-product-list',
            compact(
                'products',
                'page',
                'total_product',
                'maxPrice',
                'minPrice',
                'productMaxPrice',
                'category',
                'categories',
                'request'
            )
        );
    }

    public function get_sub_categories_ids($ids, $categories)
    {
        $categoryIds = $categories->pluck('id');
        $ids = array_merge($ids, $categoryIds->toArray());
        foreach ($categoryIds as $id) {
            $subCategory = ProductCategory::find($id);
            $subSubCategories = $subCategory->child_categories;
            if ($subSubCategories && $subSubCategories->count()) {
                $ids = $this->get_sub_categories_ids($ids, $subSubCategories);
            }
        }

        return $ids;
    }

    public function categories($conditions=null){

        if($conditions){

        }
        else{
            $categories = DB::select('SELECT ifnull(c.name, "Uncategorized") as cat, ifnull(c.id,0) as cid,count(ifnull(c.id,0)) as total_products FROM `products` a left join product_categories c on c.id=a.category_id where a.deleted_at is null and a.status="PUBLISHED" GROUP BY c.name,c.id ORDER BY c.name');


            $data = '<ul class="listing-category">';
            foreach($categories as $category) {
                $ul2 = '';
                if ($category->child_categories) {
                    $ul2 = '<ul>';
                    $ul3 = '';
                }
                $data .= '<li><a href="#" onclick="filter_category('.$category->id.')">'.$category->name.'</a><li>';
            }
            $data .= '</ul>';
        }

        return $data;
    }
}
