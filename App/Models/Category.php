<?php

namespace App\Models;

class Category extends \ActiveRecord\Model
{
    public static $belongs_to = [['parent', 'class_name'=>'Category', 'foreign_key'=>'parent_id']];
    public static $has_many = [['children', 'class_name'=>'Category', 'foreign_key'=>'parent_id']];
}
