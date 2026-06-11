<?php
declare(strict_types=1);
session_start();

require __DIR__ . '/database/db.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['ok'=>false,'error'=>'Método no permitido']);
  exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!is_array($data)) {
  http_response_code(400);
  echo json_encode(['ok'=>false,'error'=>'JSON inválido']);
  exit;
}

$action = (string)($data['action'] ?? '');

function validarFecha(string $f): bool {
  return (bool)preg_match('/^\d{4}-\d{2}-\d{2}$/', $f);
}

function requireWorkerEditor(): void {
  if (empty($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== 'trabajador') {
    http_response_code(401);
    echo json_encode(['ok'=>false,'error'=>'No autenticado (trabajador)']);
    exit;
  }
  $rol = $_SESSION['rol'] ?? 'trabajador';
  if (!in_array($rol, ['jefe','encargado'], true)) {
    http_response_code(403);
    echo json_encode(['ok'=>false,'error'=>'Acceso denegado: solo jefe o encargado']);
    exit;
  }
}

try {
  $pdo = db();

  // === PUBLIC: menú del día para INDEX (no requiere login)
  if ($action === 'public_get') {
    $fecha = trim((string)($data['fecha'] ?? date('Y-m-d')));
    if (!validarFecha($fecha)) { http_response_code(422); echo json_encode(['ok'=>false,'error'=>'Fecha inválida']); exit; }

    $st = $pdo->prepare("SELECT id, fecha FROM menu_diario WHERE fecha=:f LIMIT 1");
    $st->execute([':f'=>$fecha]);
    $menu = $st->fetch(PDO::FETCH_ASSOC);

    if (!$menu) {
      echo json_encode(['ok'=>true,'fecha'=>$fecha,'menu'=>null]);
      exit;
    }

    $st2 = $pdo->prepare("
      SELECT i.tipo, i.orden, p.categoria, p.nombre, p.descripcion, p.precio
      FROM menu_diario_items i
      JOIN carta_platos p ON p.id = i.plato_id
      WHERE i.menu_id=:mid
      ORDER BY CASE i.tipo WHEN 'primero' THEN 1 WHEN 'segundo' THEN 2 WHEN 'postre' THEN 3 ELSE 9 END,
               i.orden ASC
    ");
    $st2->execute([':mid'=>(int)$menu['id']]);
    $items = $st2->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
      'ok'=>true,
      'fecha'=>$fecha,
      'menu'=>[
        'fecha'=>$menu['fecha'],
        'incluye'=>'Agua, vino, casera y pan',
        'items'=>$items
      ]
    ]);
    exit;
  }

  // === editor worker
  requireWorkerEditor();

  if ($action === 'platos_list') {
    $st = $pdo->query("
      SELECT id, categoria, nombre, descripcion, precio
      FROM carta_platos
      ORDER BY
        CASE categoria
          WHEN 'Ensaladas' THEN 1 WHEN 'Carnes' THEN 2 WHEN 'Pescados' THEN 3 WHEN 'Pasta' THEN 4
          WHEN 'Bocadillos' THEN 5 WHEN 'Sandwiches' THEN 6 WHEN 'Postres' THEN 7 WHEN 'Bebidas' THEN 8
        ELSE 99
        END,
      nombre ASC
    ");
    echo json_encode(['ok'=>true,'items'=>$st->fetchAll(PDO::FETCH_ASSOC)]);
    exit;
  }

  if ($action === 'get_menu') {
    $fecha = trim((string)($data['fecha'] ?? date('Y-m-d')));
    if (!validarFecha($fecha)) { http_response_code(422); echo json_encode(['ok'=>false,'error'=>'Fecha inválida']); exit; }

    $st = $pdo->prepare("SELECT id, fecha FROM menu_diario WHERE fecha=:f LIMIT 1");
    $st->execute([':f'=>$fecha]);
    $menu = $st->fetch(PDO::FETCH_ASSOC);

    if (!$menu) {
      echo json_encode(['ok'=>true,'fecha'=>$fecha,'menu'=>['primero'=>[],'segundo'=>[],'postre'=>[]]]);
      exit;
    }

    $st2 = $pdo->prepare("
      SELECT tipo, orden, plato_id
      FROM menu_diario_items
      WHERE menu_id=:mid
      ORDER BY tipo, orden
    ");
    $st2->execute([':mid'=>(int)$menu['id']]);
    $rows = $st2->fetchAll(PDO::FETCH_ASSOC);

    $out = ['primero'=>[], 'segundo'=>[], 'postre'=>[]];
    foreach ($rows as $r) {
      $t = (string)$r['tipo'];
      $out[$t][] = [
        'orden'=>(int)$r['orden'],
        'plato_id'=>(int)$r['plato_id']
      ];
    }

    echo json_encode(['ok'=>true,'fecha'=>$fecha,'menu'=>$out]);
    exit;
  }

  if ($action === 'save_menu') {
    $fecha = trim((string)($data['fecha'] ?? date('Y-m-d')));
    if (!validarFecha($fecha)) { http_response_code(422); echo json_encode(['ok'=>false,'error'=>'Fecha inválida']); exit; }

    $items = $data['items'] ?? null;
    if (!is_array($items)) { http_response_code(422); echo json_encode(['ok'=>false,'error'=>'Items inválidos']); exit; }

    // Items esperados: [{tipo:'primero', orden:1..10, plato_id:int}, ...]
    $validTipos = ['primero','segundo','postre'];

    $clean = [];
    foreach ($items as $it) {
      if (!is_array($it)) continue;
      $tipo = (string)($it['tipo'] ?? '');
      $orden = (int)($it['orden'] ?? 0);
      $pid = (int)($it['plato_id'] ?? 0);

      if (!in_array($tipo, $validTipos, true)) continue;
      if ($orden < 1 || $orden > 10) continue;
      if ($pid <= 0) continue;

      $clean[] = ['tipo'=>$tipo,'orden'=>$orden,'plato_id'=>$pid];
    }

    $pdo->beginTransaction();

    // upsert menu_diario
    $st = $pdo->prepare("SELECT id FROM menu_diario WHERE fecha=:f LIMIT 1");
    $st->execute([':f'=>$fecha]);
    $row = $st->fetch(PDO::FETCH_ASSOC);

    if ($row) {
      $menuId = (int)$row['id'];
      $pdo->prepare("UPDATE menu_diario SET updated_at=NOW() WHERE id=:id")->execute([':id'=>$menuId]);
      $pdo->prepare("DELETE FROM menu_diario_items WHERE menu_id=:id")->execute([':id'=>$menuId]);
    } else {
      $ins = $pdo->prepare("
        INSERT INTO menu_diario (fecha, creado_por, creado_por_nombre, updated_at)
        VALUES (:f, :uid, :un, NOW())
      ");
      $ins->execute([
        ':f'=>$fecha,
        ':uid'=>(int)($_SESSION['trabajador_id'] ?? 0),
        ':un'=>(string)($_SESSION['nombre'] ?? '')
      ]);
      $menuId = (int)$pdo->lastInsertId();
    }

    if (count($clean) > 0) {
      $insItem = $pdo->prepare("
        INSERT INTO menu_diario_items (menu_id, tipo, orden, plato_id)
        VALUES (:mid, :t, :o, :pid)
      ");
      foreach ($clean as $it) {
        $insItem->execute([
          ':mid'=>$menuId,
          ':t'=>$it['tipo'],
          ':o'=>$it['orden'],
          ':pid'=>$it['plato_id'],
        ]);
      }
    }

    $pdo->commit();

    echo json_encode(['ok'=>true]);
    exit;
  }

  if ($action === 'clear_menu') {
    $fecha = trim((string)($data['fecha'] ?? date('Y-m-d')));
    if (!validarFecha($fecha)) { http_response_code(422); echo json_encode(['ok'=>false,'error'=>'Fecha inválida']); exit; }

    $pdo->beginTransaction();
    $st = $pdo->prepare("SELECT id FROM menu_diario WHERE fecha=:f LIMIT 1");
    $st->execute([':f'=>$fecha]);
    $row = $st->fetch(PDO::FETCH_ASSOC);

    if ($row) {
      $menuId = (int)$row['id'];
      $pdo->prepare("DELETE FROM menu_diario_items WHERE menu_id=:id")->execute([':id'=>$menuId]);
      $pdo->prepare("DELETE FROM menu_diario WHERE id=:id")->execute([':id'=>$menuId]);
    }
    $pdo->commit();

    echo json_encode(['ok'=>true]);
    exit;
  }

  http_response_code(400);
  echo json_encode(['ok'=>false,'error'=>'Acción inválida']);
} catch (Throwable $e) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
      $pdo->rollBack();
    }

    http_response_code(500);
    echo json_encode([
      'ok' => false,
      'error' => 'Error interno'
    ]);
  }
