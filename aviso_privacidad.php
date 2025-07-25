<?php
// aviso_privacidad.php  
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Aviso de Privacidad - Reminiscencia Photography</title>
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
      background: rgba(10, 10, 10, 0.95) !important;
      backdrop-filter: blur(20px);
      border-bottom: 1px solid var(--border-light);
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
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
    
    .nav-link:hover {
      color: var(--accent-gold) !important;
      background: var(--glass-bg);
      transform: translateY(-2px);
      box-shadow: 0 10px 25px rgba(212, 175, 55, 0.2);
    }
    
    /* Page Header */
    .page-header {
      background: linear-gradient(135deg, var(--primary-dark) 0%, var(--secondary-dark) 100%);
      color: white;
      padding: 150px 0 80px;
      text-align: center;
      position: relative;
      overflow: hidden;
    }
    
    .page-header::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="0.5" fill="rgba(255,255,255,0.02)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
      opacity: 0.5;
    }
    
    .page-header h1 {
      font-size: 3.5rem;
      margin-bottom: 1rem;
      position: relative;
      z-index: 2;
    }
    
    .page-header p {
      font-size: 1.2rem;
      opacity: 0.9;
      position: relative;
      z-index: 2;
    }
    
    /* Content Section */
    .content-section {
      padding: 80px 0;
      background: white;
      position: relative;
    }
    
    .privacy-content {
      max-width: 900px;
      margin: 0 auto;
      background: rgba(255, 255, 255, 0.9);
      backdrop-filter: blur(10px);
      border-radius: 25px;
      padding: 3rem;
      box-shadow: var(--shadow-soft);
      border: 1px solid rgba(255, 255, 255, 0.3);
    }
    
    .privacy-content h2 {
      color: var(--accent-purple);
      margin-bottom: 1.5rem;
      font-size: 2rem;
      position: relative;
      padding-bottom: 0.5rem;
    }
    
    .privacy-content h2::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      width: 50px;
      height: 3px;
      background: linear-gradient(90deg, var(--accent-gold), var(--accent-purple));
      border-radius: 2px;
    }
    
    .privacy-content h3 {
      color: var(--primary-dark);
      margin-top: 2rem;
      margin-bottom: 1rem;
      font-size: 1.4rem;
    }
    
    .privacy-content p {
      margin-bottom: 1.2rem;
      text-align: justify;
      font-size: 1rem;
      line-height: 1.8;
    }
    
    .privacy-content ul {
      margin-bottom: 1.5rem;
      padding-left: 2rem;
    }
    
    .privacy-content li {
      margin-bottom: 0.5rem;
      line-height: 1.6;
    }
    
    .contact-info {
      background: linear-gradient(135deg, rgba(212,175,55,0.1) 0%, rgba(139,90,150,0.1) 100%);
      border-radius: 15px;
      padding: 2rem;
      margin-top: 2rem;
      border-left: 5px solid var(--accent-gold);
    }
    
    .btn-back {
      background: linear-gradient(135deg, var(--accent-gold), var(--accent-gold-hover));
      color: white;
      padding: 12px 32px;
      border-radius: 50px;
      font-weight: 500;
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      border: none;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 10px;
      box-shadow: 0 8px 25px rgba(212, 175, 55, 0.3);
    }
    
    .btn-back:hover {
      transform: translateY(-3px);
      box-shadow: 0 15px 35px rgba(212, 175, 55, 0.4);
      color: white;
      text-decoration: none;
    }
    
    /* Footer moderno */
    footer {
      background: linear-gradient(135deg, var(--primary-dark) 0%, var(--secondary-dark) 100%);
      color: white;
      padding: 3rem 0;
      position: relative;
    }
    
    footer::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 1px;
      background: linear-gradient(90deg, transparent, var(--accent-gold), transparent);
    }
    
    .social-links a {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 45px;
      height: 45px;
      border-radius: 50%;
      background: var(--glass-bg);
      border: 1px solid var(--border-light);
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      margin: 0 8px;
    }
    
    .social-links a:hover {
      transform: translateY(-5px) scale(1.1);
      background: var(--accent-gold);
      border-color: var(--accent-gold);
      box-shadow: 0 10px 25px rgba(212, 175, 55, 0.4);
    }
    
    /* Responsive */
    @media (max-width: 768px) {
      .page-header {
        padding: 120px 0 60px;
      }
      
      .page-header h1 {
        font-size: 2.5rem;
      }
      
      .privacy-content {
        padding: 2rem 1.5rem;
        margin: 0 1rem;
      }
      
      .content-section {
        padding: 60px 0;
      }
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
                <a class="nav-link" href="index.php" style="color: white;">
                  <i class="bi bi-house"></i> Inicio
                </a>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </nav>
  </header>
  
  <!-- Page Header -->
  <section class="page-header">
    <div class="container">
      <h1>Aviso de Privacidad</h1>
      <p>Protegemos tu información personal con el máximo cuidado</p>
    </div>
  </section>
  
  <!-- Content Section -->
  <section class="content-section">
    <div class="container">
      <div class="privacy-content">
        <div class="text-center mb-4">
          <a href="index.php" class="btn-back">
            <i class="bi bi-arrow-left"></i> Volver al Inicio
          </a>
        </div>
        
        <h2>Reminiscencia Photography</h2>
        <p><strong>Fecha de última actualización:</strong> Diciembre 2024</p>
        
        <h3>1. Responsable del Tratamiento de Datos</h3>
        <p>Reminiscencia Photography, con domicilio en Aguascalientes, Aguascalientes, México, es responsable del tratamiento de sus datos personales conforme a la Ley Federal de Protección de Datos Personales en Posesión de los Particulares.</p>
        
        <h3>2. Datos Personales que Recabamos</h3>
        <p>Para brindar nuestros servicios de fotografía profesional, recabamos los siguientes datos personales:</p>
        <ul>
          <li>Datos de identificación: nombre completo, fecha de nacimiento</li>
          <li>Datos de contacto: teléfono, correo electrónico, domicilio</li>
          <li>Datos del evento: fecha, tipo de celebración, ubicación</li>
          <li>Datos de facturación: RFC, razón social (en caso de requerirse factura)</li>
          <li>Imágenes fotográficas y audiovisuales del evento contratado</li>
        </ul>
        
        <h3>3. Finalidades del Tratamiento</h3>
        <p>Sus datos personales serán utilizados para las siguientes finalidades:</p>
        <ul>
          <li>Prestación de servicios fotográficos contratados</li>
          <li>Comunicación relacionada con el servicio</li>
          <li>Facturación y cobranza</li>
          <li>Entrega de productos fotográficos</li>
          <li>Seguimiento post-evento y atención al cliente</li>
          <li>Promoción de servicios (con su consentimiento)</li>
          <li>Creación de portafolio profesional (con su autorización)</li>
        </ul>
        
        <h3>4. Uso de Imágenes Fotográficas</h3>
        <p>Las fotografías tomadas durante el evento podrán ser utilizadas por Reminiscencia Photography para:</p>
        <ul>
          <li>Entrega al cliente según el paquete contratado</li>
          <li>Portafolio profesional y material promocional (con autorización previa)</li>
          <li>Redes sociales y sitio web (con consentimiento explícito)</li>
          <li>Participación en concursos de fotografía profesional</li>
        </ul>
        
        <h3>5. Fundamento Legal</h3>
        <p>El tratamiento de sus datos personales se fundamenta en:</p>
        <ul>
          <li>El consentimiento otorgado al contratar nuestros servicios</li>
          <li>La ejecución del contrato de servicios fotográficos</li>
          <li>Cumplimiento de obligaciones fiscales y legales</li>
        </ul>
        
        <h3>6. Transferencias de Datos</h3>
        <p>Sus datos personales no serán transferidos a terceros, salvo en los siguientes casos:</p>
        <ul>
          <li>Laboratorios fotográficos para impresión de productos</li>
          <li>Proveedores de servicios tecnológicos (almacenamiento en la nube)</li>
          <li>Autoridades competentes cuando sea requerido por ley</li>
        </ul>
        
        <h3>7. Tiempo de Conservación</h3>
        <p>Sus datos personales serán conservados durante:</p>
        <ul>
          <li>Datos contractuales: 5 años posteriores a la prestación del servicio</li>
          <li>Datos fiscales: según lo establecido por las autoridades fiscales</li>
          <li>Fotografías: de forma indefinida para efectos de portafolio (con su autorización)</li>
        </ul>
        
        <h3>8. Derechos ARCO</h3>
        <p>Usted tiene derecho a:</p>
        <ul>
          <li><strong>Acceder</strong> a sus datos personales en nuestro poder</li>
          <li><strong>Rectificar</strong> datos inexactos o incompletos</li>
          <li><strong>Cancelar</strong> sus datos cuando considere que no son necesarios</li>
          <li><strong>Oponerse</strong> al tratamiento para fines específicos</li>
        </ul>
        
        <h3>9. Revocación del Consentimiento</h3>
        <p>Usted puede revocar su consentimiento para el tratamiento de datos personales en cualquier momento, sin que esto afecte la licitud del tratamiento previo a la revocación.</p>
        
        <h3>10. Medidas de Seguridad</h3>
        <p>Implementamos medidas de seguridad físicas, técnicas y administrativas para proteger sus datos personales contra daño, pérdida, alteración, destrucción o uso no autorizado.</p>
        
        <div class="contact-info">
          <h3>Contacto para Ejercer sus Derechos</h3>
          <p>Para ejercer sus derechos ARCO o realizar consultas sobre este aviso de privacidad, puede contactarnos a través de:</p>
          <p><strong>WhatsApp:</strong> +52 449 154 3138</p>
          <p><strong>Redes sociales:</strong> @reminiscencias.photography</p>
          <p><strong>Tiempo de respuesta:</strong> 20 días hábiles</p>
        </div>
        
        <h3>11. Cambios al Aviso de Privacidad</h3>
        <p>Nos reservamos el derecho de modificar este aviso de privacidad. Las modificaciones serán comunicadas a través de nuestro sitio web y redes sociales oficiales.</p>
        
        <p class="text-center mt-4">
          <small class="text-muted">
            Al contratar nuestros servicios, usted acepta haber leído y estar de acuerdo con los términos establecidos en este Aviso de Privacidad.
          </small>
        </p>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer>
    <div class="container">
      <div class="row">
        <div class="col-12 text-center">
          <p>&copy; 2024 Reminiscencia Photography. Todos los derechos reservados.</p>
          <div class="social-links mt-3">
            <a href="https://www.facebook.com/presenda.ph" class="text-white"><i class="bi bi-facebook"></i></a>
            <a href="https://www.instagram.com/reminiscencias.photography/" class="text-white"><i class="bi bi-instagram"></i></a>
            <a href="https://wa.me/524491543138?text=Hola,%20quiero%20pedir%20informes%20sobre%20sus%20servicios." class="text-white"><i class="bi bi-whatsapp"></i></a>
          </div>
        </div>
      </div>
    </div>
  </footer>

  <!-- Vincular Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
</body>
</html>