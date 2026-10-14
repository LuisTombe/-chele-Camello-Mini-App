<?php
namespace App\Infrastructure\Adapter\Mail;

interface MailAdapterInterface {
    public function send(string $to, string $subject, string $body, bool $isHtml = true): bool;
}
