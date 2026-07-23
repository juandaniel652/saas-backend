<?php

declare(strict_types=1);

namespace App\Modules\Settings\Services;

use App\Modules\Settings\Repositories\SettingsRepository;

final class SettingsService
{
    /** Valores por defecto: nunca se persisten hasta que la empresa los pisa explicitamente. */
    private const DEFAULTS = [
        'business_name' => '',
        'business_timezone' => 'America/Argentina/Buenos_Aires',
        'business_currency' => 'ARS',
        'appointment_buffer_minutes' => '0',
    ];

    public function __construct(private readonly SettingsRepository $settings)
    {
    }

    /** @return array<string, string> */
    public function allForCompany(int $companyId): array
    {
        return array_merge(self::DEFAULTS, $this->settings->allForCompany($companyId));
    }

    public function get(int $companyId, string $key): string
    {
        return $this->settings->get($companyId, $key) ?? (self::DEFAULTS[$key] ?? '');
    }

    public function getInt(int $companyId, string $key): int
    {
        return (int) $this->get($companyId, $key);
    }

    /** @param array<string, string> $values */
    public function update(int $companyId, array $values): void
    {
        foreach ($values as $key => $value) {
            $this->settings->set($companyId, $key, (string) $value);
        }
    }
}