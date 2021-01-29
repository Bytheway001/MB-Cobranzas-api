<?php

namespace App\Controllers;

use App\Models\Category;

class categoriesController extends Controller {
    public function list() {
        $result = [
            'egresos' => [],
            'ingresos'=> [],
        ];
        $categories = Category::all(['conditions'=>['parent_id is null']]);
        foreach ($categories as $category) {
            if ($category->type == 'MainI') {
                $result['ingresos'][$category->name] = [];
                foreach ($category->children as $c) {
                    $result['ingresos'][$category->name][] = $c->to_array();
                }
            } else {
                $result['egresos'][$category->name] = [];
                foreach ($category->children as $c) {
                    $result['egresos'][$category->name][] = $c->to_array();
                }
            }
        }
        $this->response(['errors'=>false, 'data'=>$result]);
    }

    public function create() {
        $category = new Category($this->payload);
        if ($category->save()) {
            $this->response(['errors'=>false, 'data'=>'Categoria creada con exito']);
        } else {
            $this->response(['errors'=>true, 'data'=>'No se pudo crear la categoria']);
        }
    }

    public function update($id) {
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

    public function getTree() {
        $result = [
            'egresos' => [],
            'ingresos'=> [],
        ];
        $categories = Category::all(['conditions'=>['parent_id is null']]);
        foreach ($categories as $category) {
            if ($category->type == 'MainI') {
                $result['ingresos'][$category->name] = [];
                foreach ($category->children as $c) {
                    $result['ingresos'][$category->name][] = $c->to_array();
                }
            } else {
                $result['egresos'][$category->name] = [];
                foreach ($category->children as $c) {
                    $result['egresos'][$category->name][] = $c->to_array();
                }
            }
        }
        $this->response(['errors'=>false, 'data'=>$result]);
    }

    public function delete() {
    }
}
