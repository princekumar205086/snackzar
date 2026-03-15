<?php

namespace App\Notifications\Messages;

class InfobipSmsMessage
{
    public function __construct(
        public readonly string $content,
        public readonly ?string $sender = null,
        public readonly ?string $templateId = null,
    ) {}
}