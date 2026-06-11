<?php
declare(strict_types=1);
session_start();

require __DIR__ . '/db.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Método no permitido']);
    exit;
}

if (empty($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== 'trabajador') {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'No autenticado']);
    exit;
}

$rol = $_SESSION['rol'] ?? 'trabajador';
if ($rol !== 'jefe') {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Acceso denegado: solo el jefe']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'JSON inválido']);
    exit;
}

$action = (string)($data['action'] ?? 'list');

$CATS = ['Ensaladas','Carnes','Pescados','Pasta','Bocadillos','Sandwiches','Postres','Bebidas'];

function normPrecio(string $raw): float {
    $raw = trim($raw);
    $raw = str_replace(',', '.', $raw);
    return (float)$raw;
}

function listar(PDO $pdo): array {
    $st = $pdo->query("
      SELECT id, categoria, nombre, descripcion, precio, activo
      FROM carta_platos
      ORDER BY
        CASE categoria
            WHEN 'Ensaladas' THEN 1
            WHEN 'Carnes' THEN 2
            WHEN 'Pescados' THEN 3
            WHEN 'Pasta' THEN 4
            WHEN 'Bocadillos' THEN 5
            WHEN 'Sandwiches' THEN 6
            WHEN 'Postres' THEN 7
            WHEN 'Bebidas' THEN 8
            ELSE 99
        END,
        LOWER(nombre) ASC
    ");
    return $st->fetchAll(PDO::FETCH_ASSOC);
}

try {
    $pdo = db();

    if ($action === 'list') {
        echo json_encode(['ok' => true, 'items' => listar($pdo)]);
        exit;
    }

    if ($action === 'create') {
        $categoria = trim((string)($data['categoria'] ?? ''));
        $nombre = trim((string)($data['nombre'] ?? ''));
        $descripcion = trim((string)($data['descripcion'] ?? ''));
        $precioRaw = (string)($data['precio'] ?? '');

        if (!in_array($categoria, $CATS, true)) { http_response_code(422); echo json_encode(['ok'=>false,'error'=>'Categoría inválida']); exit; }
        if ($nombre === '') { http_response_code(422); echo json_encode(['ok'=>false,'error'=>'Nombre obligatorio']); exit; }

        $precio = normPrecio($precioRaw);
        if ($precio <= 0 || $precio > 999) { http_response_code(422); echo json_encode(['ok'=>false,'error'=>'Precio inválido']); exit; }

        $st = $pdo->prepare("
          INSERT INTO carta_platos (categoria, nombre, descripcion, precio, activo)
          VALUES (:c, :n, :d, :p, 1)
        ");
        $st->execute([':c'=>$categoria, ':n'=>$nombre, ':d'=>$descripcion, ':p'=>$precio]);

        echo json_encode(['ok'=>true, 'items'=> listar($pdo)]);
        exit;
    }

    if ($action === 'update') {
        $id = (int)($data['id'] ?? 0);
        $categoria = trim((string)($data['categoria'] ?? ''));
        $nombre = trim((string)($data['nombre'] ?? ''));
        $descripcion = trim((string)($data['descripcion'] ?? ''));
        $precioRaw = (string)($data['precio'] ?? '');

        if ($id <= 0) { http_response_code(422); echo json_encode(['ok'=>false,'error'=>'ID inválido']); exit; }
        if (!in_array($categoria, $CATS, true)) { http_response_code(422); echo json_encode(['ok'=>false,'error'=>'Categoría inválida']); exit; }
        if ($nombre === '') { http_response_code(422); echo json_encode(['ok'=>false,'error'=>'Nombre obligatorio']); exit; }

        $precio = normPrecio($precioRaw);
        if ($precio <= 0 || $precio > 999) { http_response_code(422); echo json_encode(['ok'=>false,'error'=>'Precio inválido']); exit; }

        $st = $pdo->prepare("
          UPDATE carta_platos
          SET categoria=:c, nombre=:n, descripcion=:d, precio=:p
          WHERE id=:id
        ");
        $st->execute([':c'=>$categoria, ':n'=>$nombre, ':d'=>$descripcion, ':p'=>$precio, ':id'=>$id]);

        echo json_encode(['ok'=>true, 'items'=> listar($pdo)]);
        exit;
    }

    if ($action === 'delete') {
        $id = (int)($data['id'] ?? 0);
        if ($id <= 0) { http_response_code(422); echo json_encode(['ok'=>false,'error'=>'ID inválido']); exit; }

        $st = $pdo->prepare("DELETE FROM carta_platos WHERE id=:id");
        $st->execute([':id'=>$id]);

        echo json_encode(['ok'=>true, 'items'=> listar($pdo)]);
        exit;
    }

    http_response_code(400);
    echo json_encode(['ok'=>false,'error'=>'Acción inválida']);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok'=>false,'error'=>'Error interno']);
}
