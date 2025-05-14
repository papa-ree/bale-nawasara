<?php

namespace Paparee\BaleNawasara\App\Notifications;

use Carbon\Carbon;
use Illuminate\Notifications\Messages\SlackAttachment;
use Illuminate\Notifications\Messages\SlackMessage;
use Paparee\BaleNawasara\App\Models\NawasaraNotification;
use Spatie\UptimeMonitor\Notifications\Notifications\UptimeCheckRecovered as NotificationsUptimeCheckRecovered;

class UptimeCheckRecovered extends NotificationsUptimeCheckRecovered
{
    public function toSlack($notifiable)
    {
        $slack = (new SlackMessage())
            ->success()
            ->attachment(function (SlackAttachment $attachment) {
                $attachment
                    ->title($this->getMessageText())
                    ->fallback($this->getMessageText())
                    ->footer($this->getLocationDescription())
                    ->timestamp(Carbon::now());
            });

        $database = NawasaraNotification::create([
            'type' => self::class,
            'notifiable_id' => 1, // ID pengguna atau entitas terkait
            'notifiable_type' => 'App\Models\User', // Sesuaikan model pengguna
            'data' => json_encode($slack),
            'read_at' => null,
        ]);

        return $slack;
    }
}
