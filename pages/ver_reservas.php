<?php
declare(strict_types=1);
session_start();

require __DIR__ . '/../database/db.php';

if (empty($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== 'trabajador') {
    header("Location: login.php");
    exit;
}

$nombreTrabajador = $_SESSION['nombre'] ?? 'Trabajador';
$rol = $_SESSION['rol'] ?? 'trabajador';


$fecha = $_GET['fecha'] ?? date('Y-m-d');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
    $fecha = date('Y-m-d');
}

?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Reservas - BAR LOLI</title>
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
    <a class="nav-link nav-link--primary" href="ver_reservas.php">Reservas</a>
    <a class="nav-link" href="logout.php">Cerrar sesión</a>
  </div>
</nav>

<main class="container">
  <section class="card">
  <h2 class="res-title">Gestión de reservas por turnos</h2>

  <div class="res-top">
    <div class="res-top__left">
      <p class="res-sub">
        Sesión: <strong><?php echo htmlspecialchars($nombreTrabajador); ?></strong>
        (rol: <strong><?php echo htmlspecialchars($rol); ?></strong>)
      </p>
      <p class="res-sub">
        Mesa en rojo = ocupada.
        Si hay más de 5 comensales, el sistema asigna mesas adicionales automáticamente.
      </p>
    </div>

    <div class="res-top__right">
      <label class="date-label" for="fechaReserva">Fecha</label>
      <input id="fechaReserva" class="date-input" type="date" value="<?php echo htmlspecialchars($fecha); ?>" />
      <small class="date-help">Cambia la fecha para ver/gestionar reservas.</small>
    </div>
  </div>

  <p id="msg" class="msg" style="display:none;"></p>
  </section>


  <!-- TURNO COMIDA -->
  <section class="card">
    <div class="turno-header">
      <h3 class="turno-title">Turno de comidas (14:00 - 16:00)</h3>

      <div class="resumen-mesas">
        <div class="kpi">
          <div class="kpi__label">Mesas disponibles</div>
          <div class="kpi__value" id="kpiMesasComida">-</div>
        </div>
        <div class="kpi">
          <div class="kpi__label">Capacidad restante</div>
          <div class="kpi__value" id="kpiCapacidadComida">-</div>
        </div>
        <div class="kpi">
          <div class="kpi__label">Ocupadas</div>
          <div class="kpi__value" id="kpiOcupadasComida">-</div>
        </div>
      </div>
    </div>

    <div id="gridMesasComida" class="grid-mesas" aria-label="Plano de mesas comida">
      <?php
      for ($i = 1; $i <= 10; $i++) {
          echo '<button type="button" class="mesa mesa--loading" data-turno="comida" data-mesa-id="'.$i.'">'.$i.'</button>';
      }
      ?>
    </div>
  </section>

  <!-- TURNO CENA -->
  <section class="card">
    <div class="turno-header">
      <h3 class="turno-title">Turno de cenas (21:00 - 23:00)</h3>

      <div class="resumen-mesas">
        <div class="kpi">
          <div class="kpi__label">Mesas disponibles</div>
          <div class="kpi__value" id="kpiMesasCena">-</div>
        </div>
        <div class="kpi">
          <div class="kpi__label">Capacidad restante</div>
          <div class="kpi__value" id="kpiCapacidadCena">-</div>
        </div>
        <div class="kpi">
          <div class="kpi__label">Ocupadas</div>
          <div class="kpi__value" id="kpiOcupadasCena">-</div>
        </div>
      </div>
    </div>

    <div id="gridMesasCena" class="grid-mesas" aria-label="Plano de mesas cena">
      <?php
      for ($i = 1; $i <= 10; $i++) {
          echo '<button type="button" class="mesa mesa--loading" data-turno="cena" data-mesa-id="'.$i.'">'.$i.'</button>';
      }
      ?>
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

<script>
  window.__FECHA_RESERVAS__ = "<?php echo $fecha; ?>";
  window.__ROL_TRABAJADOR__ = "<?php echo htmlspecialchars($rol, ENT_QUOTES); ?>";
</script>
<script src="/BarApp/assets/js/ver_reservas.js?v=2"></script>
</body>
</html>


