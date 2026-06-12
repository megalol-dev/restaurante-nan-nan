<?php
declare(strict_types=1);
session_start();

if (empty($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== 'trabajador') {
  header("Location: login.php");
  exit;
}

$rol = $_SESSION['rol'] ?? 'trabajador';
if ($rol !== 'jefe') {
  http_response_code(403);
  echo "Acceso denegado: solo el jefe puede moderar reseñas.";
  exit;
}

$nombre = $_SESSION['nombre'] ?? 'Jefe';
?>

<?php require_once "../components/header.php"; ?>

<nav class="site-nav">
  <div class="container nav-inner">
    <a class="nav-link" href="zona_trabajadores.php">⬅ Volver</a>
    <a class="nav-link nav-link--primary" href="resenas_admin.php">⭐ Reseñas</a>
    <a class="nav-link" href="logout.php">Cerrar sesión</a>
  </div>
</nav>

<main class="container">
  <section class="card">
    <h2 class="res-title" style="margin-top:0;">Moderación de reseñas</h2>
    <p class="res-sub">
      Sesión: <strong><?php echo htmlspecialchars($nombre); ?></strong> (rol: <strong>jefe</strong>)
    </p>
    <p class="hint">Aquí puedes <strong>bloquear</strong> reseñas para que no salgan públicamente.</p>

    <div class="field" style="max-width:260px;">
      <label for="filtroEstado">Filtrar por estado</label>
      <select id="filtroEstado">
        <option value="">Todas</option>
        <option value="visible">Visibles</option>
        <option value="oculta">Ocultas</option>
        <option value="en_revision">En revisión</option>
      </select>
    </div>

    <p id="msgAdminRes" class="msg" style="display:none;"></p>
    <div id="adminResenas" class="reviews-list">Cargando...</div>
  </section>
</main>


<script src="/BarApp/assets/js/resenas_admin.js?v=1"></script>
<?php require_once "../components/footer.php"; ?>
