<?php

declare(strict_types=1);

namespace App\Modules\Appointments\Policies;

use App\Core\Auth\AuthenticatedUser;

final class AppointmentPolicy
{
    /** @param array<string, mixed> $appointment */
    public function canCancel(AuthenticatedUser $auth, array $appointment): bool
    {
        if ((int) $appointment['company_id'] !== $auth->companyId) {
            return false;
        }

        return $auth->hasPermission('appointments.cancel');
    }
}