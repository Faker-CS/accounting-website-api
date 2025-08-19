<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class SendCompanyFileMail extends Mailable
{
    use Queueable, SerializesModels;

    public $subject;
    public $filePath;
    public $fileName;
    public $data;

    /**
     * Create a new message instance.
     */
    public function __construct($filePath, $fileName, $subject = 'Document Delivery - MoneyTeers', $data = [])
    {
        $this->filePath = $filePath;
        $this->fileName = $fileName;
        $this->subject = $subject;
        $this->data = $data;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.send_company_file',
            with: [
                'data' => $this->data,
                'fileName' => $this->fileName,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $fullPath = Storage::disk('public')->path($this->filePath);
        
        return [
            Attachment::fromPath($fullPath)
                ->as($this->fileName)
                ->withMime('application/octet-stream'),
        ];
    }
} 