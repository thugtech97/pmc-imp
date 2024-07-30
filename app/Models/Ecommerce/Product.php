<?php

namespace App\Models\Ecommerce;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\User;
use App\Models\Issuance;

use App\Models\Ecommerce\{
    ProductTag, ProductCategory, ProductPhoto, PromoProducts, Cart, SalesDetail
};

use Carbon\Carbon;
use DB;
use Spatie\Activitylog\Traits\LogsActivity;

class Product extends Model
{
    use SoftDeletes;
    use LogsActivity;

    public $table = 'products';
    protected $casts = [
        'code' => 'string',
    ];
    protected $fillable = [ 
        'code', 
        'category_id', 
        'name', 
        'slug', 
        'short_description', 
        'description', 
        'currency', 
        'price', 
        'size',
        'weight', 
        'status', 
        'is_featured', 
        'uom',
        'oem',
        'created_by', 
        'meta_title', 
        'meta_keyword', 
        'meta_description',
        'brand',
        'reorder_point',
        'critical_qty',
        'usage_rate_qty',
        'usage_frequency',
        'min_qty',
        'max_qty',
        'stock_type',
        'inv_code',
        'on_hand'
    ];
    protected static $logAttributes = ['*'];
    protected static $logName = 'products';

    public function get_url()
    {
        return env('APP_URL')."/products/".$this->slug;
    }

    public function user()
    {
        return $this->belongsTo(User::class,'created_by');
    }

    public function getPriceWithCurrencyAttribute()
    {
    	return " ".number_format($this->price,2);
    }

    public function tags() {
        return $this->hasMany(ProductTag::class);
    }

    public function category() {
        return $this->belongsTo(ProductCategory::class)->withTrashed()->withDefault(['id' => '0','name' => 'Uncategorized']);
    }

    public static function colors($value) {
        return \DB::table('products_variations')->select('color')->distinct()->where('product_id',$value)->get();
    }

    public static function sizes($value) {
        return \DB::table('products_variations')->select('size')->distinct()->where('product_id',$value)->get();
    }

    public function photos()
    {
        return $this->hasMany(ProductPhoto::class);
    }

    public function getPhotoSmallAttribute()
    {

        $img = $this->photos()->where('description','small.png')->first();
        if(!$img){
            return env('APP_URL').'/images/no-image.png';
        }
        else{
            return str_replace("For Catalogue.png", "Front/small.png", $img->path);
        }

    }

    public function getPhotoPrimaryAttribute()
    {
        $photo = $this->photos()->where('is_primary', 1)->first();
        if(!$photo){
            return env('APP_URL').'/images/no-image.png';
        }
        else{
            return env('APP_URL') . '/storage/' . $photo->path;
        }
    }

    /*public function getInventoryAttribute()
    {
        
        $in = \DB::table('inventory_receiver_details')
                ->leftJoin('inventory_receiver_header', 'inventory_receiver_details.header_id', '=', 'inventory_receiver_header.id')
                ->where('inventory_receiver_details.product_id','=',$this->id)
                ->where('inventory_receiver_header.status','=','POSTED')
                ->sum('inventory_receiver_details.inventory');
        if(empty($in))
            $in=0;

        $cart = Cart::where('product_id',$this->id)->sum('qty');
         if(empty($cart))
            $cart=0;

        $out = \DB::table('ecommerce_sales_details')
                ->leftJoin('ecommerce_sales_headers', 'ecommerce_sales_details.sales_header_id', '=', 'ecommerce_sales_headers.id')
                ->where('ecommerce_sales_details.product_id','=',$this->id)
                ->where('ecommerce_sales_headers.payment_status','=','PAID')
                ->where('ecommerce_sales_headers.status','=','active')
                ->sum('qty');
        if(empty($out))
            $out=0;
        
        return ($in - ($out + $cart));
      
    }*/

    public function getInventoryActualAttribute()
    {
        $in = \DB::table('inventory_receiver_details')
                ->leftJoin('inventory_receiver_header', 'inventory_receiver_details.header_id', '=', 'inventory_receiver_header.id')
                ->where('inventory_receiver_details.product_id','=',$this->id)
                ->where('inventory_receiver_header.status','=','POSTED')
                ->sum('inventory_receiver_details.inventory');
        if(empty($in))
            $in=0;        

        
        $out = \DB::table('ecommerce_sales_details')
                ->leftJoin('ecommerce_sales_headers', 'ecommerce_sales_details.sales_header_id', '=', 'ecommerce_sales_headers.id')
                ->where('ecommerce_sales_details.product_id','=',$this->id)
                ->where('ecommerce_sales_headers.payment_status','=','PAID')
                ->where('ecommerce_sales_headers.status','=','active')
                ->sum('qty');
        if(empty($out))
            $out=0;
        
        return ($in - $out);
      
    }

