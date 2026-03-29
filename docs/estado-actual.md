# Estado actual del proyecto

Fecha de corte: 2026-03-28

## 1. Modulos implementados

### Frontend publico institucional

- Inicio (`/`): home dinamica con hero, enlaces rapidos, noticias, proyecto destacado y proximos eventos.
- Institucion (`/institucion`): landing y 8 paginas internas (historia, mision/vision, simbolos, equipo institucional, sedes, PEI, manual, directorio) con soporte CMS/fallback.
- Academico (`/academico`): landing y 6 paginas internas (niveles, modalidad, planes, evaluacion, proyectos pedagogicos, calendario) con consultas a documentos/proyectos/eventos.
- Transparencia (`/transparencia`): landing, listado filtrable de documentos, detalle de documento con relacionados. Incluye modulo de contratacion (`/transparencia/contratacion`) con listado y detalle de procesos.
- Atencion al Ciudadano (`/atencion-ciudadano`): landing, contacto, PQRS (formulario, radicado y consulta de estado), tramites filtrables, FAQ filtrables, mapa del sitio y participacion.
- Proyectos (`/proyectos`): listado paginado con filtros por busqueda, categoria y ordenamiento. Detalle por slug con proyectos relacionados. Soporte para proyecto destacado.
- Noticias (`/noticias`): listado paginado con filtros por busqueda, categoria y ordenamiento. Hasta 3 noticias destacadas. Detalle por slug con noticias relacionadas.
- Eventos (`/eventos/{slug}`): detalle de evento individual.
- Busqueda global (`/buscar`): busqueda unificada de contenido.
- Sitemap XML (`/sitemap.xml`): generacion automatica.

### Backoffice y autenticacion

- Panel Filament 5 en `/admin` con 18 recursos organizados por grupo.
- Control de acceso con Spatie Permission (Shield) y 5 roles predefinidos.
- `is_admin` como flag base para acceso al panel; permisos granulares via Shield.
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
- `staff_members`: personal institucional.
- `settings`: configuracion general del sitio.
- `categories`: categorias jerarquicas (`parent_id`).
- `categorizables`: pivote polimorfico para categorizar `posts`, `events`, `documents`, `projects`.

### Tablas de contratacion

- `contracts`: procesos de contratacion.
- `contract_types`: tipos de contrato.
- `contractors`: contratistas.
- `contract_documents`: documentos asociados a contratos.
- `contract_participants`: participantes en procesos.

### Tablas de atencion al ciudadano

- `procedures`: tramites y servicios.
- `faqs`: preguntas frecuentes.
- `pqrs_requests`: radicados PQRS.
- `pqrs_messages`: mensajes/historial de cada PQRS.

### Tablas de permisos (Spatie/Shield)

- `roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions`.

### Relaciones clave

- `categories` 1:N `faqs`.
- `categories` 1:N `procedures`.
- `categories` N:M polimorfica con `posts`, `events`, `documents`, `projects` via `categorizables`.
- `pqrs_requests` 1:N `pqrs_messages`.
- `pqrs_requests.assigned_to` -> `users.id`.
- `contracts` N:1 `contract_types`, N:1 `contractors`.
- `contract_documents` N:1 `contracts`.
- `contract_participants` N:1 `contracts`.
- Campos `created_by` y `updated_by` en tablas de contenido apuntan a `users.id`.

## 3. Rutas publicas principales

- `GET /` -> `home`.
- `GET /buscar` -> busqueda global.
- `GET /sitemap.xml` -> sitemap XML.
- `GET /institucion` y subrutas:
  - `/historia`, `/mision-vision`, `/simbolos`, `/equipo-institucional`, `/sedes`, `/pei`, `/manual-convivencia`, `/directorio`.
- `GET /academico` y subrutas:
  - `/niveles-educativos`, `/modalidad`, `/planes-area`, `/sistema-evaluacion`, `/proyectos-pedagogicos`, `/calendario-academico`.
- `GET /proyectos` -> listado filtrable con paginacion.
- `GET /proyectos/{slug}` -> detalle de proyecto.
- `GET /noticias` -> listado filtrable con paginacion.
- `GET /noticias/{slug}` -> detalle de noticia.
- `GET /eventos/{slug}` -> detalle de evento.
- `GET /transparencia` -> landing.
- `GET /transparencia/contratacion` -> listado de procesos.
- `GET /transparencia/contratacion/{processCode}` -> detalle de proceso.
- `GET /transparencia/documentos` -> listado filtrable.
- `GET /transparencia/documentos/{slug}` -> detalle de documento.
- `GET /atencion-ciudadano` -> landing.
- `GET /atencion-ciudadano/contactenos` -> formulario de contacto.
- `GET /atencion-ciudadano/pqrs` -> formulario PQRS.
- `POST /atencion-ciudadano/pqrs` (throttle `pqrs`) -> envio PQRS.
- `GET /atencion-ciudadano/pqrs/consulta` -> consulta de estado PQRS.
- `POST /atencion-ciudadano/pqrs/consulta` (throttle `pqrs`) -> buscar radicado.
- `GET /atencion-ciudadano/tramites-servicios` -> listado filtrable.
- `GET /atencion-ciudadano/preguntas-frecuentes` -> FAQ filtrables.
- `GET /atencion-ciudadano/mapa-sitio` -> mapa del sitio.
- `GET /atencion-ciudadano/participacion` -> participacion ciudadana.

