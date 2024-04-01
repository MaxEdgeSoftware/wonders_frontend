<?php

namespace App\Http\Controllers;

use App\Http\Resources\CategoryResource;
use App\Http\Resources\ShowCategoryResource;
use App\Models\Category;
use App\Models\ProductCategory;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Category::with("products")->get();
        $response = CategoryResource::collection($categories);
        return okResponse("Categories fetched", $response);
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
    public function show(Request $request, string $id)
    {
        $category = Category::where("slug", $id)->first();
        if(!$category){
            return okResponse("Category not found", null);
        }
        $page = $request->query("page") ? $request->query("page") : 1;
        $offset = ($page - 1) * 10;
        $products = ProductCategory::where("category_id", $category->id)->skip($offset)->take(10)->get();
        $all = ProductCategory::where("category_id", $category->id)->count();
        $category->products = $products ? $products : [];
        $response = new ShowCategoryResource($category);
        
        return okResponse("Categories fetched", simplePagination($response, $all, $page, 10));
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
