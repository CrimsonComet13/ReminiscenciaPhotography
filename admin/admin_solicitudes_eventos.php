<?php
require_once(__DIR__ . '/../includes/config.php');
require_once(__DIR__ . '/../includes/auth.php');

// Solo administradores pueden acceder
if (!isAdmin()) {
    header("Location: /login.php");
    exit();
}

$error = '';
$mensaje_exito = '';

// Procesar aprobación o rechazo de solicitudes
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['aprobar_solicitud'])) {
        $solicitud_id = intval($_POST['solicitud_id']);
        $evento_id = intval($_POST['evento_id']);
        $colaborador_id = intval($_POST['colaborador_id']);

        try {
            $conn->beginTransaction();

            // 1. Actualizar el estado de la solicitud a 'aprobado'
            $stmt = $conn->prepare("UPDATE solicitudes_colaborador SET estado = 'aprobado' WHERE id = ?");
            $stmt->execute([$solicitud_id]);

            // 2. Insertar el colaborador en la tabla evento_colaborador
            $stmt = $conn->prepare("INSERT INTO evento_colaborador (evento_id, colaborador_id) VALUES (?, ?)");
            $stmt->execute([$evento_id, $colaborador_id]);

            $conn->commit();
            $_SESSION['mensaje_exito'] = "Solicitud aprobada y colaborador asignado al evento.";
        } catch (PDOException $e) {
            $conn->rollBack();
            error_log("Error al aprobar solicitud: " . $e->getMessage());
            $_SESSION['mensaje_error'] = "Error al aprobar la solicitud: " . $e->getMessage();
        }
        header("Location: admin_solicitudes_eventos.php");
        exit();
    }

    if (isset($_POST['rechazar_solicitud'])) {
        $solicitud_id = intval($_POST['solicitud_id']);

        try {
            $stmt = $conn->prepare("UPDATE solicitudes_colaborador SET estado = 'rechazado' WHERE id = ?");
            $stmt->execute([$solicitud_id]);
            $_SESSION['mensaje_exito'] = "Solicitud rechazada.";
        } catch (PDOException $e) {
            error_log("Error al rechazar solicitud: " . $e->getMessage());
            $_SESSION['mensaje_error'] = "Error al rechazar la solicitud: " . $e->getMessage();
        }
        header("Location: admin_solicitudes_eventos.php");
        exit();
    }
}

// Recuperar mensajes de sesión
if (isset($_SESSION['mensaje_exito'])) {
    $mensaje_exito = $_SESSION['mensaje_exito'];
    unset($_SESSION['mensaje_exito']);
}
if (isset($_SESSION['mensaje_error'])) {
    $error = $_SESSION['mensaje_error'];
    unset($_SESSION['mensaje_error']);
}

