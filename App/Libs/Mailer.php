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
        $this->mail->isHTML(true);
        $this->mail->setFrom('siscob@megabrokerslatam.com', 'SIS-COB');
        $this->mail->Username = 'siscob@megabrokerslatam.com';
        $this->mail->Password = 'Silvereye1990';
        foreach ($recipients as $recipient) {
            $this->mail->addAddress($recipient['email'], $recipient['name']);
        }
        // Set email format to HTML
        $this->mail->Subject = 'Registro de Cobranza';
        $this->mail->Body = $body;
    }
}
