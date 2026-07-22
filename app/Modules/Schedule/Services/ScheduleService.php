<?php

declare(strict_types=1);

namespace App\Modules\Schedule\Services;

use App\Core\Exceptions\ValidationException;
use App\Modules\Employees\Services\EmployeeService;
use App\Modules\Schedule\DTO\WeeklyScheduleDTO;
use App\Modules\Schedule\Repositories\ScheduleRepository;

final class ScheduleService
{
    public function __construct(
        private readonly ScheduleRepository $schedules,
        private readonly EmployeeService $employeeService,
    ) {
    }

    /** @return array<int, array<string, mixed>> */
    public function forEmployee(int $employeeId, int $companyId): array
    {
        $this->employeeService->findOrFail($employeeId, $companyId);

        return $this->schedules->findByEmployee($employeeId);
    }

    public function setWeeklySchedule(int $employeeId, int $companyId, array $rawData): void
    {
        $this->employeeService->findOrFail($employeeId, $companyId);

        $dto = WeeklyScheduleDTO::fromArray($rawData);
        $this->validateDays($dto->days);

        $this->schedules->replaceForEmployee($employeeId, $dto->days);
    }

    /** @param array<int, array{weekday: int, start_time: string, end_time: string}> $days */
    private function validateDays(array $days): void
    {
        $errors = [];

        foreach ($days as $index => $day) {
            if ($day['weekday'] < 1 || $day['weekday'] > 7) {
                $errors["days.{$index}.weekday"][] = 'El dia de la semana debe estar entre 1 (lunes) y 7 (domingo)';
            }

            if ($day['start_time'] >= $day['end_time']) {
                $errors["days.{$index}"][] = 'La hora de inicio debe ser anterior a la hora de fin';
            }
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }
    }
}