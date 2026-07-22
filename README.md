# Backend Gestor SaaS

Backend desarrollado en PHP 8.3 siguiendo arquitectura MVC modular.

## Tecnologías

- PHP 8.3
- MySQL 8
- Composer
- JWT
- PDO

## Instalación

```bash
composer install
cp .env.example .env
php bin/migrate.php
php -S localhost:8080 -t public# saas-backend
