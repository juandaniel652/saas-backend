# Backend Gestor SaaS - Roadmap de Desarrollo

Este documento describe la evolución planificada del backend, organizado por fases. Cada fase agrega capacidades sin romper la arquitectura existente.

---

## ✅ Fase 0 — Fundación (Core)

Objetivo: construir una base sólida y reutilizable.

### Componentes

- Router
- Container / Dependency Injection
- Request / Response
- ResponseHelper
- Exception Handler
- Config Loader (`.env`)
- Database Connection (PDO)
- Migrator
- Logger
- Arquitectura MVC modular
- Estructura de carpetas
- Composer + Autoload

---

## ✅ Fase 1 — Identity & Tenancy

Objetivo: autenticación, autorización y multiempresa.

### Módulos

- Auth (JWT)
- Users
- Roles
- Permissions
- Companies
- Branches

### Funcionalidades

- Registro de empresa
- Login
- JWT
- Middleware de autenticación
- Middleware de permisos
- Multiempresa (Tenancy)
- CRUD de sucursales

---

## 🚧 Fase 2 — Núcleo del negocio (MVP)

Objetivo: disponer de un sistema funcional para la gestión de turnos.

### Módulos

- Clients
- Employees
- Services
- Schedule
- Appointments

### Resultado esperado

Sistema completamente funcional para:

- Gestión de clientes
- Gestión de empleados
- Gestión de servicios
- Configuración de horarios
- Reserva y administración de turnos

---

## 📈 Fase 3 — Comercial

Objetivo: incorporar la parte administrativa y comercial.

### Módulos

- Sales
- Payments
- Products
- Stock
- Facturación básica

---

## 🔔 Fase 4 — Comunicación y Calidad

Objetivo: mejorar la experiencia y el monitoreo del sistema.

### Módulos

- Notifications
- Auditoría
- Logs estructurados
- Reports

### Integraciones iniciales

- Email

### Futuras

- SMS
- WhatsApp

---

## ☁️ Fase 5 — SaaS Completo

Objetivo: convertir el proyecto en un SaaS multiempresa.

### Funcionalidades

- Onboarding automático de empresas
- Multiempresa completo
- Multisucursal avanzada
- Configuración por empresa
- Roles personalizados por empresa

---

## 🚀 Fase 6 — Integraciones y Escalabilidad

Objetivo: preparar el sistema para producción a gran escala.

### Integraciones

- Google Calendar
- Mercado Pago
- Stripe
- WhatsApp
- Webhooks públicos

### Infraestructura

- API pública documentada
- Queue Workers
- Cache
- Optimización de rendimiento

---

## 📊 Fase 7 — Plataforma Completa

Objetivo: completar el ecosistema del producto.

### Componentes

- Dashboard
- Backups automáticos
- Aplicación móvil utilizando la misma API

---

# Estado actual

| Fase | Estado |
|------|--------|
| Fase 0 | ✅ Completada |
| Fase 1 | ✅ Completada |
| Fase 2 | 🚧 En desarrollo |
| Fase 3 | ⏳ Pendiente |
| Fase 4 | ⏳ Pendiente |
| Fase 5 | ⏳ Pendiente |
| Fase 6 | ⏳ Pendiente |
| Fase 7 | ⏳ Pendiente |