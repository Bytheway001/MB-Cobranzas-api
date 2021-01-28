<?php

namespace App\Controllers;

use App\Models\Category;

class categoriesController extends Controller
{
    public function list()
    {
        $result = [];
        $categories = Category::all();
        foreach ($categories as $category) {
            $r = $category->to_array();
            $r['parent'] = $category->parent ? $category->parent->name : null;
            $result[] = $r;
        }
        $this->response(['errors'=>false, 'data'=>$result]);
    }

    public function create()
    {
        $category = new Category($this->payload);
        if ($category->save()) {
            $this->response(['errors'=>false, 'data'=>'Categoria creada con exito']);
        } else {
            $this->response(['errors'=>true, 'data'=>'No se pudo crear la categoria']);
        }
    }

    public function update($id)
    {
        $category = Category::find([$id]);
        if ($this->payload['parent_id'] === '') {
            $this->payload['parent_id'] = null;
        }
        if ($category->update_attributes($this->payload)) {
            $this->response(['errors'=>false, 'data'=>'Categoria Modificada con exito']);
        } else {
            $this->response(['errors'=>true, 'data'=>'No se pudo Modificar']);
        }
    }

    public function delete()
    {
    }
}
