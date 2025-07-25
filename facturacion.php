<?php
// facturacion.php  
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Facturación - Reminiscencia Photography</title>
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
    
    /* Navbar */
    header {
      width: 100%;
      position: fixed;
      top: 0;
      z-index: 1000;
    }
    
    .navbar {
      padding: 20px 0;
      background: rgba(10, 10, 10, 0.8) !important;
      backdrop-filter: blur(20px);
      border-bottom: 1px solid var(--border-light);
    }
    
    .navbar-brand {
      font-family: 'Playfair Display', serif;
      font-size: 1.8rem;
      font-weight: 700;
      background: linear-gradient(135deg, #fff, var(--accent-gold));
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }
    
    /* Contenedor principal */
    .main-container {
      margin-top: 100px;
      padding: 2rem 0;
    }
    
    /* Factura */
    .invoice-container {
      background: white;
      border-radius: 15px;
      box-shadow: var(--shadow-soft);
      overflow: hidden;
      margin-bottom: 2rem;
    }
    
    .invoice-header {
      background: linear-gradient(135deg, var(--primary-dark) 0%, var(--secondary-dark) 100%);
      color: white;
      padding: 2rem;
      text-align: center;
      position: relative;
    }
    
    .invoice-header::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 50%;
      transform: translateX(-50%);
      width: 80%;
      height: 1px;
      background: linear-gradient(90deg, transparent, var(--accent-gold), transparent);
    }
    
    .invoice-title {
      font-size: 2rem;
      margin-bottom: 0.5rem;
    }
    
    .invoice-subtitle {
      font-size: 1rem;
      opacity: 0.8;
    }
    
    .invoice-body {
      padding: 2rem;
    }
    
    .company-info, .client-info {
      margin-bottom: 2rem;
    }
    
    .info-title {
      font-weight: 600;
      color: var(--accent-gold);
      margin-bottom: 1rem;
      border-bottom: 1px solid #eee;
      padding-bottom: 0.5rem;
    }
    
    .invoice-details {
      background: rgba(212, 175, 55, 0.05);
      border-radius: 10px;
      padding: 1.5rem;
      margin-bottom: 2rem;
    }
    
    .detail-item {
      display: flex;
      justify-content: space-between;
      margin-bottom: 0.5rem;
    }
    
    .detail-label {
      font-weight: 500;
    }
    
    .detail-value {
      font-weight: 600;
    }
    
    .invoice-table {
      width: 100%;
      margin-bottom: 2rem;
    }
    
    .invoice-table thead {
      background: linear-gradient(135deg, var(--accent-gold), var(--accent-gold-hover));
      color: white;
    }
    
    .invoice-table th {
      padding: 1rem;
      text-align: left;
    }
    
    .invoice-table td {
      padding: 1rem;
      border-bottom: 1px solid #eee;
    }
    
    .invoice-totals {
      background: rgba(139, 90, 150, 0.05);
      border-radius: 10px;
      padding: 1.5rem;
      margin-top: 2rem;
    }
    
    .total-row {
      display: flex;
      justify-content: space-between;
      margin-bottom: 1rem;
      font-size: 1.1rem;
    }
    
    .total-label {
      font-weight: 500;
    }
    
    .total-amount {
      font-weight: 700;
    }
    
    .grand-total {
      font-size: 1.3rem;
      color: var(--accent-gold);
    }
    
    .invoice-footer {
      text-align: center;
      padding: 2rem;
      border-top: 1px solid #eee;
      margin-top: 2rem;
    }
    
    .payment-methods {
      margin-top: 2rem;
    }
    
    .payment-method {
      display: inline-block;
      margin: 0 1rem;
      text-align: center;
    }
    
    .payment-icon {
      font-size: 2rem;
      color: var(--accent-gold);
      margin-bottom: 0.5rem;
    }
    
    /* Botones */
    .btn-print {
      background: linear-gradient(135deg, var(--accent-purple), #7a4d84);
      color: white;
      padding: 12px 32px;
      border-radius: 50px;
      font-weight: 500;
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      border: none;
    }
    
    .btn-print:hover {
      background: linear-gradient(135deg, #7a4d84, var(--accent-purple));
      transform: translateY(-3px);
      box-shadow: 0 15px 35px rgba(139, 90, 150, 0.4);
      color: white;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
      .main-container {
        margin-top: 80px;
        padding: 1rem 0;
      }
      
      .invoice-header {
        padding: 1.5rem;
      }
      
      .invoice-title {
        font-size: 1.5rem;
      }
      
      .invoice-body {
        padding: 1.5rem;
      }
      
      .invoice-table th, 
      .invoice-table td {
        padding: 0.75rem;
        font-size: 0.9rem;
      }
    }
    
    @media print {
      body {
        background: white;
      }
      
      .no-print {
        display: none !important;
      }
      
      .invoice-container {
        box-shadow: none;
        border: none;
      }
      
      .main-container {
        margin-top: 0;
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
          <ul class="navbar-nav ms-auto">
            <li class="nav-item">
              <a class="nav-link" href="index.php"><i class="bi bi-house"></i> Inicio</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="login_cliente.php"><i class="bi bi-person"></i> Área de Clientes</a>
            </li>
          </ul>
        </div>
      </div>
    </nav>
  </header>
  
  <!-- Contenido principal -->
  <div class="main-container">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-10">
          <div class="invoice-container">
            <!-- Encabezado de la factura -->
            <div class="invoice-header">
              <h1 class="invoice-title">FACTURA</h1>
              <p class="invoice-subtitle">Reminiscencia Photography - Servicios Fotográficos Profesionales</p>
            </div>
            
            <!-- Cuerpo de la factura -->
            <div class="invoice-body">
              <!-- Información de la empresa y cliente -->
              <div class="row">
                <div class="col-md-6">
                  <div class="company-info">
                    <h4 class="info-title">Datos de la Empresa</h4>
                    <p><strong>Reminiscencia Photography S.A. de C.V.</strong></p>
                    <p>RFC: REM210524M76</p>
                    <p>Calle Madero #456, Colonia Centro</p>
                    <p>Aguascalientes, Ags. C.P. 20000</p>
                    <p>Teléfono: (449) 123 4567</p>
                    <p>Email: contacto@reminiscenciaphoto.com</p>
                    <p>Régimen Fiscal: Régimen General de Ley Personas Morales</p>
                  </div>
                </div>
                
                <div class="col-md-6">
                  <div class="client-info">
                    <h4 class="info-title">Datos del Cliente</h4>
                    <p><strong>Nombre: </strong>Ana María López Rodríguez</p>
                    <p><strong>RFC: </strong>LORA850315M76</p>
                    <p><strong>Dirección: </strong>Av. Constituyentes #456, Col. Juriquilla</p>
                    <p><strong>Ciudad: </strong>Querétaro, Qro. C.P. 76230</p>
                    <p><strong>Email: </strong>ana.lopez@example.com</p>
                    <p><strong>Teléfono: </strong>(442) 987 6543</p>
                  </div>
                </div>
              </div>
              
              <!-- Detalles de la factura -->
              <div class="invoice-details">
                <div class="row">
                  <div class="col-md-4">
                    <div class="detail-item">
                      <span class="detail-label">Folio:</span>
                      <span class="detail-value">REM-2023-0042</span>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="detail-item">
                      <span class="detail-label">Fecha de emisión:</span>
                      <span class="detail-value">24 de Mayo, 2023</span>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="detail-item">
                      <span class="detail-label">Forma de pago:</span>
                      <span class="detail-value">Transferencia electrónica</span>
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Tabla de conceptos -->
              <table class="invoice-table table">
                <thead>
                  <tr>
                    <th>Cantidad</th>
                    <th>Descripción</th>
                    <th>Precio Unitario</th>
                    <th>Importe</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>1</td>
                    <td>Servicio fotográfico para XV años (8 horas)</td>
                    <td>$8,500.00</td>
                    <td>$8,500.00</td>
                  </tr>
                  <tr>
                    <td>1</td>
                    <td>Álbum fotográfico premium (50 páginas)</td>
                    <td>$3,200.00</td>
                    <td>$3,200.00</td>
                  </tr>
                  <tr>
                    <td>1</td>
                    <td>Sesión previa de fotos (2 horas)</td>
                    <td>$1,800.00</td>
                    <td>$1,800.00</td>
                  </tr>
                  <tr>
                    <td>1</td>
                    <td>Video profesional del evento</td>
                    <td>$4,500.00</td>
                    <td>$4,500.00</td>
                  </tr>
                </tbody>
              </table>
              
              <!-- Totales -->
              <div class="invoice-totals">
                <div class="total-row">
                  <span class="total-label">Subtotal:</span>
                  <span class="total-amount">$18,000.00</span>
                </div>
                <div class="total-row">
                  <span class="total-label">IVA (16%):</span>
                  <span class="total-amount">$2,880.00</span>
                </div>
                <div class="total-row grand-total">
                  <span class="total-label">Total:</span>
                  <span class="total-amount">$20,880.00</span>
                </div>
              </div>
              
              <!-- Métodos de pago -->
              <div class="payment-methods">
                <h4 class="info-title">Métodos de Pago</h4>
                <div class="d-flex justify-content-center flex-wrap">
                  <div class="payment-method">
                    <div class="payment-icon"><i class="bi bi-bank"></i></div>
                    <div>Transferencia Bancaria</div>
                    <small>BBVA: 0123 4567 8910 1112</small>
                  </div>
                  <div class="payment-method">
                    <div class="payment-icon"><i class="bi bi-credit-card"></i></div>
                    <div>Tarjeta de Crédito</div>
                    <small>Mercado Pago / PayPal</small>
                  </div>
                  <div class="payment-method">
                    <div class="payment-icon"><i class="bi bi-cash"></i></div>
                    <div>Efectivo</div>
                    <small>Oficinas en Aguascalientes</small>
                  </div>
                </div>
              </div>
              
              <!-- Notas -->
              <div class="invoice-notes mt-4">
                <p><small><strong>Notas:</strong> El pago debe realizarse dentro de los 5 días hábiles siguientes a la emisión de esta factura. Para cualquier aclaración, favor de contactar a nuestro departamento de atención a clientes.</small></p>
                <p><small>Esta factura es un documento digital válido ante el SAT y no requiere sello ni firma autógrafa.</small></p>
              </div>
            </div>
            
            <!-- Pie de página de la factura -->
            <div class="invoice-footer">
              <p>¡Gracias por confiar en Reminiscencia Photography!</p>
              <p>contacto@reminiscenciaphoto.com | (449) 123 4567</p>
              <div class="mt-3 no-print">
                <button onclick="window.print()" class="btn btn-print">
                  <i class="bi bi-printer"></i> Imprimir Factura
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Vincular Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
  
  <script>
    // Ajustar margen superior para impresión
    window.addEventListener('beforeprint', function() {
      document.querySelector('.main-container').style.marginTop = '0';
    });
    
    // Generar PDF (simulado)
    document.getElementById('generate-pdf').addEventListener('click', function() {
      alert('La generación de PDF se realizaría con una librería como jsPDF en un entorno real');
    });
  </script>
</body>
</html>