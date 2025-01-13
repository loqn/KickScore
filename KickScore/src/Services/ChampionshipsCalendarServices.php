<?php

namespace App\Services;

use App\Entity\Championship;
use DateTime;

class ChampionshipsCalendarServices
{
    public function generateCalendarContent(array $championships): string
    {
        $calendar = "BEGIN:VCALENDAR\r\n";
        $calendar .= "VERSION:2.0\r\n";
        $calendar .= "PRODID:-//Your Organization//Championship Calendar//EN\r\n";
        $calendar .= "CALSCALE:GREGORIAN\r\n";
        $calendar .= "METHOD:PUBLISH\r\n";

        foreach ($championships as $championship) {
            $calendar .= $this->createEvent($championship);
        }

        $calendar .= "END:VCALENDAR\r\n";

        return $calendar;
    }

    private function createEvent(Championship $championship): string
    {
        $event = "BEGIN:VEVENT\r\n";
        
        $event .= sprintf("UID:%s@yourorganization.com\r\n", $championship->getId());
        
        $startDate = $championship->getStartDate()->format('Ymd\THis\Z');
        $endDate = $championship->getEndDate()->format('Ymd\THis\Z');
        
        $event .= sprintf("DTSTAMP:%s\r\n", (new DateTime())->format('Ymd\THis\Z'));
        $event .= sprintf("DTSTART:%s\r\n", $startDate);
        $event .= sprintf("DTEND:%s\r\n", $endDate);
        $event .= sprintf("SUMMARY:%s\r\n", $this->escapeString($championship->getName()));
        
        if ($championship->getDescription()) {
            $event .= sprintf("DESCRIPTION:%s\r\n", $this->escapeString($championship->getDescription()));
        }
        
        if ($championship->getOrganizer()) {
            $event .= sprintf("ORGANIZER;CN=%s\r\n", $this->escapeString($championship->getOrganizer()->getName()));
        }
        
        $teams = [];
        foreach ($championship->getTeams() as $team) {
            $teams[] = $team->getName();
        }
        
        if (!empty($teams)) {
            $event .= sprintf("DESCRIPTION:Participating teams: %s\r\n", 
                $this->escapeString(implode(', ', $teams))
            );
        }

        $event .= "END:VEVENT\r\n";
        
        return $event;
    }

    private function escapeString(string $string): string
    {
        return str_replace(
            ['\\', "\r\n", "\n", "\r", ',', ';'],
            ['\\\\', '\n', '\n', '\n', '\,', '\;'],
            $string
        );
    }
}