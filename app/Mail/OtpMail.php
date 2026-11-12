<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public $otp;
    public $action;

    public function __construct($otp, $action = 'التحقق')
    {
        $this->otp = $otp;
        $this->action = $action;
    }

    public function build()
    {
        return $this->subject("رمز التحقق - {$this->action}")
            ->view('emails.otp')
            ->with([
                'otp' => $this->otp,
                'action' => $this->action
            ]);
    }
}
