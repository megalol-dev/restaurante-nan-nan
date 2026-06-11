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

/** Validaciones */
function validarTurno(string $t): bool { return $t === 'comida' || $t === 'cena'; }
function validarFecha(string $f): bool { return (bool)preg_match('/^\d{4}-\d{2}-\d{2}$/', $f); }

function horaToMin(string $hhmm): int {
    $parts = explode(':', $hhmm);
    $h = isset($parts[0]) ? (int)$parts[0] : 0;
    $m = isset($parts[1]) ? (int)$parts[1] : 0;
    return $h * 60 + $m;
}

/**
 * ✅ Seguridad servidor: impedir reservar días pasados.
 * Formato YYYY-MM-DD permite comparación lexicográfica.
 */
function esFechaPasada(string $fecha): bool {
    $hoy = date('Y-m-d');
    return $fecha < $hoy;
}

/**
 * Reglas de negocio (SOLO para HOY):
 * - Desde 15:00 -> no se permite reservar COMIDA para HOY
 * - Desde 22:00 -> no se permite reservar CENA para HOY
 * Para fechas futuras => OK (a cualquier hora).
 */
function validarVentanaCliente(string $fecha, string $turno): ?string {
    $hoy = date('Y-m-d');
    if ($fecha !== $hoy) return null; // fechas futuras: permitido siempre

    $nowMin = horaToMin(date('H:i'));
    $min15 = horaToMin('15:00');
    $min22 = horaToMin('22:00');

    if ($turno === 'comida' && $nowMin >= $min15) {
        return "A partir de las 15:00 no se permite reservar para comer para HOY. Puedes reservar para días posteriores.";
    }
    if ($turno === 'cena' && $nowMin >= $min22) {
        return "A partir de las 22:00 no se permite reservar para cenar para HOY. Puedes reservar para días posteriores.";
    }
    return null;
}

