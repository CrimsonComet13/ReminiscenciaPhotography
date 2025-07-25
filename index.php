<?php
// index.php  
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reminiscencia Photography</title>
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
    
    /* Hero Section con parallax mejorado */
    #home {
      background-image: 
        linear-gradient(135deg, rgba(10, 10, 10, 0.7) 0%, rgba(139, 90, 150, 0.3) 100%),
        url('assets/images/Foto-20.jpg');
      background-size: cover;
      background-position: center;
      background-attachment: fixed;
      height: 100vh;
      color: white;
      display: flex;
      justify-content: center;
      align-items: center;
      text-align: center;
      position: relative;
      overflow: hidden;
    }
    
    #home::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: radial-gradient(circle at center, transparent 0%, rgba(0,0,0,0.3) 100%);
    }
    
    .hero-content {
      max-width: 900px;
      padding: 3rem;
      position: relative;
      z-index: 2;
      animation: heroFadeIn 1.2s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .hero h1 {
      font-size: 4.5rem;
      margin-bottom: 1.5rem;
      text-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
      background: linear-gradient(135deg, #fff 0%, var(--accent-gold) 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      line-height: 1.2;
    }
    
    .hero p {
      font-size: 1.4rem;
      margin-bottom: 2.5rem;
      text-shadow: 0 5px 15px rgba(0, 0, 0, 0.5);
      opacity: 0.95;
      font-weight: 300;
    }
    
    .hero .btn {
      padding: 15px 40px;
      font-size: 1.1rem;
      border: 2px solid rgba(255,255,255,0.3);
      background: var(--glass-bg);
      backdrop-filter: blur(10px);
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      position: relative;
      overflow: hidden;
    }
    
    .hero .btn::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
      transition: left 0.6s;
    }
    
    .hero .btn:hover::before {
      left: 100%;
    }
    
    .hero .btn:hover {
      transform: translateY(-5px);
      box-shadow: 0 20px 40px rgba(212, 175, 55, 0.3);
      border-color: var(--accent-gold);
      background: rgba(212, 175, 55, 0.1);
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
    
    /* Secciones con diseño moderno */
    section {
      padding: 7rem 0;
      position: relative;
    }
    
    .section-title {
      position: relative;
      margin-bottom: 4rem;
      padding-bottom: 1.5rem;
      font-size: 3rem;
      text-align: center;
    }
    
    .section-title::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 50%;
      transform: translateX(-50%);
      width: 100px;
      height: 4px;
      background: linear-gradient(90deg, var(--accent-gold), var(--accent-purple));
      border-radius: 2px;
    }
    
    /* Cards de servicios con glassmorphism */
    #services {
      background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
      position: relative;
    }
    
    #services::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 100px;
      background: linear-gradient(180deg, var(--light-bg) 0%, transparent 100%);
    }
    
    .service-card {
      border: none;
      border-radius: 25px;
      overflow: hidden;
      transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
      box-shadow: var(--shadow-soft);
      margin-bottom: 2rem;
      height: 100%;
      background: rgba(255, 255, 255, 0.9);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.3);
      position: relative;
    }
    
    .service-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: linear-gradient(135deg, rgba(212, 175, 55, 0.05) 0%, rgba(139, 90, 150, 0.05) 100%);
      opacity: 0;
      transition: opacity 0.5s ease;
      z-index: 1;
    }
    
    .service-card:hover::before {
      opacity: 1;
    }
    
    .service-card:hover {
      transform: translateY(-15px) scale(1.02);
      box-shadow: var(--shadow-hover);
      border-color: rgba(212, 175, 55, 0.3);
    }
    
    .service-card img {
      height: 280px;
      object-fit: cover;
      transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
      position: relative;
      z-index: 2;
    }
    
    .service-card:hover img {
      transform: scale(1.1);
      filter: brightness(1.1) saturate(1.2);
    }
    
    .service-card .card-body {
      padding: 2rem;
      position: relative;
      z-index: 3;
    }
    
    .service-card .card-title {
      font-size: 1.5rem;
      margin-bottom: 1rem;
      color: var(--primary-dark);
    }
    
    .service-card .card-text {
      color: var(--text-muted);
      line-height: 1.6;
      margin-bottom: 1.5rem;
    }
    
    /* Portfolio con efecto cinematográfico */
    #portfolio {
      background: linear-gradient(135deg, var(--primary-dark) 0%, var(--secondary-dark) 100%);
      color: white;
      position: relative;
    }
    
    #portfolio::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="0.5" fill="rgba(255,255,255,0.02)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
      opacity: 0.5;
    }
    
    .carousel-inner {
      border-radius: 20px;
      overflow: hidden;
      box-shadow: 0 25px 60px rgba(0, 0, 0, 0.4);
    }
    
    .carousel-inner img {
      height: 650px;
      object-fit: cover;
      width: 100%;
      transition: transform 0.8s ease;
    }
    
    .carousel-item.active img {
      transform: scale(1.02);
    }
    
    #portfolioCarousel .carousel-caption {
      background: linear-gradient(135deg, rgba(0, 0, 0, 0.8) 0%, rgba(26, 26, 26, 0.9) 100%);
      border-radius: 15px;
      padding: 1.5rem 2rem;
      bottom: 40px;
      left: 50%;
      transform: translateX(-50%);
      width: 85%;
      max-width: 700px;
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.1);
      font-size: 1.2rem;
      font-weight: 300;
    }
    
    .carousel-control-prev, .carousel-control-next {
      width: 8%;
      background: linear-gradient(135deg, rgba(0,0,0,0.3), rgba(0,0,0,0.1));
      border-radius: 0 15px 15px 0;
      transition: all 0.3s ease;
    }
    
    .carousel-control-next {
      border-radius: 15px 0 0 15px;
    }
    
    .carousel-control-prev:hover, .carousel-control-next:hover {
      background: linear-gradient(135deg, rgba(212,175,55,0.3), rgba(212,175,55,0.1));
    }
    
    /* Botones modernos */
    .btn {
      padding: 12px 32px;
      border-radius: 50px;
      font-weight: 500;
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      border: none;
      position: relative;
      overflow: hidden;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      font-size: 0.9rem;
    }
    
    .btn::before {
      content: '';
      position: absolute;
      top: 50%;
      left: 50%;
      width: 0;
      height: 0;
      background: rgba(255, 255, 255, 0.1);
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
    
    .btn-dark {
      background: linear-gradient(135deg, var(--secondary-dark), var(--tertiary-dark));
      color: white;
      box-shadow: 0 8px 25px rgba(26, 26, 26, 0.3);
    }
    
    .btn-dark:hover {
      background: linear-gradient(135deg, var(--primary-dark), var(--secondary-dark));
      transform: translateY(-3px);
      box-shadow: 0 15px 35px rgba(10, 10, 10, 0.4);
    }
    
    .btn-admin {
      background: linear-gradient(135deg, var(--accent-purple), #7a4d84);
      color: white;
      box-shadow: 0 8px 25px rgba(139, 90, 150, 0.3);
    }
    
    .btn-admin:hover {
      background: linear-gradient(135deg, #7a4d84, var(--accent-purple));
      transform: translateY(-3px);
      box-shadow: 0 15px 35px rgba(139, 90, 150, 0.4);
    }
    
    /* CTA Section */
    .cta-section {
      background: linear-gradient(135deg, var(--light-bg) 0%, #f0f2f5 100%);
      position: relative;
      overflow: hidden;
    }
    
    .cta-section::before {
      content: '';
      position: absolute;
      top: -50%;
      left: -50%;
      width: 200%;
      height: 200%;
      background: radial-gradient(circle, rgba(212,175,55,0.05) 0%, transparent 70%);
      animation: rotate 20s linear infinite;
    }
    
    /* Testimonios elegantes */
    #testimonials {
      background: white;
      position: relative;
    }
    
    blockquote {
      border: none;
      background: linear-gradient(135deg, rgba(212,175,55,0.05) 0%, rgba(139,90,150,0.05) 100%);
      border-radius: 20px;
      padding: 2.5rem;
      margin: 2rem auto;
      max-width: 900px;
      font-style: italic;
      font-size: 1.2rem;
      box-shadow: var(--shadow-soft);
      position: relative;
      border-left: 5px solid var(--accent-gold);
    }
    
    blockquote::before {
      content: '"';
      font-size: 4rem;
      color: var(--accent-gold);
      position: absolute;
      top: 10px;
      left: 20px;
      font-family: 'Playfair Display', serif;
      opacity: 0.3;
    }
    
    .blockquote-footer {
      margin-top: 1.5rem;
      color: var(--accent-purple);
      font-weight: 600;
      font-style: normal;
      font-size: 1rem;
    }
    
    .blockquote-footer::before {
      content: '— ';
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
    
    /* Nav buttons mejorados */
    .nav-buttons {
      display: flex;
      gap: 15px;
      align-items: center;
      flex-wrap: wrap;
    }
    
    /* Animaciones */
    @keyframes heroFadeIn {
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
      0%, 100% {
        transform: translateY(0px) rotate(0deg);
      }
      50% {
        transform: translateY(-20px) rotate(180deg);
      }
    }
    
    @keyframes rotate {
      from { transform: rotate(0deg); }
      to { transform: rotate(360deg); }
    }
    
    /* Smooth animations on scroll */
    .fade-in {
      opacity: 0;
      transform: translateY(30px);
      transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .fade-in.visible {
      opacity: 1;
      transform: translateY(0);
    }
    
    /* Responsive mejorado */
    @media (max-width: 992px) {
      .hero h1 {
        font-size: 3.2rem;
      }
      
      .hero p {
        font-size: 1.1rem;
      }
      
      .carousel-inner img {
        height: 450px;
      }
      
      .section-title {
        font-size: 2.5rem;
      }
    }
    
    @media (max-width: 768px) {
      section {
        padding: 4rem 0;
      }
      
      .hero h1 {
        font-size: 2.5rem;
      }
      
      .hero p {
        font-size: 1rem;
      }
      
      .hero .btn {
        padding: 12px 28px;
        font-size: 1rem;
      }
      
      .service-card {
        margin-bottom: 2rem;
      }
      
      .nav-buttons {
        margin-top: 1rem;
        justify-content: center;
      }
      
      .carousel-inner img {
        height: 350px;
      }
      
      #portfolioCarousel .carousel-caption {
        font-size: 1rem;
        bottom: 20px;
        padding: 1rem 1.5rem;
      }
      
      .section-title {
        font-size: 2rem;
      }
      
      blockquote {
        font-size: 1.1rem;
        padding: 2rem;
      }
    }
    
    @media (max-width: 576px) {
      .navbar-brand {
        font-size: 1.4rem;
      }
      
      .hero h1 {
        font-size: 2rem;
      }
      
      .carousel-inner img {
        height: 280px;
      }
      
      #portfolioCarousel .carousel-caption {
        display: none;
      }
      
      .hero-content {
        padding: 2rem 1rem;
      }
      
      .service-card img {
        height: 220px;
      }
      
      .section-title {
        font-size: 1.8rem;
        margin-bottom: 3rem;
      }
    }
    
    /* Scroll indicator */
    .scroll-indicator {
      position: absolute;
      bottom: 30px;
      left: 50%;
      transform: translateX(-50%);
      color: white;
      animation: bounce 2s infinite;
      opacity: 0.8;
    }
    
    @keyframes bounce {
      0%, 20%, 50%, 80%, 100% {
        transform: translateX(-50%) translateY(0);
      }
      40% {
        transform: translateX(-50%) translateY(-10px);
      }
      60% {
        transform: translateX(-50%) translateY(-5px);
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
                <a class="nav-link" href="login_colaborador.php" style="color: white;">
                  <i class="bi bi-people"></i> Colaboradores
                </a>
              </li>
               <li class="nav-item">
                <a class="nav-link" href="login_cliente.php" style="color: white;">
                  <i class="bi bi-people"></i> Clientes
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="ecommerce.html" style="color: white;">
                  <i class="bi bi-cart"></i> Shop
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link btn-admin" href="admin_login.php" style="color: white;">
                  <i class="bi bi-shield-lock"></i> Admin
                </a>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </nav>
  </header>
  
  <!-- Hero Section -->
  <section id="home" class="hero">
    <div class="particles">
      <div class="particle"></div>
      <div class="particle"></div>
      <div class="particle"></div>
      <div class="particle"></div>
      <div class="particle"></div>
    </div>
    <div class="hero-content">
      <h1 class="display-4">Captura la esencia del momento</h1>
      <p class="lead">Fotografía profesional que trasciende el tiempo y preserva tus recuerdos más preciados</p>
      <a href="#services" class="btn btn-outline-light btn-lg mt-3">Descubre nuestros servicios</a>
    </div>
    <div class="scroll-indicator">
      <i class="bi bi-chevron-down fs-4"></i>
    </div>
  </section>
  
  <!-- Servicios -->
  <section id="services" class="services">
    <div class="container">
      <h2 class="section-title fade-in">Nuestros Servicios</h2>
      <div class="row">
        <div class="col-lg-4 col-md-6 fade-in">
          <div class="service-card card">
            <img src="assets/images/Foto-12.jpg" alt="Bodas" class="card-img-top">
            <div class="card-body">
              <h3 class="card-title">Bodas</h3>
              <p class="card-text">Documentamos el día más importante de tu vida con sensibilidad artística y atención a cada detalle emocional.</p>
              <a href="agendar_llamada.php" class="btn btn-primary">Más información</a>
            </div>
          </div>
        </div>
        <div class="col-lg-4 col-md-6 fade-in">
          <div class="service-card card">
            <img src="assets/images/Foto-4.jpg" alt="XV años" class="card-img-top">
            <div class="card-body">
              <h3 class="card-title">XV años</h3>
              <p class="card-text">Capturamos cada momento mágico de tus XV años, para que revivas esos recuerdos especiales por siempre.</p>
              <a href="agendar_llamada.php" class="btn btn-primary">Más información</a>
            </div>
          </div>
        </div>
        <div class="col-lg-4 col-md-6 fade-in">
          <div class="service-card card">
            <img src="assets/images/Foto-7.jpg" alt="Eventos" class="card-img-top">
            <div class="card-body">
              <h3 class="card-title">Eventos</h3>
              <p class="card-text">Cobertura profesional de eventos sociales y corporativos con un enfoque creativo y discreto.</p>
              <a href="agendar_llamada.php" class="btn btn-primary">Más información</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Carrusel Portafolio -->
  <section id="portfolio" class="portfolio">
    <div class="container">
      <h2 class="section-title fade-in">Nuestro Trabajo</h2>
      <div id="portfolioCarousel" class="carousel slide fade-in" data-bs-ride="carousel">
        <div class="carousel-inner">
          <div class="carousel-item active">
            <img src="assets/images/Foto-11.jpg" class="d-block w-100" alt="Mágico momento de XV años">
            <div class="carousel-caption">
              <p>Mágico momento de XV años</p>
            </div>
          </div>
          <div class="carousel-item">
            <img src="assets/images/Foto-12.jpg" class="d-block w-100" alt="¡Vivan los novios!">
            <div class="carousel-caption">
              <p>¡Vivan los novios!</p>
            </div>
          </div>
          <div class="carousel-item">
            <img src="assets/images/Foto-15.jpg" class="d-block w-100" alt="Padres Orgullosos">
            <div class="carousel-caption">
              <p>Padres Orgullosos</p>
            </div>
          </div>
          <div class="carousel-item">
            <img src="assets/images/Foto-1.jpg" class="d-block w-100" alt="Reminiscencia del XV años">
            <div class="carousel-caption">
              <p>Reminiscencia del XV años</p>
            </div>
          </div>
          <div class="carousel-item">
            <img src="assets/images/Foto-2.jpg" class="d-block w-100" alt="Antes del Altar">
            <div class="carousel-caption">
              <p>Antes del Altar</p>
            </div>
          </div>
          <div class="carousel-item">
            <img src="assets/images/Foto-3.jpg" class="d-block w-100" alt="Felicidad Absoluta">
            <div class="carousel-caption">
              <p>Felicidad Absoluta</p>
            </div>
          </div>
          <div class="carousel-item">
            <img src="assets/images/Foto-4.jpg" class="d-block w-100" alt="Sonrisas Juveniles">
            <div class="carousel-caption">
              <p>Sonrisas Juveniles</p>
            </div>
          </div>
          <div class="carousel-item">
            <img src="assets/images/Foto-5.jpg" class="d-block w-100" alt="¡Que no pare la fiesta!">
            <div class="carousel-caption">
              <p>¡Que no pare la fiesta!</p>
            </div>
          </div>
          <div class="carousel-item">
            <img src="assets/images/Foto-10.jpg" class="d-block w-100" alt="Recuerdos Invaluables">
            <div class="carousel-caption">
              <p>Recuerdos Invaluables</p>
            </div>
          </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#portfolioCarousel" data-bs-slide="prev">
          <span class="carousel-control-prev-icon" aria-hidden="true"></span>
          <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#portfolioCarousel" data-bs-slide="next">
          <span class="carousel-control-next-icon" aria-hidden="true"></span>
          <span class="visually-hidden">Next</span>
        </button>
      </div>
    </div>
  </section>

  <!-- CTA -->
  <section class="cta-section">
    <div class="container text-center">
      <h2 class="section-title fade-in">¿Listo para capturar tus momentos especiales?</h2>
      <p class="lead mb-4 fade-in">Contáctanos ahora y hagamos realidad tus ideas</p>
      <div class="d-flex justify-content-center gap-3 fade-in">
        <a href="https://wa.me/524491543138?text=Hola,%20quiero%20pedir%20informes%20sobre%20sus%20servicios." class="btn btn-dark btn-lg" target="_blank">
          <i class="bi bi-whatsapp"></i> WhatsApp
        </a>
        <a href="agendar_llamada.php" class="btn btn-primary btn-lg">
          <i class="bi bi-telephone"></i> Agendar Llamada
        </a>
      </div>
    </div>
  </section>

  <!-- Testimonios -->
  <section id="testimonials" class="testimonials">
    <div class="container">
      <h2 class="section-title fade-in">Lo que dicen nuestros clientes</h2>
      <div class="row justify-content-center">
        <div class="col-lg-10">
          <blockquote class="blockquote fade-in">
            <p>"Una experiencia increíble. Las fotos son más que perfectas, ¡capturaron cada momento mágico de nuestra boda! El equipo fue profesional y muy atento en todo momento."</p>
            <p>-Ana y Luis</p>
          </blockquote>
          <blockquote class="blockquote fade-in">
            <p>"Muy profesionales y atentos. Sin duda, los recomiendo para cualquier tipo de evento. Las fotos de los XV de mi hija superaron todas nuestras expectativas."</p>
            <p>-Carlos Gómez</p>
          </blockquote>
        </div>
      </div>
    </div>
  </section>

  <!-- Footer -->
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
          <!-- Nuevos botones de políticas -->
          <div class="mt-3 d-flex justify-content-center gap-3 flex-wrap">
            <a href="aviso_privacidad.php" class="btn btn-outline-light btn-sm border-0" style="font-size: 0.8rem; opacity: 0.7;">
              <i class="bi bi-shield-check"></i> Aviso de Privacidad
            </a>
            <a href="terminos_condiciones.php" class="btn btn-outline-light btn-sm  border-0" style="font-size: 0.8rem; opacity: 0.7;">
              <i class="bi bi-file-text"></i> Términos y Condiciones
            </a>
          </div>
          <a href="facturacion.php" class="btn btn-outline-light btn-sm  border-0 ms-4" style="font-size: 0.8rem; opacity: 0.7;">
              <i class="bi bi-file-text"></i> Facturación
            </a>
        </div>
      </div>
    </div>
  </footer>

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
    
    // Smooth scrolling para los enlaces
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', function(e) {
        e.preventDefault();
        
        const targetId = this.getAttribute('href');
        if (targetId === '#') return;
        
        const targetElement = document.querySelector(targetId);
        if (targetElement) {
          window.scrollTo({
            top: targetElement.offsetTop - 80,
            behavior: 'smooth'
          });
        }
      });
    });

    // Intersection Observer para animaciones al hacer scroll
    const observerOptions = {
      threshold: 0.2,
      rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
        }
      });
    }, observerOptions);

    // Observar todos los elementos con clase fade-in
    document.querySelectorAll('.fade-in').forEach(el => {
      observer.observe(el);
    });

    // Parallax suave para el hero
    window.addEventListener('scroll', function() {
      const scrolled = window.pageYOffset;
      const parallax = document.querySelector('#home');
      const speed = scrolled * 0.5;
      
      if (parallax) {
        parallax.style.transform = `translateY(${speed}px)`;
      }
    });

    // Efecto de typing para el título (opcional)
    function typeWriter(element, text, speed = 100) {
      let i = 0;
      element.innerHTML = '';
      
      function type() {
        if (i < text.length) {
          element.innerHTML += text.charAt(i);
          i++;
          setTimeout(type, speed);
        }
      }
      
      type();
    }

    // Aplicar efecto cuando la página carga
    window.addEventListener('load', function() {
      setTimeout(() => {
        const heroTitle = document.querySelector('.hero h1');
        if (heroTitle) {
          const originalText = heroTitle.textContent;
          typeWriter(heroTitle, originalText, 80);
        }
      }, 500);
    });
  </script>
</body>
</html>