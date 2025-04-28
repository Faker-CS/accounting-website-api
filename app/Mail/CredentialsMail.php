<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class CredentialsMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $password;

    public function __construct(User $user, string $password)
    {
        $this->user     = $user;
        $this->password = $password;
    }

    public function build()
    {
        return $this->subject('Vos identifiants de connexion')
                    ->view('emails.credentials');
    }
}