try {
    $pdo = db();

    // ===== helper estado por fecha+turno =====
    $getState = function(string $fecha, string $turno) use ($pdo) {
        $st = $pdo->prepare("
          SELECT rm.mesa_id,
                 r.id AS reserva_id,
                 r.cliente_nombre,
                 r.comensales,
                 r.mesas_usadas,
                 r.trabajador_nombre,
                 r.created_at,
                 r.canal
          FROM reserva_mesas rm
          JOIN reservas r ON r.id = rm.reserva_id
          WHERE r.estado = 'activa' AND r.fecha = :fecha AND r.turno = :turno
        ");
        $st->execute([':fecha' => $fecha, ':turno' => $turno]);

        $ocupadas = [];
        foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $canal = (string)($row['canal'] ?? '');
            $clienteNombre = (string)($row['cliente_nombre'] ?? '');

            $ocupadas[(int)$row['mesa_id']] = [
                'reserva_id' => (int)$row['reserva_id'],
                'cliente_nombre' => $clienteNombre,
                'cliente_mostrar' => ($canal === 'online')
                    ? ($clienteNombre . ' [RESERVA ONLINE]')
                    : $clienteNombre,
                'comensales' => (int)$row['comensales'],
                'mesas_usadas' => (int)$row['mesas_usadas'],
                'trabajador_nombre' => (string)$row['trabajador_nombre'],
                'created_at' => (string)$row['created_at'],
                'canal' => $canal
            ];
        }

        $total = 10;
        $ocupadasCount = count($ocupadas);
        $disponibles = $total - $ocupadasCount;

        return [
            'fecha' => $fecha,
            'turno' => $turno,
            'ocupadas' => $ocupadas,
            'kpi' => [
                'total' => $total,
                'ocupadas' => $ocupadasCount,
                'disponibles' => $disponibles,
                'capacidad_restante' => $disponibles * 5
            ]
        ];
    };

    // ===== helper resumen día (comida + cena) =====
    $getResumenDia = function(string $fecha) use ($getState) {
        $c = $getState($fecha, 'comida');
        $n = $getState($fecha, 'cena');
        return ['comida' => $c['kpi'], 'cena' => $n['kpi']];
    };

    // ============================
    // ACCIÓN: resumen día (cliente)
    // ============================
    if ($action === 'resumen_dia') {
        $fecha = trim((string)($data['fecha'] ?? date('Y-m-d')));
        if (!validarFecha($fecha)) {
            http_response_code(422);
            echo json_encode(['ok' => false, 'error' => 'Fecha inválida']);
            exit;
        }

        echo json_encode([
            'ok' => true,
            'fecha' => $fecha,
            'resumen' => $getResumenDia($fecha)
        ]);
        exit;
    }

    // ============================
    // ACCIÓN: trabajador (state/ocupar/liberar)
    // ============================
    if (in_array($action, ['state','ocupar','liberar'], true)) {
        if (empty($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== 'trabajador') {
            http_response_code(401);
            echo json_encode(['ok' => false, 'error' => 'No autenticado (trabajador)']);
            exit;
        }

        $fecha = trim((string)($data['fecha'] ?? date('Y-m-d')));
        $turno = trim((string)($data['turno'] ?? ''));

        if (!validarFecha($fecha) || !validarTurno($turno)) {
            http_response_code(422);
            echo json_encode(['ok' => false, 'error' => 'Fecha/turno inválido']);
            exit;
        }

        if ($action === 'state') {
            echo json_encode(['ok' => true, 'state' => $getState($fecha, $turno)]);
            exit;
        }

        if ($action === 'ocupar') {
            $mesaBase = (int)($data['mesa_id'] ?? 0);
            $clienteNombre = trim((string)($data['cliente_nombre'] ?? ''));
            $comensales = (int)($data['comensales'] ?? 0);

            if ($mesaBase < 1 || $mesaBase > 10) { http_response_code(422); echo json_encode(['ok'=>false,'error'=>'Mesa inválida']); exit; }
            if ($clienteNombre === '') { http_response_code(422); echo json_encode(['ok'=>false,'error'=>'Nombre cliente obligatorio']); exit; }
            if ($comensales < 1 || $comensales > 50) { http_response_code(422); echo json_encode(['ok'=>false,'error'=>'Comensales 1-50']); exit; }

            $mesasNecesarias = (int)ceil($comensales / 5);

            $st = $pdo->prepare("
              SELECT rm.mesa_id
              FROM reserva_mesas rm
              JOIN reservas r ON r.id = rm.reserva_id
              WHERE r.estado='activa' AND r.fecha=:fecha AND r.turno=:turno
            ");
            $st->execute([':fecha'=>$fecha, ':turno'=>$turno]);
            $ocupadas = array_map('intval', $st->fetchAll(PDO::FETCH_COLUMN));
            $set = array_fill_keys($ocupadas, true);

            if (isset($set[$mesaBase])) { http_response_code(409); echo json_encode(['ok'=>false,'error'=>'Mesa ocupada']); exit; }

            $mesasAsignadas = [$mesaBase];
            for ($m=1; $m<=10 && count($mesasAsignadas)<$mesasNecesarias; $m++) {
                if ($m === $mesaBase) continue;
                if (!isset($set[$m])) $mesasAsignadas[] = $m;
            }
            if (count($mesasAsignadas) < $mesasNecesarias) {
                http_response_code(409);
                echo json_encode(['ok'=>false,'error'=>'No hay mesas suficientes libres']);
                exit;
            }

            $pdo->beginTransaction();

            $trabajadorId = (int)($_SESSION['trabajador_id'] ?? 0);
            $trabajadorNombre = (string)($_SESSION['nombre'] ?? 'Trabajador');

            $ins = $pdo->prepare("
              INSERT INTO reservas (cliente_nombre, comensales, mesas_usadas, trabajador_id, trabajador_nombre, estado, fecha, turno, canal)
              VALUES (:cliente,:comensales,:mesas,:tid,:tnombre,'activa',:fecha,:turno,'manual')
            ");
            $ins->execute([
                ':cliente'=>$clienteNombre, ':comensales'=>$comensales, ':mesas'=>$mesasNecesarias,
                ':tid'=>$trabajadorId, ':tnombre'=>$trabajadorNombre, ':fecha'=>$fecha, ':turno'=>$turno
            ]);

            $rid = (int)$pdo->lastInsertId();
            $insRM = $pdo->prepare("INSERT INTO reserva_mesas (reserva_id, mesa_id) VALUES (:rid,:mid)");
            foreach ($mesasAsignadas as $mid) $insRM->execute([':rid'=>$rid, ':mid'=>$mid]);

            $pdo->commit();

            echo json_encode(['ok'=>true,'reserva_id'=>$rid,'mesas_asignadas'=>$mesasAsignadas,'state'=>$getState($fecha,$turno)]);
            exit;
        }

        if ($action === 'liberar') {
            $mesaId = (int)($data['mesa_id'] ?? 0);
            if ($mesaId < 1 || $mesaId > 10) { http_response_code(422); echo json_encode(['ok'=>false,'error'=>'Mesa inválida']); exit; }

            $st = $pdo->prepare("
              SELECT r.id
              FROM reservas r
              JOIN reserva_mesas rm ON rm.reserva_id = r.id
              WHERE r.estado='activa' AND r.fecha=:fecha AND r.turno=:turno AND rm.mesa_id=:mid
              LIMIT 1
            ");
            $st->execute([':fecha'=>$fecha, ':turno'=>$turno, ':mid'=>$mesaId]);
            $row = $st->fetch(PDO::FETCH_ASSOC);
            if (!$row) { http_response_code(404); echo json_encode(['ok'=>false,'error'=>'Mesa ya libre']); exit; }

            $rid = (int)$row['id'];
            $pdo->prepare("UPDATE reservas SET estado='cancelada' WHERE id=:id")->execute([':id'=>$rid]);

            echo json_encode(['ok'=>true,'state'=>$getState($fecha,$turno)]);
            exit;
        }
    }

    // ============================
    // ACCIÓN: cliente
    // ============================
    if (in_array($action, ['cliente_listar','cliente_mi_reserva','cliente_reservar','cliente_cancelar'], true)) {
        if (empty($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== 'cliente') {
            http_response_code(401);
            echo json_encode(['ok' => false, 'error' => 'No autenticado (cliente)']);
            exit;
        }

        $clienteId = (int)($_SESSION['cliente_id'] ?? 0);
        $clienteNombre = trim((string)(($_SESSION['cliente_nombre'] ?? '') ?: ($_SESSION['cliente_email'] ?? 'Cliente')));

        // ✅ LISTAR: NO depende del calendario
        if ($action === 'cliente_listar') {
            $st = $pdo->prepare("
              SELECT id, comensales, mesas_usadas, turno, fecha, created_at
              FROM reservas
              WHERE estado='activa' AND (cliente_id=:cid OR cliente_nombre=:cn)
              ORDER BY fecha ASC,
                       CASE turno WHEN 'comida' THEN 1 WHEN 'cena' THEN 2 ELSE 9 END ASC,
                       id DESC
            ");
            $st->execute([':cid' => $clienteId, ':cn' => $clienteNombre]);
            $rows = $st->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['ok' => true, 'reservas' => $rows]);
            exit;
        }

        // ✅ CANCELAR: por ID, NO depende de fecha/turno, NI aplica ventana horaria
        if ($action === 'cliente_cancelar') {
            $rid = (int)($data['reserva_id'] ?? 0);
            if ($rid <= 0) {
                http_response_code(422);
                echo json_encode(['ok'=>false,'error'=>'Reserva inválida']);
                exit;
            }

            $st = $pdo->prepare("
              SELECT id
              FROM reservas
              WHERE id=:id AND estado='activa' AND (cliente_id=:cid OR cliente_nombre=:cn)
              LIMIT 1
            ");
            $st->execute([':id'=>$rid, ':cid'=>$clienteId, ':cn'=>$clienteNombre]);
            if (!$st->fetch()) {
                http_response_code(403);
                echo json_encode(['ok'=>false,'error'=>'No puedes cancelar esa reserva (o no existe).']);
                exit;
            }

            $pdo->prepare("UPDATE reservas SET estado='cancelada' WHERE id=:id")->execute([':id'=>$rid]);

            $st2 = $pdo->prepare("
              SELECT id, comensales, mesas_usadas, turno, fecha, created_at
              FROM reservas
              WHERE estado='activa' AND (cliente_id=:cid OR cliente_nombre=:cn)
              ORDER BY fecha ASC,
                       CASE turno WHEN 'comida' THEN 1 WHEN 'cena' THEN 2 ELSE 9 END ASC,
                       id DESC
            ");
            $st2->execute([':cid' => $clienteId, ':cn' => $clienteNombre]);
            $rows = $st2->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['ok'=>true, 'reservas'=>$rows]);
            exit;
        }

        // A partir de aquí sí necesitamos fecha/turno
        $fecha = trim((string)($data['fecha'] ?? date('Y-m-d')));
        $turno = trim((string)($data['turno'] ?? ''));

        if (!validarFecha($fecha) || !validarTurno($turno)) {
            http_response_code(422);
            echo json_encode(['ok' => false, 'error' => 'Fecha/turno inválido']);
            exit;
        }

        // ✅ Seguridad: no permitir reservar días pasados (SERVDOR)
        if ($action === 'cliente_reservar' && esFechaPasada($fecha)) {
            http_response_code(409);
            echo json_encode(['ok' => false, 'error' => 'No se puede reservar para un día que ya pasó.']);
            exit;
        }

        // Ventana horaria SOLO para crear reserva (y solo si fecha=HOY)
        if ($action === 'cliente_reservar') {
            $errVentana = validarVentanaCliente($fecha, $turno);
            if ($errVentana) {
                http_response_code(409);
                echo json_encode(['ok' => false, 'error' => $errVentana]);
                exit;
            }
        }

        if ($action === 'cliente_mi_reserva') {
            $st = $pdo->prepare("
              SELECT id, comensales, mesas_usadas, turno, fecha, created_at
              FROM reservas
              WHERE estado='activa' AND fecha=:fecha AND turno=:turno AND (cliente_id=:cid OR cliente_nombre=:cn)
              ORDER BY id DESC
              LIMIT 1
            ");
            $st->execute([':fecha'=>$fecha, ':turno'=>$turno, ':cid'=>$clienteId, ':cn'=>$clienteNombre]);
            $row = $st->fetch(PDO::FETCH_ASSOC);

            echo json_encode(['ok'=>true, 'reserva'=>$row ?: null, 'resumen'=>$getResumenDia($fecha)]);
            exit;
        }

        if ($action === 'cliente_reservar') {
            $comensales = (int)($data['comensales'] ?? 0);
            if ($comensales < 1 || $comensales > 50) {
                http_response_code(422);
                echo json_encode(['ok'=>false,'error'=>'Comensales debe estar entre 1 y 50']);
                exit;
            }
            $mesasNecesarias = (int)ceil($comensales / 5);

            $state = $getState($fecha, $turno);
            $disp = (int)$state['kpi']['disponibles'];

            if ($disp <= 0) {
                http_response_code(409);
                echo json_encode(['ok'=>false,'error'=>'Lo sentimos, ese turno está completo.']);
                exit;
            }
            if ($mesasNecesarias > $disp) {
                http_response_code(409);
                echo json_encode([
                    'ok'=>false,
                    'error' => "No hay sitio suficiente. Máximo disponible: {$disp} mesas (" . ($disp * 5) . " personas)."
                ]);
                exit;
            }

            // Una reserva por turno y fecha
            $st = $pdo->prepare("
              SELECT id FROM reservas
              WHERE estado='activa' AND fecha=:fecha AND turno=:turno AND (cliente_id=:cid OR cliente_nombre=:cn)
              LIMIT 1
            ");
            $st->execute([':fecha'=>$fecha, ':turno'=>$turno, ':cid'=>$clienteId, ':cn'=>$clienteNombre]);
            if ($st->fetch()) {
                http_response_code(409);
                echo json_encode(['ok'=>false,'error'=>'Ya tienes una reserva activa en ese turno. Cancélala para crear otra.']);
                exit;
            }

            $ocupadasSet = array_fill_keys(array_map('intval', array_keys($state['ocupadas'] ?? [])), true);
            $mesasAsignadas = [];
            for ($m=1; $m<=10 && count($mesasAsignadas)<$mesasNecesarias; $m++) {
                if (!isset($ocupadasSet[$m])) $mesasAsignadas[] = $m;
            }

            $pdo->beginTransaction();

            $ins = $pdo->prepare("
              INSERT INTO reservas (cliente_nombre, cliente_id, comensales, mesas_usadas, trabajador_id, trabajador_nombre, estado, fecha, turno, canal)
              VALUES (:cn,:cid,:com,:mu,0,'Online','activa',:fecha,:turno,'online')
            ");
            $ins->execute([
                ':cn'=>$clienteNombre,
                ':cid'=>$clienteId,
                ':com'=>$comensales,
                ':mu'=>$mesasNecesarias,
                ':fecha'=>$fecha,
                ':turno'=>$turno
            ]);

            $rid = (int)$pdo->lastInsertId();
            $insRM = $pdo->prepare("INSERT INTO reserva_mesas (reserva_id, mesa_id) VALUES (:rid,:mid)");
            foreach ($mesasAsignadas as $mid) $insRM->execute([':rid'=>$rid, ':mid'=>$mid]);

            $pdo->commit();

            echo json_encode([
                'ok'=>true,
                'reserva_id'=>$rid,
                'mesas_asignadas'=>$mesasAsignadas,
                'resumen'=>$getResumenDia($fecha)
            ]);
            exit;
        }
    }

    http_response_code(400);
    echo json_encode(['ok'=>false,'error'=>'Acción inválida']);
} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['ok'=>false,'error'=>'Error interno']);
}

