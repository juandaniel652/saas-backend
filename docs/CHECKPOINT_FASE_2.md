# Checkpoint Fase 2 — Núcleo de negocio (MVP Turnos)

Fecha: 22/07/2026

## Estado del proyecto

La Fase 2 del backend SaaS de gestión de turnos fue completada.

Se incorporó el núcleo funcional del sistema permitiendo:

- Gestión de clientes.
- Gestión de empleados.
- Catálogo de servicios.
- Asociación empleados-servicios.
- Configuración de horarios laborales.
- Creación y validación de turnos.
- Cancelación de turnos.
- Validaciones de disponibilidad.

El sistema actualmente permite manejar el flujo principal:

Empresa → Sucursal → Empleado → Servicio → Cliente → Turno


---

# Arquitectura implementada

Se mantiene arquitectura modular MVC con separación de responsabilidades:

```
Controller
    ↓
Service
    ↓
Repository
    ↓
Database
```

Cada módulo posee:

- Controllers
- DTOs
- Services
- Repositories
- Routes


---

# Módulos completados

## Clients

Ubicación:

```
app/Modules/Clients
```

Responsabilidad:

Administración de clientes pertenecientes a una empresa.


Funciones:

- Crear clientes.
- Consultar clientes.


Endpoint probado:

```
POST /api/v1/clients
```


Ejemplo:

```json
{
    "name":"Maria Lopez",
    "phone":"1122334455"
}
```


Resultado:

```
201 Created
```


---

# Services

Ubicación:

```
app/Modules/Services
```

Responsabilidad:

Catálogo de servicios ofrecidos por la empresa.


Funciones:

- Crear servicios.
- Definir duración.
- Definir precio.
- Activar/desactivar servicios.


Endpoint:

```
POST /api/v1/services
```


Ejemplo:

```json
{
    "name":"Manicura semipermanente",
    "duration_minutes":60,
    "price":5000
}
```


Resultado:

```
201 Created
```


---

# Employees

Ubicación:

```
app/Modules/Employees
```


Responsabilidad:

Gestión de trabajadores de una sucursal.


Funciones:

- Crear empleados.
- Asociarlos a una sucursal.
- Asociar servicios disponibles.


Endpoint:

```
POST /api/v1/employees
```


Ejemplo:

```json
{
    "branch_id":1,
    "name":"Sofia",
    "service_ids":[1]
}
```


Resultado:

```
201 Created
```


---

# Schedule

Ubicación:

```
app/Modules/Schedule
```


Responsabilidad:

Administración de disponibilidad semanal.


Funciones:

- Configurar días laborales.
- Definir hora inicio.
- Definir hora fin.


Endpoint:

```
PUT /api/v1/employees/{id}/schedule
```


Ejemplo:

```json
{
 "days":[
    {
      "weekday":1,
      "start_time":"09:00:00",
      "end_time":"18:00:00"
    }
 ]
}
```


Resultado:

```
200 OK
```


---

# Appointments

Ubicación:

```
app/Modules/Appointments
```


Responsabilidad:

Motor principal de turnos.


Funciones implementadas:

- Crear turnos.
- Validar horario del empleado.
- Validar disponibilidad.
- Evitar solapamientos.
- Cancelar turnos.
- Estados de turno.


Estados:

```
AppointmentStatus
```

Ejemplo:

```
scheduled
cancelled
```


---

# Validaciones implementadas


## Turno dentro del horario laboral

Correcto:

```
Lunes 10:00
```

Empleado:

```
09:00 - 18:00
```


Resultado:

```
201 Created
```


---

## Evitar doble reserva


Intento:

```
10:00
```

Luego:

```
10:30
```


Resultado:

```
422 Unprocessable Entity
```


Motivo:

El empleado ya posee una reserva incompatible.


---

## Fuera del horario


Intento:

```
20:00
```


Resultado:

```
422 Unprocessable Entity
```


Motivo:

Fuera del rango laboral.


---

# Base de datos


Migraciones agregadas:


```
2026_07_22_000001_create_clients_table.php

2026_07_22_000002_create_employees_table.php

2026_07_22_000003_create_services_table.php

2026_07_22_000004_create_employee_services_table.php

2026_07_22_000005_create_employee_schedules_table.php

2026_07_22_000006_create_appointments_table.php
```


Tablas actuales:


```
companies

branches

users

roles

permissions

clients

employees

services

employee_services

employee_schedules

appointments

refresh_tokens
```


---

# Seguridad


Mantiene:

- JWT Authentication.
- Middleware de autenticación.
- Middleware de permisos.
- Multiempresa mediante company_id.


Permisos agregados:

```
employees.view

employees.manage

services.view

services.manage

schedule.view

schedule.manage

appointments.view

appointments.manage

appointments.cancel
```


---

# Pruebas realizadas


## Login

```
POST /api/v1/auth/login
```


Resultado:

```
200 OK
```


Genera:

```
access_token JWT
```


---

## Clientes

```
POST /api/v1/clients
```

Resultado:

```
201
```


---

## Servicios

```
POST /api/v1/services
```

Resultado:

```
201
```


---

## Empleados

```
POST /api/v1/employees
```

Resultado:

```
201
```


---

## Horarios

```
PUT /api/v1/employees/1/schedule
```

Resultado:

```
200
```


---

## Turnos

Crear:

```
POST /api/v1/appointments
```

Resultado:

```
201
```


Cancelar:

```
POST /api/v1/appointments/1/cancel
```

Resultado:

```
200
```


---

# Estado actual


## Completado

✅ Arquitectura base  
✅ Autenticación JWT  
✅ Roles y permisos  
✅ Multiempresa inicial  
✅ Sucursales  
✅ Clientes  
✅ Servicios  
✅ Empleados  
✅ Horarios  
✅ Turnos  
✅ Validación de disponibilidad  


---

# Próxima fase

## Fase 3 — Comercial


Pendiente:


- Ventas.
- Pagos.
- Productos.
- Stock básico.
- Facturación simple.


---

# Commit sugerido


```
Fase 2 completada: clientes, empleados, servicios y sistema de turnos
```