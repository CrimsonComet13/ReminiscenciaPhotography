<?php
session_start();
require_once(__DIR__ . '/../includes/config.php');
require_once(__DIR__ . '/../includes/auth.php');

// Verificar permisos de administrador
if (!isAdmin()) {
    header("Location: /login.php");
    exit();
}

// Generar token CSRF si no existe
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Verificar que se haya proporcionado un ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: gestion_colaboradores.php?error=id_invalido");
    exit();
}

$colaborador_id = intval($_GET['id']);

// Obtener datos del colaborador
try {
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE id = ? AND rol = 'colaborador'");
    $stmt->execute([$colaborador_id]);
    $colaborador = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$colaborador) {
        header("Location: gestion_colaboradores.php?error=colaborador_no_encontrado");
        exit();
    }

    // Tipos y rangos disponibles
    $tipos_colaborador = [
        'fotografo' => 'Fotógrafo',
        'videografo' => 'Videógrafo',
        'auxiliar' => 'Auxiliar',
    ];

    $rangos_colaborador = [
        'I' => 'I',
        'II' => 'II',
        'III' => 'III',
    ];

} catch (PDOException $e) {
    die("Error al obtener datos del colaborador: " . $e->getMessage());
}

// Procesar actualización si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error_actualizacion'] = "Token CSRF inválido";
        header("Location: detalles_colaborador.php?id=" . $colaborador_id);
        exit();
    }

    try {
        $nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_STRING) ?? '';
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL) ?? '';
        $telefono = filter_input(INPUT_POST, 'telefono', FILTER_SANITIZE_STRING) ?? '';
        $tipo_colaborador = $_POST['tipo_colaborador'] ?? '';
        $rango_colaborador = $_POST['rango_colaborador'] ?? '';
        $activo = isset($_POST['activo']) ? 1 : 0;

        // Validaciones básicas
        if (empty($nombre) || empty($email) || empty($tipo_colaborador) || empty($rango_colaborador)) {
            throw new Exception("Todos los campos obligatorios deben completarse");
        }

        // Validar longitudes
        if (strlen($nombre) > 100) {
            throw new Exception("El nombre no puede exceder los 100 caracteres");
        }

        if (strlen($telefono) > 20) {
            throw new Exception("El teléfono no puede exceder los 20 caracteres");
        }

        // Validar tipo y rango contra listas blancas
        if (!array_key_exists($tipo_colaborador, $tipos_colaborador)) {
            throw new Exception("Tipo de colaborador no válido");
        }

        if (!array_key_exists($rango_colaborador, $rangos_colaborador)) {
            throw new Exception("Rango de colaborador no válido");
        }

        // Verificar si el email ya existe (excluyendo al usuario actual)
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
        $stmt->execute([$email, $colaborador_id]);
        if ($stmt->fetch()) {
            throw new Exception("El email ya está registrado por otro usuario");
        }

        // Actualizar datos del colaborador
        $stmt = $conn->prepare("UPDATE usuarios SET 
                                nombre = ?, 
                                email = ?, 
                                telefono = ?, 
                                tipo_colaborador = ?, 
                                rango_colaborador = ?, 
                                activo = ? 
                                WHERE id = ? AND rol = 'colaborador'");
        
        $stmt->execute([
            $nombre,
            $email,
            $telefono,
            $tipo_colaborador,
            $rango_colaborador,
            $activo,
            $colaborador_id
        ]);

        $_SESSION['mensaje_exito'] = "Datos del colaborador actualizados correctamente";
        
        // Actualizar datos locales para mostrar cambios
        $colaborador['nombre'] = $nombre;
        $colaborador['email'] = $email;
        $colaborador['telefono'] = $telefono;
        $colaborador['tipo_colaborador'] = $tipo_colaborador;
        $colaborador['rango_colaborador'] = $rango_colaborador;
        $colaborador['activo'] = $activo;

    } catch (Exception $e) {
        $_SESSION['error_actualizacion'] = $e->getMessage();
    } catch (PDOException $e) {
        $_SESSION['error_actualizacion'] = "Error en la base de datos: " . $e->getMessage();
    }
}

