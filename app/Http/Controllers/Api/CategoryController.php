<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index()
    {
        return Category::all();
    }

    public function store(CategoryRequest $request)
    {
        $request->validated();
        $category = Category::create($request->all());
        $category->refresh();
        // return new CategoryResource($category);
        return $category;
    }

    public function show(Category $category)
    {
        return $category;
    }

    public function update(CategoryRequest $request, Category $category)
    {
        $request->validated();

        $category->update($request->all());
        return $category;
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return response()->noContent();
    }
}
