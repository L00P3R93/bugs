<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\Notification;

class AdminNotificationRouter
{
    /**
     * Get all admins and super-admins who should receive notifications.
     */
    public static function getAdmins(): Collection
    {
        return User::query()
            ->role(['Admin', 'Super Admin'])
            ->where('status', 'active')
            ->get();
    }

    /**
     * Get admins who prefer a specific notification type on a channel.
     */
    public static function getAdminsForNotification(string $type, string $channel = 'email'): Collection
    {
        return self::getAdmins()->filter(
            fn (User $admin) => $admin->prefersNotification($type, $channel)
        );
    }

    /**
     * Notify all admins with a notification.
     */
    public static function notifyAdmins(Notification $notification): void
    {
        self::getAdmins()->each->notify($notification);
    }

    /**
     * Notify specific users with a notification.
     */
    public static function notifyUsers($users, Notification $notification): void
    {
        if ($users instanceof Collection) {
            $users->each->notify($notification);
        } elseif ($users instanceof User) {
            $users->notify($notification);
        }
    }
}
