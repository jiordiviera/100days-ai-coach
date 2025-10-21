<?php

namespace App\Services\Notifications;

use App\Models\User;

class NotificationChannelResolver
{
    /**
     * Resolve notification channels for the given notification type.
     *
     * @return array<int, string>
     */
    public function resolve(User $user, string $type): array
    {
        $profile = $user->profile;

        if (! $profile) {
            return [];
        }

        $preferences = $profile->preferences ?? [];

        if (! data_get($preferences, "notification_types.{$type}", false)) {
            return [];
        }

        $channels = [];

        if (data_get($preferences, 'channels.email', false)) {
            $channels[] = 'mail';
        }

        if (data_get($preferences, 'channels.telegram', false)) {
            $chatId = $user->routeNotificationForTelegram();

            if ($chatId) {
                $channels[] = 'telegram';
            }
        }

        return array_values(array_unique(array_filter($channels)));
    }
}
