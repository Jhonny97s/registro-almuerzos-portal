<?php
// ajax/login_cliente_ajax.php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cedula = trim($_POST['cedula'] ?? '');

    if (empty($cedula)) {
        echo json_encode(['success' => false, 'message' => 'Por favor, ingrese su número de cédula.']);
        exit;
    }

    try {
        $db = getDB();
        // Para el portal de clientes, el "login" es solo la cédula por simplicidad según el requerimiento.
        $stmt = $db->prepare("SELECT id, nombres, apellidos, saldo_actual, activo FROM clientes WHERE cedula = ?");
        $stmt->execute([$cedula]);
        $cliente = $stmt->fetch();

        if ($cliente) {
            if (!$cliente['activo']) {
                echo json_encode(['success' => false, 'message' => 'Cuenta inactiva. Comuníquese con administración.']);
                exit;
            }

            // Set session variables
            $_SESSION['cliente_id'] = $cliente['id'];
            $_SESSION['cliente_nombre'] = $cliente['nombres'] . ' ' . $cliente['apellidos'];

            echo json_encode(['success' => true, 'redirect' => 'portal.php']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Cédula no encontrada.']);
        }
    } catch (PDOException $e) {
        error_log("Login Cliente error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error de conexión al servidor.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
}
?>