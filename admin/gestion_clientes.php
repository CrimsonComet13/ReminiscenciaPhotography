<?php
require_once(__DIR__ . '/../includes/config.php');
require_once(__DIR__ . '/../includes/auth.php');

// Verificar permisos
if (!isAdmin()) {
    header("Location: /login.php");
    exit();
}

// Procesar acciones de aprobación/rechazo
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['aprobar_cliente'])) {
        $prospecto_id = intval($_POST['prospecto_id']);
        try {
            $stmt = $conn->prepare("CALL aprobar_cliente_prospecto(?)");
            $stmt->execute([$prospecto_id]);
            $_SESSION['mensaje_exito'] = "Cliente aprobado exitosamente";
        } catch (PDOException $e) {
            $_SESSION['mensaje_error'] = "Error al aprobar cliente: " . $e->getMessage();
        }
        header("Location: gestion_clientes.php");
        exit();
    }
    
    if (isset($_POST['rechazar_cliente'])) {
        $prospecto_id = intval($_POST['prospecto_id']);
        try {
            $stmt = $conn->prepare("CALL rechazar_cliente_prospecto(?)");
            $stmt->execute([$prospecto_id]);
            $_SESSION['mensaje_exito'] = "Cliente rechazado";
        } catch (PDOException $e) {
            $_SESSION['mensaje_error'] = "Error al rechazar cliente: " . $e->getMessage();
        }
        header("Location: gestion_clientes.php");
        exit();
    }
    
    if (isset($_POST['cambiar_estado'])) {
        $cliente_id = intval($_POST['cliente_id']);
        $nuevo_estado = intval($_POST['nuevo_estado']);
        try {
            $stmt = $conn->prepare("UPDATE usuarios SET activo = ? WHERE id = ? AND rol = 'cliente'");
            $stmt->execute([$nuevo_estado, $cliente_id]);
            $_SESSION['mensaje_exito'] = "Estado del cliente actualizado correctamente";
        } catch (PDOException $e) {
            $_SESSION['mensaje_error'] = "Error al actualizar estado: " . $e->getMessage();
        }
        header("Location: gestion_clientes.php");
        exit();
    }
}

// Procesar búsqueda y filtros
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';
$filtro_estado = isset($_GET['estado']) ? $_GET['estado'] : 'todos';
$pagina = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$por_pagina = 10;

