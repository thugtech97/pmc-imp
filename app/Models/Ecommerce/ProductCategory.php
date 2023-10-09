<?php

namespace App\Models\Ecommerce;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\ActivityLog;
use App\Models\Ecommerce\Product;

class ProductCategory extends Model
{
    use SoftDeletes;

    public $table = 'product_categories';
    protected $fillable = [ 'parent_id', 'name', 'slug', 'description', 'status', 'created_by', 'image'];

    public function get_url()
    {
        return env('APP_URL')."/product-categories/".$this->slug;
    }

    public function child_categories() {
        return  $this->hasMany(ProductCategory::class, 'parent_id')->where('status','PUBLISHED');
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    public function published_products()
    {
        return $this->hasMany(Product::class, 'category_id')->where('status','PUBLISHED');
    }

    public function getPhotoAttribute(){

        $dr = 'storage/product-images/['.$this->name.']/';
        if(is_dir($dr)){
            $iti = new \RecursiveDirectoryIterator($dr);
            foreach(new \RecursiveIteratorIterator($iti) as $file){
                if($file->getFilename() == 'small.png'){
                    return $file;                
                }               
            }
        }

        return env('APP_URL').'/images/no-image.png';
      
        
        

    }

    public function getProductwithpicsAttribute()
    {
        $prods = \DB::select("SELECT distinct p.* FROM product_photos ph
                    left join products p on p.id=ph.product_id
                    left join product_categories c on c.id=p.category_id
                    WHERE c.id='".$this->id."' and ph.description='small.png'");
        return collect($prods);

        /*
        $prods = $this->published_products;

        foreach ($this->published_products as $prod) {

            $has_photos = \App\Models\Ecommerce\ProductPhoto::where('product_id',$prod->id)->first();
            if ($has_photos === null) {
               $prods = $prods->except($prod->id);
            }
        }

        return $prods;
        */
    }

    public function featured_products()
    {
        return $this->products()->where('is_featured', 1)->get();
    }



    // ******** AUDIT LOG ******** //
    // Need to change every model
    static $oldModel;
    static $tableTitle = 'product category';
    static $name = 'name';
    static $unrelatedFields = ['id', 'slug', 'created_at', 'updated_at', 'deleted_at'];
    static $logName = [
        'parent_id' => 'parent id',
        'name' => 'name',
        'description' => 'description',
        'status' => 'status',
        'image' => 'image',
    ];
    // END Need to change every model

    public static function boot()
    {
        parent::boot();

        self::created(function($model) {
            $name = $model[self::$name];

            ActivityLog::create([
                'log_by' => auth()->id(),
                'activity_type' => 'insert',
                'dashboard_activity' => 'created a new '. self::$tableTitle,
                'activity_desc' => 'created the '. self::$tableTitle .' '. $name,
                'activity_date' => date("Y-m-d H:i:s"),
                'db_table' => $model->getTable(),
                'old_value' => '',
                'new_value' => $name,
                'reference' => $model->id
            ]);
        });

        self::updating(function($model) {
            self::$oldModel = $model->fresh();
        });

        self::updated(function($model) {
            $name = $model[self::$name];
            $oldModel = self::$oldModel->toArray();
            foreach ($oldModel as $fieldName => $value) {
                if (in_array($fieldName, self::$unrelatedFields)) {
                    continue;
                }

                $oldValue = $model[$fieldName];
                if ($oldValue != $value) {
                    ActivityLog::create([
                        'log_by' => auth()->id(),
                        'activity_type' => 'update',
                        'dashboard_activity' => 'updated the '. self::$tableTitle .' '. self::$logName[$fieldName],
                        'activity_desc' => 'updated the '. self::$tableTitle .' '. self::$logName[$fieldName] .' of '. $name .' from '. $oldValue .' to '. $value,
                        'activity_date' => date("Y-m-d H:i:s"),
                        'db_table' => $model->getTable(),
                        'old_value' => $oldValue,
                        'new_value' => $value,
                        'reference' => $model->id
                    ]);
                }
            }
        });

        self::deleted(function($model){
            $name = $model[self::$name];
            ActivityLog::create([
                'log_by' => auth()->id(),
                'activity_type' => 'delete',
                'dashboard_activity' => 'deleted a '. self::$tableTitle,
                'activity_desc' => 'deleted the '. self::$tableTitle .' '. $name,
                'activity_date' => date("Y-m-d H:i:s"),
                'db_table' => $model->getTable(),
                'old_value' => '',
                'new_value' => '',
                'reference' => $model->id
            ]);
        });
    }
}
