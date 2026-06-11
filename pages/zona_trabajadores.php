<?php
declare(strict_types=1);
session_start();

/* Seguridad: solo trabajadores */
if (empty($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== 'trabajador') {
    header("Location: login.php");
    exit;
}

$nombre = $_SESSION['nombre'] ?? 'Trabajador';
$rol = $_SESSION['rol'] ?? 'trabajador';

/*
"Poderes de CRUD de los trabajadors -> crear empleados, crear platos y añadirlos a la carta general, crear menus y gestionar reversar online o liberar mesas, según el rol, puedes hacer unas cosas u otras."
-------------------------------------------------------------------------------------
Roles:
- jefe        -> ve TODO y puede hacer todo
- encargado   -> ve TODO menos crear empleados
- trabajador  -> ve SOLO gestionar reservas online y liberar mesas.
*/
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Zona Trabajadores - BAR LOLI</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
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
    <a class="nav-link" href="index.html">Inicio</a>
    <a class="nav-link nav-link--primary" href="zona_trabajadores.php">Zona trabajadores</a>
    <a class="nav-link" href="logout.php">Cerrar sesión</a>
  </div>
</nav>

<main class="container">
  <section class="card">
    <h2 style="margin-top:0;">Zona trabajadores</h2>
    <p style="color:#6b7280;">
      Sesión iniciada como <strong><?php echo htmlspecialchars($nombre); ?></strong>
      (rol: <strong><?php echo htmlspecialchars($rol); ?></strong>)
    </p>
  </section>

  <section class="card">
    <h3 style="margin-top:0;">Panel de gestión</h3>

    <div class="panel-trabajadores">

      <?php if ($rol === 'jefe'): ?>
        <a href="empleados.php" class="panel-btn">👥 Crear / Editar / Ver empleados</a>
      <?php endif; ?>
      
      <a class="panel-btn" href="ver_reservas.php">📅 Ver reservas</a>
      
      <?php if ($rol === 'jefe'): ?>
        <a href="platos.php" class="panel-btn">🍽️ Crear / Editar platos (carta)</a>
      <?php endif; ?>

      <?php if ($rol === 'jefe' || $rol === 'encargado'): ?>
        <a href="menu_diario.php" class="panel-btn">📝 Crear menú diario</a>
      <?php endif; ?>

      <a class="panel-btn" href="clientes.php">👤 Ver clientes registrados</a>

      <?php if (($rol ?? '') === 'jefe'): ?>
        <a class="panel-btn" href="resenas_admin.php">⭐ Reseñas</a>
      <?php endif; ?>

      <?php if (($_SESSION['rol'] ?? '') === 'jefe'): ?>
        <button 
          class="panel-btn" onclick="location.href='frase_web.php'">📝 Frase web
        </button>
      <?php endif; ?>

      <button class="panel-btn">📖 Futura función</button>



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

</body>
</html>