try {
    // Obtener prospectos pendientes
    $sql_prospectos = "SELECT * FROM prospectos_clientes WHERE estado = 'pendiente' ORDER BY fecha_registro DESC";
    $stmt_prospectos = $conn->prepare($sql_prospectos);
    $stmt_prospectos->execute();
    $prospectos = $stmt_prospectos->fetchAll(PDO::FETCH_ASSOC);
    
    // Construir consulta para clientes activos
    $sql = "SELECT * FROM usuarios WHERE rol = 'cliente'";
    $params = [];
    
    // Aplicar filtros
    if (!empty($busqueda)) {
        $sql .= " AND (nombre LIKE ? OR email LIKE ? OR telefono LIKE ?)";
        $param_busqueda = "%$busqueda%";
        $params = array_merge($params, [$param_busqueda, $param_busqueda, $param_busqueda]);
    }
    
    if ($filtro_estado === 'activos') {
        $sql .= " AND activo = 1";
    } elseif ($filtro_estado === 'inactivos') {
        $sql .= " AND activo = 0";
    }
    
    // Contar total de registros
    $stmt_count = $conn->prepare(str_replace('*', 'COUNT(*)', $sql));
    $stmt_count->execute($params);
    $total_clientes = $stmt_count->fetchColumn();
    $total_paginas = ceil($total_clientes / $por_pagina);
    
    // Aplicar paginación
    $offset = ($pagina - 1) * $por_pagina;
    $sql .= " ORDER BY nombre ASC LIMIT " . intval($por_pagina) . " OFFSET " . intval($offset);
    
    // Obtener clientes
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    die("Error al obtener datos: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Gestión de Clientes - Reminiscencia Photography</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
  
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
      padding: 1.5rem;
      box-shadow: var(--shadow-glow);
      transition: all 0.3s ease;
    }

    .glass-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
    }

    /* Buttons */
    .btn-gradient {
      background: var(--primary-gradient);
      border: none;
      color: white;
      padding: 0.75rem 1.5rem;
      border-radius: 12px;
      font-weight: 600;
      transition: all 0.3s ease;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
    }

    .btn-gradient:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
      color: white;
    }

    .btn-success-gradient {
      background: var(--success-gradient);
    }

    .btn-danger-gradient {
      background: var(--danger-gradient);
    }

    .btn-warning-gradient {
      background: var(--warning-gradient);
    }

    /* Tables */
    .table-container {
      background: var(--card-bg);
      backdrop-filter: blur(20px);
      border: 1px solid var(--glass-border);
      border-radius: 20px;
      overflow: hidden;
      box-shadow: var(--shadow-glow);
    }

    .table {
      color: var(--text-primary);
      margin-bottom: 0;
    }

    .table th {
      background: rgba(255, 255, 255, 0.1);
      border: none;
      padding: 1rem;
      font-weight: 600;
      color: var(--text-primary);
    }

    .table td {
      border: none;
      padding: 1rem;
      vertical-align: middle;
      border-bottom: 1px solid var(--glass-border);
    }

    .table tbody tr:hover {
      background: rgba(255, 255, 255, 0.05);
    }

    /* Photo preview */
    .photo-preview {
      width: 60px;
      height: 60px;
      border-radius: 10px;
      object-fit: cover;
      border: 2px solid var(--glass-border);
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .photo-preview:hover {
      transform: scale(1.1);
      border-color: #667eea;
    }

    /* Status badges */
    .status-badge {
      padding: 0.5rem 1rem;
      border-radius: 20px;
      font-size: 0.85rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .status-pendiente {
      background: linear-gradient(135deg, #ffa726, #ff9800);
      color: white;
    }

    .status-activo {
      background: var(--success-gradient);
      color: white;
    }

    .status-inactivo {
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
      border: 1px solid rgba(76, 175, 80, 0.3);
      color: #4caf50;
    }

    .alert-danger {
      background: rgba(244, 67, 54, 0.2);
      border: 1px solid rgba(244, 67, 54, 0.3);
      color: #f44336;
    }

    /* Forms */
    .form-control {
      background: rgba(255, 255, 255, 0.1);
      border: 1px solid var(--glass-border);
      border-radius: 12px;
      color: var(--text-primary);
      padding: 0.75rem 1rem;
    }

    .form-control:focus {
      background: rgba(255, 255, 255, 0.15);
      border-color: #667eea;
      box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.25);
      color: var(--text-primary);
    }

    .form-control::placeholder {
      color: var(--text-secondary);
    }

    /* Modal styles */
    .modal-content {
      background: var(--card-bg);
      backdrop-filter: blur(20px);
      border: 1px solid var(--glass-border);
      border-radius: 20px;
      color: var(--text-primary);
    }

    .modal-header {
      border-bottom: 1px solid var(--glass-border);
    }

    .modal-footer {
      border-top: 1px solid var(--glass-border);
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

    /* Prospecto card styles */
    .prospecto-card {
      border-left: 4px solid #ffa726;
      margin-bottom: 1rem;
    }

    .prospecto-info {
      display: flex;
      align-items: center;
      gap: 1rem;
      margin-bottom: 1rem;
    }

    .prospecto-photo {
      width: 80px;
      height: 80px;
      border-radius: 15px;
      object-fit: cover;
      border: 2px solid var(--glass-border);
    }

    .prospecto-details h5 {
      margin-bottom: 0.5rem;
      color: var(--text-primary);
    }

    .prospecto-details p {
      margin-bottom: 0.25rem;
      color: var(--text-secondary);
      font-size: 0.9rem;
    }

    .prospecto-actions {
      display: flex;
      gap: 0.5rem;
      flex-wrap: wrap;
    }
  </style>
</head>
<body>
  <!-- Sidebar -->
  <?php include(__DIR__ . '/../includes/admin_sidebar.php'); ?>

  <!-- Main Content -->
  <div class="main-content">
    <!-- Page Header -->
    <div class="page-header">
      <h1 class="page-title">Gestión de Clientes</h1>
      <p class="page-subtitle">Administra prospectos y clientes registrados</p>
    </div>

    <!-- Mensajes -->
    <?php if (isset($_SESSION['mensaje_exito'])): ?>
      <div class="alert alert-success">
        <i class="bi bi-check-circle me-2"></i>
        <?php echo $_SESSION['mensaje_exito']; unset($_SESSION['mensaje_exito']); ?>
      </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['mensaje_error'])): ?>
      <div class="alert alert-danger">
        <i class="bi bi-exclamation-triangle me-2"></i>
        <?php echo $_SESSION['mensaje_error']; unset($_SESSION['mensaje_error']); ?>
      </div>
    <?php endif; ?>

    <!-- Prospectos Pendientes -->
    <?php if (!empty($prospectos)): ?>
    <div class="glass-card mb-4">
      <h3 class="mb-3">
        <i class="bi bi-clock-history me-2"></i>
        Solicitudes Pendientes (<?php echo count($prospectos); ?>)
      </h3>
      
      <?php foreach ($prospectos as $prospecto): ?>
      <div class="prospecto-card glass-card">
        <div class="prospecto-info">
          <?php if (!empty($prospecto['foto_path']) && file_exists(__DIR__ . '/../' . $prospecto['foto_path'])): ?>
            <img src="../<?php echo htmlspecialchars($prospecto['foto_path']); ?>" 
                 alt="Foto de <?php echo htmlspecialchars($prospecto['nombre']); ?>" 
                 class="prospecto-photo"
                 onclick="showPhotoModal('../<?php echo htmlspecialchars($prospecto['foto_path']); ?>', '<?php echo htmlspecialchars($prospecto['nombre']); ?>')">
          <?php else: ?>
            <div class="prospecto-photo d-flex align-items-center justify-content-center" style="background: var(--glass-border);">
              <i class="bi bi-person-fill" style="font-size: 2rem; color: var(--text-secondary);"></i>
            </div>
          <?php endif; ?>
          
          <div class="prospecto-details flex-grow-1">
            <h5><?php echo htmlspecialchars($prospecto['nombre']); ?></h5>
            <p><i class="bi bi-envelope me-1"></i> <?php echo htmlspecialchars($prospecto['email']); ?></p>
            <?php if (!empty($prospecto['telefono'])): ?>
              <p><i class="bi bi-telephone me-1"></i> <?php echo htmlspecialchars($prospecto['telefono']); ?></p>
            <?php endif; ?>
            <p><i class="bi bi-calendar me-1"></i> Registrado: <?php echo date('d/m/Y H:i', strtotime($prospecto['fecha_registro'])); ?></p>
          </div>
        </div>
        
        <div class="prospecto-actions">
          <form method="POST" style="display: inline;">
            <input type="hidden" name="prospecto_id" value="<?php echo $prospecto['id']; ?>">
            <button type="submit" name="aprobar_cliente" class="btn btn-success-gradient btn-sm" 
                    onclick="return confirm('¿Está seguro de aprobar este cliente?')">
              <i class="bi bi-check-lg me-1"></i> Aprobar
            </button>
          </form>
          
          <form method="POST" style="display: inline;">
            <input type="hidden" name="prospecto_id" value="<?php echo $prospecto['id']; ?>">
            <button type="submit" name="rechazar_cliente" class="btn btn-danger-gradient btn-sm"
                    onclick="return confirm('¿Está seguro de rechazar este cliente?')">
              <i class="bi bi-x-lg me-1"></i> Rechazar
            </button>
          </form>
          
          <button type="button" class="btn btn-gradient btn-sm" 
                  onclick="showClientDetails(<?php echo htmlspecialchars(json_encode($prospecto)); ?>)">
            <i class="bi bi-eye me-1"></i> Ver Detalles
          </button>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Filtros y Búsqueda -->
    <div class="glass-card mb-4">
      <form method="GET" class="row g-3">
        <div class="col-md-4">
          <input type="text" class="form-control" name="busqueda" 
                 placeholder="Buscar por nombre, email o teléfono..." 
                 value="<?php echo htmlspecialchars($busqueda); ?>">
        </div>
        <div class="col-md-3">
          <select class="form-control" name="estado">
            <option value="todos" <?php echo $filtro_estado === 'todos' ? 'selected' : ''; ?>>Todos los estados</option>
            <option value="activos" <?php echo $filtro_estado === 'activos' ? 'selected' : ''; ?>>Activos</option>
            <option value="inactivos" <?php echo $filtro_estado === 'inactivos' ? 'selected' : ''; ?>>Inactivos</option>
          </select>
        </div>
        <div class="col-md-2">
          <button type="submit" class="btn btn-gradient w-100">
            <i class="bi bi-search me-1"></i> Buscar
          </button>
        </div>
        <div class="col-md-3">
          <a href="gestion_clientes.php" class="btn btn-outline-light w-100">
            <i class="bi bi-arrow-clockwise me-1"></i> Limpiar
          </a>
        </div>
      </form>
    </div>

    <!-- Tabla de Clientes -->
    <div class="table-container">
      <div class="table-responsive">
        <table class="table">
          <thead>
            <tr>
              <th>Foto</th>
              <th>Cliente</th>
              <th>Email</th>
              <th>Teléfono</th>
              <th>Estado</th>
              <th>Registro</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($clientes)): ?>
              <tr>
                <td colspan="7" class="text-center py-4">
                  <i class="bi bi-inbox" style="font-size: 3rem; color: var(--text-secondary);"></i>
                  <p class="mt-2 mb-0" style="color: var(--text-secondary);">No se encontraron clientes</p>
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($clientes as $cliente): ?>
                <tr>
                  <td>
                    <?php if (!empty($cliente['foto_id']) && file_exists(__DIR__ . '/../' . $cliente['foto_id'])): ?>
                      <img src="../<?php echo htmlspecialchars($cliente['foto_id']); ?>" 
                           alt="Foto de <?php echo htmlspecialchars($cliente['nombre']); ?>" 
                           class="photo-preview"
                           onclick="showPhotoModal('../<?php echo htmlspecialchars($cliente['foto_id']); ?>', '<?php echo htmlspecialchars($cliente['nombre']); ?>')">
                    <?php else: ?>
                      <div class="photo-preview d-flex align-items-center justify-content-center" style="background: var(--glass-border);">
                        <i class="bi bi-person-fill" style="color: var(--text-secondary);"></i>
                      </div>
                    <?php endif; ?>
                  </td>
                  <td>
                    <strong><?php echo htmlspecialchars($cliente['nombre']); ?></strong>
                  </td>
                  <td><?php echo htmlspecialchars($cliente['email']); ?></td>
                  <td><?php echo htmlspecialchars($cliente['telefono'] ?? 'No especificado'); ?></td>
                  <td>
                    <span class="status-badge <?php echo $cliente['activo'] ? 'status-activo' : 'status-inactivo'; ?>">
                      <?php echo $cliente['activo'] ? 'Activo' : 'Inactivo'; ?>
                    </span>
                  </td>
                  <td><?php echo date('d/m/Y', strtotime($cliente['fecha_registro'])); ?></td>
                  <td>
                    <div class="d-flex gap-2">
                      <a href="detalles_cliente.php?id=<?php echo $cliente['id']; ?>" 
                         class="btn btn-gradient btn-sm">
                        <i class="bi bi-eye"></i>
                      </a>
                      
                      <form method="POST" style="display: inline;">
                        <input type="hidden" name="cliente_id" value="<?php echo $cliente['id']; ?>">
                        <input type="hidden" name="nuevo_estado" value="<?php echo $cliente['activo'] ? 0 : 1; ?>">
                        <button type="submit" name="cambiar_estado" 
                                class="btn <?php echo $cliente['activo'] ? 'btn-warning-gradient' : 'btn-success-gradient'; ?> btn-sm"
                                onclick="return confirm('¿Está seguro de cambiar el estado de este cliente?')">
                          <i class="bi bi-<?php echo $cliente['activo'] ? 'pause' : 'play'; ?>"></i>
                        </button>
                      </form>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Paginación -->
    <?php if ($total_paginas > 1): ?>
      <nav class="mt-4">
        <ul class="pagination justify-content-center">
          <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
            <li class="page-item <?php echo $i === $pagina ? 'active' : ''; ?>">
              <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => $i])); ?>">
                <?php echo $i; ?>
              </a>
            </li>
          <?php endfor; ?>
        </ul>
      </nav>
    <?php endif; ?>
  </div>

  <!-- Modal para ver foto -->
  <div class="modal fade" id="photoModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="photoModalTitle">Foto de Identificación</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body text-center">
          <img id="photoModalImage" src="" alt="Foto" class="img-fluid rounded">
        </div>
      </div>
    </div>
  </div>

  <!-- Modal para detalles del prospecto -->
  <div class="modal fade" id="detailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Detalles del Prospecto</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body" id="detailsModalBody">
          <!-- Contenido dinámico -->
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  
  <script>
    function showPhotoModal(photoSrc, clientName) {
      document.getElementById('photoModalImage').src = photoSrc;
      document.getElementById('photoModalTitle').textContent = 'Foto de ' + clientName;
      new bootstrap.Modal(document.getElementById('photoModal')).show();
    }

    function showClientDetails(prospecto) {
      const modalBody = document.getElementById('detailsModalBody');
      
      let photoHtml = '';
      if (prospecto.foto_path) {
        photoHtml = `
          <div class="text-center mb-3">
            <img src="../${prospecto.foto_path}" alt="Foto de ${prospecto.nombre}" 
                 class="img-fluid rounded" style="max-width: 200px;">
          </div>
        `;
      }
      
      modalBody.innerHTML = `
        ${photoHtml}
        <div class="row">
          <div class="col-md-6">
            <h6>Información Personal</h6>
            <p><strong>Nombre:</strong> ${prospecto.nombre}</p>
            <p><strong>Email:</strong> ${prospecto.email}</p>
            <p><strong>Teléfono:</strong> ${prospecto.telefono || 'No especificado'}</p>
          </div>
          <div class="col-md-6">
            <h6>Información de Registro</h6>
            <p><strong>Estado:</strong> <span class="status-badge status-pendiente">${prospecto.estado}</span></p>
            <p><strong>Fecha de registro:</strong> ${new Date(prospecto.fecha_registro).toLocaleString('es-ES')}</p>
          </div>
        </div>
      `;
      
      new bootstrap.Modal(document.getElementById('detailsModal')).show();
    }
  </script>
</body>
</html>

