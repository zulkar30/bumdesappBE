<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use NotificationChannels\Fcm\FcmMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class SendNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['fcm'];
    }

    public function toFcm($notifiable): FcmMessage
    {
        $deviceToken = $notifiable->device_token;
        Log::info("Sending notification to: " . $deviceToken);
        if (!$deviceToken) {
            Log::error("Device token is missing for user ID: " . $notifiable->id);
        }
        $message = [
            'title' => 'Test Notification',
            'body' => 'This is a test notification sent from Laravel.'
        ];
        Log::info("Notification payload: ", $message);
        return [
            'to' => $deviceToken,
            'notification' => $message,
        ];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }

}
