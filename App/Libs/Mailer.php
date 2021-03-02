<?php

namespace App\Libs;

use PHPMailer\PHPMailer\PHPMailer;

class Mailer {
    public function __construct($recipients, $body) {
        $this->mail = new PHPMailer();
        $this->mail->SMTPDebug = false;
        $this->mail->isSMTP();
        $this->mail->SMTPAuth = true;
        $this->mail->Host = 'mail.quotiapp.com';
        $this->mail->CharSet = 'UTF-8';
        $this->mail->Port = 587;
        $this->mail->isHTML();
        $this->mail->setFrom('rcastillo@quotiapp.com', 'Rafael');
        $this->mail->Username = 'rcastillo@quotiapp.com';
        $this->mail->Password = 'Silvereye1990';
        /*$this->mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        */                       // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above

        //Recipients
        $this->mail->setFrom('hello@megabrokerslatam.com', 'SIS-COB');
        foreach ($recipients as $recipient) {
            $this->mail->addAddress($recipient['email'], $recipient['name']);
        }
        // Add a recipient

        // Content
    $this->mail->isHTML(true);                                  // Set email format to HTML
    $this->mail->Subject = 'Registro de Cobranza';
        $this->mail->Body = $body;
    }
}
