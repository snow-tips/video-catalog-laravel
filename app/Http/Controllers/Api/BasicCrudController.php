<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

abstract class BasicCrudController extends Controller
{
    protected abstract function model();
    protected abstract function rulesStore();

    public function index()
    {
        return $this->model()::all();
    }

    public function store(Request $request)
    {
        $validatedData = $this->validate($request, $this->rulesStore());
        $model = $this->model()::create($validatedData);
        $model->refresh();
        return $model;
    }

    public function show($id)
    {
        return $this->findOrFail($id);
    }

    protected function findOrFail($id)
    {
        $model = $this->model();
        $keyName = (new $model)->getRouteKeyName();
        return $this->model()::where($keyName, $id)->firstOrFail();
    }

    public function update(Request $request, $id)
    {
        $validatedData = $this->validate($request, $this->rulesStore());
        $model = $this->findOrFail($id);
        $model->update($validatedData);
        $model->refresh();
        return $model;
    }

    public function destroy($id)
    {
        $model = $this->findOrFail($id);
        $model->delete();
        return response()->noContent();
    }
}
