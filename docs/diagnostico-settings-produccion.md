# Diagnóstico operativo: `Settings` se queda colgado en producción

Este procedimiento permite comparar instalación **buena** vs **mala** para aislar fallos de `Livewire` upload, sesión, permisos de `storage` o proxy.

## 1) Ejecutar snapshot en ambas instalaciones

Dentro del contenedor/app (`/var/www/html`):

```bash
cd /var/www/html
./scripts/diagnose-settings-upload.sh
```

El comando genera un reporte en:

`storage/logs/diagnostics/settings-upload-diagnosis-<host>-<timestamp>.log`

Compara ambos reportes y revisa diferencias en:

- `app_url`
- `session.driver`, `session.domain`, `session.secure`
- `public_root_writable`
- `public_storage_link_is_symlink`
- `has_sessions_table`
- `livewire_routes`
- Findings `CRITICAL` / `WARNING`

## 2) Reproducción con logs en vivo (instalación mala)

Abre dos terminales:

```bash
tail -f /var/www/html/storage/logs/laravel.log
```

```bash
tail -f /var/log/nginx/error.log
```

Luego en el navegador (panel admin > Settings):

1. Subir `logo` (PNG <= 2MB).
2. Guardar.
3. Repetir guardado solo con paleta de colores.

## 3) Evidencia de red en navegador

En DevTools -> Network, capturar:

- `POST /livewire/upload-file`
- `POST /livewire/update`

Registrar:

- `status code` (419/422/413/500 u otro)
- body de respuesta
- si queda `pending` más de 10 segundos

## 4) Lectura rápida por código de error

- `419`: foco en sesión/cookies (`APP_URL`, `SESSION_DOMAIN`, `SESSION_SECURE_COOKIE`, HTTPS/proxy).
- `413` o timeout: foco en proxy/webserver (límites o timeout upstream).
- `500`: foco en permisos/escritura de disco `public` o excepciones de backend.
- `422`: foco en validación/mime del archivo en el campo.

## 5) Criterios de aceptación del diagnóstico

- Ningún request de `Livewire` queda `pending` > 10s.
- No hay 4xx/5xx en `upload-file`/`update`.
- `settings.updated_at` cambia y persiste tras refrescar.
