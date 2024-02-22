<?php
namespace App\Notifications;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
class ResetPasswordRequest extends Notification implements ShouldQueue
{
    use Queueable;
    protected $token;
    protected $newPassword;
    /**
    * Create a new notification instance.
    *
    * @return void
    */
    public function __construct($token,$newPassword)
    {
        $this->token = $token;
        $this->newPassword = $newPassword;
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
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $url = url('reset-password/?token=' . $this->token);
        $password = $this->newPassword; // Lấy giá trị của mật khẩu mới từ thông báo
    
        return (new MailMessage)
            ->line('Bạn nhận được email này vì chúng tôi đã nhận được yêu cầu đặt lại mật khẩu cho tài khoản của bạn.')
            ->line('Mật khẩu mới của bạn là: '.$password); // Thêm dòng này để hiển thị mật khẩu mới
    }
}
