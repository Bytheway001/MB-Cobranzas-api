<?php
namespace App\Controllers;

// UPDATED
use App\Models\Category;
use Core\Response;

class categoriesController extends Controller
{
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
        Response::send(200, $result);
    }
}
