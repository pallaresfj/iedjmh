# Configuracion Institucional (`config/institution.php`)

Este archivo define la metadata y estructura de navegacion del sitio publico. Su contenido se usa como fallback cuando no existen datos en la tabla `settings` del CMS.

## Estructura

### Datos basicos

| Clave | Tipo | Descripcion |
|-------|------|-------------|
| `name` | string | Nombre oficial de la institucion |
| `display_name` | string | Nombre para mostrar en encabezados (mayusculas) |
| `short_name` | string | Sigla o abreviatura |
| `city` | string | Municipio |
| `department` | string | Departamento |
| `address` | string | Direccion fisica (via `INSTITUTION_ADDRESS` en .env) |
| `phone` | string | Telefono (via `INSTITUTION_PHONE` en .env) |
| `email` | string | Correo electronico (via `INSTITUTION_EMAIL` en .env) |

### SEO

```php
'seo' => [
    'default_description' => '...',  // Meta description por defecto
    'default_image' => '...',        // Imagen OG por defecto
]
```

### Barra de gobierno

```php
'govbar' => [
    'label' => 'GOV.CO',  // Texto del enlace de gobierno digital
]
```

### Aliados

Array de enlaces que se muestran en el footer:

```php
'allies' => [
    ['label' => 'MinEducacion', 'url' => 'https://...'],
    ['label' => 'Gobernacion', 'url' => 'https://...'],
]
```

### Navegacion principal (`navigation.primary`)

Array de items del menu principal. Cada item tiene:

| Campo | Tipo | Descripcion |
|-------|------|-------------|
| `label` | string | Texto visible del enlace |
| `route` | string | Nombre de ruta Laravel |
| `children` | array | Sub-items del menu desplegable |

Cada `children` item tiene:

| Campo | Tipo | Descripcion |
|-------|------|-------------|
| `label` | string | Texto visible |
| `route` | string | Nombre de ruta Laravel |
| `icon` | string | Icono en formato canonico (`ms:*` o `fa:*:*`) |

### Navegacion del footer (`navigation.footer`)

Array simple de enlaces:

```php
['label' => 'Texto', 'route' => 'nombre.ruta']
```

### Secciones (`sections`)

Mapa de secciones del sitio con metadata:

| Campo | Tipo | Descripcion |
|-------|------|-------------|
| `label` | string | Nombre de la seccion |
| `route` | string | Ruta principal de la seccion |
| `description` | string | Descripcion breve |
| `icon` | string | Icono en formato canonico (`ms:*` o `fa:*:*`) |

## Formato de iconos

- Formato canonico Material Symbols: `ms:agriculture`
- Formato canonico Font Awesome: `fa:solid:house`
- Estilos FA soportados: `solid`, `regular`, `brands`
- Compatibilidad legacy: valores Material sin prefijo (`agriculture`) siguen funcionando de forma temporal.
- Fallback seguro: cuando el valor es invalido, el sistema renderiza `ms:help`.

## Relacion con el CMS

- Los valores de este archivo son **fallbacks**. Si existe un registro en la tabla `settings`, esos datos tienen prioridad.
- La clase `App\Support\PublicSettings` gestiona la resolucion: primero consulta `settings`, luego cae a `config('institution.*')`.
- Los campos de contacto (`address`, `phone`, `email`) se leen desde variables de entorno para facilitar la configuracion por ambiente.

## Cambios futuros

Si se decide migrar la navegacion a la base de datos (administrable desde CMS), este archivo quedaria unicamente como fuente de datos basicos y fallbacks.