// Obtener solicitudes de eventos pendientes
try {
    $stmt = $conn->prepare("
        SELECT 
            sc.id AS solicitud_id, 
            sc.estado AS solicitud_estado, 
            sc.fecha_solicitud, 
            e.id AS evento_id, 
            e.nombre AS evento_nombre, 
            e.fecha_evento, 
            e.tipo AS evento_tipo, 
            u.id AS colaborador_id, 
            u.nombre AS colaborador_nombre, 
            u.email AS colaborador_email, 
            u.tipo_colaborador 
        FROM solicitudes_colaborador sc
        JOIN eventos e ON sc.evento_id = e.id
        JOIN usuarios u ON sc.colaborador_id = u.id
        WHERE sc.estado = 'pendiente'
        ORDER BY sc.fecha_solicitud DESC
    ");
    $stmt->execute();
    $solicitudes_pendientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Error al obtener solicitudes de eventos: " . $e->getMessage());
    $error = "Error al cargar las solicitudes. Por favor, inténtelo de nuevo más tarde.";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestión de Solicitudes de Eventos - Reminiscencia Photography</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
      --warning-gradient: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
      --danger-gradient: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
      --info-gradient: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
      --dark-bg: #0f1419;
      --card-bg: rgba(255, 255, 255, 0.08);
      --glass-border: rgba(255, 255, 255, 0.18);
      --text-primary: #ffffff;
      --text-secondary: rgba(255, 255, 255, 0.7);
      --shadow-glow: 0 8px 32px rgba(0, 0, 0, 0.3);
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Inter', sans-serif;
      background: var(--dark-bg);
      background-image: 
        radial-gradient(circle at 20% 50%, rgba(120, 119, 198, 0.3) 0%, transparent 50%),
        radial-gradient(circle at 80% 20%, rgba(255, 119, 198, 0.3) 0%, transparent 50%),
        radial-gradient(circle at 40% 80%, rgba(120, 219, 255, 0.3) 0%, transparent 50%);
      min-height: 100vh;
      color: var(--text-primary);
      overflow-x: hidden;
    }

    /* Sidebar Styles */
    .sidebar {
      position: fixed;
      left: 0;
      top: 0;
      height: 100vh;
      width: 280px;
      background: rgba(15, 20, 25, 0.9);
      backdrop-filter: blur(20px);
      border-right: 1px solid var(--glass-border);
      z-index: 1000;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .sidebar-header {
      padding: 2rem 1.5rem;
      border-bottom: 1px solid var(--glass-border);
    }

    .logo {
      font-size: 1.5rem;
      font-weight: 700;
      background: var(--primary-gradient);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    .nav-menu {
      padding: 1rem 0;
    }

    .nav-item {
      margin: 0.5rem 1rem;
    }

    .nav-link {
      display: flex;
      align-items: center;
      padding: 1rem 1.5rem;
      color: var(--text-secondary);
      text-decoration: none;
      border-radius: 12px;
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
    }

    .nav-link::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: var(--primary-gradient);
      transition: left 0.3s ease;
      z-index: -1;
    }

    .nav-link:hover::before,
    .nav-link.active::before {
      left: 0;
    }

    .nav-link:hover,
    .nav-link.active {
      color: var(--text-primary);
      transform: translateX(5px);
    }

    .nav-link i {
      margin-right: 1rem;
      font-size: 1.2rem;
    }

    /* Main Content */
    .main-content {
      margin-left: 280px;
      padding: 2rem;
      min-height: 100vh;
    }

    .page-header {
      background: var(--card-bg);
      backdrop-filter: blur(20px);
      border: 1px solid var(--glass-border);
      border-radius: 20px;
      padding: 2rem;
      margin-bottom: 2rem;
      box-shadow: var(--shadow-glow);
    }

    .page-title {
      font-size: 2.5rem;
      font-weight: 700;
      background: var(--primary-gradient);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      margin-bottom: 0.5rem;
    }

    .page-subtitle {
      color: var(--text-secondary);
      font-size: 1.1rem;
    }

    /* Cards */
    .glass-card {
      background: var(--card-bg);
      backdrop-filter: blur(20px);
      border: 1px solid var(--glass-border);
      border-radius: 20px;
      padding: 2rem;
      margin-bottom: 2rem;
      box-shadow: var(--shadow-glow);
    }

    /* Buttons */
    .btn {
      border: none;
      border-radius: 12px;
      padding: 0.75rem 1.5rem;
      font-weight: 600;
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
    }

    .btn::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: rgba(255, 255, 255, 0.1);
      transition: left 0.3s ease;
    }

    .btn:hover::before {
      left: 100%;
    }

    .btn-gradient {
      background: var(--primary-gradient);
      color: white;
    }

    .btn-success-gradient {
      background: var(--success-gradient);
      color: white;
    }

    .btn-danger-gradient {
      background: var(--danger-gradient);
      color: white;
    }

    /* Table Styles */
    .table-container {
      background: var(--card-bg);
      backdrop-filter: blur(20px);
      border: 1px solid var(--glass-border);
      border-radius: 20px;
      overflow: hidden;
      box-shadow: var(--shadow-glow);
    }

    .table {
      margin: 0;
      color: var(--text-primary);
    }

    .table th {
      background: rgba(255, 255, 255, 0.05);
      border: none;
      padding: 1.5rem 1rem;
      font-weight: 600;
      color: var(--text-primary);
      border-bottom: 1px solid var(--glass-border);
    }

    .table td {
      border: none;
      padding: 1rem;
      border-bottom: 1px solid rgba(255, 255, 255, 0.05);
      vertical-align: middle;
    }

    .table tbody tr:hover {
      background: rgba(255, 255, 255, 0.02);
    }

    /* Status Badges */
    .status-badge {
      padding: 0.5rem 1rem;
      border-radius: 20px;
      font-size: 0.85rem;
      font-weight: 600;
    }

    .status-pendiente {
      background: var(--warning-gradient);
      color: var(--dark-bg);
    }

    .status-aprobado {
      background: var(--success-gradient);
      color: white;
    }

    .status-rechazado {
      background: var(--danger-gradient);
      color: white;
    }

    /* Alerts */
    .alert {
      border: none;
      border-radius: 15px;
      padding: 1rem 1.5rem;
      margin-bottom: 1.5rem;
      backdrop-filter: blur(20px);
    }

    .alert-success {
      background: rgba(76, 175, 80, 0.2);
      border-left: 4px solid #4caf50;
      color: #4caf50;
    }

    .alert-danger {
      background: rgba(244, 67, 54, 0.2);
      border-left: 4px solid #f44336;
      color: #f44336;
    }

    /* Responsive */
    @media (max-width: 768px) {
      .sidebar {
        transform: translateX(-100%);
      }
      
      .sidebar.show {
        transform: translateX(0);
      }
      
      .main-content {
        margin-left: 0;
        padding: 1rem;
      }
      
      .page-title {
        font-size: 2rem;
      }
      
      .table-responsive {
        border-radius: 20px;
      }
    }

    .menu-toggle {
      display: none;
      position: fixed;
      top: 1rem;
      left: 1rem;
      z-index: 1100;
      background: var(--primary-gradient);
      color: white;
      border: none;
      border-radius: 50%;
      width: 50px;
      height: 50px;
      font-size: 1.5rem;
      box-shadow: var(--shadow-glow);
    }

    @media (max-width: 768px) {
      .menu-toggle {
        display: block;
      }
    }
  </style>
</head>
<body>
  <button class="menu-toggle" id="sidebarToggle">
    <i class="bi bi-list"></i>
  </button>
  <!-- Sidebar -->
  <?php include(__DIR__ . '/../includes/admin_sidebar.php'); ?>

  <!-- Main Content -->
  <div class="main-content">
    <!-- Page Header -->
    <div class="page-header">
      <h1 class="page-title">Gestión de Solicitudes de Eventos</h1>
      <p class="page-subtitle">Revisa y gestiona las solicitudes de colaboradores para eventos</p>
    </div>

    <!-- Mensajes -->
    <?php if (!empty($mensaje_exito)): ?>
      <div class="alert alert-success">
        <i class="bi bi-check-circle me-2"></i>
        <?php echo htmlspecialchars($mensaje_exito); ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger">
        <i class="bi bi-exclamation-triangle me-2"></i>
        <?php echo htmlspecialchars($error); ?>
      </div>
    <?php endif; ?>

    <!-- Solicitudes Pendientes -->
    <div class="glass-card">
      <h3 class="mb-4">
        <i class="bi bi-hourglass-split me-2"></i>
        Solicitudes Pendientes (<?php echo count($solicitudes_pendientes); ?>)
      </h3>
      
      <?php if (empty($solicitudes_pendientes)): ?>
        <div class="text-center py-4">
          <i class="bi bi-check-circle" style="font-size: 3rem; opacity: 0.3;"></i>
          <p class="mt-3">No hay solicitudes de eventos pendientes en este momento.</p>
        </div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>ID Solicitud</th>
                <th>Evento</th>
                <th>Fecha Evento</th>
                <th>Colaborador</th>
                <th>Email Colaborador</th>
                <th>Tipo Colaborador</th>
                <th>Fecha Solicitud</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($solicitudes_pendientes as $solicitud): ?>
                <tr>
                  <td><?php echo htmlspecialchars($solicitud['solicitud_id']); ?></td>
                  <td>
                    <strong><?php echo htmlspecialchars($solicitud['evento_nombre']); ?></strong><br>
                    <small class="text-secondary"><?php echo ucfirst($solicitud['evento_tipo']); ?></small>
                  </td>
                  <td><?php echo date('d/m/Y', strtotime($solicitud['fecha_evento'])); ?></td>
                  <td>
                    <strong><?php echo htmlspecialchars($solicitud['colaborador_nombre']); ?></strong>
                  </td>
                  <td><?php echo htmlspecialchars($solicitud['colaborador_email']); ?></td>
                  <td><?php echo ucfirst($solicitud['tipo_colaborador']); ?></td>
                  <td><?php echo date('d/m/Y H:i', strtotime($solicitud['fecha_solicitud'])); ?></td>
                  <td>
                    <form method="POST" style="display: inline-block; margin-right: 5px;">
                      <input type="hidden" name="solicitud_id" value="<?php echo $solicitud['solicitud_id']; ?>">
                      <input type="hidden" name="evento_id" value="<?php echo $solicitud['evento_id']; ?>">
                      <input type="hidden" name="colaborador_id" value="<?php echo $solicitud['colaborador_id']; ?>">
                      <button type="submit" name="aprobar_solicitud" class="btn btn-success-gradient btn-sm"
                              onclick="return confirm('¿Está seguro de aprobar esta solicitud? Esto asignará al colaborador al evento.')">
                        <i class="bi bi-check-lg"></i> Aprobar
                      </button>
                    </form>
                    <form method="POST" style="display: inline-block;">
                      <input type="hidden" name="solicitud_id" value="<?php echo $solicitud['solicitud_id']; ?>">
                      <button type="submit" name="rechazar_solicitud" class="btn btn-danger-gradient btn-sm"
                              onclick="return confirm('¿Está seguro de rechazar esta solicitud?')">
                        <i class="bi bi-x-lg"></i> Rechazar
                      </button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Toggle sidebar
    document.getElementById('sidebarToggle').addEventListener('click', function() {
      const sidebar = document.querySelector('.sidebar');
      sidebar.classList.toggle('show');
      
      // Cambiar ícono según estado
      const icon = this.querySelector('i');
      if (sidebar.classList.contains('show')) {
        icon.classList.remove('bi-list');
        icon.classList.add('bi-x');
      } else {
        icon.classList.remove('bi-x');
        icon.classList.add('bi-list');
      }
    });

    // Cerrar menú al hacer clic fuera en móviles
    document.addEventListener('click', function(event) {
      const sidebar = document.querySelector('.sidebar');
      const toggleBtn = document.getElementById('sidebarToggle');
      
      if (window.innerWidth <= 768 && 
          sidebar.classList.contains('show') &&
          !sidebar.contains(event.target) &&
          event.target !== toggleBtn && 
          !toggleBtn.contains(event.target)) {
        sidebar.classList.remove('show');
        const icon = toggleBtn.querySelector('i');
        icon.classList.remove('bi-x');
        icon.classList.add('bi-list');
      }
    });
  </script>
</body>
</html>