## 4. Recursos Filament existentes

### Grupo Contenido

- `PageResource` (Paginas).
- `PostResource` (Noticias) — filtro de consulta por rol, moderacion.
- `EventResource` (Eventos) — accion de replicar.
- `DocumentResource` (Documentos).
- `ProjectResource` (Proyectos).
- `BannerResource` (Banners) — accion de replicar, visibilidad permanente.
- `PqrsRequestResource` (PQRSF) — CRUD + pagina de vista detalle.

### Grupo Institucion

- `CampusResource` (Sedes).
- `CategoryResource` (Categorias) — jerarquica con parent, soft delete.
- `StaffMemberResource` (Personal).
- `SettingResource` (Configuracion).
- `RoleResource` (Roles) — via Shield.

### Grupo Transparencia

- `ContractResource` (Contratos).
- `ContractTypeResource` (Tipos de contrato).
- `ContractorResource` (Contratistas).

### Grupo Atencion al Ciudadano

- `FaqResource` (Preguntas frecuentes).
- `ProcedureResource` (Tramites).

### Grupo Usuarios

- `UserResource` (Usuarios).

Todos incluyen list/create/edit + schema de formulario + tabla. Algunos incluyen paginas adicionales (view, replicate).

## 5. Componentes Blade y Livewire 4 creados

### Blade publico (custom)

- Layout publico: `resources/views/layouts/public/app.blade.php` — incluye SEO (OG, Twitter, canonical).
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

## 6. Infraestructura de desarrollo

### Factories (20 archivos)

Todas las entidades principales tienen factory: Banner, Campus, Category, Contract, ContractDocument, ContractParticipant, ContractType, Contractor, Document, Event, Faq, Page, Post, PqrsMessage, PqrsRequest, Procedure, Project, Setting, StaffMember, User.

### Seeders

- `DatabaseSeeder` -> orquesta `ShieldRolesAndUsersSeeder` + `DemoContentSeeder`.
- `ShieldRolesAndUsersSeeder` -> 5 roles (super_admin, administrador, editor, colaborador, soporte) con permisos granulares.
- `DemoContentSeeder` -> contenido demo completo para todos los modulos (settings, categorias, paginas, banners, sedes, personal, noticias, eventos, proyectos, documentos, FAQ, tramites, contratos, PQRS).

### Tests (22 archivos en `tests/Feature/`)

- Admin: `AdminDashboardTest`, `PqrsRequestResourceTest`, `PostModerationFilterTest`, `PostSubmissionNotificationTest`, `BannerPermanentVisibilityTest`, `BannerReplicateActionTest`, `EventReplicateActionTest`, `PageReplicateActionTest`.
- Publico: `PublicHomeTest`, `PublicNewsTest`, `PublicProjectsTest`, `PublicAcademicCalendarTest`, `PublicContractingTest`, `PublicEventDetailTest`, `PublicInstitutionStaffDirectoryTest`, `PublicInstitutionSymbolsPageTest`, `PublicPqrsSubmissionTest`, `PublicPageMenuBindingTest`, `PublicSettingsTest`.
- Auth/Settings: `tests/Feature/Auth/*`, `tests/Feature/Settings/*`.

## 7. Pendientes actuales

- Implementar seccion publica de Zona Academica (`/academico/zona-academica`) — actualmente no tiene ruta activa.
- Ampliar cobertura de tests del frontend publico (faltan: Transparencia docs, Atencion completo, Institucion paginas internas, Academico paginas internas).
- Verificar consistencia del flujo editorial (auto-poblado de `created_by`/`updated_by`, auto-set de `published_at` al publicar).
- Eliminar codigo muerto: `SectionController` y vista `section.blade.php` ya no tienen rutas activas.
- Gestion de navegacion publica administrable (actualmente en `config/institution.php`) — diferido a post-lanzamiento.

## 8. Instrucciones basicas para continuar el desarrollo

### Preparacion local

1. Instalar dependencias:
   - `composer install`
   - `npm install`
2. Configurar entorno:
   - `cp .env.example .env`
   - ajustar variables de base de datos en `.env`
   - `php artisan key:generate`
3. Migrar y sembrar:
   - `php artisan migrate`
   - `php artisan db:seed` (contenido demo completo)
4. Compilar assets:
   - desarrollo: `npm run dev`
   - produccion: `npm run build`

### Ejecucion diaria

- Levantar stack local con: `composer run dev`.
- Ejecutar pruebas: `php artisan test`.
- Aplicar formato: `composer run lint`.

### Acceso admin

- El seeder crea un usuario admin (`admin@iedagropivijay.edu.co`) con rol `super_admin`.

### Criterios de continuidad

- Mantener separacion estricta entre frontend publico y panel Filament.
- Mantener sintaxis y patrones de Livewire 4 (evitar sintaxis legada).
- Priorizar componentes reutilizables y evitar duplicaciones entre modulos.
- Usar el trait `ResolvesPublicContent` para queries defensivas en controladores publicos.
