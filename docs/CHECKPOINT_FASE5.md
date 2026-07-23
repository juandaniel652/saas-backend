# CHECKLIST - FASE 5 (Administración + RBAC)

## Configuración de empresa
- [x] Configuración por empresa (company_settings)
- [x] GET /settings
- [x] PUT /settings
- [x] Configuración dinámica

## Roles y permisos
- [x] Catálogo de permisos
- [x] CRUD de roles
- [x] Asignación de permisos
- [x] Permisos por slug
- [x] Roles dinámicos por empresa

## Usuarios
- [x] Creación de usuarios
- [x] Asignación de roles
- [x] Login con roles dinámicos
- [x] JWT con permisos reales

## Seguridad
- [x] PermissionMiddleware funcionando
- [x] RBAC completo
- [x] Protección por permisos
- [x] Validación de empresa

## Integridad
- [x] Roles pertenecen a la empresa
- [x] Usuarios pertenecen a la empresa
- [x] Settings aislados por empresa

## Validaciones realizadas

- [x] Crear rol
- [x] Crear usuario
- [x] Login del usuario nuevo
- [x] /auth/me devuelve únicamente los permisos asignados
- [x] PermissionMiddleware devuelve 403 cuando corresponde

Estado: APROBADA