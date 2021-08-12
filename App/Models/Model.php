<?php
namespace App\Models;

use \ActiveRecord\Model as MainModel;

class Model extends MainModel
{
    public static function list($filters=null, array $includes=[]) {
        $result=[];
        if ($filters) {
            $models = static::all($filters);
        } else {
            $models = static::all();
        }
        
        foreach ($models as $model) {
            $result[]=$model->to_array($includes);
        }

        return $result;
    }
}
