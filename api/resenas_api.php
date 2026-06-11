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

$action = (string)($data['action'] ?? '');

function cleanText($s): string {
  $s = trim((string)$s);
  $s = str_replace("\0", "", $s);
  return $s;
}

function str_len(string $s): int {
  // No dependemos de mbstring
  return strlen($s);
}

function ensureColumn(PDO $pdo, string $table, string $colDef): void {
  // $colDef ejemplo: "estado TEXT NOT NULL DEFAULT 'visible'"
  // SQLite no soporta IF NOT EXISTS en ADD COLUMN en versiones antiguas
  try {
    $pdo->exec("ALTER TABLE {$table} ADD COLUMN {$colDef}");
  } catch (Throwable $e) {
    // Si ya existe la columna, SQLite lanza error -> lo ignoramos
  }
}

try {
  $pdo = db();

  // ✅ Asegurar tabla (estructura base)
  $pdo->exec("
    CREATE TABLE IF NOT EXISTS resenas (
      id INT AUTO_INCREMENT PRIMARY KEY,
      cliente_id INT NOT NULL UNIQUE,
      nombre_publico VARCHAR(100),
      puntuacion INT NOT NULL,
      texto TEXT NOT NULL,
      fecha DATE NOT NULL DEFAULT CURRENT_DATE,
      created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
      updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )
  ");

  // ✅ Añadir columnas nuevas para moderación (si no existen)
  ensureColumn($pdo, 'resenas', "estado TEXT NOT NULL DEFAULT 'visible'");           // visible | oculta | en_revision
  ensureColumn($pdo, 'resenas', "moderada_por TEXT");                                // nombre del trabajador
  ensureColumn($pdo, 'resenas', "moderada_at TEXT");                                 // datetime('now')
  ensureColumn($pdo, 'resenas', "motivo_moderacion TEXT");                           // opcional

  // =========================================================
  // ✅ PÚBLICO: últimas reseñas (para index.html)
  // action: latest, limit
  // SOLO visible
  // =========================================================
  if ($action === 'latest') {
    $limit = (int)($data['limit'] ?? 5);
    if ($limit <= 0) $limit = 5;
    if ($limit > 20) $limit = 20;

    $st = $pdo->prepare("
      SELECT id, nombre_publico, puntuacion, texto, fecha, created_at
      FROM resenas
      WHERE estado = 'visible'
      ORDER BY created_at DESC, id DESC
      LIMIT :lim
    ");
    $st->bindValue(':lim', $limit, PDO::PARAM_INT);
    $st->execute();
    $rows = $st->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['ok' => true, 'items' => $rows]);
    exit;
  }

  // =========================================================
  // ✅ PÚBLICO: listar todas (para resenas.php)
  // action: list_all, puntuacion (opcional 1-5)
  // SOLO visible
  // =========================================================
  if ($action === 'list_all') {
    $p = (int)($data['puntuacion'] ?? 0);

    if ($p >= 1 && $p <= 5) {
      $st = $pdo->prepare("
        SELECT id, nombre_publico, puntuacion, texto, fecha, created_at
        FROM resenas
        WHERE estado = 'visible' AND puntuacion = :p
        ORDER BY created_at DESC, id DESC
      ");
      $st->execute([':p' => $p]);
    } else {
      $st = $pdo->query("
        SELECT id, nombre_publico, puntuacion, texto, fecha, created_at
        FROM resenas
        WHERE estado = 'visible'
        ORDER BY created_at DESC, id DESC
      ");
    }

    $rows = $st->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['ok' => true, 'items' => $rows]);
    exit;
  }

  // =========================================================
  // ✅ ADMIN (solo JEFE): listar todas
  // action: admin_list, estado(opcional)
  // =========================================================
  if ($action === 'admin_list') {
    if (empty($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== 'trabajador' || (($_SESSION['rol'] ?? '') !== 'jefe')) {
      http_response_code(403);
      echo json_encode(['ok' => false, 'error' => 'Acceso denegado']);
      exit;
    }

    $estado = cleanText($data['estado'] ?? '');
    $where = "";
    $params = [];

    if ($estado !== '') {
      if (!in_array($estado, ['visible', 'oculta', 'en_revision'], true)) {
        http_response_code(422);
        echo json_encode(['ok' => false, 'error' => 'Estado inválido']);
        exit;
      }
      $where = "WHERE r.estado = :estado";
      $params[':estado'] = $estado;
    }

    // OJO: asumimos que existe tabla "clientes" con id,email,nombre,apellidos (según tu login)
    $st = $pdo->prepare("
      SELECT
        r.id, r.cliente_id, r.nombre_publico, r.puntuacion, r.texto, r.fecha, r.created_at, r.updated_at,
        r.estado, r.moderada_por, r.moderada_at, r.motivo_moderacion,
        c.email AS cliente_email,
        TRIM(CONCAT(COALESCE(c.nombre,''), ' ', COALESCE(c.apellidos,''))) AS cliente_nombre
      FROM resenas r
      LEFT JOIN clientes c ON c.id = r.cliente_id
      $where
      ORDER BY r.created_at DESC, r.id DESC
      LIMIT 500
    ");
    $st->execute($params);
    $rows = $st->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['ok' => true, 'items' => $rows]);
    exit;
  }

  // =========================================================
  // ✅ ADMIN (solo JEFE): cambiar estado (bloquear/desbloquear)
  // action: admin_set_estado, id, estado
  // =========================================================
  if ($action === 'admin_set_estado') {
    if (empty($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== 'trabajador' || (($_SESSION['rol'] ?? '') !== 'jefe')) {
      http_response_code(403);
      echo json_encode(['ok' => false, 'error' => 'Acceso denegado']);
      exit;
    }

    $id = (int)($data['id'] ?? 0);
    $estado = cleanText($data['estado'] ?? '');

    if ($id <= 0) {
      http_response_code(422);
      echo json_encode(['ok' => false, 'error' => 'ID inválido']);
      exit;
    }
    if (!in_array($estado, ['visible', 'oculta', 'en_revision'], true)) {
      http_response_code(422);
      echo json_encode(['ok' => false, 'error' => 'Estado inválido']);
      exit;
    }

    // comprobar que existe
    $st = $pdo->prepare("SELECT id FROM resenas WHERE id=:id LIMIT 1");
    $st->execute([':id' => $id]);
    if (!$st->fetch(PDO::FETCH_ASSOC)) {
      http_response_code(404);
      echo json_encode(['ok' => false, 'error' => 'Reseña no encontrada']);
      exit;
    }

    $moderadaPor = (string)($_SESSION['nombre'] ?? 'Jefe');
    $now = date('Y-m-d H:i:s');

    $upd = $pdo->prepare("
      UPDATE resenas
      SET estado = :e,
          moderada_por = :mp,
          moderada_at = :ma
      WHERE id = :id
    ");
    $upd->execute([
      ':e' => $estado,
      ':mp' => $moderadaPor,
      ':ma' => $now,
      ':id' => $id
    ]);

    echo json_encode(['ok' => true]);
    exit;
  }

  // =========================================================
  // ✅ ACCIONES DE CLIENTE (requiere sesión cliente)
  // action: my_get / my_save
  // =========================================================
  if ($action === 'my_get' || $action === 'my_save') {
    if (empty($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== 'cliente') {
      http_response_code(401);
      echo json_encode(['ok' => false, 'error' => 'No autenticado (cliente)']);
      exit;
    }

    $clienteId = (int)($_SESSION['cliente_id'] ?? 0);
    if ($clienteId <= 0) {
      http_response_code(401);
      echo json_encode(['ok' => false, 'error' => 'Cliente inválido']);
      exit;
    }

    if ($action === 'my_get') {
      $st = $pdo->prepare("
        SELECT id, nombre_publico, puntuacion, texto, fecha, created_at, updated_at, estado, moderada_por, moderada_at, motivo_moderacion
        FROM resenas
        WHERE cliente_id = :cid
        LIMIT 1
      ");
      $st->execute([':cid' => $clienteId]);
      $row = $st->fetch(PDO::FETCH_ASSOC);

      echo json_encode(['ok' => true, 'resena' => $row ?: null]);
      exit;
    }

    // my_save
    $nombrePublico = cleanText($data['nombre_publico'] ?? '');
    $texto = cleanText($data['texto'] ?? '');
    $puntuacion = (int)($data['puntuacion'] ?? 0);

    if ($puntuacion < 1 || $puntuacion > 5) {
      http_response_code(422);
      echo json_encode(['ok' => false, 'error' => 'Puntuación inválida (1-5)']);
      exit;
    }
    if (str_len($texto) < 5) {
      http_response_code(422);
      echo json_encode(['ok' => false, 'error' => 'Texto demasiado corto (mínimo 5 caracteres)']);
      exit;
    }
    if (str_len($texto) > 1000) {
      http_response_code(422);
      echo json_encode(['ok' => false, 'error' => 'Texto demasiado largo (máximo 1000 caracteres)']);
      exit;
    }
    if (str_len($nombrePublico) > 40) {
      http_response_code(422);
      echo json_encode(['ok' => false, 'error' => 'Nombre público demasiado largo (máximo 40)']);
      exit;
    }

    // upsert
    $st = $pdo->prepare("SELECT id, estado FROM resenas WHERE cliente_id = :cid LIMIT 1");
    $st->execute([':cid' => $clienteId]);
    $row = $st->fetch(PDO::FETCH_ASSOC);

    if ($row) {
      // Nota: NO tocamos estado aquí (si estaba oculta, sigue oculta)
      $upd = $pdo->prepare("
        UPDATE resenas
        SET nombre_publico = :n,
            puntuacion = :p,
            texto = :t,
            updated_at = datetime('now')
        WHERE cliente_id = :cid
      ");
      $upd->execute([
        ':n' => ($nombrePublico === '' ? null : $nombrePublico),
        ':p' => $puntuacion,
        ':t' => $texto,
        ':cid' => $clienteId
      ]);
    } else {
      $ins = $pdo->prepare("
        INSERT INTO resenas (cliente_id, nombre_publico, puntuacion, texto, fecha, created_at, updated_at, estado)
        VALUES (:cid, :n, :p, :t, date('now'), datetime('now'), datetime('now'), 'visible')
      ");
      $ins->execute([
        ':cid' => $clienteId,
        ':n' => ($nombrePublico === '' ? null : $nombrePublico),
        ':p' => $puntuacion,
        ':t' => $texto
      ]);
    }

    echo json_encode(['ok' => true]);
    exit;
  }

  http_response_code(400);
  echo json_encode(['ok' => false, 'error' => 'Acción inválida']);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode([
    'ok' => false,
    'error' => $e->getMessage()
  ]);
}



