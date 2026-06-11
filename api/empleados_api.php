<?php
declare(strict_types=1);
session_start();
require __DIR__ . '/../database/db.php';

header('Content-Type: application/json; charset=utf-8');

/* Seguridad: solo jefe/encargado */
if (empty($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== 'trabajador') {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'No autenticado']);
    exit;
}
$rolSesion = $_SESSION['rol'] ?? 'trabajador';
if ($rolSesion !== 'jefe' && $rolSesion !== 'encargado') {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Sin permisos']);
    exit;
}

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

/* Acción: create | update | delete */
$action = (string)($data['action'] ?? 'create');

try {
    $pdo = db();

    /* =========================
       CREATE
       ========================= */
    if ($action === 'create') {
        $nombre   = trim((string)($data['nombre'] ?? ''));
        $apellido = trim((string)($data['apellido'] ?? ''));
        $email    = trim((string)($data['email'] ?? ''));
        $tlf      = trim((string)($data['tlf'] ?? ''));
        $rol      = trim((string)($data['rol'] ?? ''));
        $password = (string)($data['password'] ?? '');

        // Validación servidor (aunque exista JS)
        if ($nombre === '' || $apellido === '') {
            http_response_code(422);
            echo json_encode(['ok' => false, 'error' => 'Nombre y apellido obligatorios']);
            exit;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(422);
            echo json_encode(['ok' => false, 'error' => 'Email inválido']);
            exit;
        }
        // Solo se permiten estos roles al crear
        if ($rol !== 'encargado' && $rol !== 'trabajador') {
            http_response_code(422);
            echo json_encode(['ok' => false, 'error' => 'Rol inválido']);
            exit;
        }

        // Teléfono: + opcional, números/espacios, mínimo 9 dígitos
        $telNoSpaces = preg_replace('/\s+/', '', $tlf);
        $telDigits = ltrim($telNoSpaces, '+');
        if (!preg_match('/^\d+$/', $telDigits) || strlen($telDigits) < 9) {
            http_response_code(422);
            echo json_encode(['ok' => false, 'error' => 'Teléfono inválido']);
            exit;
        }

        if (strlen($password) < 8) {
            http_response_code(422);
            echo json_encode(['ok' => false, 'error' => 'La contraseña debe tener mínimo 8 caracteres']);
            exit;
        }

        // Email único
        $st = $pdo->prepare("SELECT id FROM trabajadores WHERE email = :email LIMIT 1");
        $st->execute([':email' => $email]);
        if ($st->fetch()) {
            http_response_code(409);
            echo json_encode(['ok' => false, 'error' => 'Ese email ya existe']);
            exit;
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);

        $ins = $pdo->prepare("
          INSERT INTO trabajadores (nombre, apellido, tlf, rol, email, password_hash)
          VALUES (:nombre, :apellido, :tlf, :rol, :email, :hash)
        ");
        $ins->execute([
            ':nombre' => $nombre,
            ':apellido' => $apellido,
            ':tlf' => $tlf,
            ':rol' => $rol,
            ':email' => $email,
            ':hash' => $hash
        ]);

        $newId = (int)$pdo->lastInsertId();

        // ✅ IMPORTANTE: devolvemos el empleado creado para pintarlo en la tabla sin recargar
        echo json_encode([
            'ok' => true,
            'empleado' => [
                'id' => $newId,
                'nombre' => $nombre,
                'apellido' => $apellido,
                'email' => $email,
                'tlf' => $tlf,
                'rol' => $rol
            ]
        ]);
        exit;
    }

    /* =========================
       UPDATE
       ========================= */
    if ($action === 'update') {
        $id       = (int)($data['id'] ?? 0);
        $nombre   = trim((string)($data['nombre'] ?? ''));
        $apellido = trim((string)($data['apellido'] ?? ''));
        $tlf      = trim((string)($data['tlf'] ?? ''));
        $rol      = trim((string)($data['rol'] ?? ''));

        if ($id <= 0) {
            http_response_code(422);
            echo json_encode(['ok' => false, 'error' => 'ID inválido']);
            exit;
        }
        // En edición solo permitimos encargado/trabajador
        if ($rol !== 'encargado' && $rol !== 'trabajador') {
            http_response_code(422);
            echo json_encode(['ok' => false, 'error' => 'Rol inválido']);
            exit;
        }
        if ($nombre === '' || $apellido === '') {
            http_response_code(422);
            echo json_encode(['ok' => false, 'error' => 'Nombre y apellido obligatorios']);
            exit;
        }

        $telNoSpaces = preg_replace('/\s+/', '', $tlf);
        $telDigits = ltrim($telNoSpaces, '+');
        if (!preg_match('/^\d+$/', $telDigits) || strlen($telDigits) < 9) {
            http_response_code(422);
            echo json_encode(['ok' => false, 'error' => 'Teléfono inválido']);
            exit;
        }

        // No permitir editar al jefe
        $st = $pdo->prepare("SELECT id, rol FROM trabajadores WHERE id = :id LIMIT 1");
        $st->execute([':id' => $id]);
        $row = $st->fetch();

        if (!$row) {
            http_response_code(404);
            echo json_encode(['ok' => false, 'error' => 'Empleado no encontrado']);
            exit;
        }
        if (($row['rol'] ?? '') === 'jefe') {
            http_response_code(403);
            echo json_encode(['ok' => false, 'error' => 'No se puede editar al jefe']);
            exit;
        }

        $up = $pdo->prepare("
          UPDATE trabajadores
          SET nombre = :nombre, apellido = :apellido, tlf = :tlf, rol = :rol
          WHERE id = :id
        ");
        $up->execute([
            ':nombre' => $nombre,
            ':apellido' => $apellido,
            ':tlf' => $tlf,
            ':rol' => $rol,
            ':id' => $id
        ]);

        echo json_encode([
            'ok' => true,
            'empleado' => [
                'id' => $id,
                'nombre' => $nombre,
                'apellido' => $apellido,
                'tlf' => $tlf,
                'rol' => $rol
            ]
        ]);
        exit;
    }

    /* =========================
       DELETE
       ========================= */
    if ($action === 'delete') {
        $id = (int)($data['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(422);
            echo json_encode(['ok' => false, 'error' => 'ID inválido']);
            exit;
        }

        $st = $pdo->prepare("SELECT id, rol FROM trabajadores WHERE id = :id LIMIT 1");
        $st->execute([':id' => $id]);
        $row = $st->fetch();

        if (!$row) {
            http_response_code(404);
            echo json_encode(['ok' => false, 'error' => 'Empleado no encontrado']);
            exit;
        }

        // Impedir borrar al jefe
        if (($row['rol'] ?? '') === 'jefe') {
            http_response_code(403);
            echo json_encode(['ok' => false, 'error' => 'No se puede borrar al jefe']);
            exit;
        }

        $del = $pdo->prepare("DELETE FROM trabajadores WHERE id = :id");
        $del->execute([':id' => $id]);

        echo json_encode(['ok' => true]);
        exit;
    }

    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Acción inválida']);
    exit;

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Error interno']);
    exit;
}
