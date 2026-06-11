<?php
declare(strict_types=1);
session_start();

if (empty($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== 'trabajador') {
    header("Location: login.php");
    exit;
}

$rol = $_SESSION['rol'] ?? 'trabajador';
if (!in_array($rol, ['jefe', 'encargado'], true)) {
    http_response_code(403);
    echo "Acceso denegado: solo jefe o encargado puede crear/editar el menú diario.";
    exit;
}

$nombreTrabajador = $_SESSION['nombre'] ?? 'Trabajador';
$fechaHoy = date('Y-m-d');
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Crear menú diario - BAR LOLI</title>
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
    <a class="nav-link nav-link--primary" href="menu_diario.php">Crear menú diario</a>
    <a class="nav-link" href="logout.php">Cerrar sesión</a>
  </div>
</nav>

<main class="container">
  <section class="card">
    <h2 class="res-title" style="margin-top:0;">Crear / editar menú del día</h2>
    <p class="res-sub">
      Sesión: <strong><?php echo htmlspecialchars($nombreTrabajador); ?></strong>
      (rol: <strong><?php echo htmlspecialchars($rol); ?></strong>)
    </p>

    <div class="res-top" style="margin-top:12px;">
      <div class="res-top__right">
        <label class="date-label" for="fechaMenu">Fecha</label>
        <input id="fechaMenu" class="date-input" type="date" value="<?php echo htmlspecialchars($fechaHoy); ?>" />
        <small class="date-help">Selecciona el día del menú. Puedes crear menús futuros.</small>
      </div>
    </div>

    <p class="hint" style="margin-top:10px;">
      <strong>Nota:</strong> Crea el menú diario y pulsa «Guardar menú» al final de la página.
    </p>

    <p id="msgMenu" class="msg" style="display:none;"></p>
  </section>

  <section class="card">
    <h3 class="res-title" style="margin-top:0;">Primeros (hasta 10)</h3>
    <div id="gridPrimero" class="menu-grid-edit"></div>
  </section>

  <section class="card">
    <h3 class="res-title" style="margin-top:0;">Segundos (hasta 10)</h3>
    <div id="gridSegundo" class="menu-grid-edit"></div>
  </section>

  <section class="card">
    <h3 class="res-title" style="margin-top:0;">Postres (hasta 10)</h3>
    <div id="gridPostre" class="menu-grid-edit"></div>
  </section>

  <section class="card">
    <div class="btn-row">
      <button class="btn" type="button" id="btnGuardarMenu">Guardar menú</button>
      <button class="btn btn--outline" type="button" id="btnLimpiarMenu">Limpiar menú (dejar vacío)</button>
    </div>
  </section>
</main>

<footer class="site-footer">
  <div class="container footer-inner">
    <div><strong>BAR LOLI</strong><br />Calle Ejemplo 123, Madrid</div>
    <div>Tel: 600 000 000<br />Horario: 09:00 - 23:00</div>
    <div>© <?php echo date('Y'); ?> BAR LOLI</div>
  </div>
</footer>

<script src="assets/js/menu_diario.js?v=1"></script>
</body>
</html>
