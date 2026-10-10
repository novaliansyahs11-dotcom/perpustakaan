<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mime\MessageConverter;
use Resend\Client;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Mail::extend('resend', function () {
            return new class extends AbstractTransport {
                protected function doSend(SentMessage $message): void
                {
                    $email = MessageConverter::toEmail($message->getOriginalMessage());
                    $resend = new Client(env('RESEND_KEY'));
                    
                    $htmlBody = $email->getHtmlBody();
                    if (is_resource($htmlBody)) {
                        $htmlBody = stream_get_contents($htmlBody);
                    }

                    $textBody = $email->getTextBody();
                    if (is_resource($textBody)) {
                        $textBody = stream_get_contents($textBody);
                    }

                    $resend->emails->send([
                        'from' => $email->getFrom()[0]->getAddress(),
                        'to' => collect($email->getTo())->map(fn ($item) => $item->getAddress())->all(),
                        'subject' => (string) $email->getSubject(),
                        'html' => (string) ($htmlBody ?: $textBody ?: ''),
                    ]);
                }

                public function __toString(): string
                {
                    return 'resend';
                }
            };
        });
    }
}