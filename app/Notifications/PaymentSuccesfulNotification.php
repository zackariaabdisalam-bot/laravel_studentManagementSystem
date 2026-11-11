<?php

namespace App\Notifications;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentSuccessfulNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Payment $payment
    ) {
        //
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Payment Successful - Student Management System')
            ->greeting('Dear ' . $notifiable->name . ',')
            ->line('Your payment has been successfully received.')
            ->line('Payment Details:')
            ->line('Amount: KSh ' . number_format((float) $this->payment->amount, 2))
            ->line('Payment Method: ' . ucfirst($this->payment->payment_method))
            ->line('Payment Date: ' . $this->payment->payment_date)
            ->line('Transaction Reference: ' . $this->payment->transaction_reference)
            ->line('System Receipt Number: ' . $this->payment->receipt_number)
            ->line(
                'M-Pesa Receipt Number: '
                . ($this->payment->mpesa_receipt_number ?? 'Not available')
            )
            ->line('Thank you for your payment.');
    }
}