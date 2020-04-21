<?php 
namespace App\Controllers;
use \Core\View;
use \App\Models\User;
class homeController extends Controller{
	public function index(){
		$this->response(['errors'=>false,'data'=>'Welcome to MB-Cobranzas']);
	}
}


 ?>