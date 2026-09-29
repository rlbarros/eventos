<?php

namespace Tests\Unit;

use App\Models\Event;
use App\Models\EventParticipantAllocation;
use App\Models\EventSite;
use App\Models\EventSiteRoom;
use App\Models\Person;
use App\Services\Messaging\IcsBuilder;
use App\Services\Messaging\LodgingNotice;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class IcsBuilderTest extends TestCase
{
    private function notice(): LodgingNotice
    {
        $site = new EventSite(['name' => 'Chácara, Bela Vista', 'address' => 'Rua A', 'number' => '10']);
        $site->setRelation('city', null)->setRelation('state', null);
        $event = new Event(['name' => 'Congresso', 'start_date' => '2026-10-10', 'end_date' => '2026-10-12']);
        $event->setRelation('event_site', $site);
        $room = new EventSiteRoom(['name' => 'Quarto 3']);
        $room->setRelation('event_site_room_type', null);
        $person = new Person(['name' => 'João']);

        $allocation = new EventParticipantAllocation(['event_id' => 1, 'event_site_room_id' => 3]);
        $allocation->id = 7;
        $allocation->setRelation('event', $event)->setRelation('person', $person)->setRelation('event_site_room', $room);
        $allocation->setRelation('event_site_room_type', null);

        return new LodgingNotice($allocation);
    }

    public function test_gera_ics_de_dia_inteiro_com_saida_exclusiva(): void
    {
        $ics = IcsBuilder::lodging($this->notice(), Carbon::parse('2026-09-29 12:00:00', 'UTC'));

        $this->assertStringContainsString("BEGIN:VCALENDAR\r\n", $ics);
        $this->assertStringContainsString('DTSTART;VALUE=DATE:20261010', $ics);
        $this->assertStringContainsString('DTEND;VALUE=DATE:20261013', $ics);
        $this->assertStringContainsString('UID:EV1-7@eventos.ieabrasil', $ics);
        $this->assertStringContainsString('DTSTAMP:20260929T120000Z', $ics);
        $this->assertStringEndsWith("END:VCALENDAR\r\n", $ics);
    }

    public function test_escapa_virgulas_e_pronto_so_com_quarto(): void
    {
        $notice = $this->notice();
        $this->assertTrue($notice->isReady());
        $this->assertStringContainsString('Chácara\, Bela Vista', IcsBuilder::lodging($notice));
        $this->assertStringContainsString('Quarto: Quarto 3', $notice->whatsappText());
    }
}
