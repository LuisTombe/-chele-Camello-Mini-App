<?php
namespace App\Infrastructure\Adapter\Mail;

class LocalFileMailAdapter implements MailAdapterInterface {
    private string $logPath;

    public function __construct(?string $logPath = null) {
        $this->logPath = $logPath ?? (__DIR__ . '/../../../../assets/logs/mail_outbox.log');
        $dir = dirname($this->logPath);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
    }

    public function send(string $to, string $subject, string $body, bool $isHtml = true): bool {
        $entry = sprintf(
            "[%s] TO: %s | SUBJECT: %s | FORMAT: %s\nBODY:\n%s\n%s\n",
            date('Y-m-d H:i:s'),
            $to,
            $subject,
            $isHtml ? 'HTML' : 'TEXT',
            $body,
            str_repeat('-', 60)
        );
        return (bool)@file_put_contents($this->logPath, $entry, FILE_APPEND);
    }
}
