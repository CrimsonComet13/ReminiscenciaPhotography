# Reminiscencia Photography

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

