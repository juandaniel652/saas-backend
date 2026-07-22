<?php

declare(strict_types=1);

use App\Core\Events\EventDispatcher;
use App\Modules\Appointments\Events\AppointmentCancelled;
use App\Modules\Appointments\Events\AppointmentCreated;
use App\Modules\Audit\Listeners\AuditAppointmentCancelled;
use App\Modules\Audit\Listeners\AuditAppointmentCreated;
use App\Modules\Audit\Listeners\AuditPaymentRegistered;
use App\Modules\Audit\Listeners\AuditSaleCreated;
use App\Modules\Payments\Events\PaymentRegistered;
use App\Modules\Sales\Events\SaleCreated;

return function (EventDispatcher $dispatcher): void {
    $dispatcher->listen(AppointmentCreated::class, AuditAppointmentCreated::class);
    $dispatcher->listen(AppointmentCancelled::class, AuditAppointmentCancelled::class);
    $dispatcher->listen(SaleCreated::class, AuditSaleCreated::class);
    $dispatcher->listen(PaymentRegistered::class, AuditPaymentRegistered::class);
};