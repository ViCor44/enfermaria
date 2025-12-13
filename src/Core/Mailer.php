<?php

namespace App\Core;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer {

    private PHPMailer $mail;

    public function __construct() {
        $this->mail = new PHPMailer(true);

        // CONFIGURAÇÃO SMTP – adapta com os teus dados
        $this->mail->isSMTP();
        $this->mail->Host       = 'smtp.gmail.com';  // Ex: Gmail, sendgrid, empresa, etc
        $this->mail->SMTPAuth   = true;
        $this->mail->Username   = 'slide.rocketchat@gmail.com';
        $this->mail->Password   = 'abel jacr oqpd hdit';
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mail->Port       = 587;

        // Remetente padrão
        $this->mail->setFrom('no-reply@sae.pt', 'SAE - Sistema de Apoio à Enfermaria');

        // Charset
        $this->mail->CharSet = 'UTF-8';
    }

    public function send(string $to, string $subject, string $htmlMessage): bool {
        try {
            $this->mail->clearAddresses();
            $this->mail->addAddress($to);

            $this->mail->isHTML(true);
            $this->mail->Subject = $subject;
            $this->mail->Body    = $htmlMessage;

            return $this->mail->send();
        } catch (Exception $e) {
            error_log("Erro ao enviar email: " . $e->getMessage());
            return false;
        }
    }
}
