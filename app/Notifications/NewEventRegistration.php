<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Ntfy\Message;
use Wijourdil\NtfyNotificationChannel\Channels\NtfyChannel;

class NewEventRegistration extends Notification
{
    public function __construct(private $registration) {}

    public function via($notifiable): array
    {
        return [NtfyChannel::class];
    }

    public function toNtfy(mixed $notifiable): Message
    {
        $session = $this->registration->eventSession;
        $venue = $session->venue ?? null;
        
        // Tính số ghế còn lại
        $remaining = $session->capacity_total - $session->capacity_reserved;
        
        // Format ngày giờ
        $startsAt = \Carbon\Carbon::parse($session->starts_at)->timezone('Asia/Ho_Chi_Minh');
        $dayOfWeek = ucfirst($startsAt->locale('vi')->isoFormat('dddd')); // Thứ Hai, Thứ Ba...
        $dateTime = $startsAt->format('d/m/Y H:i');
        
        // Build chi tiết khách
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
        
        $guestInfo = implode(', ', $guestDetails);
        $attendWith = $this->registration->attend_with_guest ? ' (đi cùng)' : '';

        $message = new Message();
        $message->topic(config('ntfy-notification-channel.topic', 'tiec_tra_shenyun_alerts'));
        $message->title('Đăng ký mới!');
        $message->markdownBody(
            "**{$this->registration->full_name}**{$attendWith} - {$this->registration->total_count} khách\n" .
            "_{$guestInfo}_\n" .
            "{$dayOfWeek}, {$dateTime}\n" .
            "Còn lại: **{$remaining}**/**{$session->capacity_total}** ghế"
        );
        $message->tags(['bell', 'tada']);
        $message->priority(4);

        return $message;
    }
}
