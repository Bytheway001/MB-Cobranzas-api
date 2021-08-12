<?php

namespace App\Libs;

use PHPMailer\PHPMailer\PHPMailer;

class Mailer
{
    public function __construct($recipients, $body) {
        $this->mail = new PHPMailer();
        $this->mail->isSMTP();
        $this->mail->SMTPDebug = false;
        $this->mail->SMTPAuth = true;
        $this->SMTPSecure = "tls";
        $this->mail->Host = 'mail.quotiapp.com';
        $this->DKIM_domain = 'quotiapp.com';
        $this->DKIM_private = realpath('./../../../../.ssh/dkim');
        $this->DKIM_selector = 'dkim';
        $this->mail->CharSet = 'UTF-8';
        $this->mail->AuthType='LOGIN';
        $this->mail->Username = 'siscob@megabrokerslatam.com';
        $this->mail->Password = 'Silvereye1990';
        $this->mail->isHTML(true);
        $this->mail->setFrom('siscob@megabrokerslatam.com', 'SIS-COB');
       
        foreach ($recipients as $recipient) {
            $this->mail->addAddress($recipient['email'], $recipient['name']);
        }
        // Set email format to HTML
        $this->mail->Subject = 'Registro de Cobranza';
        $this->mail->Body = $body;
    }
}
