<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Ntfy\Message;
use Wijourdil\NtfyNotificationChannel\Channels\NtfyChannel;

class NewEventRegistration extends Notification
{
    public function __construct(private $registration, private string $type = 'new')
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

        $titleMap = [
            'new' => 'Đăng ký mới!',
            'updated' => 'Cập nhật đăng ký!',
            'cancelled' => 'Hủy đăng ký!',
            'deleted' => 'Xóa đăng ký!',
            'reactivated' => 'Đăng ký lại!',
        ];
        $titleAction = $titleMap[$this->type] ?? 'Hoạt động đăng ký!';
        $message->title("Tiệc trà {$venueName} - {$titleAction}");

        $message->icon('https://yeushenyun.com/shen-yun.webp');

        $remainingText = $remaining === 0
            ? "Đã FULL {$session->capacity_total} ghế"
            : "Còn lại: {$remaining}/{$session->capacity_total} ghế";

        $statusTag = '';
        if ($this->type === 'cancelled') {
            $statusTag = '❌ ';
        }
        if ($this->type === 'deleted') {
            $statusTag = '🗑️ ';
        }
        if ($this->type === 'updated') {
            $statusTag = '⚙️ ';
        }

        $editUrl = config('app.url') . "/admin/registrations/{$this->registration->id}/edit";

        $message->body(
            "{$statusTag}{$this->registration->full_name}{$attendWith}\n" .
            "👥 {$this->registration->total_count} khách:\n".
            "{$guestInfo}\n\n" .
            "🗓 {$dayOfWeek} {$dateTime}\n" .
            "🎫 {$remainingText}"
        );
        
        $message->clickAction($editUrl);
        
        $action = new \Ntfy\Action\View();
        $action->label('Xem chi tiết');
        $action->url($editUrl);
        $message->action($action);

        $message->priority(Message::PRIORITY_HIGH);
        $message->tags([$this->type === 'new' ? 'registration' : $this->type, 'event']);

        return $message;
    }
}
