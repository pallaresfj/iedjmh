# Siguientes pasos de desarrollo

Fecha de corte: 2026-03-18

## Objetivo inmediato

Completar los modulos publicos pendientes y cerrar brechas del CMS para que el sitio institucional pueda operar con contenido real sin depender de placeholders.

## Prioridad 1 (bloqueante funcional)

1. Implementar secciones publicas pendientes con datos reales:
   - `proyectos`
   - `comunidad`
   - `zona-academica`
2. Crear recursos Filament faltantes para operacion:
   - Categorias (`CategoryResource`).
   - PQRS (`PqrsRequestResource` y, segun necesidad, gestion de mensajes).
3. Definir flujo editorial minimo:
   - borrador/publicado
   - responsable (`created_by`/`updated_by`)
   - fechas de publicacion y orden.

## Prioridad 2 (consistencia operativa)

1. Unificar gestion de navegacion publica:
   - mover menus clave desde `config/institution.php` a una fuente administrable (si se decide CMS).
2. Estandarizar SEO minimo por modulo:
   - titulo
   - descripcion
   - imagen social
   - canonical cuando aplique.
3. Completar semilla de contenido institucional inicial para ambientes nuevos.

## Prioridad 3 (calidad y mantenimiento)

1. Expandir pruebas del frontend publico:
   - Transparencia (filtros/listado/detalle).
   - Atencion (PQRS/tramites/FAQ).
   - Paginas de Institucion y Academico.
2. Definir estrategia de permisos:
   - reemplazar gradualmente `is_admin` por roles/capacidades cuando se habiliten perfiles operativos.
3. Registrar decisiones tecnicas en `docs/` cada vez que se cierre un modulo.

## Secuencia recomendada de trabajo

1. Cerrar modulo `comunidad` (noticias/eventos) porque ya existen modelos y resources.
2. Cerrar modulo `proyectos` reutilizando `Project` + categorias.
3. Cerrar `zona-academica` con enlaces/servicios y contenido administrable.
4. Implementar recursos de `categorias` y `pqrs` en Filament.
5. Aumentar pruebas de regresion del frontend.

## Criterios de hecho (DoD) para cada modulo publico

- Rutas publicas completas y navegables.
- Contenido editable desde CMS (sin textos fijos en controlador, salvo fallback justificado).
- Metadatos SEO basicos presentes.
- Vista responsive funcional en movil y desktop.
- Pruebas feature minimas para carga y elementos clave.

## Comandos base para el equipo

- Instalar: `composer install && npm install`
- Configurar: `cp .env.example .env && php artisan key:generate`
- Migrar: `php artisan migrate`
- Desarrollo: `composer run dev`
- Tests: `php artisan test`
- Lint: `composer run lint`

