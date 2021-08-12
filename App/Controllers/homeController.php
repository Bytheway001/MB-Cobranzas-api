<?php
namespace App\Controllers;

use Core\Request;
use Core\Response;
use App\Models\Account;
use App\Models\Company;
use App\Models\Expense;
use App\Models\Notification;
use App\Models\Income;
use App\Models\Check;
use App\Models\Category;
use App\Models\User;
use App\Models\Plan;

class homeController extends Controller
{
    public function index() {
        Response::send(200, "WELCOME TO MB-COBRANZAS");
    }

    public function listAccounts() {
        $accounts =Account::list(['order'=>'name']);
        Response::send(200, $accounts);
    }

    public function listChecks() {
        $req = Request::instance();
        if (!empty($req->params->status) && $req->params->status == 'Collected') {
            $checks = Check::list(['conditions'=>['status != ?', 'Abonado en cuenta']], ['include'=>'client']);
        } else {
            $checks = Check::list(null, ['include'=>'client']);
        }
       
        Response::send(200, $checks);
    }

    public function listCompanies() {
        $companies = Company::list(null, ['include'=>'plans']);
        Response::send(200, $companies);
    }

    public function listPlans($company_id) {
        $plans = Plan::list(['conditions'=>['company_id = ?',$company_id]]);
        Response::send(200, $plans);
    }

    public function getNotifications() {
        $notifications = Notification::list(['conditions'=>['user_id = ?',$this->current_id]]);
        Response::send(200, $notifications);
    }

    public function auth() {
        $user = User::find_by_email($_GET['id']);
        if ($user) {
            $response = $user->to_array(['include'=>'account']);
            Response::send(200, $response);
        } else {
            Response::crash(404, "User not found");
        }
    }

    public function routeNotFound() {
        $this->error('Route Not Found', 404);
    }
}
