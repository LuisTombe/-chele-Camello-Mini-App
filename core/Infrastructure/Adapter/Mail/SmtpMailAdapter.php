<?php
namespace App\Infrastructure\Adapter\Mail;

class SmtpMailAdapter implements MailAdapterInterface {
    private string $fromEmail;
    private string $fromName;

    public function __construct(string $fromEmail = 'no-reply@echelecamello.co', string $fromName = 'Échele Camello') {
        $this->fromEmail = $fromEmail;
        $this->fromName = $fromName;
    }

    public function send(string $to, string $subject, string $body, bool $isHtml = true): bool {
        $headers = [];
        $headers[] = 'MIME-Version: 1.0';
        if ($isHtml) {
            $headers[] = 'Content-type: text/html; charset=utf-8';
        } else {
            $headers[] = 'Content-type: text/plain; charset=utf-8';
        }
        $headers[] = sprintf('From: =?UTF-8?B?%s?= <%s>', base64_encode($this->fromName), $this->fromEmail);
        $headers[] = 'X-Mailer: EcheleCamello/1.0';

        // Intento de envío vía mail() estándar de PHP
        return @mail($to, '=?UTF-8?B?' . base64_encode($subject) . '?=', $body, implode("\r\n", $headers));
    }
}