    public function getMaxpurchaseAttribute() //use for identifying the maximum qty a customer can order
    {
        

        $in = \DB::table('inventory_receiver_details')
                ->leftJoin('inventory_receiver_header', 'inventory_receiver_details.header_id', '=', 'inventory_receiver_header.id')
                ->where('inventory_receiver_details.product_id','=',$this->id)
                ->where('inventory_receiver_header.status','=','POSTED')
                ->sum('inventory_receiver_details.inventory');
        if(empty($in))
            $in=0;

        $cart = Cart::where('product_id',$this->id)->sum('qty');
         if(empty($cart))
            $cart=0;
        
        $out = \DB::table('ecommerce_sales_details')
                ->leftJoin('ecommerce_sales_headers', 'ecommerce_sales_details.sales_header_id', '=', 'ecommerce_sales_headers.id')
                ->where('ecommerce_sales_details.product_id','=',$this->id)
                ->where('ecommerce_sales_headers.payment_status','=','PAID')
                ->where('ecommerce_sales_headers.status','=','active')
                ->sum('ecommerce_sales_details.qty');
        if(empty($out))
            $out=0;
        
        $inventory = $in - ($out + $cart + $this->reorder_point);

        return $inventory;
      
    }

    public static function related_products($id){

        $products = Product::whereStatus('PUBLISHED')->where('id','<>',$id)->take(3)->get();


        $data = '';

        foreach($products as $product){
            $data .= '
                <div class="col-md-4 col-sm-6 item">
                    <div class="product-link">
                        <div class="product-card">
                            <a href="'.route("product.front.show",$product->slug).'">
                                <div class="product-img">
                                    <img src="'.asset("storage/products/".$product->photoPrimary).'" alt="" />
                                </div>
                                <div class="gap-30"></div>
                                <p class="product-title">'.$product->name.'</p>
                            </a>
                            <div class="rating small">
                                '.$product->ratingStar.'
                            </div>
                            <h3 class="product-price">'.$product->priceWithCurrency.'</h3>
                        </div>
                    </div>
                </div>
            ';
        }

        return $data;

    }

    public static function totalProduct()
    {
        $total = Product::withTrashed()->get()->count();

        return $total;
    }

    public function is_editable()
    {
        return $this->status != 'UNEDITABLE';
    }

    public static function info($p){

        $pd = Product::where('name','=',$p)->first();

        return $pd;
    }

    public static function detail($p){

        $pd = Product::where('name',$p)->get();

        return $pd;
    }    

    public function get_image_file_name()
    {
        $path = explode('/', $this->zoom_image);
        $nameIndex = count($path) - 1;
        if ($nameIndex < 0)
            return '';

        return $path[$nameIndex];
    }

    public function reviews()
    {
        return $this->hasMany('App\Models\Ecommerce\ProductReview');
    }

    public function getRatingAttribute()
    {
        return $this->reviews->avg('rating');
    }

    public function getRatingStarAttribute(){
        $star = 5 - (integer) $this->rating;
        $front = '';
        for($x = 1; $x<=$this->rating; $x++){
            $front.='<span class="fa fa-star checked"></span>';
        }

        for($x = 1; $x<=$star; $x++){
            $front.='<span class="fa fa-star"></span>';
        }

        return $front;
    }

    public function getDiscountedPriceAttribute()
    {
        $promo = DB::table('promos')->join('promo_products','promos.id','=','promo_products.promo_id')->where('promos.status','ACTIVE')->where('promos.is_expire',0)->where('promo_products.product_id',$this->id);

        if($promo->count() > 0){
            $discount = $promo->max('promos.discount');

            $percentage = ($discount/100);
            $discountedAmount = ($this->price * $percentage);

            $price = ($this->price - $discountedAmount);
        } else {
            $price = $this->price;
        }

        return $price;
    }

    public static function onsale_checker($id)
    {
        $checkproduct = DB::table('promos')->join('promo_products','promos.id','=','promo_products.promo_id')->where('promos.status','ACTIVE')->where('promos.is_expire',0)->where('promo_products.product_id',$id)->count();

        return $checkproduct;
    }

    public function on_sale()
    {
        return $this->belongsTo(PromoProducts::class,'id','product_id');
    }

    public function getPromoDiscountAttribute()
    {
        $discount = DB::table('promos')->join('promo_products','promos.id','=','promo_products.promo_id')->where('promos.status','ACTIVE')->where('promos.is_expire',0)->where('promo_products.product_id',$this->id)->max('promos.discount');

        return $discount;
    }

    public function getImagesmallAttribute(){
        return $this->id;
        return $this->photos->where('description','small.png')->first();
    }

    public function stocks()
    {
        return 10;
    }

    public function orders()
    {
        return $this->hasMany(SalesDetail::class);
    }

    public function releasedOrders()
    {
        return $this->hasManyThrough(Issuance::class, SalesDetail::class, 'product_id', 'ecommerce_sales_details_id', 'id', 'id');
    }
}
