<?php
// Iniciar sesión de forma segura
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'use_strict_mode' => true
    ]);
}

require_once(__DIR__ . '/../includes/config.php');
require_once(__DIR__ . '/../includes/auth.php');

// Solo colaboradores pueden acceder
if (!isCollaborator()) {
    header("Location: /login.php");
    exit();
}

// Generar token CSRF si no existe
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Obtener información del colaborador
$user_id = $_SESSION['user_id'];
$error = '';
$mensaje_exito = '';

try {
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE id = :id");
    $stmt->bindParam(':id', $user_id);
    $stmt->execute();
    $colaborador = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Obtener eventos asignados
    $stmt = $conn->prepare("SELECT e.*, u.nombre as cliente_nombre 
                          FROM eventos e 
                          JOIN usuarios u ON e.cliente_id = u.id
                          JOIN evento_colaborador ec ON e.id = ec.evento_id
                          WHERE ec.colaborador_id = :colaborador_id
                          ORDER BY e.fecha_evento ASC");
    $stmt->bindParam(':colaborador_id', $user_id);
    $stmt->execute();
    $eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Obtener eventos disponibles para solicitar
    $stmt = $conn->prepare("SELECT e.*, u.nombre as cliente_nombre 
                          FROM eventos e
                          JOIN usuarios u ON e.cliente_id = u.id
                          WHERE e.id NOT IN (
                              SELECT evento_id FROM evento_colaborador WHERE colaborador_id = :colaborador_id
                          )
                          AND e.estado = 'pendiente'
                          AND e.fecha_evento >= CURDATE()
                          ORDER BY e.fecha_evento ASC");
    $stmt->bindParam(':colaborador_id', $user_id);
    $stmt->execute();
    $eventos_disponibles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    error_log("Error en dashboard: " . $e->getMessage());
    $error = "Error al obtener datos. Por favor, inténtelo de nuevo más tarde.";
}

// Procesar solicitud para trabajar en un evento
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['solicitar_evento'])) {
    // Validar token CSRF
    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = 'Token de seguridad inválido. Por favor, recargue la página e intente nuevamente.';
        // Regenerar token para el próximo intento
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    } else {
        $evento_id = $_POST['evento_id'];
        try {
            $stmt = $conn->prepare("INSERT INTO solicitudes_colaborador (evento_id, colaborador_id, estado) 
                                  VALUES (:evento_id, :colaborador_id, 'pendiente')");
            $stmt->bindParam(':evento_id', $evento_id);
            $stmt->bindParam(':colaborador_id', $user_id);
            $stmt->execute();
            
            $mensaje_exito = "Tu solicitud para trabajar en este evento ha sido enviada.";
            // Redirección segura para evitar reenvío de formulario
            header("Location: dashboard.php");
            exit();
        } catch(PDOException $e) {
            error_log("Error en solicitud evento: " . $e->getMessage());
            $error = "Error al enviar la solicitud. Por favor, inténtelo de nuevo más tarde.";
        }
    }
}

