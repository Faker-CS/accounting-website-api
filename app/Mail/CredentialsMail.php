<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CredentialsMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $password;

    /**
     * Create a new message instance.
     *
     * @param  $user  The user model instance
     * @param  string $password The plain password to send
     */
    public function __construct($user, $password)
    {
        $this->user = $user;
        $this->password = $password;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Your Account Credentials')
            ->view('emails.usercreated')
            ->with([
                'data' => [
                    'email' => $this->user->email,
                    'password' => $this->password,
                    'name' => $this->user->name ?? '',
                ]
            ]);
    }
}