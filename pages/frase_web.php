<?php
declare(strict_types=1);
session_start();

if (empty($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== 'trabajador' || (($_SESSION['rol'] ?? '') !== 'jefe')) {
  header("Location: zona_trabajadores.php");
  exit;
}

$nombre = $_SESSION['nombre'] ?? 'Jefe';
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Frase web - BAR LOLI</title>
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
    <a class="nav-link nav-link--primary" href="frase_web.php">Frase web</a>
    <a class="nav-link" href="logout.php">Cerrar sesión</a>
  </div>
</nav>

<main class="container">
  <section class="card" style="max-width:760px; margin: 18px auto;">
    <h2 class="res-title" style="margin-top:0; text-align:center;">Frase web</h2>
    <p class="res-sub" style="text-align:center;">
      Sesión: <strong><?php echo htmlspecialchars($nombre); ?></strong>
      (rol: <strong>jefe</strong>)
    </p>

    <p class="hint" style="margin-top:10px;">
      Esta frase aparece en la página principal y se mantiene guardada hasta que la cambies.
    </p>

    <form id="formFraseWeb" novalidate>
      <div class="field">
        <label for="titulo">Título (H2)</label>
        <input id="titulo" type="text" maxlength="80" placeholder="Ej: BAR LOLI les desea Feliz Navidad" required />
        <small class="hint">Máx 80 caracteres</small>
      </div>

      <div class="field">
        <label for="subtitulo">Subtítulo (H3)</label>
        <textarea id="subtitulo" rows="3" maxlength="160" placeholder="Ej: Durante diciembre repartimos chocolatinas a los peques..." required></textarea>
        <small class="hint">Máx 160 caracteres</small>
      </div>

      <p id="msgFraseWeb" class="msg" style="display:none;"></p>

      <div class="btn-row" style="gap:10px;">
        <button class="btn btn-success" type="submit" id="btnGuardarFrase">Guardar frase</button>
      </div>
    </form>
  </section>
</main>

<script src="/BarApp/assets/js/frase_web.js?v=1"></script>
</body>
</html>
