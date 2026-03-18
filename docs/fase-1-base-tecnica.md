# Fase 1 - Base tecnica

## Alcance aplicado

- Instalacion de Filament 5 para habilitar el CMS administrativo.
- Creacion del panel base en `/admin`.
- Separacion inicial de rutas publicas en `routes/public.php`.
- Creacion de la base del frontend institucional:
  - controlador publico inicial
  - layout publico
  - componentes globales de cabecera y pie de pagina
  - vista home base
- Definicion de configuracion institucional inicial en `config/institution.php`.

## Convenciones acordadas

- Frontend publico:
  - Controladores: `App\Http\Controllers\Public`
  - Vistas: `resources/views/public`
  - Layouts: `resources/views/layouts/public`
  - Componentes: `resources/views/components/public`
- CMS administrativo:
  - Panel provider: `App\Providers\Filament\AdminPanelProvider`
  - Recursos/paginas/widgets: `App\Filament\...` (descubrimiento automatico de Filament)

## Notas

- Esta fase no incluye implementacion de secciones funcionales (Institucion, Academico, etc.).
- Esta fase no incluye recursos CRUD de Filament ni modelos de contenido.
