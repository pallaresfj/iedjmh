# Estado actual del proyecto

Fecha de corte: 2026-03-18

## 1. Modulos implementados

### Frontend publico institucional

- Inicio (`/`): home dinamica con hero, enlaces rapidos, noticias, proyecto destacado y proximos eventos.
- Institucion (`/institucion`): landing y paginas internas (historia, mision/vision, simbolos, equipo directivo, sedes, PEI, manual, directorio) con soporte CMS/fallback.
- Academico (`/academico`): landing y paginas internas (niveles, modalidad, planes, evaluacion, proyectos pedagogicos, calendario, zona academica) con consultas a documentos/proyectos/eventos.
- Transparencia (`/transparencia`): landing, listado filtrable de documentos y detalle de documento con relacionados.
- Atencion al Ciudadano (`/atencion-ciudadano`): landing, contacto, PQRS (formulario y radicado), tramites filtrables, FAQ filtrables, mapa del sitio y participacion.
- Secciones base (actualmente genericas): `proyectos`, `comunidad`, `zona-academica`.

### Backoffice y autenticacion

- Panel Filament 5 en `/admin`.
- Control de acceso al panel por `users.is_admin`.
- Autenticacion Fortify + Livewire/Flux (login, registro, recuperacion, verificacion, 2FA).
- Ajustes de usuario (perfil, seguridad, apariencia) via rutas Livewire.

## 2. Estructura de base de datos

### Tablas de infraestructura Laravel

- `users` (incluye `is_admin` y campos 2FA).
- `password_reset_tokens`, `sessions`.
- `cache`, `cache_locks`.
- `jobs`, `job_batches`, `failed_jobs`.

### Tablas de contenido institucional/CMS

- `pages`: paginas institucionales con SEO, estado y fechas.
- `posts`: noticias/comunicados con destacado y SEO.
- `events`: eventos con fechas, ubicacion y registro.
- `documents`: documentos con archivo/url externa y metadatos.
- `projects`: proyectos institucionales/pedagogicos.
- `banners`: banners para portada y secciones.
- `campuses`: sedes institucionales.
- `categories`: categorias jerarquicas (`parent_id`).
- `categorizables`: pivote polimorfico para categorizar `posts`, `events`, `documents`, `projects`.

### Tablas de atencion al ciudadano

- `procedures`: tramites y servicios.
- `faqs`: preguntas frecuentes.
- `pqrs_requests`: radicados PQRS.
- `pqrs_messages`: mensajes/historial de cada PQRS.

### Relaciones clave

- `categories` 1:N `faqs`.
- `categories` 1:N `procedures`.
- `categories` N:M polimorfica con `posts`, `events`, `documents`, `projects` via `categorizables`.
- `pqrs_requests` 1:N `pqrs_messages`.
- `pqrs_requests.assigned_to` -> `users.id`.
- Campos `created_by` y `updated_by` en tablas de contenido apuntan a `users.id`.

## 3. Rutas publicas principales

- `GET /` -> `home`.
- `GET /institucion` y subrutas:
  - `/historia`, `/mision-vision`, `/simbolos`, `/equipo-directivo`, `/sedes`, `/pei`, `/manual-convivencia`, `/directorio`.
- `GET /academico` y subrutas:
  - `/niveles-educativos`, `/modalidad-agropecuaria`, `/planes-area`, `/sistema-evaluacion`, `/proyectos-pedagogicos`, `/calendario-academico`, `/zona-academica`.
- `GET /proyectos` (vista seccion generica).
- `GET /comunidad` (vista seccion generica).
- `GET /transparencia`.
- `GET /transparencia/documentos`.
- `GET /transparencia/documentos/{slug}`.
- `GET /atencion-ciudadano`.
- `GET /atencion-ciudadano/contactenos`.
- `GET /atencion-ciudadano/pqrs`.
- `POST /atencion-ciudadano/pqrs` (throttle `pqrs`).
- `GET /atencion-ciudadano/tramites-servicios`.
- `GET /atencion-ciudadano/preguntas-frecuentes`.
- `GET /atencion-ciudadano/mapa-sitio`.
- `GET /atencion-ciudadano/participacion`.
- `GET /zona-academica` (vista seccion generica).

## 4. Recursos Filament existentes

### Grupo Contenido

- `PageResource` (Paginas).
- `PostResource` (Noticias).
- `EventResource` (Eventos).
- `DocumentResource` (Documentos).
- `ProjectResource` (Proyectos).
- `BannerResource` (Banners).

### Grupo Institucion

- `CampusResource` (Sedes).

### Grupo Atencion al Ciudadano

- `FaqResource` (Preguntas frecuentes).
- `ProcedureResource` (Tramites).

Todos incluyen list/create/edit + schema de formulario + tabla.

## 5. Componentes Blade y Livewire 4 creados

### Blade publico (custom)

- Layout publico: `resources/views/layouts/public/app.blade.php`.
- Vistas publicas por modulo en `resources/views/public/**`.
- Componentes reutilizables en `resources/views/components/public/**`:
  - `header`, `topbar`, `nav`, `footer`, `internal-page`.
  - componentes por dominio: `home/*`, `institucion/*`, `academico/*`, `transparencia/*`, `atencion/*`.

### Livewire 4 local

- Accion Livewire: `app/Livewire/Actions/Logout.php`.

### Livewire/Volt (starter kit integrado)

- Rutas Livewire de ajustes:
  - `settings/profile` -> `pages::settings.profile`
  - `settings/appearance` -> `pages::settings.appearance`
  - `settings/security` -> `pages::settings.security`
- Vistas Volt/Livewire en `resources/views/pages/settings/⚡*.blade.php`.

## 6. Pendientes actuales

- Implementar contenido funcional real para secciones actualmente genericas:
  - `proyectos`, `comunidad`, `zona-academica` (hoy usan `SectionController` + `public/section.blade.php`).
- Completar recursos CMS faltantes para cobertura total del alcance documental:
  - gestion de categorias desde panel (tabla/modelo existe, resource no).
  - gestion de PQRS desde panel (tabla/modelos existen, resources no).
  - gestion de menus/estructura navegacion desde CMS (hoy es config estatica).
- Definir y aplicar modelo de roles/permisos mas granular (actualmente solo `is_admin`).
- Ampliar pruebas funcionales del frontend publico (hoy la cobertura publica es basica en Home).
- Poblacion inicial de contenido: no hay seeders de contenido institucional, solo usuario de prueba.

## 7. Instrucciones basicas para continuar el desarrollo

### Preparacion local

1. Instalar dependencias:
   - `composer install`
   - `npm install`
2. Configurar entorno:
   - `cp .env.example .env`
   - ajustar variables de base de datos en `.env`
   - `php artisan key:generate`
3. Migrar base de datos:
   - `php artisan migrate`
4. Compilar assets:
   - desarrollo: `npm run dev`
   - produccion: `npm run build`

### Ejecucion diaria

- Levantar stack local con: `composer run dev`.
- Ejecutar pruebas: `php artisan test`.
- Aplicar formato: `composer run lint`.

### Acceso admin

- Crear/ajustar usuario administrador (`is_admin = true`) para entrar a `/admin`.

### Criterios de continuidad

- Mantener separacion estricta entre frontend publico y panel Filament.
- Mantener sintaxis y patrones de Livewire 4 (evitar sintaxis legada).
- Priorizar componentes reutilizables y evitar duplicaciones entre modulos.
