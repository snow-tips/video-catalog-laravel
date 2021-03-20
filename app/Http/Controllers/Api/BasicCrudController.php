<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;

abstract class BasicCrudController extends Controller
{
    protected abstract function model();

    public function index()
    {
        return $this->model()::all();
    }

    // public function store(FormRequest $request)
    // {
    //     $request->validated();
    //     $category = Category::create($request->all());
    //     $category->refresh();
    //     return $category;
    // }

    // public function show(Category $category)
    // {
    //     return $category;
    // }

    // public function update(FormRequest $request, Category $category)
    // {
    //     $request->validated();

    //     $category->update($request->all());
    //     return $category;
    // }

    // public function destroy(Category $category)
    // {
    //     $category->delete();
    //     return response()->noContent();
    // }
}
