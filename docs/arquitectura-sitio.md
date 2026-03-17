# Arquitectura del Sitio Web Institucional

## Proyecto
Sitio web institucional de la IED Agropecuaria José María Herrera de Pivijay, Magdalena.

## Objetivo
Desarrollar una sede electrónica moderna, accesible y escalable que cumpla con lineamientos de Gobierno Digital en Colombia y permita una gestión eficiente de contenidos institucionales.

## Stack tecnológico

- Laravel 12
- Filament 5.x
- Livewire 4
- Tailwind CSS
- MySQL
- PHP 8.3+

## Principios del sistema

- Modularidad
- Escalabilidad
- Usabilidad
- Accesibilidad
- Cumplimiento normativo (transparencia y acceso a la información)
- Integración con otras aplicaciones institucionales

## Estructura del sitio (frontend)

- Inicio
- Institución
- Académico
- Proyectos
- Comunidad
- Transparencia
- Atención al Ciudadano
- Zona Académica

## Módulos CMS requeridos

- Páginas (contenido institucional)
- Noticias
- Eventos
- Documentos (transparencia)
- Categorías
- Menús
- Banners
- Proyectos
- Sedes
- PQRS
- Trámites

## Panel administrativo (Filament)

Debe permitir:

- Gestión de contenidos
- Gestión documental
- Gestión de usuarios y roles
- Publicación y edición de páginas
- Control de visibilidad de información

## Roles del sistema

- Superadministrador
- Administrador
- Comunicaciones
- Académico
- Transparencia
- Atención al ciudadano
- Consulta (solo lectura)

## Integraciones futuras

- Sistema de planes académicos
- Sistema de asistencia
- Sistema SSO (auth)
- Plataforma académica

## Reglas técnicas

- Código limpio y modular
- No duplicar lógica
- Uso de componentes reutilizables
- Separación clara entre CMS y frontend público