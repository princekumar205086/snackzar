<?php

namespace App\Notifications\Channels;

use App\Notifications\Messages\InfobipSmsMessage;
use App\Services\Sms\InfobipSmsService;

class InfobipSmsChannel
{
    public function __construct(
        private readonly InfobipSmsService $smsService
    ) {}

    public function send(object $notifiable, object $notification): void
    {
        if (! method_exists($notification, 'toInfobip')) {
            return;
        }

        $recipient = $notifiable->routeNotificationFor('infobip');
        if (! $recipient) {
            return;
        }

        $message = $notification->toInfobip($notifiable);
        if (! $message instanceof InfobipSmsMessage) {
            return;
        }

        $this->smsService->sendMessage(
            $recipient,
            $message->content,
            $message->sender,
            $message->templateId,
        );
    }
}