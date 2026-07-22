<?php

declare(strict_types=1);

namespace App\Modules\Schedule\DTO;

final class WeeklyScheduleDTO
{
    /** @param array<int, array{weekday: int, start_time: string, end_time: string}> $days */
    public function __construct(public readonly array $days)
    {
    }

    public static function fromArray(array $data): self
    {
        $days = [];

        foreach ($data['days'] ?? [] as $day) {
            $days[] = [
                'weekday' => (int) $day['weekday'],
                'start_time' => (string) $day['start_time'],
                'end_time' => (string) $day['end_time'],
            ];
        }

        return new self($days);
    }
}