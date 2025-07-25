
# 📸 Reminiscencia Photography

**Reminiscencia Photography** es una plataforma web especializada en la gestión de eventos fotográficos como bodas, XV años, graduaciones, entre otros. El sistema está diseñado para facilitar la interacción entre clientes, colaboradores (fotógrafos y staff), y administradores, permitiendo una experiencia fluida y profesional de principio a fin.

---

## 🧩 Estructura General del Proyecto

### Archivos Principales

- `index.php` — Página principal del sitio.
- `admin_login.php` — Inicio de sesión para administradores.
- `login_cliente.php` — Inicio de sesión para clientes.
- `login_colaborador.php` — Inicio de sesión para colaboradores.
- `register_cliente.php` — Registro de nuevos clientes.
- `register_colaborador.php` — Registro de nuevos colaboradores.
- `agendar_llamada.php` — Formulario para agendar una llamada informativa.
- `ecommerce.html` — Página estática o integrada para mostrar servicios/productos.
- `facturacion.php` — Acceso a documentos de facturación.
- `aviso_privacidad.php` — Documento legal de privacidad.
- `terminos_condiciones.php` — Términos y condiciones del servicio.
- `logout.php` — Cierre de sesión.
- `verificar_identidad.php` — Proceso de verificación de identidad o email.

---

## 📁 Estructura de Carpetas

```bash
/
├── admin/               # Archivos y panel del administrador
├── cliente/             # Panel del cliente autenticado
├── colaborador/         # Panel del colaborador autenticado
├── assets/              # Recursos estáticos: CSS, JS, imágenes
├── includes/            # Funciones PHP, DB connections, templates
├── uploads/             # Archivos subidos por los usuarios
├── logs/                # Logs de actividad y errores
├── vendor/              # Dependencias de Composer (autoloader, libs)
```

---

## 🔐 Credenciales de Acceso — Ambiente de Pruebas

> ⚠️ **Importante:** Estas credenciales están destinadas exclusivamente para ambientes de desarrollo o demostración. No utilizar en producción. Se recomienda cambiar o deshabilitar estos accesos una vez terminado el entorno de pruebas.

### 👑 Administrador
Acceso completo al sistema. Puede gestionar usuarios, eventos, llamadas, prospectos y notificaciones.

| Campo         | Valor                        |
|---------------|------------------------------|
| **Usuario**   | `admin@reminiscencia.com`    |
| **Contraseña**| `password`                   |
🔗 Accede desde: `admin_login.php`

---

### 👤 Cliente
Rol para clientes que desean agendar y gestionar eventos.

| Campo         | Valor                       |
|---------------|-----------------------------|
| **Usuario**   | `am_ibz2005@gmail.com`      |
| **Contraseña**| `12345678`                  |
🔗 Accede desde: `login_cliente.php`

---

### 🤝 Colaborador
Rol para fotógrafos, editores u otros perfiles que colaboran en eventos.

| Campo         | Valor                        |
|---------------|------------------------------|
| **Usuario**   | `Alg_Juan23223@gmail.com`    |
| **Contraseña**| `12345678`                   |
🔗 Accede desde: `login_colaborador.php`

---

## 📝 Notas Adicionales

- Todos los roles requieren verificación de identidad tras iniciar sesión.
- Para pruebas de funcionalidad, se recomienda utilizar sesiones independientes o navegación en modo incógnito.
- En caso de errores, revisar el archivo `logs/` para rastrear actividad.

---

*Este documento forma parte de la documentación técnica del proyecto.*  
*Última actualización: `{{Fecha del despliegue o revisión}}`
