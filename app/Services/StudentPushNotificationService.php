<?php

namespace App\Services;

use App\Models\StudentNotification;
use App\Models\StudentPushToken;
use App\Models\StudentSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class StudentPushNotificationService
{
    /**
     * Send after the HTTP response has been prepared. A push-service outage
     * must never delay or fail the existing student activity request.
     */
    public function queue(StudentNotification $notification): void
    {
        $notificationId = $notification->id;

        app()->terminating(function () use ($notificationId) {
            $freshNotification = StudentNotification::find($notificationId);
            if ($freshNotification) {
                $this->send($freshNotification);
            }
        });
    }

    public function send(StudentNotification $notification): void
    {
        $settings = StudentSetting::where('student_id', $notification->student_id)->first();
        if ($settings && ! $settings->notifications_enabled) {
            return;
        }

        $tokens = StudentPushToken::where('student_id', $notification->student_id)->get();
        foreach ($tokens as $token) {
            $this->sendToToken($token, $notification);
        }
    }

    private function sendToToken(StudentPushToken $token, StudentNotification $notification): void
    {
        try {
            $response = Http::timeout(5)->post('https://exp.host/--/api/v2/push/send', [
                'to' => $token->expo_push_token,
                'title' => $notification->title,
                'body' => $notification->message,
                'sound' => 'default',
                'channelId' => 'default',
                'data' => [
                    'notification_id' => $notification->id,
                    'type' => $notification->type,
                    'action_url' => $notification->action_url,
                ],
            ]);

            $error = data_get($response->json(), 'data.0.details.error');
            if ($error === 'DeviceNotRegistered') {
                $token->delete();
                return;
            }

            if ($response->failed() || $error) {
                Log::warning('Expo push notification was not accepted.', [
                    'notification_id' => $notification->id,
                    'student_id' => $notification->student_id,
                    'status' => $response->status(),
                    'error' => $error,
                ]);
            }
        } catch (\Throwable $exception) {
            Log::warning('Expo push notification failed without affecting student activity.', [
                'notification_id' => $notification->id,
                'student_id' => $notification->student_id,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
