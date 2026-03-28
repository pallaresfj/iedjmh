# Siguientes pasos de desarrollo

Fecha de corte: 2026-03-28

## Objetivo inmediato

Cerrar la unica seccion publica pendiente (Zona Academica), ampliar la cobertura de tests y realizar limpieza de codigo muerto para que el sitio pueda operar con contenido real.

## Prioridad 1 (bloqueante funcional)

1. Implementar seccion publica de Zona Academica (`/academico/zona-academica`):
   - Agregar definicion en `pageDefinitions()` del `AcademicController`.
   - Registrar ruta en `routes/public.php`.
   - Renderizar enlaces a plataformas (SIEE, Aula Virtual) desde `Settings`.
   - Mostrar documentos/recursos categorizados como zona-academica.
   - Actualizar vista `public.academico.page` con seccion condicional.
   - Agregar test dedicado.

## Prioridad 2 (calidad y cobertura)

1. Expandir pruebas del frontend publico:
   - Transparencia: index, documentos listado/detalle, filtros.
   - Atencion al Ciudadano: index, FAQ, tramites, participacion, mapa del sitio.
   - Institucion: landing + sub-paginas (historia, mision-vision, PEI, manual, sedes).
   - Academico: landing + sub-paginas (niveles, modalidad, planes, evaluacion, proyectos pedagogicos).
2. Verificar consistencia del flujo editorial:
   - Auto-poblado de `created_by`/`updated_by` en todos los modelos de contenido.
   - Auto-set de `published_at` al cambiar status de draft a published.

## Prioridad 3 (limpieza y mantenimiento)

1. Eliminar codigo muerto:
   - `SectionController` y vista `section.blade.php` (sin rutas activas).
   - Secciones huerfanas en `config/institution.php` si ya no se referencian.
2. Registrar decisiones tecnicas en `docs/` al cerrar cada modulo.

## Completados recientemente (para referencia)

Los siguientes items estaban pendientes en la version anterior de este documento y ya fueron implementados:

- Seccion publica de Proyectos (`/proyectos`) con `ProjectController`, index+show, filtros, categorias.
- Seccion publica de Noticias (`/noticias`) con `NewsController`, index+show, featured, categorias.
- `CategoryResource` en Filament con CRUD completo.
- `PqrsRequestResource` en Filament con CRUD + vista detalle.
- Factories para todos los modelos (20 archivos).
- `DemoContentSeeder` con contenido demo completo para todos los modulos.
- SEO basico estandarizado en layout publico (OG, Twitter, canonical).
- Sistema de roles/permisos via Shield con 5 roles predefinidos.

## Criterios de hecho (DoD) para cada modulo publico

- Rutas publicas completas y navegables.
- Contenido editable desde CMS (sin textos fijos en controlador, salvo fallback justificado).
- Metadatos SEO basicos presentes.
- Vista responsive funcional en movil y desktop.
- Pruebas feature minimas para carga y elementos clave.

## Comandos base para el equipo

- Instalar: `composer install && npm install`
- Configurar: `cp .env.example .env && php artisan key:generate`
- Migrar y sembrar: `php artisan migrate && php artisan db:seed`
- Desarrollo: `composer run dev`
- Tests: `php artisan test`
- Lint: `composer run lint`
