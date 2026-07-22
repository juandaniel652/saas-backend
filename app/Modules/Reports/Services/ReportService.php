<?php

declare(strict_types=1);

namespace App\Modules\Reports\Services;

use App\Core\Exceptions\ValidationException;
use App\Modules\Reports\Repositories\ReportRepository;

final class ReportService
{
    public function __construct(private readonly ReportRepository $reports)
    {
    }

    /** @return array<string, mixed> */
    public function salesSummary(int $companyId, ?string $from, ?string $to): array
    {
        [$from, $to] = $this->resolveRange($from, $to);

        return $this->reports->salesSummary($companyId, $from, $to);
    }

    /** @return array<int, array<string, mixed>> */
    public function appointmentsSummary(int $companyId, ?string $from, ?string $to): array
    {
        [$from, $to] = $this->resolveRange($from, $to);

        return $this->reports->appointmentsByStatus($companyId, $from, $to);
    }

    /** @return array<int, array<string, mixed>> */
    public function topServices(int $companyId, ?string $from, ?string $to, int $limit): array
    {
        [$from, $to] = $this->resolveRange($from, $to);

        return $this->reports->topServices($companyId, $from, $to, $limit);
    }

    /** @return array{0: string, 1: string} */
    private function resolveRange(?string $from, ?string $to): array
    {
        $from ??= date('Y-m-d', strtotime('-30 days'));
        $to ??= date('Y-m-d');

        if ($from > $to) {
            throw new ValidationException(['from' => ['La fecha "from" no puede ser posterior a "to"']]);
        }

        return [$from, $to];
    }
}