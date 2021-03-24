<?php

namespace App\Http\Controllers\Api;

use App\Models\Category;

class CategoryController extends BasicCrudController
{
    private $rules = [
        'name' => 'required|string|max:255',
        'is_active' => 'boolean',
        'description' => ''
    ];

    protected function model()
    {
        return Category::class;
    }

    protected function rulesStore()
    {
        return $this->rules;
    }
}
