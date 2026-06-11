<?php
declare(strict_types=1);
session_start();

if (empty($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== 'trabajador') {
    header("Location: login.php");
    exit;
}

$nombreTrabajador = $_SESSION['nombre'] ?? 'Trabajador';
$rol = $_SESSION['rol'] ?? 'trabajador';
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Clientes registrados - BAR LOLI</title>
  <link rel="stylesheet" href="/BarApp/assets/css/styles.css">
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
    <a class="nav-link nav-link--primary" href="clientes.php">Clientes</a>
    <a class="nav-link" href="logout.php">Cerrar sesión</a>
  </div>
</nav>

<main class="container">
  <section class="card">
    <h2 class="res-title" style="margin-top:0;">Clientes registrados</h2>
    <p class="res-sub">
      Sesión: <strong><?php echo htmlspecialchars($nombreTrabajador); ?></strong>
      (rol: <strong><?php echo htmlspecialchars($rol); ?></strong>)
    </p>

    <div class="field" style="max-width:760px;">
      <label for="q">Busca escribiendo un solo campo (nombre, apellidos, email, teléfono)</label>

      <div class="btn-row" style="gap:10px; align-items:flex-end;">
        <input id="q" type="text" placeholder="Ej: paco | gmail | 600" style="flex:1;" />
        <button class="btn" type="button" id="btnBuscarClientes">Buscar</button>
        <button class="btn btn--outline" type="button" id="btnLimpiarClientes">Limpiar</button>
      </div>

      <small class="hint">Pulsa <strong>Buscar</strong> (o Enter) para aplicar el filtro.</small>
    </div>

    <p id="msgClientes" class="msg" style="display:none;"></p>

    <div class="table-wrap" style="margin-top:12px;">
      <table class="table">
        <thead>
          <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Apellidos</th>
                <th>Email</th>
                <th>Teléfono</th>
                <th>Alta</th>
            </tr>
        </thead>
        <tbody id="tbodyClientes">
          <tr><td colspan="6">Cargando...</td></tr>
        </tbody>
      </table>
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

<script src="/BarApp/assets/js/clientes.js?v=2"></script>
</body>
</html>


