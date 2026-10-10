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
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Mail::extend('resend', function () {
            return new class extends AbstractTransport {
                protected function doSend(SentMessage $message): void
                {
                    $email = MessageConverter::toEmail($message->getOriginalMessage());
                    $resend = new Client(env('RESEND_KEY'));
                    
                    $resend->emails->send([
                        'from' => $email->getFrom()[0]->getAddress(),
                        'to' => collect($email->getTo())->map(fn ($item) => $item->getAddress())->all(),
                        'subject' => $email->getSubject(),
                        'html' => $email->getHtmlBody() ?: $email->getTextBody(),
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