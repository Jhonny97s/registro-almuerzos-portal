<?php
session_start();
if (isset($_SESSION['cliente_id'])) {
    header("Location: portal.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal de Clientes - Registro de Almuerzos</title>
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

        .hero-bg {
            background: linear-gradient(135deg, #10b981 0%, #047857 100%);
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 50vh;
            z-index: -1;
            border-bottom-left-radius: 50px;
            border-bottom-right-radius: 50px;
        }

        .card-login {
            border: none;
            border-radius: 20px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
        }
    </style>
</head>

<body class="d-flex align-items-center justify-content-center min-vh-100 position-relative">
    <div class="hero-bg"></div>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card card-login p-4 p-sm-5">
                    <div class="text-center mb-4">
                        <div class="bg-success text-white rounded-circle d-inline-flex mb-3 align-items-center justify-content-center shadow"
                            style="width: 80px; height: 80px;">
                            <i class="fa-solid fa-utensils fa-3x"></i>
                        </div>
                        <h2 class="fw-bold text-gray-800">NUTRIPLAN</h2>
                        <p class="text-muted">Consulta tu saldo de manera rápida y segura.</p>
                    </div>

                    <div id="loginAlert" class="alert alert-danger d-none rounded-3"></div>

                    <form id="loginClienteForm">
                        <div class="form-floating mb-4">
                            <input type="text" class="form-control form-control-lg rounded-3" id="cedula" name="cedula"
                                placeholder="Cédula" required>
                            <label for="cedula"><i class="fa-solid fa-id-card me-2 text-success"></i>Número de
                                Cédula</label>
                        </div>

                        <button type="submit"
                            class="btn btn-success w-100 py-3 rounded-pill fw-bold fs-5 shadow-sm transition-transform text-uppercase"
                            id="btnSubmit">
                            <i class="fa-solid fa-magnifying-glass me-2"></i> Consultar Saldo
                        </button>
                    </form>

                    <div class="text-center mt-4">
                        <a href="login.php" class="text-decoration-none text-muted small"><i
                                class="fa-solid fa-shield-halved me-1"></i>Acceso a Cajeros</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('loginClienteForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const btn = document.getElementById('btnSubmit');
            const alert = document.getElementById('loginAlert');
            const formData = new FormData(this);

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Buscando...';
            alert.classList.add('d-none');

            fetch('ajax/login_cliente_ajax.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = data.redirect;
                    } else {
                        alert.textContent = data.message;
                        alert.classList.remove('d-none');
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fa-solid fa-magnifying-glass me-2"></i> Consultar Saldo';
                    }
                })
                .catch(error => {
                    alert.textContent = 'Error de conexión.';
                    alert.classList.remove('d-none');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa-solid fa-magnifying-glass me-2"></i> Consultar Saldo';
                });
        });
    </script>
</body>

</html>