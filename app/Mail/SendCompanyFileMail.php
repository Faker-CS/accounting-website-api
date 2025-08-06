<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Storage;

class SendCompanyFileMail extends Mailable
{
    use Queueable, SerializesModels;

    public $filePath;
    public $fileName;

    public function __construct($filePath, $fileName)
    {
        $this->filePath = $filePath;
        $this->fileName = $fileName;
    }

    public function build()
    {
        $fullPath = Storage::disk('public')->path($this->filePath);
        return $this->subject('File from Company')
            ->view('emails.send_company_file')
            ->attach($fullPath, [
                'as' => $this->fileName,
            ]);
    }
} 