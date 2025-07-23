<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Kreait\Firebase\Factory;
use App\Http\Controllers\Controller;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class NotificationController extends Controller
{
    public function sendNotification(Request $request)
    {
        $deviceToken = $request->device_token;
        $firebase_credential = (new Factory)
            ->withServiceAccount(config('firebase.projects.app.credentials'));

        $messaging = $firebase_credential->createMessaging();

        $message = CloudMessage::withTarget('token', $deviceToken)
            ->withNotification(Notification::create('First Firebase Notification', 'This is our First Notification Through Laravel'));

        try {
            $messaging->send($message);
            return response()->json(['message' => 'Notification Sent Successfully']);
        } catch (\Kreait\Firebase\Exception\Messaging\NotFound $e) {
            return response()->json(['error' => 'Notification entity not found: ' . $e->getMessage()], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }

        // Return success response
        return response()->json(['message' => 'Notification Sent Successfully']);
    }
}
