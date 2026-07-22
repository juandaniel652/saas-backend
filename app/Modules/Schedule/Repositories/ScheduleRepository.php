<?php

declare(strict_types=1);

namespace App\Modules\Schedule\Repositories;

use App\Core\Database\Connection;

final class ScheduleRepository
{
    public function __construct(private readonly Connection $connection)
    {
    }

    /** @return array<int, array<string, mixed>> */
    public function findByEmployee(int $employeeId): array
    {
        $stmt = $this->connection->pdo()->prepare(
            'SELECT * FROM employee_schedules WHERE employee_id = :employee_id ORDER BY weekday, start_time',
        );
        $stmt->execute(['employee_id' => $employeeId]);

        return $stmt->fetchAll();
    }

    /** @param array<int, array{weekday: int, start_time: string, end_time: string}> $days */
    public function replaceForEmployee(int $employeeId, array $days): void
    {
        $pdo = $this->connection->pdo();

        $delete = $pdo->prepare('DELETE FROM employee_schedules WHERE employee_id = :employee_id');
        $delete->execute(['employee_id' => $employeeId]);

        $insert = $pdo->prepare(
            'INSERT INTO employee_schedules (employee_id, weekday, start_time, end_time)
             VALUES (:employee_id, :weekday, :start_time, :end_time)',
        );

        foreach ($days as $day) {
            $insert->execute([
                'employee_id' => $employeeId,
                'weekday' => $day['weekday'],
                'start_time' => $day['start_time'],
                'end_time' => $day['end_time'],
            ]);
        }
    }

    /** @return array<string, mixed>|null */
    public function findForEmployeeAndWeekday(int $employeeId, int $weekday): ?array
    {
        $stmt = $this->connection->pdo()->prepare(
            'SELECT * FROM employee_schedules WHERE employee_id = :employee_id AND weekday = :weekday LIMIT 1',
        );
        $stmt->execute(['employee_id' => $employeeId, 'weekday' => $weekday]);

        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }
}