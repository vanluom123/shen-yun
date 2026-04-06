<?php

namespace App\Services;

use GuzzleHttp\Client as GuzzleClient;
use Ntfy\Message;
use Wijourdil\NtfyNotificationChannel\Services\AbstractSendService;

class CustomNtfySendService extends AbstractSendService
{
    public function send(Message $message): void
    {
        $guzzleClient = new GuzzleClient([
            'verify' => false,
            'timeout' => 10,
        ]);

        $data = $message->getData();

        $guzzleClient->post($this->getServerUrl(), [
            'json' => $data,
        ]);
    }
}
