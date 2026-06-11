<?php
declare(strict_types=1);
session_start();

require __DIR__ . '/database/db.php';

$pdo = db();

// Traemos platos activos (si usas "activo")
$st = $pdo->prepare("
  SELECT categoria, nombre, descripcion, precio
  FROM carta_platos
  WHERE activo = 1
  ORDER BY
    CASE categoria
      WHEN 'Ensaladas' THEN 1
      WHEN 'Carnes' THEN 2
      WHEN 'Pescados' THEN 3
      WHEN 'Pasta' THEN 4
      WHEN 'Bocadillos' THEN 5
      WHEN 'Sandwiches' THEN 6
      WHEN 'Postres' THEN 7
      WHEN 'Bebidas' THEN 8
      ELSE 99
    END,
    LOWER(nombre) ASC
");
$st->execute();
$rows = $st->fetchAll(PDO::FETCH_ASSOC);

// Agrupar por categoría
$carta = [];
foreach ($rows as $r) {
    $cat = (string)$r['categoria'];
    if (!isset($carta[$cat])) $carta[$cat] = [];
    $carta[$cat][] = $r;
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Carta - BAR LOLI</title>
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
    <a class="nav-link" href="index.html">Inicio</a>
    <a class="nav-link nav-link--primary" href="carta.php">Carta</a>
    <a class="nav-link" href="login.php">Reservas</a>
  </div>
</nav>

<main class="container">
  <section class="card">
    <h2 class="res-title" style="margin-top:0;">MENÚ A LA CARTA</h2>
    <p class="hint">
      Aquí verás la carta actual del restaurante. (Se carga directamente desde la base de datos.)
    </p>
  </section>

  <?php if (empty($carta)): ?>
    <section class="card">
      <p>No hay platos en la carta todavía.</p>
    </section>
  <?php else: ?>
    <?php foreach ($carta as $categoria => $platos): ?>
      <section class="card">
        <div class="carta-cat">
          <h3 class="carta-cat__title"><?php echo htmlspecialchars($categoria); ?></h3>
        </div>

        <div class="carta-list">
          <?php foreach ($platos as $p): ?>
            <article class="carta-item">
              <div class="carta-item__top">
                <strong class="carta-item__name"><?php echo htmlspecialchars((string)$p['nombre']); ?></strong>
                <span class="carta-item__price"><?php echo number_format((float)$p['precio'], 2, ',', '.'); ?> €</span>
              </div>

              <?php if (!empty($p['descripcion'])): ?>
                <p class="carta-item__desc"><?php echo htmlspecialchars((string)$p['descripcion']); ?></p>
              <?php endif; ?>
            </article>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endforeach; ?>
  <?php endif; ?>
</main>

<footer class="site-footer">
  <div class="container footer-inner">
    <div><strong>BAR LOLI</strong><br />Calle Ejemplo 123, Madrid</div>
    <div>Tel: 600 000 000<br />Horario: 09:00 - 23:00</div>
    <div>© <?php echo date('Y'); ?> BAR LOLI</div>
  </div>
</footer>
</body>
</html>
