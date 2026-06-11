<?php
declare(strict_types=1);
session_start();
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Reseñas - BAR LOLI</title>
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
    <a class="nav-link nav-link--primary" href="resenas.php">Reseñas</a>
  </div>
</nav>

<main class="container">
  <section class="card">
    <h2 style="margin-top:0;">Todas las reseñas</h2>
    <p class="hint">Filtra por puntuación o mira todas.</p>

    <div class="field" style="max-width:260px;">
      <label for="filtroPunt">Filtrar por puntuación</label>
      <select id="filtroPunt">
        <option value="">Todas</option>
        <option value="5">5</option>
        <option value="4">4</option>
        <option value="3">3</option>
        <option value="2">2</option>
        <option value="1">1</option>
      </select>
    </div>

    <div id="listaResenas" class="reviews-list">Cargando...</div>
  </section>
</main>

<footer class="site-footer">
  <div class="container footer-inner">
    <div><strong>BAR LOLI</strong><br />Calle Ejemplo 123, Madrid</div>
    <div>Tel: 600 000 000<br />Horario: 09:00 - 23:00</div>
    <div>© <?php echo date('Y'); ?> BAR LOLI</div>
  </div>
</footer>
<script src="/BarApp/assets/js/resenas_public.js?v=2"></script>
</body>
</html>
