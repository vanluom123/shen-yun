<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Ntfy\Message;
use Wijourdil\NtfyNotificationChannel\Channels\NtfyChannel;

class NewEventRegistration extends Notification
{
    public function __construct(private $registration)
    {
    }

    public function via($notifiable): array
    {
        return [NtfyChannel::class];
    }

    public function toNtfy(mixed $notifiable): Message
    {
        $session = $this->registration->eventSession;

        if (!$session) {
            throw new \RuntimeException('Event session not found');
        }

        $venueName = $session->venue?->name ?? 'Không rõ địa điểm';

        $remaining = max(0, $session->capacity_total - $session->capacity_reserved);

        $startsAt = \Carbon\Carbon::parse($session->starts_at)
            ->setTimezone('Asia/Ho_Chi_Minh')
            ->locale('vi');

        $dayOfWeek = ucfirst($startsAt->isoFormat('dddd'));
        $dateTime = $startsAt->format('d/m/Y H:i');

        $guestDetails = [];

        if ($this->registration->adult_count > 0) {
            $guestDetails[] = "Khách: {$this->registration->adult_count}";
        }
        if ($this->registration->ntl_count > 0) {
            $guestDetails[] = "NTL: {$this->registration->ntl_count}";
        }
        if ($this->registration->ntl_new_count > 0) {
            $guestDetails[] = "NTL mới: {$this->registration->ntl_new_count}";
        }
        if ($this->registration->child_count > 0) {
            $guestDetails[] = "Trẻ em: {$this->registration->child_count}";
        }

        $guestInfo = !empty($guestDetails)
            ? implode(', ', $guestDetails)
            : 'Không có thông tin khách';

        $attendWith = $this->registration->attend_with_guest ? ' (đi cùng khách)' : '';

        $message = new Message();

        $message->topic(config('ntfy-notification-channel.topic'));
        $message->title("Tiệc trà {$venueName} - Đăng ký mới!");
        $message->icon('https://yeushenyun.com/shen-yun.webp');

        $remainingText = $remaining === 0
            ? "Đã FULL **{$session->capacity_total}** ghế"
            : "Còn lại: **{$remaining}/{$session->capacity_total}** ghế";

        $message->markdownBody(
            "**{$this->registration->full_name}**{$attendWith} - 👥 **{$this->registration->total_count}** khách:\n" .
            "_{$guestInfo}_\n\n" .
            "🗓 **{$dayOfWeek} {$dateTime}**\n" .
            "🎫 {$remainingText}"
        );

        $message->priority(Message::PRIORITY_HIGH);
        $message->tags(['registration', 'event']);

        $message->clickAction(
            url("/admin/registrations")
        );

        return $message;
    }
}
