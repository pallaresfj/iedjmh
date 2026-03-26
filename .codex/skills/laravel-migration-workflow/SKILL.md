---
name: laravel-migration-workflow
description: Gestionar cambios de estructura de base de datos en Laravel de forma segura. Usar cuando se pidan migraciones locales o remotas, validación de estado de migraciones, o consolidación para instalaciones limpias con schema dump.
---

# Laravel Migration Workflow

## Objetivo

Gestionar el ciclo de vida de cambios de esquema en Laravel:
- crear y aplicar migraciones incrementales,
- desplegar migraciones a entornos remotos con seguridad,
- consolidar el esquema para instalaciones limpias sin reescribir migraciones históricas.

## Reglas obligatorias

- Nunca modificar migraciones históricas ya ejecutadas en pruebas o producción.
- Crear siempre migraciones incrementales para cambios nuevos.
- Verificar estado real con `php artisan migrate:status`; no asumir por existencia en el repo.
- Probar primero en local antes de proponer ejecución remota.
- Para consolidación usar `php artisan schema:dump --prune`.
- Evitar comandos destructivos (`migrate:fresh`, `db:wipe`) sin instrucción explícita.

## Flujo estándar

### Escenario 1: desarrollo normal
1. Revisar migraciones pendientes:
   - `php artisan migrate:status`
2. Ejecutar migraciones locales:
   - `php artisan migrate`
3. Verificar que la aplicación siga funcionando.
4. Revisar archivos modificados:
   - `git status --short`
5. Preparar resumen con:
   - migraciones nuevas,
   - cambios relacionados en modelos, seeders, factories o código asociado.

### Escenario 2: despliegue a pruebas o producción
1. Confirmar que las migraciones nuevas están en el repositorio.
2. En el servidor, ejecutar:
   - `php artisan migrate --force`
3. Si hay múltiples nodos o procesos paralelos, ejecutar:
   - `php artisan migrate --force --isolated`
4. Verificar estado posterior:
   - `php artisan migrate:status`

### Escenario 3: consolidación para instalación limpia
1. Confirmar que las migraciones recientes ya están aplicadas y estabilizadas.
2. Ejecutar:
   - `php artisan schema:dump --prune`
3. Verificar cambios en:
   - `database/schema`
   - `database/migrations`
4. Preparar cambios para commit.
5. Reportar qué quedó consolidado y qué migraciones posteriores siguen vigentes.

## Comandos de referencia

### Local
- `php artisan migrate:status`
- `php artisan migrate`
- `php artisan schema:dump --prune`

### Remoto
- `php artisan migrate --force`
- `php artisan migrate --force --isolated`

### Git
- `git status --short`
- `git add .`
- `git commit -m "mensaje"`

## Entrega obligatoria

Responder siempre con esta estructura:
1. Estado actual de migraciones.
2. Comandos ejecutados o recomendados.
3. Archivos afectados.
4. Riesgos o validaciones pendientes.
5. Siguiente acción o commit sugerido.

## Qué evitar

- Fusionar cambios editando migraciones antiguas.
- Asumir que el entorno remoto está alineado con local sin verificar.
- Eliminar migraciones históricas manualmente si no existe un schema dump válido.
