# Guía de despliegue: IEDJMH en VPS Hostinger con Dokploy

> Público objetivo: persona con poca experiencia en despliegue.
>
> Objetivo: dejar el proyecto `iedjmh` funcionando en producción con dominio y HTTPS, usando Dokploy y MySQL.

---

## 1) Qué ya trae este proyecto para producción

Antes de desplegar, este proyecto **ya viene preparado** para entorno productivo. Esto es lo que ya está listo:

1. `Dockerfile` multi-stage:
   - Stage 1: instala dependencias PHP de Composer.
   - Stage 2: compila assets con Node/Vite.
   - Stage 3: imagen final con PHP-FPM + Nginx + Supervisor.

2. Stack interno del contenedor:
   - Nginx atiende HTTP dentro del contenedor.
   - PHP-FPM ejecuta Laravel.
   - Supervisor levanta procesos y reinicia si algo cae.
   - Worker de colas activo (`php artisan queue:work database ...`).

3. `docker/entrypoint.sh` automatiza al arrancar:
   - Valida variables críticas (`APP_KEY`, DB, etc.).
   - Prepara permisos de `storage`.
   - Crea enlace `storage:link`.
   - Espera conexión de base de datos.
   - Ejecuta `php artisan migrate --force --no-interaction`.
   - Genera cachés (`config`, `routes`, `views`, `events`, Filament).

4. Healthcheck:
   - Laravel expone `/up` y el contenedor lo usa como healthcheck.

5. Puerto interno de aplicación:
   - El contenedor expone **puerto 80**.
   - En Dokploy, al crear dominio, el **Container Port** debe ser **80**.

### Qué deberías ver

- Si todo está bien, en logs de despliegue aparecerá algo similar a:
  - "Starting IEDJMH application"
  - "Running migrations"
  - "Application ready. Starting Supervisor"

---

## 2) Prerequisitos en Hostinger VPS

Debes tener esto antes de tocar Dokploy:

1. VPS activo en Hostinger.
2. Acceso SSH al VPS (usuario root o sudo).
3. IP pública del VPS identificada.
4. Puertos disponibles:
   - `80` (HTTP)
   - `443` (HTTPS)
   - `3000` (panel Dokploy)

### Checklist rápido

- [ ] Puedo entrar por SSH al VPS.
- [ ] Sé la IP pública del VPS.
- [ ] No hay otro servicio ocupando 80/443/3000 (si lo hay, resolver antes).

---

## 3) Verificación rápida de Dokploy instalado

Asumiendo que Dokploy ya está instalado (según tu contexto):

1. En navegador abre: `http://IP_DE_TU_VPS:3000`
2. Si carga login/dashboard, Dokploy está activo.
3. Si no abre:
   - revisar que el puerto 3000 esté permitido en firewall,
   - revisar estado de Docker en el VPS,
   - reiniciar Dokploy si aplica.

### Contingencia mínima (si no estuviera instalado)

En el VPS:

```bash
curl -sSL https://dokploy.com/install.sh | sh
```

Luego vuelve a abrir `http://IP_DE_TU_VPS:3000`.

### Qué deberías ver

- Pantalla de login o dashboard de Dokploy.

---

## 4) Conexión de dominio en Hostinger (DNS)

Este paso conecta tu dominio real al VPS.

1. En Hostinger entra al panel del dominio (`DNS Zone`).
2. Elimina registros que choquen con `@` o `www` (A/AAAA/CNAME antiguos).
3. Crea estos registros:
   - Registro A:
     - `Host`: `@`
     - `Points to`: `IP_DE_TU_VPS`
   - Registro A:
     - `Host`: `www`
     - `Points to`: `IP_DE_TU_VPS`
4. Guarda cambios.
5. Espera propagación DNS (puede tardar minutos u horas; en algunos casos hasta 24h).

### Qué deberías ver

- Tu dominio deja de apuntar al hosting anterior y empieza a resolver a tu VPS.

---

## 5) Alta de base de datos MySQL en Dokploy

1. En Dokploy crea un servicio de tipo **Database > MySQL**.
2. Define:
   - Database name (ejemplo: `iedjmh`)
   - Username (ejemplo: `iedjmh`)
   - Password fuerte (mínimo 16 caracteres)
3. Guarda y despliega la base de datos.
4. Toma nota de:
   - host interno del servicio (nombre del servicio en Dokploy),
   - puerto interno (normalmente `3306`),
   - nombre de BD, usuario y contraseña.

### Recomendaciones importantes

