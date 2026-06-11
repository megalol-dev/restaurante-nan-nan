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

$data = json_decode(file_get_contents('php://input'), true);
if (!is_array($data)) {
  http_response_code(400);
  echo json_encode(['ok' => false, 'error' => 'JSON inválido']);
  exit;
}

$action = (string)($data['action'] ?? 'list');

function cleanText($s): string {
  $s = trim((string)$s);
  $s = str_replace("\0", "", $s);
  return $s;
}

try {
  if (empty($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== 'trabajador') {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'No autenticado (trabajador)']);
    exit;
  }

  $pdo = db();

  if (!in_array($action, ['list', 'search'], true)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Acción inválida']);
    exit;
  }

  $q = cleanText($data['q'] ?? '');

  // LISTAR (sin filtro)
  if ($action === 'list' || $q === '') {
    $st = $pdo->query("
      SELECT
        id,
        COALESCE(nombre, '') AS nombre,
        COALESCE(apellidos, '') AS apellidos,
        email,
        telefono,
        created_at
      FROM clientes
      ORDER BY id DESC
      LIMIT 500
    ");
    $rows = $st->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['ok' => true, 'items' => $rows]);
    exit;
  }

  // SEARCH (con filtro) - sin mbstring
  // SQLite: usamos lower(...) para comparar sin mayúsculas/minúsculas
  // Nota: lower() en SQLite es "ASCII-friendly", pero para tu caso (emails/teléfonos/nombres comunes) va perfecto.
  $like = '%' . strtolower($q) . '%';

  $st = $pdo->prepare("
    SELECT
      id,
      COALESCE(nombre, '') AS nombre,
      COALESCE(apellidos, '') AS apellidos,
      email,
      telefono,
      created_at
    FROM clientes
    WHERE
      lower(COALESCE(nombre,'')) LIKE :q
      OR lower(COALESCE(apellidos,'')) LIKE :q
      OR lower(COALESCE(email,'')) LIKE :q
      OR lower(COALESCE(telefono,'')) LIKE :q
    ORDER BY id DESC
    LIMIT 200
  ");
  $st->execute([':q' => $like]);

  $rows = $st->fetchAll(PDO::FETCH_ASSOC);
  echo json_encode(['ok' => true, 'items' => $rows]);
  exit;

} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok' => false, 'error' => 'Error interno: ' . $e->getMessage()]);
  exit;
}




