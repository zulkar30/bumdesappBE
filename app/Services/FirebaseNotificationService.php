<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Illuminate\Support\Facades\Log;

class FirebaseNotificationService
{
    protected $messaging;

    public function __construct()
    {
        $firebase_credential = (new Factory)
            ->withServiceAccount(config('firebase.projects.app.credentials'));

        $this->messaging = $firebase_credential->createMessaging();
    }

    public function sendNotification($deviceToken, $title, $body)
    {
        $message = CloudMessage::withTarget('token', $deviceToken)
            ->withNotification(Notification::create($title, $body));

        try {
            $this->messaging->send($message);
            return true;
        } catch (\Kreait\Firebase\Exception\Messaging\NotFound $e) {
            \Log::error('Notification entity not found: ' . $e->getMessage());
            return false;
        } catch (\Exception $e) {
            \Log::error('Firebase Notification Error: ' . $e->getMessage());
            return false;
        }
    }
}