- Mantén volumen persistente activo para la base de datos (evita pérdida de datos al redeploy).
- Configura backups de base de datos en Dokploy (idealmente a destino S3).

### Qué deberías ver

- Estado de la DB en "Running" y logs sin errores de inicialización.

---

## 6) Alta de aplicación en Dokploy desde GitHub

### Datos exactos de este proyecto

- Repositorio: `https://github.com/pallaresfj/iedjmh.git`
- Rama: `main`
- Build Type: `Dockerfile`
- Ruta del Dockerfile: `./Dockerfile`

### Paso a paso

1. En Dokploy crea una nueva **Application**.
2. Source provider: GitHub.
3. Conecta o selecciona el repositorio `pallaresfj/iedjmh`.
4. Selecciona rama `main`.
5. En Build Type selecciona `Dockerfile`.
6. Define Dockerfile path: `./Dockerfile`.
7. Guarda.

### Recomendación de persistencia para Laravel

En `Advanced > Volumes/Mounts`, agrega un volumen para persistir archivos subidos y logs:

- Volume name: `iedjmh-storage`
- Mount path: `/var/www/html/storage`

Esto evita perder archivos de `storage` al redeploy.

### Qué deberías ver

- La app queda creada y lista para configurar variables + dominio.

---

## 7) Variables de entorno en Dokploy

Configura estas variables en la Application (pestaña `Environment`).

## 7.1 Obligatorias (si faltan, el contenedor falla al arrancar)

```env
APP_KEY=base64:...
APP_URL=https://tudominio.com
DB_CONNECTION=mysql
DB_HOST=nombre-servicio-mysql-en-dokploy
DB_PORT=3306
DB_DATABASE=iedjmh
DB_USERNAME=iedjmh
DB_PASSWORD=tu_password_fuerte
```

> `DB_HOST` debe ser el host interno del servicio MySQL creado en Dokploy.

## 7.2 Recomendadas para producción

```env
APP_ENV=production
APP_DEBUG=false
QUEUE_CONNECTION=database
CACHE_STORE=database
SESSION_DRIVER=database
SESSION_ENCRYPT=true
APP_TIMEZONE=America/Bogota
APP_LOCALE=es
APP_FALLBACK_LOCALE=es
```

## 7.3 Correo (SMTP) recomendado

```env
MAIL_MAILER=smtp
MAIL_HOST=...
MAIL_PORT=587
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=no-reply@tudominio.com
MAIL_FROM_NAME="IED AGROPECUARIA JOSE MARIA HERRERA"
```

## 7.4 Google OAuth (opcional)

Solo si vas a usar login con Google:

```env
GOOGLE_CLIENT_ID=...
GOOGLE_CLIENT_SECRET=...
GOOGLE_REDIRECT_URI=https://tudominio.com/auth/google/callback
```

## 7.5 Generar un `APP_KEY` seguro

Puedes generarlo localmente o en cualquier terminal con PHP:

```bash
php -r "echo 'base64:'.base64_encode(random_bytes(32)).PHP_EOL;"
```

Copia el resultado y pégalo en `APP_KEY` dentro de Dokploy.

### Qué deberías ver

- La app ya no debe fallar por "Missing required env vars".

---

## 8) Dominio y HTTPS en Dokploy

1. En la Application abre `Domains`.
2. Clic en `Create Domain`.
3. Configura:
   - `Host`: `tudominio.com` (y opcionalmente `www.tudominio.com` en otro dominio)
   - `Path`: `/`
   - `Container Port`: `80`
   - `HTTPS`: `ON`
   - `Certificate`: `Let's Encrypt`
4. Guarda.

### Importante

- En Domains, `Container Port = 80` enruta tráfico interno vía Traefik.
- Esto es diferente a publicar puertos manualmente en `Advanced > Ports`.

### Qué deberías ver

- Certificado emitido y sitio accesible por `https://tudominio.com`.

---

## 9) Primer deploy y validación funcional

1. Clic en `Deploy` en la Application.
2. Sigue logs de despliegue hasta finalizar.
3. Valida en este orden:

### 9.1 Salud técnica

- `https://tudominio.com/up` debe responder OK.

### 9.2 Frontend público

- `https://tudominio.com/` carga home.

### 9.3 Panel administrativo

- `https://tudominio.com/admin` carga login de Filament.

### 9.4 Migraciones

- En logs debe verse ejecución de `php artisan migrate --force` sin errores.

### 9.5 Cola de trabajos

- Supervisor debe iniciar `queue-worker` automáticamente.
- Si hay errores de cola, revisa logs (`storage/logs/queue-worker.log` dentro del contenedor).

