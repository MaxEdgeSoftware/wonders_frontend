<?php

namespace App\Http\Controllers;

use App\Http\Resources\CategoryProductResource;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $page = $request->query("page") ? $request->query("page") : 1;
        $offset = ($page - 1) * 10;
        $products = Product::with(["Categories", "Gallery"])->where("status", "active")->orderBy("id", "DESC")->skip($offset)->take(10)->get();
        $all = Product::where("status", "active")->count();
        $products = ProductResource::collection($products);
        $response = simplePagination($products, $all, $page, 10);
        return okResponse("Fetched products list", $response);
    }
    public function newProducts(Request $request)
    {
        $page = $request->query("page") ? $request->query("page") : 1;
        $offset = ($page - 1) * 10;
        $products = Product::with(["Categories", "Gallery"])->where("status", "active")->orderBy("id", "DESC")->skip($offset)->take(10)->get();
        $all = Product::where("status", "active")->count();
        $products = ProductResource::collection($products);
        $response = simplePagination($products, $all, $page, 10);
        return okResponse("Fetched products list", $response);
    }
    public function featuredProducts(Request $request)
    {
        $page = $request->query("page") ? $request->query("page") : 1;
        $offset = ($page - 1) * 10;
        $products = Product::with(["Categories", "Gallery"])->where("status", "active")->where("featured", "1")->orderBy("id", "DESC")->skip($offset)->take(10)->get();
        $all = Product::where("status", "active")->count();
        $products = ProductResource::collection($products);
        $response = simplePagination($products, $all, $page, 10);
        return okResponse("Fetched products list", $response);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $product = Product::with(["Categories", "Gallery"])->where("status", "active")->where("slug", $id)->first();
        if(!$product){
            return okResponse("No product found", null);
        }
        $productCategories = ProductCategory::where("product_id", $product->id)->select("category_id")->get();
        $categoriesIDs = $this->getCategoryIDS($productCategories);

        $items = ProductCategory::whereIn('category_id', $categoriesIDs)->where("product_id", "!=", $product->id)->select("product_id")->distinct()->get();

        $response = new ProductResource($product);
        return okResponse("Fetched product", [
            "product" => $response,
            "related" => CategoryProductResource::collection($items)
        ]);
    }
    protected function getCategoryIDS($categories){
        $categoriesIDs = [];
        foreach ($categories as $cate) {
            array_push($categoriesIDs, $cate->category_id);
        }
        return $categoriesIDs;
    }
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
