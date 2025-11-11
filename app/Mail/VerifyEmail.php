<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VerifyEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $verificationUrl;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->verificationUrl = env('APP_URL_FRONTEND').'/verify?token=' . $user->email_verification_token.'&email=' . urlencode($user->email);
    }

    public function build()
    {
        return $this->subject('Verify Your Email Address')
            ->view('emails.verify_email')
            ->with([
                'user' => $this->user,
                'verificationUrl' => $this->verificationUrl,
            ]);
    }
}