### Qué deberías ver

- Sitio público operativo, panel admin accesible, sin bucles de error en logs.

---

## 10) Seed inicial opcional y endurecimiento

Este proyecto trae seeders de roles/usuarios. Úsalos solo si necesitas poblar por primera vez.

### 10.1 Seed de roles/usuarios

Puedes ejecutarlo desde Dokploy (Run Command o consola del contenedor):

```bash
php artisan db:seed --class=ShieldRolesAndUsersSeeder --force
```

### 10.2 Seed de contenido demo (opcional)

Por defecto, en producción no carga demo salvo que habilites `SEED_DEMO_CONTENT=true`.

```bash
php artisan db:seed --force
```

### 10.3 Seguridad obligatoria después de seed

- Cambia inmediatamente contraseñas de usuarios sembrados.
- Verifica cuentas administrativas activas.
- Configura correo real para recuperación de contraseña.

> Nota: el seeder define una contraseña por defecto (`pass1234`) para usuarios sembrados. No dejarla en producción.

---

## 11) Operación diaria (actualizaciones)

Flujo recomendado:

1. Hacer cambios en local.
2. Commit y push a `main`.
3. Ir a Dokploy y ejecutar redeploy de la app.
4. Revisar logs.
5. Probar `/up`, `/` y `/admin`.

### Checklist post-deploy

- [ ] `/up` responde.
- [ ] Home pública carga.
- [ ] Admin carga.
- [ ] No hay errores críticos en logs.

### Qué revisar si falla

1. Variables de entorno faltantes o mal escritas.
2. `DB_HOST` incorrecto (host interno de MySQL mal configurado).
3. DNS no propagado todavía.
4. Certificado Let's Encrypt aún en emisión o bloqueado por DNS.

---

## 12) Anexo de troubleshooting (errores comunes)

## Error A: "Missing required env vars"

### Síntoma

- El contenedor se cae al arrancar y logs muestran variables faltantes.

### Solución

1. Revisar variables obligatorias en Dokploy.
2. Guardar cambios.
3. Redeploy.

---

## Error B: "Database is not reachable"

### Síntoma

- El entrypoint agota reintentos esperando DB.

### Solución

1. Verificar que MySQL esté `Running`.
2. Confirmar `DB_HOST` correcto (servicio interno Dokploy).
3. Confirmar `DB_PORT=3306`.
4. Validar usuario/password/database.
5. Redeploy.

---

## Error C: dominio no abre o abre por HTTP sin SSL

### Síntoma

- `https://tudominio.com` no responde o muestra warning SSL.

### Solución

1. Revisar DNS `A` de `@` y `www` apuntando a IP del VPS.
2. Esperar propagación DNS.
3. En Dokploy Domain, confirmar:
   - Host correcto
   - Container Port `80`
   - HTTPS `ON`
   - Certificado `Let's Encrypt`
4. Revisar logs de Traefik/Dokploy.

---

## Error D: 502/503 después de deploy

### Síntoma

- Dominio resuelve, pero la app no responde bien.

### Solución

1. Revisar logs de la app y de despliegue.
2. Confirmar que el proceso terminó en "Application ready. Starting Supervisor".
3. Probar endpoint `/up`.
4. Verificar que no haya errores de caché/config.
5. Redeploy tras corregir variables o DB.

---

## Error E: archivos subidos desaparecen al redeploy

### Síntoma

- Documentos o archivos en `storage` se pierden tras nueva versión.

### Solución

1. En app Dokploy, configurar volumen persistente:
   - mount `/var/www/html/storage`
2. Redeploy.

---

## Validación final (antes de dar por terminado)

- [ ] Dominio funcionando por HTTPS.
- [ ] `/up` responde correctamente.
- [ ] Home pública y `/admin` operativos.
- [ ] Migraciones ejecutadas sin error.
- [ ] Worker de colas activo.
- [ ] DB con volumen persistente.
- [ ] Backup de base de datos configurado.
- [ ] Credenciales por defecto cambiadas.

---

## Referencias oficiales

- Dokploy Installation:
  - <https://docs.dokploy.com/docs/core/installation>
- Dokploy Domains (Others):
  - <https://docs.dokploy.com/docs/core/domains/others>
- Dokploy Databases:
  - <https://docs.dokploy.com/docs/core/databases>
- Hostinger: apuntar dominio al VPS:
  - <https://www.hostinger.com/support/1583227-how-to-point-a-domain-to-your-vps-at-hostinger/>

