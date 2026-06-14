<?php
declare(strict_types=1);
session_start();
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

<script src="/BarApp/assets/js/components/ui.js"></script>
<script src="/BarApp/assets/js/resenas_public.js?v=2"></script>

<?php require_once "../components/footer.php"; ?>
