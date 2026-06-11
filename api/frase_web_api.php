<?php
declare(strict_types=1);
session_start();

require __DIR__ . '/../database/db.php';

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
function str_len(string $s): int { return strlen($s); }

try {
  $pdo = db();

  // =========================================================
  // PÚBLICO: obtener frase
  // action: get
  // =========================================================
  if ($action === 'get') {
    $st = $pdo->query("SELECT id, titulo, subtitulo, updated_at FROM web_frase WHERE id=1 LIMIT 1");
    $row = $st->fetch(PDO::FETCH_ASSOC);

    echo json_encode(['ok' => true, 'frase' => $row ?: null]);
    exit;
  }

  // =========================================================
  // JEFE: guardar frase
  // action: set {titulo, subtitulo}
  // =========================================================
  if ($action === 'set') {
    if (empty($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== 'trabajador' || (($_SESSION['rol'] ?? '') !== 'jefe')) {
      http_response_code(403);
      echo json_encode(['ok' => false, 'error' => 'Acceso denegado']);
      exit;
    }

    $titulo = cleanText($data['titulo'] ?? '');
    $subtitulo = cleanText($data['subtitulo'] ?? '');

    if (str_len($titulo) < 3) {
      http_response_code(422);
      echo json_encode(['ok' => false, 'error' => 'El título es demasiado corto (mínimo 3 caracteres).']);
      exit;
    }
    if (str_len($titulo) > 80) {
      http_response_code(422);
      echo json_encode(['ok' => false, 'error' => 'El título es demasiado largo (máximo 80).']);
      exit;
    }
    if (str_len($subtitulo) < 3) {
      http_response_code(422);
      echo json_encode(['ok' => false, 'error' => 'El subtítulo es demasiado corto (mínimo 3 caracteres).']);
      exit;
    }
    if (str_len($subtitulo) > 160) {
      http_response_code(422);
      echo json_encode(['ok' => false, 'error' => 'El subtítulo es demasiado largo (máximo 160).']);
      exit;
    }

    // UPSERT con id=1
    $st = $pdo->prepare("
      UPDATE web_frase
      SET titulo = :t,
        subtitulo = :s,
        updated_at = NOW()
      WHERE id = 1
    ");
    $st->execute([':t' => $titulo, ':s' => $subtitulo]);

    echo json_encode(['ok' => true]);
    exit;
  }

  http_response_code(400);
  echo json_encode(['ok' => false, 'error' => 'Acción inválida']);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok' => false, 'error' => 'Error interno']);
}