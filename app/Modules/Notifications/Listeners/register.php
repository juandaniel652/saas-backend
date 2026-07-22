<?php

declare(strict_types=1);

use App\Core\Events\EventDispatcher;
use App\Modules\Appointments\Events\AppointmentCancelled;
use App\Modules\Appointments\Events\AppointmentCreated;
use App\Modules\Notifications\Listeners\SendAppointmentCancellationEmail;
use App\Modules\Notifications\Listeners\SendAppointmentConfirmationEmail;

return function (EventDispatcher $dispatcher): void {
    $dispatcher->listen(AppointmentCreated::class, SendAppointmentConfirmationEmail::class);
    $dispatcher->listen(AppointmentCancelled::class, SendAppointmentCancellationEmail::class);
};