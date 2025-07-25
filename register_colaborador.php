<?php
require_once(__DIR__ . '/includes/config.php');
require_once(__DIR__ . '/includes/auth.php');

// Iniciar sesión para CSRF
session_start();

// Configurar directorio de logs
$log_dir = __DIR__ . '/logs';
if (!is_dir($log_dir)) {
    mkdir($log_dir, 0777, true);
}
ini_set('error_log', $log_dir . '/error.log');

$error = '';
$success = '';

// Generar token CSRF si no existe
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = 'Error de seguridad. Por favor, recargue la página e intente nuevamente.';
        error_log('Intento de registro sin token CSRF válido. IP: ' . $_SERVER['REMOTE_ADDR']);
    } else {
        $nombre = trim($_POST['nombre']);
        $email = trim($_POST['email']);
        $telefono = trim($_POST['telefono']);
        $password = trim($_POST['password']);
        $password_confirm = trim($_POST['password_confirm']);
        $tipo = trim($_POST['tipo']);
        $rango = trim($_POST['rango']);

        // Validaciones de longitud
        if (strlen($nombre) > 100) {
            $error = 'El nombre no puede exceder los 100 caracteres';
        } elseif (strlen($telefono) > 20) {
            $error = 'El teléfono no puede exceder los 20 caracteres';
        } 
        // Validar formato de teléfono
        elseif (!empty($telefono) && !preg_match('/^[0-9+\-()\s]{8,20}$/', $telefono)) {
            $error = 'Formato de teléfono inválido. Solo se permiten números, guiones, paréntesis y espacios.';
        } 
        // Validaciones obligatorias
        elseif (empty($nombre) || empty($email) || empty($password) || empty($password_confirm)) {
            $error = 'Todos los campos marcados con * son obligatorios';
        } elseif ($password !== $password_confirm) {
            $error = 'Las contraseñas no coinciden';
        } elseif (strlen($password) < 8) {
            $error = 'La contraseña debe tener al menos 8 caracteres';
        } elseif (empty($_FILES['foto_id']['name'])) {
            $error = 'Debe subir una foto de identificación';
        } elseif (empty($_FILES['cv']['name'])) {
            $error = 'Debe subir su CV';
        } elseif ($tipo !== 'auxiliar' && empty($_FILES['portfolio']['name'])) {
            $error = 'Debe subir su portafolio (excepto auxiliares)';
        } else {
            try {
                // Verificar si el email ya existe
                $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = :email UNION SELECT id FROM prospectos_colaboradores WHERE email = :email");
                $stmt->bindParam(':email', $email);
                $stmt->execute();

                if ($stmt->rowCount() > 0) {
                    $error = 'El email ya está registrado';
                } else {
                    // Crear directorios necesarios
                    $foto_dir = __DIR__ . '/uploads/fotos_id/';
                    $cv_dir = __DIR__ . '/uploads/cvs/';
                    $portfolio_dir = __DIR__ . '/uploads/portfolios/';
                    
                    if (!is_dir($foto_dir)) mkdir($foto_dir, 0777, true);
                    if (!is_dir($cv_dir)) mkdir($cv_dir, 0777, true);
                    if (!is_dir($portfolio_dir)) mkdir($portfolio_dir, 0777, true);
                    
                    // Validar foto de identificación
                    $allowed_image_extensions = ['jpg', 'jpeg', 'png', 'gif'];
                    $foto_extension = strtolower(pathinfo($_FILES['foto_id']['name'], PATHINFO_EXTENSION));
                    
                    if (!in_array($foto_extension, $allowed_image_extensions)) {
                        $error = 'Formato de imagen no válido. Use JPG, PNG o GIF';
                    } elseif ($_FILES['foto_id']['size'] > 5 * 1024 * 1024) { // 5MB
                        $error = 'La foto es demasiado grande (máximo 5MB)';
                    } else {
                        // Validar CV
                        $allowed_doc_extensions = ['pdf', 'doc', 'docx'];
                        $cv_extension = strtolower(pathinfo($_FILES['cv']['name'], PATHINFO_EXTENSION));
                        
                        if (!in_array($cv_extension, $allowed_doc_extensions)) {
                            $error = 'Formato de CV no válido. Use PDF, DOC o DOCX';
                        } elseif ($_FILES['cv']['size'] > 10 * 1024 * 1024) { // 10MB
                            $error = 'El CV es demasiado grande (máximo 10MB)';
                        } else {
                            $portfolio_db_path = null;
                            
                            // Validar portafolio solo si no es auxiliar
                            if ($tipo !== 'auxiliar') {
                                $allowed_portfolio_extensions = ['pdf', 'zip', 'rar'];
                                $portfolio_extension = strtolower(pathinfo($_FILES['portfolio']['name'], PATHINFO_EXTENSION));
                                
                                if (!in_array($portfolio_extension, $allowed_portfolio_extensions)) {
                                    $error = 'Formato de portafolio no válido. Use PDF, ZIP o RAR';
                                } elseif ($_FILES['portfolio']['size'] > 50 * 1024 * 1024) { // 50MB
                                    $error = 'El portafolio es demasiado grande (máximo 50MB)';
                                }
                            }
                            
                            if (empty($error)) {
                                // Procesar archivos
                                $foto_nombre = uniqid('foto_id_colaborador_') . '.' . $foto_extension;
                                $cv_nombre = uniqid('cv_colaborador_') . '.' . $cv_extension;
                                
                                $foto_ruta = $foto_dir . $foto_nombre;
                                $cv_ruta = $cv_dir . $cv_nombre;
                                
                                // Rutas para la base de datos
                                $foto_db_path = 'uploads/fotos_id/' . $foto_nombre;
                                $cv_db_path = 'uploads/cvs/' . $cv_nombre;
                                
                                $upload_success = true;
                                
                                // Subir foto
                                if (!move_uploaded_file($_FILES['foto_id']['tmp_name'], $foto_ruta)) {
                                    $error = 'Error al subir la foto de identificación';
                                    $upload_success = false;
                                }
                                
                                // Subir CV
                                if ($upload_success && !move_uploaded_file($_FILES['cv']['tmp_name'], $cv_ruta)) {
                                    $error = 'Error al subir el CV';
                                    $upload_success = false;
                                }
                                
                                // Subir portafolio si no es auxiliar
                                if ($upload_success && $tipo !== 'auxiliar') {
                                    $portfolio_extension = strtolower(pathinfo($_FILES['portfolio']['name'], PATHINFO_EXTENSION));
                                    $portfolio_nombre = uniqid('portfolio_colaborador_') . '.' . $portfolio_extension;
                                    $portfolio_ruta = $portfolio_dir . $portfolio_nombre;
                                    $portfolio_db_path = 'uploads/portfolios/' . $portfolio_nombre;
                                    
                                    if (!move_uploaded_file($_FILES['portfolio']['tmp_name'], $portfolio_ruta)) {
                                        $error = 'Error al subir el portafolio';
                                        $upload_success = false;
                                    }
                                }
                                
                                if ($upload_success) {
                                    $password_hash = password_hash($password, PASSWORD_DEFAULT);

                                    // Insertar en tabla de prospectos_colaboradores
                                    $stmt = $conn->prepare("INSERT INTO prospectos_colaboradores (nombre, email, telefono, password, tipo_colaborador, rango_colaborador, foto_path, cv_path, portfolio_path) 
                                                            VALUES (:nombre, :email, :telefono, :password, :tipo, :rango, :foto_path, :cv_path, :portfolio_path)");
                                    $stmt->bindParam(':nombre', $nombre);
                                    $stmt->bindParam(':email', $email);
                                    $stmt->bindParam(':telefono', $telefono);
                                    $stmt->bindParam(':password', $password_hash);
                                    $stmt->bindParam(':tipo', $tipo);
                                    $stmt->bindParam(':rango', $rango);
                                    $stmt->bindParam(':foto_path', $foto_db_path);
                                    $stmt->bindParam(':cv_path', $cv_db_path);
                                    $stmt->bindParam(':portfolio_path', $portfolio_db_path);
                                    $stmt->execute();

                                    $success = 'Registro enviado exitosamente. Su solicitud está siendo revisada por un administrador. Recibirá una notificación cuando sea aprobada.';
                                    
                                    // Redirigir después de 5 segundos
                                    header("Refresh: 5; url=login_colaborador.php");
                                    exit;
                                }
                            }
                        }
                    }
                }
            } catch (PDOException $e) {
                $error = 'Error en el registro. Por favor, inténtelo de nuevo más tarde.';
                error_log('Error en registro_colaborador: ' . $e->getMessage());
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registro Colaborador - Reminiscencia Photography</title>
  <!-- Vincular Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Iconos de Bootstrap -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
  
  <style>
    :root {
      --primary-dark: #0a0a0a;
      --secondary-dark: #1a1a1a;
      --tertiary-dark: #2a2a2a;
      --light-bg: #fafafa;
      --accent-gold: #d4af37;
      --accent-gold-hover: #b8941f;
      --accent-purple: #8b5a96;
      --text-muted: #6c757d;
      --border-light: rgba(255,255,255,0.1);
      --glass-bg: rgba(255,255,255,0.05);
      --shadow-soft: 0 20px 60px rgba(0,0,0,0.1);
      --shadow-hover: 0 30px 80px rgba(0,0,0,0.15);
    }
    
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body {
      background: linear-gradient(135deg, var(--light-bg) 0%, #f0f2f5 100%);
      color: var(--secondary-dark);
      font-family: 'Inter', sans-serif;
      line-height: 1.7;
      overflow-x: hidden;
      min-height: 100vh;
    }
    
    h1, h2, h3, h4, h5, h6 {
      font-family: 'Playfair Display', serif;
      font-weight: 600;
      letter-spacing: -0.02em;
    }
    
    /* Navbar moderna con glassmorphism */
    header {
      width: 100%;
      position: fixed;
      top: 0;
      z-index: 1000;
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .navbar {
      padding: 20px 0;
      background: rgba(10, 10, 10, 0.8) !important;
      backdrop-filter: blur(20px);
      border-bottom: 1px solid var(--border-light);
      transition: all 0.4s ease;
    }
    
    .navbar.scrolled {
      padding: 12px 0;
      background: rgba(10, 10, 10, 0.95) !important;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
      backdrop-filter: blur(30px);
    }
    
    .navbar-brand {
      font-family: 'Playfair Display', serif;
      font-size: 1.8rem;
      font-weight: 700;
      background: linear-gradient(135deg, #fff, var(--accent-gold));
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      transition: all 0.3s ease;
    }
    
    .navbar-brand:hover {
      transform: scale(1.05);
    }
    
    .nav-link {
      font-weight: 500;
      padding: 10px 18px !important;
      border-radius: 25px;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
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
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
      transition: left 0.5s;
    }
    
    .nav-link:hover::before {
      left: 100%;
    }
    
    .nav-link:hover {
      color: var(--accent-gold) !important;
      background: var(--glass-bg);
      transform: translateY(-2px);
      box-shadow: 0 10px 25px rgba(212, 175, 55, 0.2);
    }

    /* Hero Section de registro */
    .hero-register {
      background-image: 
        linear-gradient(135deg, rgba(10, 10, 10, 0.8) 0%, rgba(139, 90, 150, 0.4) 100%),
        url('assets/images/Foto-20.jpg');
      background-size: cover;
      background-position: center;
      background-attachment: fixed;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 120px 20px 60px;
      position: relative;
      overflow: hidden;
    }

    .hero-register::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: radial-gradient(circle at center, transparent 0%, rgba(0,0,0,0.3) 100%);
    }

    /* Floating particles effect */
    .particles {
      position: absolute;
      width: 100%;
      height: 100%;
      overflow: hidden;
      top: 0;
      left: 0;
    }
    
    .particle {
      position: absolute;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 50%;
      animation: float 8s infinite ease-in-out;
    }
    
    .particle:nth-child(1) { width: 6px; height: 6px; left: 10%; animation-delay: 0s; }
    .particle:nth-child(2) { width: 4px; height: 4px; left: 20%; animation-delay: 2s; }
    .particle:nth-child(3) { width: 8px; height: 8px; left: 30%; animation-delay: 4s; }
    .particle:nth-child(4) { width: 5px; height: 5px; left: 70%; animation-delay: 1s; }
    .particle:nth-child(5) { width: 7px; height: 7px; left: 80%; animation-delay: 3s; }

    /* Form Container */
    .form-container {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(20px);
      border-radius: 25px;
      padding: 3rem;
      max-width: 700px;
      width: 100%;
      box-shadow: var(--shadow-soft);
      border: 1px solid rgba(255, 255, 255, 0.3);
      position: relative;
      z-index: 2;
      animation: formFadeIn 1s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .form-container::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: linear-gradient(135deg, rgba(212, 175, 55, 0.05) 0%, rgba(139, 90, 150, 0.05) 100%);
      border-radius: 25px;
      z-index: -1;
    }

    .form-container h2 {
      font-size: 2.5rem;
      margin-bottom: 2rem;
      background: linear-gradient(135deg, var(--primary-dark) 0%, var(--accent-gold) 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      text-align: center;
      position: relative;
    }

    .form-container h2::after {
      content: '';
      position: absolute;
      bottom: -10px;
      left: 50%;
      transform: translateX(-50%);
      width: 80px;
      height: 3px;
      background: linear-gradient(90deg, var(--accent-gold), var(--accent-purple));
      border-radius: 2px;
    }

    /* Form Controls */
    .form-label {
      font-weight: 600;
      color: var(--secondary-dark);
      margin-bottom: 0.5rem;
      font-size: 0.95rem;
    }

    .required {
      color: #dc3545;
      font-weight: 700;
    }

    .form-control, .form-select {
      border: 2px solid rgba(0, 0, 0, 0.1);
      border-radius: 15px;
      padding: 12px 18px;
      font-size: 1rem;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      background: rgba(255, 255, 255, 0.8);
      backdrop-filter: blur(5px);
    }

    .form-control:focus, .form-select:focus {
      border-color: var(--accent-gold);
      box-shadow: 0 0 0 0.25rem rgba(212, 175, 55, 0.25);
      background: rgba(255, 255, 255, 0.95);
      transform: translateY(-2px);
    }

    .password-field {
      position: relative;
    }

    .password-toggle {
      position: absolute;
      right: 15px;
      top: 50%;
      transform: translateY(-50%);
      background: none;
      border: none;
      color: var(--text-muted);
      cursor: pointer;
      font-size: 1.2rem;
      transition: all 0.3s ease;
      z-index: 3;
    }

    .password-toggle:hover {
      color: var(--accent-gold);
      transform: translateY(-50%) scale(1.1);
    }

    /* Botones modernos */
    .btn {
      padding: 15px 35px;
      border-radius: 50px;
      font-weight: 600;
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      border: none;
      position: relative;
      overflow: hidden;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      font-size: 1rem;
    }
    
    .btn::before {
      content: '';
      position: absolute;
      top: 50%;
      left: 50%;
      width: 0;
      height: 0;
      background: rgba(255, 255, 255, 0.2);
      border-radius: 50%;
      transform: translate(-50%, -50%);
      transition: width 0.6s, height 0.6s;
    }
    
    .btn:hover::before {
      width: 300px;
      height: 300px;
    }
    
    .btn-primary {
      background: linear-gradient(135deg, var(--accent-gold), var(--accent-gold-hover));
      color: white;
      box-shadow: 0 8px 25px rgba(212, 175, 55, 0.3);
    }
    
    .btn-primary:hover {
      transform: translateY(-3px);
      box-shadow: 0 15px 35px rgba(212, 175, 55, 0.4);
      background: linear-gradient(135deg, var(--accent-gold-hover), var(--accent-gold));
    }

    /* Alertas mejoradas */
    .alert {
      border-radius: 15px;
      padding: 1.2rem 1.5rem;
      font-size: 0.95rem;
      border: none;
      margin-bottom: 2rem;
      position: relative;
      overflow: hidden;
      font-weight: 500;
    }

    .alert::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 4px;
      height: 100%;
      background: currentColor;
    }

    .alert-danger {
      background: linear-gradient(135deg, rgba(220, 53, 69, 0.1), rgba(220, 53, 69, 0.05));
      color: #721c24;
      border-left: 4px solid #dc3545;
    }

    .alert-success {
      background: linear-gradient(135deg, rgba(25, 135, 84, 0.1), rgba(25, 135, 84, 0.05));
      color: #0f5132;
      border-left: 4px solid #198754;
    }

    /* Links mejorados */
    a {
      color: var(--accent-gold);
      text-decoration: none;
      font-weight: 500;
      transition: all 0.3s ease;
    }

    a:hover {
      color: var(--accent-gold-hover);
      text-decoration: underline;
    }

    /* Nav buttons mejorados */
    .nav-buttons {
      display: flex;
      gap: 15px;
      align-items: center;
      flex-wrap: wrap;
    }

    /* Foto preview */
    .foto-preview {
      margin-top: 10px;
      text-align: center;
    }

    .foto-preview img {
      max-width: 200px;
      max-height: 200px;
      border-radius: 10px;
      border: 2px solid var(--accent-gold);
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }

    /* Camera buttons */
    .camera-options {
      display: flex;
      gap: 10px;
      margin-top: 10px;
      justify-content: center;
    }

    .camera-btn {
      background: linear-gradient(135deg, var(--accent-purple), #6f4e7c);
      color: white;
      border: none;
      padding: 8px 16px;
      border-radius: 20px;
      font-size: 0.85rem;
      cursor: pointer;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      gap: 5px;
    }

    .camera-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(139, 90, 150, 0.3);
    }

    /* Animaciones */
    @keyframes formFadeIn {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    @keyframes float {
      0%, 100% { transform: translateY(0px) rotate(0deg); }
      33% { transform: translateY(-30px) rotate(120deg); }
      66% { transform: translateY(-60px) rotate(240deg); }
    }

    /* Responsive */
    @media (max-width: 768px) {
      .form-container {
        padding: 2rem 1.5rem;
        margin: 20px;
      }
      
      .form-container h2 {
        font-size: 2rem;
      }
      
      .hero-register {
        padding: 100px 10px 40px;
      }
      
      .camera-options {
        flex-direction: column;
        align-items: center;
      }
    }

    /* Estilos específicos para campos de archivo */
    .file-upload-section {
      background: rgba(248, 249, 250, 0.8);
      border-radius: 15px;
      padding: 20px;
      margin-bottom: 20px;
      border: 2px dashed rgba(212, 175, 55, 0.3);
      transition: all 0.3s ease;
    }

    .file-upload-section:hover {
      border-color: var(--accent-gold);
      background: rgba(248, 249, 250, 0.95);
    }

    .file-upload-section h5 {
      color: var(--accent-gold);
      margin-bottom: 15px;
      font-size: 1.1rem;
    }

    .conditional-field {
      opacity: 0.7;
      transition: opacity 0.3s ease;
    }

    .conditional-field.required-field {
      opacity: 1;
    }
  </style>
</head>

<body>
  <!-- Barra de navegación -->
  <header>
    <nav class="navbar navbar-expand-lg navbar-dark bg-black">
      <div class="container">
        <a class="navbar-brand" href="index.php">Reminiscencia Photography</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
          <div class="nav-buttons ms-auto">
            <ul class="navbar-nav">
              <li class="nav-item">
                <a class="nav-link" href="https://wa.me/524491543138?text=Hola,%20quiero%20pedir%20informes%20sobre%20sus%20servicios." style="color: white;">
                  <i class="bi bi-headset"></i> Atención a Clientes
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="agendar_llamada.php" style="color: white;">
                  <i class="bi bi-telephone"></i> Agendar Llamada
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="login_cliente.php" style="color: white;">
                  <i class="bi bi-person"></i> Clientes
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link active" href="login_colaborador.php" style="color: var(--accent-gold);">
                  <i class="bi bi-people"></i> Colaboradores
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="admin_login.php" style="color: white;">
                  <i class="bi bi-shield-lock"></i> Admin
                </a>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </nav>
  </header>
  
  <!-- Hero Section de Registro -->
  <section class="hero-register">
    <div class="particles">
      <div class="particle"></div>
      <div class="particle"></div>
      <div class="particle"></div>
      <div class="particle"></div>
      <div class="particle"></div>
    </div>
    
    <div class="form-container">
      <h2>Registro de Colaborador</h2>

      <?php if ($error): ?>
        <div class="alert alert-danger">
          <i class="bi bi-exclamation-triangle-fill me-2"></i>
          <?php echo $error; ?>
        </div>
      <?php endif; ?>

      <?php if ($success): ?>
        <div class="alert alert-success">
          <i class="bi bi-check-circle-fill me-2"></i>
          <?php echo $success; ?>
        </div>
      <?php endif; ?>

      <form method="POST" id="registerForm" enctype="multipart/form-data">
        <!-- Campo oculto para token CSRF -->
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        
        <div class="mb-3">
          <label for="nombre" class="form-label">Nombre Completo <span class="required">*</span></label>
          <input type="text" class="form-control" id="nombre" name="nombre" required 
                 maxlength="100" value="<?php echo htmlspecialchars($_POST['nombre'] ?? '') ?>">
        </div>

        <div class="mb-3">
          <label for="email" class="form-label">Email <span class="required">*</span></label>
          <input type="email" class="form-control" id="email" name="email" required 
                 value="<?php echo htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>

        <div class="mb-3">
          <label for="telefono" class="form-label">Teléfono</label>
          <input type="tel" class="form-control" id="telefono" name="telefono" 
                 value="<?php echo htmlspecialchars($_POST['telefono'] ?? '') ?>" 
                 placeholder="+52 449 123 4567" maxlength="20">
          <small class="form-text text-muted">Formato: +52 449 123 4567</small>
        </div>

        <div class="row">
          <div class="col-md-6 mb-3">
            <label for="tipo" class="form-label">Tipo de Colaborador</label>
            <select class="form-select" id="tipo" name="tipo" onchange="togglePortfolioField()">
              <option value="">Seleccionar tipo</option>
              <option value="fotografo" <?php echo (($_POST['tipo'] ?? '') === 'fotografo') ? 'selected' : ''; ?>>Fotógrafo</option>
              <option value="videografo" <?php echo (($_POST['tipo'] ?? '') === 'videografo') ? 'selected' : ''; ?>>Videógrafo</option>
              <option value="auxiliar" <?php echo (($_POST['tipo'] ?? '') === 'auxiliar') ? 'selected' : ''; ?>>Auxiliar</option>
            </select>
          </div>

          <div class="col-md-6 mb-3">
            <label for="rango" class="form-label">Rango</label>
            <select class="form-select" id="rango" name="rango">
              <option value="">Seleccionar rango</option>
              <option value="I" <?php echo (($_POST['rango'] ?? '') === 'I') ? 'selected' : ''; ?>>Rango I</option>
              <option value="II" <?php echo (($_POST['rango'] ?? '') === 'II') ? 'selected' : ''; ?>>Rango II</option>
              <option value="III" <?php echo (($_POST['rango'] ?? '') === 'III') ? 'selected' : ''; ?>>Rango III</option>
            </select>
          </div>
        </div>

        <!-- Sección de archivos -->
        <div class="file-upload-section">
          <h5><i class="bi bi-cloud-upload"></i> Documentos Requeridos</h5>
          
          <!-- Campo mejorado para la foto de identificación -->
          <div class="mb-3">
            <label for="foto_id" class="form-label">Foto de identificación <span class="required">*</span></label>
            <input type="file" class="form-control" id="foto_id" name="foto_id" accept="image/*" required>
            <div class="camera-options">
              <button type="button" class="camera-btn" onclick="openCamera()">
                <i class="bi bi-camera"></i> Tomar Foto
              </button>
              <button type="button" class="camera-btn" onclick="selectFile()">
                <i class="bi bi-folder"></i> Seleccionar Archivo
              </button>
            </div>
            <small class="form-text text-muted">Puede tomar una foto con su cámara o subir un archivo de imagen (JPG, PNG, GIF; máximo 5MB)</small>
            <div class="foto-preview" id="fotoPreview" style="display: none;">
              <img id="previewImg" src="" alt="Vista previa">
            </div>
          </div>

          <!-- Campo para CV -->
          <div class="mb-3">
            <label for="cv" class="form-label">Curriculum Vitae (CV) <span class="required">*</span></label>
            <input type="file" class="form-control" id="cv" name="cv" accept=".pdf,.doc,.docx" required>
            <small class="form-text text-muted">Formatos permitidos: PDF, DOC, DOCX (máximo 10MB)</small>
          </div>

          <!-- Campo para portafolio (condicional) -->
          <div class="mb-3 conditional-field" id="portfolioField">
            <label for="portfolio" class="form-label">Portafolio <span class="required" id="portfolioRequired">*</span></label>
            <input type="file" class="form-control" id="portfolio" name="portfolio" accept=".pdf,.zip,.rar">
            <small class="form-text text-muted">Formatos permitidos: PDF, ZIP, RAR (máximo 50MB). <strong>No requerido para auxiliares.</strong></small>
          </div>
        </div>

        <div class="mb-3 password-field">
          <label for="password" class="form-label">Contraseña <span class="required">*</span> (mínimo 8 caracteres)</label>
          <input type="password" class="form-control" id="password" name="password" required>
          <button type="button" class="password-toggle" onclick="togglePassword('password')">
            <i class="bi bi-eye" id="password-icon"></i>
          </button>
        </div>

        <div class="mb-3 password-field">
          <label for="password_confirm" class="form-label">Confirmar Contraseña <span class="required">*</span></label>
          <input type="password" class="form-control" id="password_confirm" name="password_confirm" required>
          <button type="button" class="password-toggle" onclick="togglePassword('password_confirm')">
            <i class="bi bi-eye" id="password_confirm-icon"></i>
          </button>
        </div>

        <div class="d-grid mb-3">
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-person-plus me-2"></i>
            Enviar Solicitud
          </button>
        </div>

        <div class="text-center">
          <p class="mb-2">¿Ya tienes una cuenta? <a href="login_colaborador.php">Inicia sesión aquí</a></p>
          <p class="mb-0">¿Eres cliente? <a href="register_cliente.php">Regístrate como cliente</a></p>
        </div>
      </form>
    </div>
  </section>

  <!-- Vincular Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
  
  <script>
    // Efecto de navbar al hacer scroll
    window.addEventListener('scroll', function() {
      const navbar = document.querySelector('.navbar');
      if (window.scrollY > 50) {
        navbar.classList.add('scrolled');
      } else {
        navbar.classList.remove('scrolled');
      }
    });

    // Toggle password visibility
    function togglePassword(id) {
      const input = document.getElementById(id);
      const icon = document.getElementById(id + '-icon');
      
      if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
      } else {
        input.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
      }
    }

    // Funciones para manejo de cámara y archivos
    function openCamera() {
      const input = document.getElementById('foto_id');
      input.setAttribute('capture', 'camera');
      input.click();
    }

    function selectFile() {
      const input = document.getElementById('foto_id');
      input.removeAttribute('capture');
      input.click();
    }

    // Preview de imagen
    document.getElementById('foto_id').addEventListener('change', function(e) {
      const file = e.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
          document.getElementById('previewImg').src = e.target.result;
          document.getElementById('fotoPreview').style.display = 'block';
        };
        reader.readAsDataURL(file);
      }
    });

    // Toggle campo de portafolio según tipo de colaborador
    function togglePortfolioField() {
      const tipo = document.getElementById('tipo').value;
      const portfolioField = document.getElementById('portfolioField');
      const portfolioInput = document.getElementById('portfolio');
      const portfolioRequired = document.getElementById('portfolioRequired');
      
      if (tipo === 'auxiliar') {
        portfolioField.classList.remove('required-field');
        portfolioInput.removeAttribute('required');
        portfolioRequired.style.display = 'none';
      } else {
        portfolioField.classList.add('required-field');
        portfolioInput.setAttribute('required', 'required');
        portfolioRequired.style.display = 'inline';
      }
    }

    // Inicializar el estado del campo de portafolio
    document.addEventListener('DOMContentLoaded', function() {
      togglePortfolioField();
    });

    // Validación de archivos
    document.getElementById('cv').addEventListener('change', function(e) {
      const file = e.target.files[0];
      if (file) {
        const allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        if (!allowedTypes.includes(file.type)) {
          alert('Formato de CV no válido. Use PDF, DOC o DOCX');
          this.value = '';
        } else if (file.size > 10 * 1024 * 1024) {
          alert('El CV es demasiado grande (máximo 10MB)');
          this.value = '';
        }
      }
    });

    document.getElementById('portfolio').addEventListener('change', function(e) {
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

