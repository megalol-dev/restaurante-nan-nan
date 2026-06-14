<?php
declare(strict_types=1);
session_start();

if (empty($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== 'trabajador') {
    header("Location: login.php");
    exit;
}

$rol = $_SESSION['rol'] ?? 'trabajador';
if ($rol !== 'jefe') {
    // Solo el JEFE puede entrar aquí
    http_response_code(403);
    echo "Acceso denegado: solo el jefe puede gestionar platos.";
    exit;
}

$nombreTrabajador = $_SESSION['nombre'] ?? 'Jefe';
?>

<?php require_once "../components/header.php"; ?>

<nav class="site-nav">
  <div class="container nav-inner">
    <a class="nav-link" href="zona_trabajadores.php">⬅ Volver</a>
    <a class="nav-link nav-link--primary" href="platos.php">Crear plato</a>
    <a class="nav-link" href="logout.php">Cerrar sesión</a>
  </div>
</nav>

<main class="container">
  <section class="card">
    <h2 class="res-title" style="margin-top:0;">Gestión de carta (solo JEFE)</h2>
    <p class="res-sub">Sesión: <strong><?php echo htmlspecialchars($nombreTrabajador); ?></strong> (rol: <strong><?php echo htmlspecialchars($rol); ?></strong>)</p>
    <p class="hint">
    Rellena todos los campos y crea un nuevo plato. Este se añadirá directamente a tu <strong>carta del bar</strong> y después podrás actualizarlo desde la opción de editar.
    </p>

  </section>

  <!-- Form crear / editar -->
  <section class="card">
    <h3 class="res-title" style="margin-top:0;">Crear nuevo plato</h3>

    <form id="formPlato" novalidate>
      <input type="hidden" id="platoId" value="" />

      <div class="field">
        <label for="categoria">Categoría</label>
        <select id="categoria" required>
          <option value="Ensaladas">Ensaladas</option>
          <option value="Carnes">Carnes</option>
          <option value="Pescados">Pescados</option>
          <option value="Pasta">Pasta</option>
          <option value="Bocadillos">Bocadillos</option>
          <option value="Sandwiches">Sandwiches</option>
          <option value="Postres">Postres</option>
          <option value="Bebidas">Bebidas</option>
        </select>
        <small class="error" id="errCategoria"></small>
      </div>

      <div class="field">
        <label for="nombre">Nombre</label>
        <input id="nombre" type="text" placeholder="Ej: Ensalada César" maxlength="80" required />
        <small class="error" id="errNombre"></small>
      </div>

      <div class="field">
        <label for="descripcion">Descripción</label>
        <textarea id="descripcion" rows="4" placeholder="Ej: Lechuga, pollo, picatostes, parmesano..." maxlength="300"></textarea>
        <small class="error" id="errDescripcion"></small>
      </div>

      <div class="field">
        <label for="precio">Precio (€)</label>
        <input id="precio" type="text" placeholder="Ej: 9.50" required />
        <small class="error" id="errPrecio"></small>
      </div>

      <div class="btn-row">
        <button class="btn btn-success" type="submit" id="btnGuardar">
          Guardar
        </button>

        <button class="btn btn-danger" type="button" id="btnCancelarEdicion" style="display:none;">
          Cancelar edición
        </button>
      </div>

    </form>
  </section>

  <!-- Lista por categorías -->
  <section class="card">
    <h3 class="res-title" style="margin-top:0;">Platos actuales</h3>
    <div id="listaPlatos">Cargando...</div>
  </section>
</main>

<script src="/BarApp/assets/js/components/ui.js"></script>
<script src="/BarApp/assets/js/platos.js?v=1"></script>
<?php require_once "../components/footer.php"; ?>
