<?php

namespace App\Mail;

use App\Services\Messaging\IcsBuilder;
use App\Services\Messaging\LodgingNotice;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Aviso de hospedagem por e-mail: texto/HTML, marcação schema.org
 * (LodgingReservation) e o .ics em anexo.
 */
class LodgingNoticeMail extends Mailable
{
    public function __construct(public readonly LodgingNotice $notice) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Hospedagem confirmada - ' . $this->notice->eventName());
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.lodging-notice',
            text: 'mail.lodging-notice-text',
            with: ['notice' => $this->notice, 'schema' => $this->schema()],
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => IcsBuilder::lodging($this->notice), 'hospedagem.ics')
                ->withMime('text/calendar; charset=UTF-8; method=PUBLISH'),
        ];
    }

    /** JSON-LD schema.org/LodgingReservation. */
    public function schema(): array
    {
        $n = $this->notice;

        return [
            '@context' => 'http://schema.org',
            '@type' => 'LodgingReservation',
            'reservationNumber' => $n->reservationNumber(),
            'reservationStatus' => 'http://schema.org/Confirmed',
            'underName' => ['@type' => 'Person', 'name' => $n->personName()],
            'reservationFor' => [
                '@type' => 'LodgingBusiness',
                'name' => $n->siteName(),
                'address' => [
                    '@type' => 'PostalAddress',
                    'streetAddress' => $n->street(),
                    'addressLocality' => $n->city(),
                    'addressRegion' => $n->state(),
                    'postalCode' => $n->zipCode(),
                    'addressCountry' => 'BR',
                ],
            ],
            'checkinDate' => $n->checkIn()->format('Y-m-d\TH:i:s'),
            'checkoutDate' => $n->checkOut()->format('Y-m-d\TH:i:s'),
            'lodgingUnitDescription' => trim($n->roomName() . ' ' . $n->roomTypeName()),
        ];
    }
}
