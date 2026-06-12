<?php
declare(strict_types=1);
session_start();

require __DIR__ . '/../database/db.php';

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
<?php require_once "../components/header.php"; ?>

<nav class="site-nav">
    <div class="container nav-inner">
        <a class="nav-link" href="index.php">Inicio</a>
        <a class="nav-link" href="login.php">Iniciar sesión (reservas)</a>
        <a class="nav-link" href="registro.php">Registrarse</a>
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

<?php require_once "../components/footer.php"; ?>

