<?php
namespace App\Controllers;

class testController extends Controller {
    public function testEmail() {
        $payment = \App\Models\Payment::find([28]);
        $tags=[['email'=>'rafael@megabadvisors.com','name'=>'Rafael Castillo']];
        $mailer = new \App\Libs\Mailer($tags, \Core\View::get_partial('partials', 'payment_created', $payment));
       
        print_r($mailer->mail->send());
    }
}
