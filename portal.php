<?php
// portal.php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['cliente_id'])) {
    header("Location: login_cliente.php");
    exit;
}

$cliente_id = $_SESSION['cliente_id'];
$db = getDB();

// Obtener datos frescos del cliente
$stmt = $db->prepare("SELECT c.*, g.nombre as grupo_nombre FROM clientes c LEFT JOIN grupos g ON c.grupo_id = g.id WHERE c.id = ?");
$stmt->execute([$cliente_id]);
$cliente = $stmt->fetch();

if (!$cliente || !$cliente['activo']) {
    session_destroy();
    header("Location: login_cliente.php");
    exit;
}

// Obtener últimos 10 consumos
$stmtConsumos = $db->prepare("
    SELECT c.*, p.nombre as producto_nombre 
    FROM consumos c 
    JOIN productos p ON c.producto_id = p.id 
    WHERE c.cliente_id = ? 
    ORDER BY c.fecha DESC LIMIT 10
");
$stmtConsumos->execute([$cliente_id]);
$consumos = $stmtConsumos->fetchAll();

// Calcular total consumido este mes
$inicio_mes = date('Y-m-01 00:00:00');
$stmtTotal = $db->prepare("SELECT SUM(total) as gastado FROM consumos WHERE cliente_id = ? AND fecha >= ?");
$stmtTotal->execute([$cliente_id, $inicio_mes]);
$gastado_mes = $stmtTotal->fetch()['gastado'] ?? 0;

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - NUTRIPLAN</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #f8f9fa;
        }

        .bg-gradient-success {
            background: linear-gradient(135deg, #10b981 0%, #047857 100%);
        }

        .card-custom {
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        .balance-card {
            background: linear-gradient(135deg, #2563eb 0%, #1e3a8a 100%);
            color: white;
            border-radius: 20px;
            padding: 30px;
            position: relative;
            overflow: hidden;
        }

        .balance-card::after {
            content: '\f555';
            /* fa-wallet */
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            right: -20px;
            bottom: -30px;
            font-size: 10rem;
            color: rgba(255, 255, 255, 0.1);
            transform: rotate(-15deg);
        }

        .timeline {
            border-left: 2px solid #e2e8f0;
            padding-left: 20px;
            list-style: none;
            margin-left: 10px;
        }

        .timeline-item {
            position: relative;
            margin-bottom: 20px;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: -27px;
            top: 5px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #10b981;
            border: 2px solid #fff;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
        }
    </style>
</head>

<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-gradient-success shadow-sm py-3 sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">
                <i class="fa-solid fa-utensils me-2"></i> NUTRIPLAN
            </a>
            <div class="d-flex align-items-center">
                <span class="text-white fw-bold me-3 d-none d-sm-block">
                    <?php echo htmlspecialchars($cliente['nombres']); ?>
                </span>
                <a href="logout_cliente.php" class="btn btn-outline-light btn-sm rounded-pill fw-bold">
                    <i class="fa-solid fa-sign-out-alt"></i> <span class="d-none d-sm-inline">Salir</span>
                </a>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="row mb-5">
            <!-- Saldo Card -->
            <div class="col-lg-8 mx-auto">
                <div class="balance-card shadow-lg mb-4">
                    <div class="row align-items-center position-relative" style="z-index: 1;">
                        <div class="col-sm-8 mb-3 mb-sm-0">
                            <h6 class="text-uppercase text-white-50 fw-bold letter-spacing-1 mb-1">Saldo Disponible</h6>
                            <h1 class="display-3 fw-bold mb-0 text-white">$
                                <?php echo number_format($cliente['saldo_actual'], 2); ?>
                            </h1>
                        </div>
                        <div class="col-sm-4 text-sm-end">
                            <p class="mb-0 text-white-50 small">Cédula:
                                <?php echo htmlspecialchars($cliente['cedula']); ?>
                            </p>
                            <p class="mb-0 text-white-50 small">Grupo:
                                <?php echo htmlspecialchars($cliente['grupo_nombre']); ?>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Resumen Mes -->
                <div class="card card-custom mb-4">
                    <div class="card-body p-4 d-flex justify-content-between align-items-center bg-light rounded-top-4">
                        <div class="d-flex align-items-center">
                            <div class="bg-danger bg-opacity-10 text-danger rounded-circle p-3 me-3">
                                <i class="fa-solid fa-chart-line fa-xl"></i>
                            </div>
                            <div>
                                <h6 class="text-muted fw-bold mb-0 text-uppercase small">Gastado este mes</h6>
                                <h3 class="fw-bold mb-0 text-gray-800">$
                                    <?php echo number_format($gastado_mes, 2); ?>
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Historial -->
                <h5 class="fw-bold text-gray-800 mb-4 ms-2"><i
                        class="fa-solid fa-clock-rotate-left text-primary me-2"></i> Últimos
                    <?php echo count($consumos); ?> Consumos
                </h5>

                <div class="card card-custom">
                    <div class="card-body p-4 pt-5">
                        <?php if (empty($consumos)): ?>
                            <div class="text-center text-muted p-5">
                                <i class="fa-solid fa-ghost fa-3x mb-3 text-light"></i>
                                <h5>Aún no tienes consumos registrados.</h5>
                                <p>Tu historial aparecerá aquí.</p>
                            </div>
                        <?php else: ?>
                            <ul class="timeline">
                                <?php foreach ($consumos as $c): ?>
                                    <li class="timeline-item d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="fw-bold mb-1 text-gray-800">
                                                <?php echo htmlspecialchars($c['producto_nombre']); ?>
                                            </h6>
                                            <small class="text-muted"><i class="fa-regular fa-calendar-alt me-1"></i>
                                                <?php echo date('d M Y, h:i A', strtotime($c['fecha'])); ?>
                                            </small>
                                        </div>
                                        <div class="text-end">
                                            <span
                                                class="badge bg-danger-subtle text-danger rounded-pill px-3 py-2 fw-bold fs-6">-$
                                                <?php echo number_format($c['total'], 2); ?>
                                            </span>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                            <div class="text-center mt-4 pt-3 border-top">
                                <small class="text-muted"><i class="fa-solid fa-info-circle me-1"></i> Mostrando las últimas
                                    10 transacciones.</small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>