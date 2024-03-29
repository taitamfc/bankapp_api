<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EarnMoneyNotification extends Notification implements ShouldQueue
{
    use Queueable;
    protected $code;
    /**
     * Create a new notification instance.
     *
    * @return void
     */
    public function __construct($code)
    {
        $this->code = $code;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
    * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
    * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $code = $this->code; 
        return (new MailMessage)
                    ->line('Bạn nhận được email này vì chúng tôi đã nhận được yêu cầu nhận mã xác nhận rút tiền của bạn.')
                    ->line('Mã xác nhận rút tiền của bạn là:'.$code );
    }

}
