<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PayMoneyNotification extends Notification
{
    use Queueable;
    protected $code_earnmoney;
    /**
     * Create a new notification instance.
     */
    public function __construct($code_earnmoney)
    {
        $this->code_earnmoney = $code_earnmoney;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $code_earnmoney = $this->code_earnmoney;
        return (new MailMessage)
            ->line('Bạn nhận được email này vì chúng tôi đã nhận được yêu cầu nhận mã xác nhận chuyển tiền của bạn.')
            ->line('Mã xác nhận chuyển tiền của bạn là: ' .$code_earnmoney);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
