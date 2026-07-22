<?php

declare(strict_types=1);

namespace App\Core\Mail;

use App\Core\Config\Config;
use App\Core\Logging\Logger;
use PHPMailer\PHPMailer\PHPMailer;
use RuntimeException;

final class Mailer
{
    public function __construct(
        private readonly Config $config,
        private readonly Logger $logger,
    ) {
    }

    public function send(string $to, string $subject, string $body): void
    {
        $driver = (string) $this->config->get('mail.driver', 'log');

        if ($driver === 'log') {
            $this->logger->info('Email (driver=log, no enviado realmente)', [
                'to' => $to,
                'subject' => $subject,
                'body' => $body,
            ]);

            return;
        }

        $this->sendViaSmtp($to, $subject, $body);
    }

    private function sendViaSmtp(string $to, string $subject, string $body): void
    {
        $mailer = new PHPMailer(true);

        try {
            $mailer->isSMTP();
            $mailer->Host = (string) $this->config->get('mail.host');
            $mailer->Port = (int) $this->config->get('mail.port');
            $mailer->SMTPAuth = true;
            $mailer->Username = (string) $this->config->get('mail.username');
            $mailer->Password = (string) $this->config->get('mail.password');
            $mailer->SMTPSecure = (string) $this->config->get('mail.encryption');

            $mailer->setFrom(
                (string) $this->config->get('mail.from_address'),
                (string) $this->config->get('mail.from_name'),
            );
            $mailer->addAddress($to);
            $mailer->Subject = $subject;
            $mailer->isHTML(true);
            $mailer->Body = $body;

            $mailer->send();
        } catch (\Throwable $e) {
            $this->logger->error('Fallo el envio de email', ['to' => $to, 'error' => $e->getMessage()]);

            throw new RuntimeException('No se pudo enviar el email: ' . $e->getMessage(), previous: $e);
        }
    }
}