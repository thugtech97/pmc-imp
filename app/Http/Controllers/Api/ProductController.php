<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ProductCategoryResource;
use App\Http\Resources\ProductReviewResource;
use App\Http\Requests\ProductReviewRequest;
use App\Models\Ecommerce\Product;
use App\Models\Ecommerce\ProductCategory;
use App\Models\Ecommerce\ProductReview;

class ProductController extends Controller
{
    public function getList(Request $request)
    {
        return new ProductResource(Product::with('category')->get());
    }

    public function getCategories()
    {
        return new ProductCategoryResource(ProductCategory::all());
    }

    public function getReviews($id)
    {
        return ProductReviewResource::collection(
            ProductReview::with(['user'])
            ->where('product_id', $id)
            ->orderBy('created_at', 'desc')
            ->get()
        );
    }

    public function submitReview(ProductReviewRequest $request)
    {
        $data = [
            "product_id" => $request->productId,
            "user_id" => $request->userId,
            "rating" => $request->rating,
            "comment" => $request->comment
        ];

        $review = ProductReview::create($data);
        
        return new ProductReviewResource($review);
    }

    public function getProducts(Request $request)
    {
        $searchTerm = $request->input('title', '');
        $products = Product::where(function($query) use ($searchTerm) {
            $query->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('code', 'like', "%{$searchTerm}%");
        })
        ->select('id', 'name', 'code', 'oem', 'uom', 'stock_type', 'inv_code', 'last_po_ref')
        ->paginate(10);
        
        return response()->json([
            'items' => $products->items(),
            'total_count' => $products->total()
        ]);
    }
}
