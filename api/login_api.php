<?php
declare(strict_types=1);
session_start();

require __DIR__ . '/database/db.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Método no permitido']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'JSON inválido']);
    exit;
}

$email = strtolower(trim((string)($data['email'] ?? '')));
$password = (string)($data['password'] ?? '');

if ($email === '' || $password === '') {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'Email y contraseña obligatorios']);
    exit;
}

try {
    $pdo = db();

    // 1) Buscar en TRABAJADORES
    $st = $pdo->prepare("
        SELECT id, nombre, apellido, email, tlf, rol, password_hash
        FROM trabajadores
        WHERE lower(email)=:e
        LIMIT 1
    ");
    $st->execute([':e' => $email]);
    $t = $st->fetch(PDO::FETCH_ASSOC);

    if ($t && password_verify($password, (string)$t['password_hash'])) {
        session_regenerate_id(true);

        $_SESSION['tipo_usuario'] = 'trabajador';
        $_SESSION['trabajador_id'] = (int)$t['id'];
        $_SESSION['nombre'] = trim((string)$t['nombre'] . ' ' . (string)$t['apellido']);
        $_SESSION['rol'] = (string)$t['rol'];
        $_SESSION['email'] = (string)$t['email'];

        echo json_encode(['ok' => true, 'tipo' => 'trabajador', 'redirect' => 'zona_trabajadores.php']);
        exit;
    }

    // 2) Buscar en CLIENTES
    $st = $pdo->prepare("
        SELECT id, nombre, apellidos, email, telefono, password_hash
        FROM clientes
        WHERE lower(email)=:e
        LIMIT 1
    ");
    $st->execute([':e' => $email]);
    $c = $st->fetch(PDO::FETCH_ASSOC);

    if ($c && password_verify($password, (string)$c['password_hash'])) {
        session_regenerate_id(true);

        $nombreCompleto = trim(((string)($c['nombre'] ?? '')) . ' ' . ((string)($c['apellidos'] ?? '')));
        if ($nombreCompleto === '') {
            $nombreCompleto = (string)($c['email'] ?? $email);
        }

        $_SESSION['tipo_usuario'] = 'cliente';
        $_SESSION['cliente_id'] = (int)$c['id'];
        $_SESSION['cliente_email'] = (string)$c['email'];

        // ✅ Esto es lo que usa reservas_api.php para mostrar bien el nombre
        $_SESSION['cliente_nombre'] = $nombreCompleto;

        // (Opcional) también lo guardamos como "nombre" genérico por comodidad
        $_SESSION['nombre'] = $nombreCompleto;

        echo json_encode(['ok' => true, 'tipo' => 'cliente', 'redirect' => 'panel_cliente.php']);
        exit;
    }

    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Credenciales incorrectas']);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => $e->getMessage()
    ]);
}




