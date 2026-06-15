<?php
declare(strict_types=1);

require __DIR__ . '/../database/db.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Método no permitido']);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'JSON inválido']);
    exit;
}

// Sanitizar/normalizar
$nombre   = trim((string)($data['nombre'] ?? ''));
$apellidos= trim((string)($data['apellidos'] ?? ''));
$email    = trim((string)($data['email'] ?? ''));
$telefono = trim((string)($data['telefono'] ?? ''));
$password = (string)($data['password'] ?? '');

// Validaciones servidor (obligatorias, aunque ya valides en JS)
if ($nombre === '' || $apellidos === '') {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'Nombre y apellidos son obligatorios']);
    exit;
}

// Solo letras, espacios, tildes y ñ
$reNombre = '/^[A-Za-zÁÉÍÓÚáéíóúÑñÜü\s]+$/u';

if (!preg_match($reNombre, $nombre)) {
    http_response_code(422);
    echo json_encode([
        'ok' => false,
        'error' => 'Nombre inválido'
    ]);
    exit;
}

if (!preg_match($reNombre, $apellidos)) {
    http_response_code(422);
    echo json_encode([
        'ok' => false,
        'error' => 'Apellidos inválidos'
    ]);
    exit;
}


// Longitud máxima nombre
if (mb_strlen($nombre) < 2 || mb_strlen($nombre) > 30) {
    http_response_code(422);
    echo json_encode([
        'ok' => false,
        'error' => 'Nombre no válido'
    ]);
    exit;
}
// Longitud máxima apellido
if (mb_strlen($apellidos) < 2 || mb_strlen($apellidos) > 60) {
    http_response_code(422);
    echo json_encode([
        'ok' => false,
        'error' => 'Apellidos no válidos'
    ]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'Email inválido']);
    exit;
}

// Teléfono: permite + al inicio, números y espacios, mínimo 9 dígitos
$telNoSpaces = preg_replace('/\s+/', '', $telefono);
$telDigits = ltrim($telNoSpaces, '+');
if (!preg_match('/^\d+$/', $telDigits) || strlen($telDigits) < 9) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'Teléfono inválido']);
    exit;
}

if (strlen($password) < 6) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'Contraseña mínima: 6 caracteres']);
    exit;
}

try {
    $pdo = db();


    // Comprobar email repetido
    $st = $pdo->prepare("SELECT id FROM clientes WHERE email = :email LIMIT 1");
    $st->execute([':email' => $email]);
    if ($st->fetch()) {
        http_response_code(409);
        echo json_encode(['ok' => false, 'error' => 'Ese email ya está registrado']);
        exit;
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);

    $ins = $pdo->prepare("
        INSERT INTO clientes (
        nombre,
        apellidos,
        email,
        telefono,
        password_hash,
        created_at
        )
        VALUES (
            :nombre,
            :apellidos,
            :email,
            :telefono,
            :hash,
            NOW()
        )"
    );
    
    $ins->execute([
        ':nombre' => $nombre,
        ':apellidos' => $apellidos,
        ':email' => $email,
        ':telefono' => $telefono,
        ':hash' => $hash
    ]);

    echo json_encode(['ok' => true]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Error interno']);
}
