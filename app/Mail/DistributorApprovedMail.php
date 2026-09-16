<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DistributorApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $namaPerusahaan,
        public string $namaPic,
        public string $username,
        public string $tempPassword,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Akun Distributor Super HD Kliner Kamu Sudah Aktif',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.distributor-approved',
        );
    }
}