// Procesar actualización de portafolio
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['actualizar_portfolio'])) {
    // Validar token CSRF
    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = 'Token de seguridad inválido. Por favor, recargue la página e intente nuevamente.';
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    } else {
        try {
            // Verificar que se haya subido un archivo
            if (empty($_FILES['nuevo_portfolio']['name'])) {
                $error = 'Debe seleccionar un archivo de portafolio.';
            } else {
                // Crear directorio si no existe
                $portfolio_dir = __DIR__ . '/../uploads/portfolios/';
                if (!is_dir($portfolio_dir)) {
                    mkdir($portfolio_dir, 0777, true);
                }
                
                // Validar tipo de archivo
                $allowed_extensions = ['pdf', 'zip', 'rar'];
                $file_extension = strtolower(pathinfo($_FILES['nuevo_portfolio']['name'], PATHINFO_EXTENSION));
                
                if (!in_array($file_extension, $allowed_extensions)) {
                    $error = 'Formato de portafolio no válido. Use PDF, ZIP o RAR.';
                } elseif ($_FILES['nuevo_portfolio']['size'] > 50 * 1024 * 1024) { // 50MB
                    $error = 'El portafolio es demasiado grande (máximo 50MB).';
                } else {
                    // Eliminar archivo anterior si existe
                    if (!empty($colaborador['portfolio_path']) && file_exists(__DIR__ . '/../' . $colaborador['portfolio_path'])) {
                        unlink(__DIR__ . '/../' . $colaborador['portfolio_path']);
                    }
                    
                    // Generar nombre único para el archivo
                    $portfolio_nombre = uniqid('portfolio_colaborador_') . '.' . $file_extension;
                    $portfolio_ruta = $portfolio_dir . $portfolio_nombre;
                    $portfolio_db_path = 'uploads/portfolios/' . $portfolio_nombre;
                    
                    if (move_uploaded_file($_FILES['nuevo_portfolio']['tmp_name'], $portfolio_ruta)) {
                        // Actualizar base de datos
                        $stmt = $conn->prepare("UPDATE usuarios SET portfolio_path = :portfolio_path WHERE id = :id");
                        $stmt->bindParam(':portfolio_path', $portfolio_db_path);
                        $stmt->bindParam(':id', $user_id);
                        $stmt->execute();
                        
                        // Actualizar datos locales
                        $colaborador['portfolio_path'] = $portfolio_db_path;
                        
                        $mensaje_exito = 'Portafolio actualizado exitosamente.';
                    } else {
                        $error = 'Error al subir el archivo. Por favor, inténtelo de nuevo.';
                    }
                }
            }
        } catch(PDOException $e) {
            error_log("Error actualizando portafolio: " . $e->getMessage());
            $error = "Error al actualizar el portafolio. Por favor, inténtelo de nuevo más tarde.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Colaborador - Reminiscencia Photography</title>
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
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .user-info {
      padding: 1rem 1.5rem;
      border-bottom: 1px solid var(--glass-border);
    }

    .user-name {
      font-weight: 600;
      margin-bottom: 0.25rem;
    }

    .user-role {
      color: var(--text-secondary);
      font-size: 0.9rem;
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

    .main-content {
      margin-left: 280px;
      padding: 2rem;
      min-height: 100vh;
    }

    .page-header {
      margin-bottom: 2rem;
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

    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 1.5rem;
      margin-bottom: 2rem;
    }

    .stat-card {
      background: var(--card-bg);
      backdrop-filter: blur(20px);
      border: 1px solid var(--glass-border);
      border-radius: 20px;
      padding: 2rem;
      position: relative;
      overflow: hidden;
      transition: all 0.3s ease;
    }

    .stat-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 4px;
      height: 100%;
      background: var(--primary-gradient);
    }

    .stat-card:hover {
      transform: translateY(-5px);
      box-shadow: var(--shadow-glow);
    }

    .stat-card.success::before {
      background: var(--success-gradient);
    }

    .stat-card.warning::before {
      background: var(--warning-gradient);
    }

    .stat-card.info::before {
      background: var(--info-gradient);
    }

    .stat-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 1rem;
    }

    .stat-icon {
      width: 50px;
      height: 50px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.5rem;
      background: var(--primary-gradient);
    }

    .stat-card.success .stat-icon {
      background: var(--success-gradient);
    }

    .stat-card.warning .stat-icon {
      background: var(--warning-gradient);
    }

    .stat-card.info .stat-icon {
      background: var(--info-gradient);
    }

    .stat-value {
      font-size: 2.5rem;
      font-weight: 700;
      margin-bottom: 0.5rem;
    }

    .stat-label {
      color: var(--text-secondary);
      font-size: 0.9rem;
      margin-bottom: 1rem;
    }

    .stat-link {
      color: var(--text-primary);
      text-decoration: none;
      font-weight: 500;
      display: flex;
      align-items: center;
      gap: 0.5rem;
      transition: all 0.3s ease;
    }

    .stat-link:hover {
      color: var(--text-primary);
      transform: translateX(5px);
    }

    .content-grid {
      display: grid;
      grid-template-columns: 1fr;
      gap: 2rem;
    }

    .card {
      background: var(--card-bg);
      backdrop-filter: blur(20px);
      border: 1px solid var(--glass-border);
      border-radius: 20px;
      overflow: hidden;
    }

    .card-header {
      padding: 1.5rem 2rem;
      border-bottom: 1px solid var(--glass-border);
      background: rgba(255, 255, 255, 0.02);
    }

    .card-title {
      font-size: 1.3rem;
      font-weight: 600;
      margin: 0;
    }

    .card-body {
      padding: 2rem;
    }

    .event-card {
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid var(--glass-border);
      border-radius: 15px;
      padding: 1.5rem;
      margin-bottom: 1rem;
      transition: all 0.3s ease;
    }

    .event-card:hover {
      background: rgba(255, 255, 255, 0.08);
      transform: translateY(-2px);
    }

    .event-card-title {
      font-size: 1.2rem;
      font-weight: 600;
      margin-bottom: 0.5rem;
    }

    .event-card-subtitle {
      color: var(--text-secondary);
      margin-bottom: 1rem;
    }

    .event-card-detail {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      margin-bottom: 0.5rem;
      color: var(--text-secondary);
    }

    .event-card-footer {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-top: 1rem;
      padding-top: 1rem;
      border-top: 1px solid var(--glass-border);
    }

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

    .btn-primary {
      background: var(--primary-gradient);
      color: white;
    }

    .btn-success {
      background: var(--success-gradient);
      color: white;
    }

    .btn-warning {
      background: var(--warning-gradient);
      color: white;
    }

    .btn-outline-light {
      border: 2px solid var(--glass-border);
      color: var(--text-primary);
      background: transparent;
    }

    .btn-outline-light:hover {
      background: var(--glass-border);
      color: var(--text-primary);
    }

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

    .form-control, .form-select {
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid var(--glass-border);
      border-radius: 12px;
      color: var(--text-primary);
      padding: 0.75rem 1rem;
    }

    .form-control:focus, .form-select:focus {
      background: rgba(255, 255, 255, 0.08);
      border-color: #667eea;
      box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.25);
      color: var(--text-primary);
    }

    .form-control::placeholder {
      color: var(--text-secondary);
    }

    .form-label {
      color: var(--text-primary);
      font-weight: 500;
      margin-bottom: 0.5rem;
    }

    /* Estilos para la sección de portafolio */
    .portfolio-section {
      background: var(--card-bg);
      backdrop-filter: blur(20px);
      border: 1px solid var(--glass-border);
      border-radius: 20px;
      padding: 2rem;
      margin-bottom: 2rem;
    }

    .portfolio-current {
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid var(--glass-border);
      border-radius: 15px;
      padding: 1.5rem;
      margin-bottom: 1.5rem;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .portfolio-info {
      display: flex;
      align-items: center;
      gap: 1rem;
    }

    .portfolio-icon {
      width: 50px;
      height: 50px;
      background: var(--info-gradient);
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.5rem;
      color: white;
    }

    .portfolio-details h5 {
      margin: 0 0 0.5rem 0;
      color: var(--text-primary);
      font-weight: 600;
    }

    .portfolio-details p {
      margin: 0;
      color: var(--text-secondary);
      font-size: 0.9rem;
    }

    .portfolio-actions {
      display: flex;
      gap: 0.5rem;
    }

    .file-link {
      background: var(--info-gradient);
      color: white;
      padding: 0.5rem 1rem;
      border-radius: 8px;
      text-decoration: none;
      font-size: 0.9rem;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .file-link:hover {
      color: white;
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }

    .no-portfolio {
      text-align: center;
      padding: 2rem;
      color: var(--text-secondary);
      font-style: italic;
    }

    .no-portfolio i {
      font-size: 3rem;
      margin-bottom: 1rem;
      opacity: 0.5;
    }

    .upload-form {
      background: rgba(255, 255, 255, 0.03);
      border: 1px solid var(--glass-border);
      border-radius: 15px;
      padding: 1.5rem;
    }

    /* Mobile Navigation */
    .mobile-nav {
      display: none;
      position: fixed;
      bottom: 0;
      left: 0;
      right: 0;
      background: rgba(15, 20, 25, 0.95);
      backdrop-filter: blur(20px);
      border-top: 1px solid var(--glass-border);
      padding: 1rem;
      z-index: 1000;
    }

    .mobile-nav-items {
      display: flex;
      justify-content: space-around;
      align-items: center;
    }

    .mobile-nav-item {
      display: flex;
      flex-direction: column;
      align-items: center;
      color: var(--text-secondary);
      text-decoration: none;
      transition: all 0.3s ease;
      padding: 0.5rem;
      border-radius: 8px;
    }

    .mobile-nav-item.active,
    .mobile-nav-item:hover {
      color: var(--text-primary);
      background: rgba(255, 255, 255, 0.1);
    }

    .mobile-nav-item i {
      font-size: 1.2rem;
      margin-bottom: 0.25rem;
    }

    .mobile-nav-item span {
      font-size: 0.7rem;
    }

    @media (max-width: 1024px) {
      .mobile-nav {
        display: block;
      }

      .main-content {
        padding-bottom: 6rem;
        margin-left: 0;
        padding: 1rem 1rem 6rem 1rem;
      }

      .sidebar {
        transform: translateX(-100%);
      }

      .sidebar.open {
        transform: translateX(0);
      }
    }

    .menu-toggle {
      display: none;
      position: fixed;
      top: 1rem;
      left: 1rem;
      z-index: 1001;
      background: var(--card-bg);
      backdrop-filter: blur(20px);
      border: 1px solid var(--glass-border);
      border-radius: 50%;
      width: 50px;
      height: 50px;
      color: var(--text-primary);
      font-size: 1.2rem;
    }

    @media (max-width: 1024px) {
      .menu-toggle {
        display: flex;
        align-items: center;
        justify-content: center;
      }

      .page-header {
        margin-top: 4rem;
      }
    }

    /* Animations */
    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .stat-card {
      animation: fadeInUp 0.6s ease forwards;
    }

    .stat-card:nth-child(1) { animation-delay: 0.1s; }
    .stat-card:nth-child(2) { animation-delay: 0.2s; }
    
    .event-card {
      animation: fadeInUp 0.6s ease forwards;
    }

    .event-card:nth-child(1) { animation-delay: 0.1s; }
    .event-card:nth-child(2) { animation-delay: 0.2s; }
    .event-card:nth-child(3) { animation-delay: 0.3s; }
    .event-card:nth-child(4) { animation-delay: 0.4s; }
  </style>
</head>
<body>
  <!-- Menu Toggle Button -->
  <button class="menu-toggle btn" onclick="toggleSidebar()">
    <i class="bi bi-list"></i>
  </button>

  <!-- Sidebar -->
  <nav class="sidebar" id="sidebar">
    <div class="sidebar-header">
      <div class="logo">
        <i class="bi bi-camera-reels"></i>
        Reminiscencia
      </div>
    </div>
    
    <div class="user-info">
      <div class="user-name"><?php echo htmlspecialchars($colaborador['nombre']); ?></div>
      <div class="user-role"><?php echo ucfirst($colaborador['tipo_colaborador'] ?? 'Colaborador'); ?></div>
    </div>
    
    <div class="nav-menu">
      <div class="nav-item">
        <a href="dashboard.php" class="nav-link active">
          <i class="bi bi-speedometer2"></i>
          Dashboard
        </a>
      </div>
      <div class="nav-item">
        <a href="eventos.php" class="nav-link">
          <i class="bi bi-calendar-event"></i>
          Mis Eventos
        </a>
      </div>
      <div class="nav-item">
        <a href="#portfolio-section" class="nav-link" onclick="scrollToPortfolio()">
          <i class="bi bi-folder"></i>
          Mi Portafolio
        </a>
      </div>
      <div class="nav-item" style="margin-top: 2rem;">
        <a href="/logout.php" class="nav-link" style="color: #ff6b6b;">
          <i class="bi bi-box-arrow-right"></i>
          Cerrar Sesión
        </a>
      </div>
    </div>
  </nav>

  <!-- Main Content -->
  <main class="main-content">
    <div class="page-header">
      <h1 class="page-title">Dashboard</h1>
      <p class="page-subtitle">Bienvenido de vuelta, <?php echo htmlspecialchars($colaborador['nombre']); ?></p>
    </div>

    <!-- Mensajes -->
    <?php if (!empty($error)): ?>
      <div class="alert alert-danger">
        <i class="bi bi-exclamation-triangle me-2"></i>
        <?php echo htmlspecialchars($error); ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($mensaje_exito)): ?>
      <div class="alert alert-success">
        <i class="bi bi-check-circle me-2"></i>
        <?php echo htmlspecialchars($mensaje_exito); ?>
      </div>
    <?php endif; ?>

    <!-- Stats Grid -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-header">
          <div class="stat-icon">
            <i class="bi bi-calendar-check"></i>
          </div>
        </div>
        <div class="stat-value"><?php echo count($eventos); ?></div>
        <div class="stat-label">Eventos Asignados</div>
        <a href="eventos.php" class="stat-link">
          Ver todos <i class="bi bi-arrow-right"></i>
        </a>
      </div>
      
      <div class="stat-card success">
        <div class="stat-header">
          <div class="stat-icon">
            <i class="bi bi-calendar-plus"></i>
          </div>
        </div>
        <div class="stat-value"><?php echo count($eventos_disponibles); ?></div>
        <div class="stat-label">Eventos Disponibles</div>
        <a href="#eventos-disponibles" class="stat-link">
          Ver disponibles <i class="bi bi-arrow-right"></i>
        </a>
      </div>
    </div>

    <!-- Sección de Portafolio -->
    <div class="portfolio-section" id="portfolio-section">
      <h3 class="mb-4">
        <i class="bi bi-folder me-2"></i>
        Mi Portafolio
      </h3>
      
      <?php if ($colaborador['tipo_colaborador'] === 'auxiliar'): ?>
        <div class="no-portfolio">
          <i class="bi bi-info-circle"></i>
          <p>Los auxiliares no requieren portafolio.</p>
        </div>
      <?php else: ?>
        <!-- Portafolio Actual -->
        <?php if (!empty($colaborador['portfolio_path']) && file_exists(__DIR__ . '/../' . $colaborador['portfolio_path'])): ?>
          <div class="portfolio-current">
            <div class="portfolio-info">
              <div class="portfolio-icon">
                <i class="bi bi-folder"></i>
              </div>
              <div class="portfolio-details">
                <h5>Portafolio Actual</h5>
                <p>Archivo subido y disponible</p>
              </div>
            </div>
            <div class="portfolio-actions">
              <a href="../<?php echo htmlspecialchars($colaborador['portfolio_path']); ?>" target="_blank" class="file-link">
                <i class="bi bi-eye" color="dark"></i> Ver Portafolio
              </a>
            </div>
          </div>
        <?php else: ?>
          <div class="no-portfolio">
            <i class="bi bi-folder-x"></i>
            <p>No tienes un portafolio subido actualmente.</p>
          </div>
        <?php endif; ?>

        <!-- Formulario para actualizar portafolio -->
        <div class="upload-form">
          <h5 class="mb-3">
            <i class="bi bi-cloud-upload me-2"></i>
            <?php echo !empty($colaborador['portfolio_path']) ? 'Actualizar Portafolio' : 'Subir Portafolio'; ?>
          </h5>
          
          <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            
            <div class="mb-3">
              <label for="nuevo_portfolio" class="form-label">Seleccionar archivo de portafolio</label>
              <input type="file" class="form-control" id="nuevo_portfolio" name="nuevo_portfolio" 
                     accept=".pdf,.zip,.rar" required>
              <div class="form-text text-secondary">
                Formatos permitidos: PDF, ZIP, RAR (máximo 50MB)
              </div>
            </div>
            
            <div class="d-flex gap-2">
              <button type="submit" name="actualizar_portfolio" class="btn btn-primary">
                <i class="bi bi-cloud-upload me-2"></i>
                <?php echo !empty($colaborador['portfolio_path']) ? 'Actualizar' : 'Subir'; ?> Portafolio
              </button>
            </div>
          </form>
        </div>
      <?php endif; ?>
    </div>

    <!-- Mis Eventos -->
    <div class="card">
      <div class="card-header">
        <h3 class="card-title text-white">
          <i class="bi bi-calendar-check me-2 text-white"></i>
          Mis Eventos Asignados
        </h3>
      </div>
      <div class="card-body">
        <?php if (count($eventos) > 0): ?>
          <div class="row">
            <?php foreach ($eventos as $evento): ?>
              <div class="col-md-6">
                <div class="event-card text-white">
                  <h4 class="event-card-title text-white"><?php echo htmlspecialchars($evento['nombre']); ?></h4>
                  <h5 class="event-card-subtitle text-white">
                    <?php echo date('d/m/Y', strtotime($evento['fecha_evento'])); ?> - 
                    <?php echo ucfirst($evento['tipo']); ?>
                  </h5>
                  
                  <div class="event-card-detail">
                    <i class="bi bi-person"></i>
                    <span><?php echo htmlspecialchars($evento['cliente_nombre']); ?></span>
                  </div>
                  
                  <div class="event-card-detail">
                    <i class="bi bi-geo-alt"></i>
                    <span><?php echo htmlspecialchars($evento['lugar']); ?></span>
                  </div>
                  
                  <div class="event-card-detail">
                    <i class="bi bi-clock"></i>
                    <span><?php echo substr($evento['hora_inicio'], 0, 5); ?> - <?php echo substr($evento['hora_fin'], 0, 5); ?></span>
                  </div>
                  
                  <div class="event-card-footer">
                    <span class="badge bg-<?php 
                      switch($evento['estado']) {
                        case 'pendiente': echo 'warning'; break;
                        case 'confirmado': echo 'success'; break;
                        case 'cancelado': echo 'danger'; break;
                        case 'completado': echo 'info'; break;
                        default: echo 'secondary';
                      }
                    ?>">
                      <?php echo ucfirst($evento['estado']); ?>
                    </span>
                    
                    
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div class="text-center py-4">
            <i class="bi bi-calendar-x text-white" style="font-size: 3rem; opacity: 0.3;"></i>
            <p class="mt-3 text-white">No tienes eventos asignados actualmente.</p>
          </div>
        <?php endif; ?>
      </div>
    </div>
    
    <!-- Eventos Disponibles -->
    <div class="card" id="eventos-disponibles">
      <div class="card-header">
        <h3 class="card-title text-white">
          <i class="bi bi-calendar-plus me-2 text-white"></i>
          Eventos Disponibles
        </h3>
      </div>
      <div class="card-body">
        <?php if (count($eventos_disponibles) > 0): ?>
          <div class="row">
            <?php foreach ($eventos_disponibles as $evento): ?>
              <div class="col-md-6">
                <div class="event-card text-white">
                  <h4 class="event-card-title text-white"><?php echo htmlspecialchars($evento['nombre']); ?></h4>
                  <h5 class="event-card-subtitle text-white">
                    <?php echo date('d/m/Y', strtotime($evento['fecha_evento'])); ?> - 
                    <?php echo ucfirst($evento['tipo']); ?>
                  </h5>
                  
                  <div class="event-card-detail">
                    <i class="bi bi-person"></i>
                    <span><?php echo htmlspecialchars($evento['cliente_nombre']); ?></span>
                  </div>
                  
                  <div class="event-card-detail">
                    <i class="bi bi-geo-alt"></i>
                    <span><?php echo htmlspecialchars($evento['lugar']); ?></span>
                  </div>
                  
                  <div class="event-card-detail">
                    <i class="bi bi-clock"></i>
                    <span><?php echo substr($evento['hora_inicio'], 0, 5); ?> - <?php echo substr($evento['hora_fin'], 0, 5); ?></span>
                  </div>
                  
                  <div class="event-card-footer">
                    <span class="badge bg-primary">
                      Disponible
                    </span>
                    
                    <form method="POST" class="d-inline">
                      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                      <input type="hidden" name="evento_id" value="<?php echo $evento['id']; ?>">
                      <button type="submit" name="solicitar_evento" class="btn btn-sm btn-success">
                        <i class="bi bi-hand-thumbs-up"></i> Solicitar
                      </button>
                    </form>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div class="text-center py-4">
            <i class="bi bi-calendar-check" style="font-size: 3rem; opacity: 0.3;"></i>
            <p class="mt-3">No hay eventos disponibles en este momento.</p>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </main>

  <!-- Mobile Navigation -->
  <nav class="mobile-nav">
    <div class="mobile-nav-items">
      <a href="dashboard.php" class="mobile-nav-item active">
        <i class="bi bi-speedometer2"></i>
        <span>Dashboard</span>
      </a>
      <a href="eventos.php" class="mobile-nav-item">
        <i class="bi bi-calendar-event"></i>
        <span>Eventos</span>
      </a>
      <a href="#portfolio-section" class="mobile-nav-item" onclick="scrollToPortfolio()">
        <i class="bi bi-folder"></i>
        <span>Portafolio</span>
      </a>
      <a href="/logout.php" class="mobile-nav-item">
        <i class="bi bi-box-arrow-right"></i>
        <span>Salir</span>
      </a>
    </div>
  </nav>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  
  <script>
    function toggleSidebar() {
      const sidebar = document.getElementById('sidebar');
      sidebar.classList.toggle('open');
    }

    function scrollToPortfolio() {
      document.getElementById('portfolio-section').scrollIntoView({ 
        behavior: 'smooth' 
      });
    }

    // Close sidebar when clicking outside
    document.addEventListener('click', function(event) {
      const sidebar = document.getElementById('sidebar');
      const menuToggle = document.querySelector('.menu-toggle');
      
      if (!sidebar.contains(event.target) && !menuToggle.contains(event.target)) {
        sidebar.classList.remove('open');
      }
    });

    // Validación de archivos
    document.getElementById('nuevo_portfolio').addEventListener('change', function(e) {
      const file = e.target.files[0];
      if (file) {
        const allowedTypes = ['application/pdf', 'application/zip', 'application/x-rar-compressed'];
        const fileName = file.name.toLowerCase();
        const isValidType = allowedTypes.includes(file.type) || 
                           fileName.endsWith('.pdf') || 
                           fileName.endsWith('.zip') || 
                           fileName.endsWith('.rar');
        
        if (!isValidType) {
          alert('Formato de portafolio no válido. Use PDF, ZIP o RAR');
          this.value = '';
        } else if (file.size > 50 * 1024 * 1024) {
          alert('El portafolio es demasiado grande (máximo 50MB)');
          this.value = '';
        }
      }
    });
  </script>
</body>
</html>

