<?php

declare(strict_types=1);

namespace App\Modules\Appointments\Services;

use App\Core\Exceptions\AppException;
use App\Core\Exceptions\NotFoundException;
use App\Core\Exceptions\ValidationException;
use App\Core\Auth\AuthenticatedUser;
use App\Core\ValueObjects\TimeRange;
use App\Modules\Appointments\DTO\CreateAppointmentDTO;
use App\Modules\Appointments\Enums\AppointmentStatus;
use App\Modules\Appointments\Policies\AppointmentPolicy;
use App\Modules\Appointments\Repositories\AppointmentRepository;
use App\Modules\Clients\Services\ClientService;
use App\Modules\Employees\Repositories\EmployeeRepository;
use App\Modules\Employees\Services\EmployeeService;
use App\Modules\Schedule\Repositories\ScheduleRepository;
use App\Modules\Services\Services\ServiceCatalogService;
use App\Core\Validation\Validator;
use DateTimeImmutable;

use App\Core\Events\EventDispatcher;
use App\Modules\Appointments\Events\AppointmentCancelled;
use App\Modules\Appointments\Events\AppointmentCreated;

final class AppointmentService
{
    public function __construct(
        private readonly AppointmentRepository $appointments,
        private readonly EmployeeRepository $employeeRepository,
        private readonly EmployeeService $employeeService,
        private readonly ClientService $clientService,
        private readonly ServiceCatalogService $serviceCatalog,
        private readonly ScheduleRepository $scheduleRepository,
        private readonly AppointmentPolicy $policy,
        private readonly EventDispatcher $events,
    ) {
    }

    /** @return array<int, array<string, mixed>> */
    public function listForCompany(int $companyId, ?int $employeeId, ?string $date): array
    {
        return $this->appointments->findByCompany($companyId, $employeeId, $date);
    }

    /** @return array<string, mixed> */
    public function findOrFail(int $id, int $companyId): array
    {
        $appointment = $this->appointments->findByIdAndCompany($id, $companyId);

        if ($appointment === null) {
            throw new NotFoundException('Turno no encontrado');
        }

        return $appointment;
    }

    public function schedule(int $companyId, array $rawData, ?int $userId = null): int
    {
        Validator::make($rawData, [
            'branch_id' => 'required|integer',
            'client_id' => 'required|integer',
            'employee_id' => 'required|integer',
            'service_id' => 'required|integer',
            'starts_at' => 'required|string',
        ])->validateOrFail();

        $dto = CreateAppointmentDTO::fromArray($rawData);

        // 1. Entidades relacionadas existen y pertenecen a la empresa
        $this->clientService->findOrFail($dto->clientId, $companyId);
        $this->employeeService->findOrFail($dto->employeeId, $companyId);
        $service = $this->serviceCatalog->findOrFail($dto->serviceId, $companyId);

        // 2. El empleado presta ese servicio
        if (!$this->employeeRepository->performsService($dto->employeeId, $dto->serviceId)) {
            throw new ValidationException([
                'employee_id' => ['El empleado seleccionado no presta ese servicio'],
            ]);
        }

        // 3. Calcular rango horario segun la duracion del servicio
        $startsAt = new DateTimeImmutable($dto->startsAt);
        $endsAt = $startsAt->modify('+' . (int) $service['duration_minutes'] . ' minutes');
        $range = new TimeRange($startsAt, $endsAt);

        // 4. El horario pedido cae dentro de la disponibilidad del empleado
        $this->assertWithinWorkingHours($dto->employeeId, $range);

        // 5. No se pisa con otro turno del mismo empleado
        if ($this->appointments->hasOverlap($dto->employeeId, $range->start->format('Y-m-d H:i:s'), $range->end->format('Y-m-d H:i:s'))) {
            throw new ValidationException([
                'starts_at' => ['El empleado ya tiene un turno en ese horario'],
            ]);
        }

        $appointmentId = $this->appointments->create(
            companyId: $companyId,
            branchId: $dto->branchId,
            clientId: $dto->clientId,
            employeeId: $dto->employeeId,
            serviceId: $dto->serviceId,
            startsAt: $range->start->format('Y-m-d H:i:s'),
            endsAt: $range->end->format('Y-m-d H:i:s'),
            status: AppointmentStatus::Confirmed->value,
            notes: $dto->notes,
        );

        $this->events->dispatch(new AppointmentCreated(
            appointmentId: $appointmentId,
            companyId: $companyId,
            userId: $userId,
            clientId: $dto->clientId,
            employeeId: $dto->employeeId,
            serviceId: $dto->serviceId,
            startsAt: $range->start->format('Y-m-d H:i:s'),
        ));

        return $appointmentId;
    }

    public function cancel(int $id, int $companyId, AuthenticatedUser $auth): void
    {
        $appointment = $this->findOrFail($id, $companyId);

        if (!$this->policy->canCancel($auth, $appointment)) {
            throw new class ('No tenes permiso para cancelar este turno', 403) extends AppException {
                public function __construct(string $message, int $status)
                {
                    parent::__construct($message, $status);
                }
            };
        }

        $this->appointments->updateStatus($id, AppointmentStatus::Cancelled->value);
        $this->events->dispatch(new AppointmentCancelled(
        appointmentId: $id,
        companyId: $companyId,
        userId: $auth->userId,
        clientId: (int) $appointment['client_id'],
    ));
    
    }

    private function assertWithinWorkingHours(int $employeeId, TimeRange $range): void
    {
        $weekday = (int) $range->start->format('N'); // 1 (lunes) a 7 (domingo)
        $schedule = $this->scheduleRepository->findForEmployeeAndWeekday($employeeId, $weekday);

        if ($schedule === null) {
            throw new ValidationException([
                'starts_at' => ['El empleado no tiene horario de atencion configurado para ese dia'],
            ]);
        }

        $workStart = new DateTimeImmutable($range->start->format('Y-m-d') . ' ' . $schedule['start_time']);
        $workEnd = new DateTimeImmutable($range->start->format('Y-m-d') . ' ' . $schedule['end_time']);

        if ($range->start < $workStart || $range->end > $workEnd) {
            throw new ValidationException([
                'starts_at' => ['El horario solicitado esta fuera del horario de atencion del empleado'],
            ]);
        }
    }
}