$error_actualizacion = $_SESSION['error_actualizacion'] ?? '';
unset($_SESSION['error_actualizacion']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Detalles del Colaborador - Reminiscencia Photography</title>
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
      margin-right: 12px;
      font-size: 1.2rem;
      width: 20px;
    }

    .main-content {
      margin-left: 280px;
      padding: 2rem;
      min-height: 100vh;
    }

    .page-header {
      margin-bottom: 2rem;
      display: flex;
      align-items: center;
      gap: 1rem;
    }

    .page-header h1 {
      font-size: 2.5rem;
      font-weight: 700;
      margin: 0;
      background: var(--primary-gradient);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    .back-button {
      background: var(--card-bg);
      backdrop-filter: blur(20px);
      border: 1px solid var(--glass-border);
      border-radius: 12px;
      padding: 0.8rem 1.2rem;
      color: var(--text-primary);
      text-decoration: none;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .back-button:hover {
      color: var(--text-primary);
      transform: translateY(-2px);
      box-shadow: var(--shadow-glow);
    }

    .profile-header {
      background: var(--card-bg);
      backdrop-filter: blur(20px);
      border: 1px solid var(--glass-border);
      border-radius: 20px;
      padding: 2rem;
      margin-bottom: 2rem;
      text-align: center;
      box-shadow: var(--shadow-glow);
    }

    .profile-pic {
      width: 120px;
      height: 120px;
      border-radius: 50%;
      margin-bottom: 1rem;
      border: 4px solid var(--glass-border);
    }

    .profile-name {
      font-size: 2rem;
      font-weight: 700;
      margin-bottom: 1rem;
      background: var(--primary-gradient);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    .badge-container {
      display: flex;
      justify-content: center;
      gap: 1rem;
      flex-wrap: wrap;
    }

    .badge {
      padding: 0.5rem 1rem;
      border-radius: 20px;
      font-size: 0.9rem;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .badge-tipo {
      background: var(--primary-gradient);
      color: white;
    }

    .badge-rango {
      background: var(--warning-gradient);
      color: white;
    }

    .badge-status {
      background: var(--danger-gradient);
      color: white;
    }

    .badge-status.active {
      background: var(--success-gradient);
    }

    .card {
      background: var(--card-bg);
      backdrop-filter: blur(20px);
      border: 1px solid var(--glass-border);
      border-radius: 20px;
      margin-bottom: 2rem;
      box-shadow: var(--shadow-glow);
    }

    .card-header {
      padding: 1.5rem 2rem;
      border-bottom: 1px solid var(--glass-border);
      border-radius: 20px 20px 0 0;
    }

    .card-title {
      font-size: 1.5rem;
      font-weight: 600;
      margin: 0;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .card-body {
      padding: 2rem;
    }

    .form-label {
      color: var(--text-primary);
      font-weight: 500;
      margin-bottom: 0.5rem;
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

    .form-text {
      color: var(--text-secondary);
      font-size: 0.875rem;
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

    .btn-danger {
      background: var(--danger-gradient);
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
      background: rgba(255, 107, 107, 0.1);
      border-color: rgba(255, 107, 107, 0.2);
    }

    .form-check-input {
      background: rgba(255, 255, 255, 0.1);
      border: 1px solid var(--glass-border);
    }

    .form-check-input:checked {
      background: var(--success-gradient);
      border-color: transparent;
    }

    .form-check-label {
      color: var(--text-primary);
    }

    /* Estilos para la sección de archivos */
    .file-section {
      background: var(--card-bg);
      backdrop-filter: blur(20px);
      border: 1px solid var(--glass-border);
      border-radius: 20px;
      padding: 2rem;
      margin-bottom: 2rem;
      box-shadow: var(--shadow-glow);
    }

    .file-item {
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid var(--glass-border);
      border-radius: 15px;
      padding: 1.5rem;
      margin-bottom: 1rem;
      display: flex;
      align-items: center;
      justify-content: space-between;
      transition: all 0.3s ease;
    }

    .file-item:hover {
      background: rgba(255, 255, 255, 0.08);
      transform: translateY(-2px);
    }

    .file-info {
      display: flex;
      align-items: center;
      gap: 1rem;
    }

    .file-icon {
      width: 50px;
      height: 50px;
      background: var(--primary-gradient);
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.5rem;
      color: white;
    }

    .file-details h5 {
      margin: 0 0 0.5rem 0;
      color: var(--text-primary);
      font-weight: 600;
    }

    .file-details p {
      margin: 0;
      color: var(--text-secondary);
      font-size: 0.9rem;
    }

    .file-actions {
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

    .no-file {
      text-align: center;
      padding: 2rem;
      color: var(--text-secondary);
      font-style: italic;
    }

    .no-file i {
      font-size: 3rem;
      margin-bottom: 1rem;
      opacity: 0.5;
    }

    /* Menu Toggle Button */
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

    /* Responsive Design */
    @media (max-width: 1024px) {
      .sidebar {
        transform: translateX(-100%);
      }

      .sidebar.open {
        transform: translateX(0);
      }

      .main-content {
        margin-left: 0;
      }

      .menu-toggle {
        display: flex;
        align-items: center;
        justify-content: center;
      }

      .mobile-nav {
        display: block;
      }

      .page-header {
        margin-top: 4rem;
      }
    }

    @media (max-width: 768px) {
      .main-content {
        padding: 1rem;
      }

      .profile-header {
        padding: 1.5rem;
      }

      .profile-pic {
        width: 100px;
        height: 100px;
      }

      .profile-name {
        font-size: 1.5rem;
      }

      .badge-container {
        flex-direction: column;
        align-items: center;
      }

      .card-body {
        padding: 1.5rem;
      }

      .file-item {
        flex-direction: column;
        text-align: center;
        gap: 1rem;
      }

      .file-actions {
        justify-content: center;
      }
    }
  </style>
</head>
<body>
  <!-- Menu Toggle Button -->
  <button class="menu-toggle" onclick="toggleSidebar()">
    <i class="bi bi-list"></i>
  </button>

  <!-- Sidebar -->
  <nav class="sidebar" id="sidebar">
    <div class="sidebar-header">
      <div class="logo">Reminiscencia</div>
    </div>
    <div class="nav-menu">
      <div class="nav-item">
        <a href="dashboard.php" class="nav-link">
          <i class="bi bi-speedometer2"></i>
          <span>Dashboard</span>
        </a>
      </div>
      <div class="nav-item">
        <a href="gestion_eventos.php" class="nav-link">
          <i class="bi bi-calendar-event"></i>
          <span>Eventos</span>
        </a>
      </div>
      <div class="nav-item">
        <a href="gestion_colaboradores.php" class="nav-link active">
          <i class="bi bi-people-fill"></i>
          <span>Colaboradores</span>
        </a>
      </div>
      <div class="nav-item">
        <a href="gestion_clientes.php" class="nav-link">
          <i class="bi bi-person-lines-fill"></i>
          <span>Clientes</span>
        </a>
      </div>
      <div class="nav-item">
        <a href="llamadas.php" class="nav-link">
          <i class="bi bi-telephone-fill"></i>
          <span>Llamadas</span>
        </a>
      </div>
      <div class="nav-item" style="margin-top: 2rem;">
        <a href="/logout.php" class="nav-link" style="color: #ff6b6b;">
          <i class="bi bi-box-arrow-right"></i>
          <span>Cerrar Sesión</span>
        </a>
      </div>
    </div>
  </nav>

  <!-- Main Content -->
  <main class="main-content">
    <div class="page-header">
      <a href="gestion_colaboradores.php" class="back-button">
        <i class="bi bi-arrow-left"></i>
        Volver
      </a>
      <h1>Detalles del Colaborador</h1>
    </div>

    <?php if (isset($_SESSION['mensaje_exito'])): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>
        <?php echo htmlspecialchars($_SESSION['mensaje_exito']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      <?php unset($_SESSION['mensaje_exito']); ?>
    <?php endif; ?>

    <?php if (!empty($error_actualizacion)): ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>
        <?php echo htmlspecialchars($error_actualizacion); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

    <!-- Profile Header -->
    <div class="profile-header">
      <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($colaborador['nombre']); ?>&background=random" 
           alt="Foto de perfil" class="profile-pic">
      <h2 class="profile-name"><?php echo htmlspecialchars($colaborador['nombre']); ?></h2>
      
      <div class="badge-container">
        <span class="badge badge-tipo">
          <i class="bi bi-person-badge"></i>
          <?php echo htmlspecialchars($tipos_colaborador[$colaborador['tipo_colaborador']] ?? 'Desconocido'); ?>
        </span>
        <span class="badge badge-rango">
          <i class="bi bi-award"></i>
          <?php echo htmlspecialchars($rangos_colaborador[$colaborador['rango_colaborador']] ?? 'Desconocido'); ?>
        </span>
        <span class="badge badge-status <?php echo $colaborador['activo'] ? 'active' : ''; ?>">
          <i class="bi bi-circle-fill"></i>
          <?php echo $colaborador['activo'] ? 'Activo' : 'Inactivo'; ?>
        </span>
      </div>
    </div>

    <!-- Sección de Archivos -->
    <div class="file-section">
      <h3 class="mb-4">
        <i class="bi bi-folder me-2"></i>
        Documentos del Colaborador
      </h3>
      
      <div class="row">
        <div class="col-md-6">
          <div class="file-item">
            <div class="file-info">
              <div class="file-icon">
                <i class="bi bi-file-earmark-text"></i>
              </div>
              <div class="file-details">
                <h5>Curriculum Vitae (CV)</h5>
                <?php if (!empty($colaborador['cv_path']) && file_exists(__DIR__ . '/../' . $colaborador['cv_path'])): ?>
                  <p>Archivo disponible</p>
                <?php else: ?>
                  <p>No disponible</p>
                <?php endif; ?>
              </div>
            </div>
            <div class="file-actions">
              <?php if (!empty($colaborador['cv_path']) && file_exists(__DIR__ . '/../' . $colaborador['cv_path'])): ?>
                <a href="../<?php echo htmlspecialchars($colaborador['cv_path']); ?>" target="_blank" class="file-link">
                  <i class="bi bi-eye"></i> Ver CV
                </a>
              <?php else: ?>
                <span class="no-file">No disponible</span>
              <?php endif; ?>
            </div>
          </div>
        </div>
        
        <div class="col-md-6">
          <div class="file-item">
            <div class="file-info">
              <div class="file-icon">
                <i class="bi bi-folder"></i>
              </div>
              <div class="file-details">
                <h5>Portafolio</h5>
                <?php if (!empty($colaborador['portfolio_path']) && file_exists(__DIR__ . '/../' . $colaborador['portfolio_path'])): ?>
                  <p>Archivo disponible</p>
                <?php elseif ($colaborador['tipo_colaborador'] === 'auxiliar'): ?>
                  <p>No requerido para auxiliares</p>
                <?php else: ?>
                  <p>No disponible</p>
                <?php endif; ?>
              </div>
            </div>
            <div class="file-actions">
              <?php if (!empty($colaborador['portfolio_path']) && file_exists(__DIR__ . '/../' . $colaborador['portfolio_path'])): ?>
                <a href="../<?php echo htmlspecialchars($colaborador['portfolio_path']); ?>" target="_blank" class="file-link">
                  <i class="bi bi-eye"></i> Ver Portafolio
                </a>
              <?php elseif ($colaborador['tipo_colaborador'] === 'auxiliar'): ?>
                <span class="no-file">No requerido</span>
              <?php else: ?>
                <span class="no-file">No disponible</span>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Form Card -->
    <div class="card">
      <div class="card-header">
        <h3 class="card-title text-white">
          <i class="bi bi-pencil-square text-white"></i>
          Editar Información
        </h3>
      </div>
      <div class="card-body">
        <form method="POST">
          <!-- Campo oculto para token CSRF -->
          <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
          
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="nombre" class="form-label">Nombre Completo</label>
              <input type="text" class="form-control" id="nombre" name="nombre" 
                     value="<?php echo htmlspecialchars($colaborador['nombre']); ?>" 
                     maxlength="100" required>
              <div class="form-text text-secondary">Máximo 100 caracteres</div>
            </div>
            
            <div class="col-md-6 mb-3">
              <label for="email" class="form-label">Email</label>
              <input type="email" class="form-control" id="email" name="email"
                     value="<?php echo htmlspecialchars($colaborador['email']); ?>" required>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="telefono" class="form-label">Teléfono</label>
              <input type="tel" class="form-control" id="telefono" name="telefono"
                     value="<?php echo htmlspecialchars($colaborador['telefono'] ?? ''); ?>" 
                     maxlength="20" placeholder="+52 449 123 4567">
              <div class="form-text text-secondary">Máximo 20 caracteres</div>
            </div>
            
            <div class="col-md-6 mb-3">
              <label for="tipo_colaborador" class="form-label">Tipo de Colaborador</label>
              <select class="form-select" id="tipo_colaborador" name="tipo_colaborador" required>
                <?php foreach ($tipos_colaborador as $key => $value): ?>
                  <option value="<?php echo htmlspecialchars($key); ?>" 
                          <?php echo ($colaborador['tipo_colaborador'] === $key) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($value); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="rango_colaborador" class="form-label">Rango</label>
              <select class="form-select" id="rango_colaborador" name="rango_colaborador" required>
                <?php foreach ($rangos_colaborador as $key => $value): ?>
                  <option value="<?php echo htmlspecialchars($key); ?>" 
                          <?php echo ($colaborador['rango_colaborador'] === $key) ? 'selected' : ''; ?>>
                    Rango <?php echo htmlspecialchars($value); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            
            <div class="col-md-6 mb-3 d-flex align-items-center">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="activo" name="activo" 
                       <?php echo $colaborador['activo'] ? 'checked' : ''; ?>>
                <label class="form-check-label" for="activo">
                  Colaborador Activo
                </label>
              </div>
            </div>
          </div>
          
          <div class="d-flex gap-3 justify-content-end">
            <a href="gestion_colaboradores.php" class="btn btn-outline-light">
              <i class="bi bi-arrow-left me-2"></i>
              Cancelar
            </a>
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-check-lg me-2"></i>
              Guardar Cambios
            </button>
          </div>
        </form>
      </div>
    </div>
  </main>

  <!-- Mobile Navigation -->
  <nav class="mobile-nav">
    <div class="mobile-nav-items">
      <a href="dashboard.php" class="mobile-nav-item">
        <i class="bi bi-speedometer2"></i>
        <span>Dashboard</span>
      </a>
      <a href="gestion_eventos.php" class="mobile-nav-item">
        <i class="bi bi-calendar-event"></i>
        <span>Eventos</span>
      </a>
      <a href="gestion_colaboradores.php" class="mobile-nav-item active">
        <i class="bi bi-people-fill"></i>
        <span>Colaboradores</span>
      </a>
      <a href="gestion_clientes.php" class="mobile-nav-item">
        <i class="bi bi-person-lines-fill"></i>
        <span>Clientes</span>
      </a>
      <a href="llamadas.php" class="mobile-nav-item">
        <i class="bi bi-telephone-fill"></i>
        <span>Llamadas</span>
      </a>
    </div>
  </nav>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  
  <script>
    function toggleSidebar() {
      const sidebar = document.getElementById('sidebar');
      sidebar.classList.toggle('open');
    }

    // Close sidebar when clicking outside
    document.addEventListener('click', function(event) {
      const sidebar = document.getElementById('sidebar');
      const menuToggle = document.querySelector('.menu-toggle');
      
      if (!sidebar.contains(event.target) && !menuToggle.contains(event.target)) {
        sidebar.classList.remove('open');
      }
    });

    // Add ripple effect to buttons
    document.querySelectorAll('.btn').forEach(button => {
      button.addEventListener('click', function(e) {
        const ripple = document.createElement('span');
        const rect = this.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        const x = e.clientX - rect.left - size / 2;
        const y = e.clientY - rect.top - size / 2;
        
        ripple.style.cssText = `
          position: absolute;
          width: ${size}px;
          height: ${size}px;
          left: ${x}px;
          top: ${y}px;
          background: rgba(255, 255, 255, 0.3);
          border-radius: 50%;
          transform: scale(0);
          animation: ripple 0.6s ease-out;
          pointer-events: none;
        `;
        
        this.appendChild(ripple);
        setTimeout(() => ripple.remove(), 600);
      });
    });

    // Add CSS for ripple animation
    const style = document.createElement('style');
    style.textContent = `
      @keyframes ripple {
        to {
          transform: scale(2);
          opacity: 0;
        }
      }
    `;
    document.head.appendChild(style);
  </script>
</body>
</html>

