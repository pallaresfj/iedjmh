# Diagnóstico operativo: guardado admin (Filament/Livewire) se queda colgado en producción

Este procedimiento aplica para **cualquier recurso del panel** (`Settings`, `Proyectos`, `Noticias`, etc.) y permite aislar fallos de `Livewire` upload/update, sesión, permisos de `storage` o proxy.

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
- `livewire_http_status`
- Findings `CRITICAL` / `WARNING`

## 2) Reproducción con logs en vivo (instalación mala)

Abre tres terminales:

```bash
tail -f /var/www/html/storage/logs/laravel.log
```

```bash
tail -f /var/log/nginx/access.log
```

```bash
tail -f /var/log/nginx/error.log
```

Luego en el navegador (panel admin > recurso con fallo):

1. Editar solo un campo de texto y guardar.
2. Repetir guardado con upload (si el recurso tiene archivos).
3. Confirmar si el botón queda en "Guardando..." más de 10 segundos.

## 3) Evidencia de red en navegador

En DevTools -> Network, capturar:

- `POST /livewire-*/upload-file`
- `POST /livewire-*/update`

Registrar:

- `status code` (419/422/413/500 u otro)
- body de respuesta
- si queda `pending` más de 10 segundos

## 4) Lectura rápida por código de error

- `419`: foco en sesión/cookies (`APP_URL`, `SESSION_DOMAIN`, `SESSION_SECURE_COOKIE`, HTTPS/proxy).
- `413` o timeout: foco en proxy/webserver (límites o timeout upstream).
- `500`: foco en permisos/escritura de disco `public` o excepciones de backend.
- `422`: foco en validación/mime del archivo en el campo.

## 5) Captura única recomendada (sin DevTools)

En el contenedor:

```bash
cd /var/www/html
TS=$(date +%Y%m%d-%H%M%S)
LOG=/tmp/livewire-save-$TS.log

timeout 90 sh -c 'tail -n0 -F /var/log/nginx/access.log /var/log/nginx/error.log storage/logs/laravel.log' | tee "$LOG"
grep -E "livewire-|POST| 419 | 422 | 413 | 499 | 500 | 502 | 504 |TokenMismatch|CSRF|ERROR" "$LOG" | tail -n 300
```

Reproduce el guardado mientras corre la captura.

## 6) Criterios de aceptación del diagnóstico

- Ningún request de `Livewire` queda `pending` > 10s.
- No hay 4xx/5xx en `upload-file`/`update`.
- El registro editado cambia y persiste tras refrescar.
