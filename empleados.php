<?php
declare(strict_types=1);
session_start();
require __DIR__ . '/database/db.php';

/* Solo trabajadores con rol jefe/encargado */
if (empty($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== 'trabajador') {
    header("Location: login.php");
    exit;
}
$rolSesion = $_SESSION['rol'] ?? 'trabajador';
if ($rolSesion !== 'jefe' && $rolSesion !== 'encargado') {
    header("Location: zona_trabajadores.php");
    exit;
}

$pdo = db();

/* Listado de empleados */
$st = $pdo->query("SELECT id, nombre, apellido, email, tlf, rol FROM trabajadores ORDER BY CASE rol WHEN 'jefe' THEN 0 WHEN 'encargado' THEN 1 ELSE 2 END, nombre ASC");
$empleados = $st->fetchAll();
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Empleados - BAR LOLI</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>

<header class="site-header">
  <div class="container">
    <h1 class="brand">BAR LOLI</h1>
  </div>
</header>

<nav class="site-nav">
  <div class="container nav-inner">
    <a class="nav-link" href="zona_trabajadores.php">⬅ Volver</a>
    <a class="nav-link nav-link--primary" href="empleados.php">Empleados</a>
    <a class="nav-link" href="logout.php">Cerrar sesión</a>
  </div>
</nav>

<main class="container">

  <!-- CREAR -->
  <section class="card">
    <h2 class="empleados-title">Crear empleados</h2>
    <div class="empleados-roles-info">
      <div><strong>
        Encargados:</strong> pueden crear menús, platos y manejar reservas.
      </div>
      <div><strong>
        Trabajadores:</strong> solo pueden manejar reservas.</div>
    </div>

    <form id="formEmpleado" novalidate>
      <div class="form-grid">
        <div class="field">
          <label for="nombre">Nombre *</label>
          <input id="nombre" name="nombre" type="text" placeholder="Ej: Ana" required>
          <small class="error" id="errNombre"></small>
        </div>

        <div class="field">
          <label for="apellido">Apellido *</label>
          <input id="apellido" name="apellido" type="text" placeholder="Ej: García" required>
          <small class="error" id="errApellido"></small>
        </div>

        <div class="field">
          <label for="email">Email *</label>
          <input id="email" name="email" type="email" placeholder="Ej: ana@barloli.com" required>
          <small class="error" id="errEmail"></small>
        </div>

        <div class="field">
          <label for="tlf">Teléfono *</label>
          <input id="tlf" name="tlf" type="tel" placeholder="Ej: +34 600123123" required>
          <small class="error" id="errTlf"></small>
        </div>

        <div class="field">
          <label for="rol">Rol *</label>
          <select id="rol" name="rol" required>
            <option value="trabajador" selected>trabajador</option>
            <option value="encargado">encargado</option>
          </select>
          <small class="error" id="errRol"></small>
        </div>

        <div class="field">
          <label for="password">Contraseña *</label>
          <input id="password" name="password" type="password" placeholder="Mínimo 8 caracteres" required>
          <small class="error" id="errPassword"></small>
        </div>

        <div class="field">
          <label for="password2">Repite contraseña *</label>
          <input id="password2" name="password2" type="password" placeholder="Repite la contraseña" required>
          <small class="error" id="errPassword2"></small>
        </div>
      </div>

      <p id="msg" class="msg" style="display:none;"></p>

      <div class="actions">
        <button class="btn btn-success" type="submit" id="btnCrear">Crear empleado</button>
      </div>
    </form>
  </section>

  <!-- EDITAR (se rellena al pulsar Editar en una fila) -->
  <section class="card" id="cardEditar" style="display:none;">
    <h2 style="margin-top:0;">Editar empleado</h2>
    <p style="color:#6b7280; margin-top:6px;">
      Se pueden editar <strong>nombre, apellido, teléfono y rol</strong>. El <strong>email</strong> no cambia.
    </p>

    <form id="formEditar" novalidate>
      <input type="hidden" id="editId">

      <div class="form-grid">
        <div class="field">
          <label>Email (no editable)</label>
          <input id="editEmail" type="text" disabled>
        </div>

        <div class="field">
          <label for="editNombre">Nombre *</label>
          <input id="editNombre" type="text" placeholder="Ej: Ana" required>
          <small class="error" id="errEditNombre"></small>
        </div>

        <div class="field">
          <label for="editApellido">Apellido *</label>
          <input id="editApellido" type="text" placeholder="Ej: García" required>
          <small class="error" id="errEditApellido"></small>
        </div>

        <div class="field">
          <label for="editTlf">Teléfono *</label>
          <input id="editTlf" type="tel" placeholder="Ej: +34 600123123" required>
          <small class="error" id="errEditTlf"></small>
        </div>

        <div class="field">
          <label for="editRol">Rol *</label>
          <select id="editRol" required>
            <option value="trabajador">trabajador</option>
            <option value="encargado">encargado</option>
          </select>
          <small class="error" id="errEditRol"></small>
        </div>
      </div>

      <p id="msgEdit" class="msg" style="display:none;"></p>

      <div class="actions">
        <button class="btn btn-success" type="submit" id="btnGuardar">Guardar cambios</button>
        <button class="btn btn-danger" type="button" id="btnCancelarEdicion">Cancelar</button>
      </div>
    </form>
  </section>

  <!-- LISTADO -->
  <section class="card">
    <h2 style="margin-top:0;">Listado de empleados</h2>

    <div class="table-wrap">
      <table class="table">
        <thead>
          <tr>
            <th>Nombre</th>
            <th>Email</th>
            <th>Teléfono</th>
            <th>Rol</th>
            <th style="width:180px;">Acciones</th>
          </tr>
        </thead>

        <tbody id="tablaEmpleados">
          <?php foreach ($empleados as $e): ?>
            <tr
              data-id="<?php echo (int)$e['id']; ?>"
              data-nombre="<?php echo htmlspecialchars($e['nombre'], ENT_QUOTES); ?>"
              data-apellido="<?php echo htmlspecialchars($e['apellido'], ENT_QUOTES); ?>"
              data-email="<?php echo htmlspecialchars($e['email'], ENT_QUOTES); ?>"
              data-tlf="<?php echo htmlspecialchars($e['tlf'], ENT_QUOTES); ?>"
              data-rol="<?php echo htmlspecialchars($e['rol'], ENT_QUOTES); ?>"
            >
              <td class="td-nombre"><?php echo htmlspecialchars($e['nombre'] . ' ' . $e['apellido']); ?></td>
              <td class="td-email"><?php echo htmlspecialchars($e['email']); ?></td>
              <td class="td-tlf"><?php echo htmlspecialchars($e['tlf']); ?></td>
              <td class="td-rol"><span class="badge"><?php echo htmlspecialchars($e['rol']); ?></span></td>
              <td>
                <?php if ($e['rol'] === 'jefe'): ?>
                  <span class="muted">Protegido</span>
                <?php else: ?>
                  <button class="btn-mini btn-mini--edit" type="button" data-action="edit">Editar</button>
                  <button class="btn-mini btn-mini--danger" type="button" data-action="delete">Eliminar</button>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>

      </table>
    </div>
  </section>

</main>

<footer class="site-footer">
  <div class="container footer-inner">
    <div><strong>BAR LOLI</strong><br>Calle Ejemplo 123, Madrid</div>
    <div>Tel: 600 000 000<br>Horario: 09:00 - 23:00</div>
    <div>© <?php echo date('Y'); ?> BAR LOLI</div>
  </div>
</footer>

<script src="assets/js/empleados.js?v=2"></script>
</body>
</html>

