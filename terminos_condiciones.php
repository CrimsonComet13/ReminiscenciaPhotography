<?php
// terminos_condiciones.php  
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Términos y Condiciones - Reminiscencia Photography</title>
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
    
    .terms-content {
      max-width: 900px;
      margin: 0 auto;
      background: rgba(255, 255, 255, 0.9);
      backdrop-filter: blur(10px);
      border-radius: 25px;
      padding: 3rem;
      box-shadow: var(--shadow-soft);
      border: 1px solid rgba(255, 255, 255, 0.3);
    }
    
    .terms-content h2 {
      color: var(--accent-purple);
      margin-bottom: 1.5rem;
      font-size: 2rem;
      position: relative;
      padding-bottom: 0.5rem;
    }
    
    .terms-content h2::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      width: 50px;
      height: 3px;
      background: linear-gradient(90deg, var(--accent-gold), var(--accent-purple));
      border-radius: 2px;
    }
    
    .terms-content h3 {
      color: var(--primary-dark);
      margin-top: 2rem;
      margin-bottom: 1rem;
      font-size: 1.4rem;
    }
    
    .terms-content p {
      margin-bottom: 1.2rem;
      text-align: justify;
      font-size: 1rem;
      line-height: 1.8;
    }
    
    .terms-content ul {
      margin-bottom: 1.5rem;
      padding-left: 2rem;
    }
    
    .terms-content li {
      margin-bottom: 0.5rem;
      line-height: 1.6;
    }
    
    .highlight-box {
      background: linear-gradient(135deg, rgba(212,175,55,0.1) 0%, rgba(139,90,150,0.1) 100%);
      border-radius: 15px;
      padding: 2rem;
      margin: 2rem 0;
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
      
      .terms-content {
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
      <h1>Términos y Condiciones</h1>
      <p>Conoce las condiciones de nuestros servicios fotográficos</p>
    </div>
  </section>
  
  <!-- Content Section -->
  <section class="content-section">
    <div class="container">
      <div class="terms-content">
        <div class="text-center mb-4">
          <a href="index.php" class="btn-back">
            <i class="bi bi-arrow-left"></i> Volver al Inicio
          </a>
        </div>
        
        <h2>Términos y Condiciones de Servicio</h2>
        <p><strong>Fecha de última actualización:</strong> Diciembre 2024</p>
        
        <div class="highlight-box">
          <p><strong>Al contratar los servicios de Reminiscencia Photography, usted acepta estar sujeto a los siguientes términos y condiciones.</strong></p>
        </div>
        
        <h3>1. Definiciones</h3>
        <p>Para efectos de estos términos:</p>
        <ul>
          <li><strong>"Prestador":</strong> Reminiscencia Photography</li>
          <li><strong>"Cliente":</strong> La persona física o moral que contrata los servicios</li>
          <li><strong>"Evento":</strong> La celebración, ceremonia o actividad a fotografiar</li>
          <li><strong>"Productos":</strong> Fotografías, álbumes y cualquier material entregable</li>
        </ul>
        
        <h3>2. Servicios Ofrecidos</h3>
        <p>Reminiscencia Photography ofrece servicios profesionales de fotografía para:</p>
        <ul>
          <li>Bodas y ceremonias religiosas</li>
          <li>Celebraciones de XV años</li>
          <li>Eventos sociales y corporativos</li>
          <li>Sesiones fotográficas especializadas</li>
          <li>Productos fotográficos personalizados</li>
        </ul>
        
        <h3>3. Contratación y Reservas</h3>
        <p>Para confirmar la reserva de fecha es necesario:</p>
        <ul>
          <li>Firma del contrato de servicios</li>
          <li>Pago del anticipo acordado (mínimo 50% del total)</li>
          <li>Proporcionar información completa del evento</li>
          <li>Confirmar disponibilidad de fecha con 48 horas de anticipación</li>
        </ul>
        
        <div class="highlight-box">
          <h4>Política de Anticipos</h4>
          <p>El anticipo no es reembolsable en caso de cancelación por parte del cliente con menos de 30 días de anticipación al evento.</p>
        </div>
        
        <h3>4. Precios y Forma de Pago</h3>
        <ul>
          <li>Los precios están sujetos a cambios sin previo aviso</li>
          <li>Se requiere anticipo del 50% para apartar fecha</li>
          <li>El saldo restante se liquida el día del evento</li>
          <li>Aceptamos pagos en efectivo, transferencia bancaria y tarjetas</li>
          <li>Los precios incluyen IVA cuando aplique</li>
        </ul>
        
        <h3>5. Cancelaciones y Reprogramaciones</h3>
        <p><strong>Por parte del Cliente:</strong></p>
        <ul>
          <li>Más de 30 días: Reembolso del 80% del anticipo</li>
          <li>15-30 días: Reembolso del 50% del anticipo</li>
          <li>Menos de 15 días: Sin reembolso del anticipo</li>
          <li>Reprogramación: Sujeta a disponibilidad</li>
        </ul>
        
        <p><strong>Por parte del Prestador:</strong></p>
        <ul>
          <li>Reembolso completo del anticipo</li>
          <li>Recomendación de fotógrafo alternativo</li>
          <li>Compensación adicional según el caso</li>
        </ul>
        
        <h3>6. Responsabilidades del Cliente</h3>
        <ul>
          <li>Proporcionar información precisa del evento</li>
          <li>Facilitar el acceso a las locaciones</li>
          <li>Informar sobre restricciones del venue</li>
          <li>Respetar los horarios acordados</li>
          <li>Designar un coordinador de evento</li>
          <li>Garantizar la seguridad del equipo fotográfico</li>
        </ul>
        
        <h3>7. Responsabilidades del Prestador</h3>
        <ul>
          <li>Llegar puntualmente al evento</li>
          <li>Utilizar equipo profesional y de respaldo</li>
          <li>Entregar productos en tiempo y forma acordados</li>
          <li>Mantener confidencialidad de la información del cliente</li>
          <li>Respetar las indicaciones específicas del cliente</li>
        </ul>
        
        <h3>8. Entrega de Productos</h3>
        <p><strong>Tiempos de entrega:</strong></p>
        <ul>
          <li>Fotografías digitales: 15-30 días hábiles</li>
          <li>Álbumes impresos: 45-60 días hábiles</li>
          <li>Productos especiales: Según especificaciones</li>
        </ul>
        
        <p><strong>Modalidades de entrega:</strong></p>
        <ul>
          <li>Galería digital en línea</li>
          <li>USB o dispositivo de almacenamiento</li>
          <li>Envío a domicilio (costo adicional)</li>
          <li>Recolección en oficinas</li>
        </ul>
        
        <h3>9. Derechos de Autor y Uso de Imágenes</h3>
        <ul>
          <li>Los derechos de autor pertenecen a Reminiscencia Photography</li>
          <li>El cliente tiene derecho de uso personal y familiar</li>
          <li>Prohibida la reproducción comercial sin autorización</li>
          <li>El prestador puede usar las imágenes para portafolio y promoción</li>
          <li>Se respetará la privacidad según las indicaciones del cliente</li>
        </ul>
        
        <h3>10. Limitaciones de Responsabilidad</h3>
        <p>El prestador no será responsable por:</p>
        <ul>
          <li>Condiciones climáticas adversas</li>
          <li>Restricciones impuestas por terceros</li>
          <li>Fallas técnicas de venues (iluminación, sonido)</li>
          <li>Cambios de último momento en la programación</li>
          <li>Pérdida de datos por causas de fuerza mayor</li>
        </ul>
        
        <h3>11. Casos de Fuerza Mayor</h3>
        <p>En caso de eventos extraordinarios que impidan la prestación del servicio (pandemia, desastres naturales, etc.), ambas partes podrán renegociar los términos del contrato de común acuerdo.</p>
        
        <h3>12. Política de Calidad</h3>
        <ul>
          <li>Revisión y retoque profesional de todas las imágenes</li>
          <li>Selección cuidadosa de las mejores fotografías</li>
          <li>Garantía de calidad en productos impresos</li>
          <li>Atención personalizada durante todo el proceso</li>
        </ul>
        
        <h3>13. Redes Sociales y Promoción</h3>
        <p>Salvo indicación contraria del cliente:</p>
        <ul>
          <li>Las imágenes pueden ser publicadas en redes sociales</li>
          <li>Se incluirán créditos fotográficos correspondientes</li>
          <li>Se respetará la privacidad de menores de edad</li>
          <li>El cliente puede solicitar la remoción de imágenes específicas</li>
        </ul>
        
        <h3>14. Resolución de Conflictos</h3>
        <p>En caso de disputas:</p>
        <ul>
          <li>Se privilegiará el diálogo y la mediación</li>
          <li>Se buscará una solución satisfactoria para ambas partes</li>
          <li>Los tribunales de Aguascalientes tendrán jurisdicción</li>
          <li>Se aplicará la legislación mexicana vigente</li>
        </ul>
        
        <div class="highlight-box">
          <h4>Contacto para Dudas y Aclaraciones</h4>
          <p><strong>WhatsApp:</strong> +52 449 154 3138</p>
          <p><strong>Redes sociales:</strong> @reminiscencias.photography</p>
          <p><strong>Horario de atención:</strong> Lunes a domingo de 9:00 AM a 8:00 PM</p>
        </div>
        
        <h3>15. Modificaciones a los Términos</h3>
        <p>Reminiscencia Photography se reserva el derecho de modificar estos términos y condiciones. Las modificaciones entrarán en vigor al momento de su publicación en el sitio web oficial.</p>
        
        <h3>16. Aceptación de Términos</h3>
        <p>La contratación de nuestros servicios implica la aceptación total de estos términos y condiciones. Es responsabilidad del cliente leer y comprender todas las cláusulas antes de la firma del contrato.</p>
        
        <div class="highlight-box">
          <p class="text-center mb-0">
            <strong>Gracias por confiar en Reminiscencia Photography para capturar sus momentos más especiales.</strong>
          </p>
        </div>
        
        <p class="text-center mt-4">
          <small class="text-muted">
            Estos términos y condiciones son efectivos a partir de diciembre de 2024.
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