<?php
declare(strict_types=1);
session_start();

// Proteger página: solo clientes logueados
if (empty($_SESSION['cliente_id'])) {
    header("Location: login.php");
    exit;
}

// Recomendado para que reservas_api.php reconozca al cliente
$_SESSION['tipo_usuario'] = $_SESSION['tipo_usuario'] ?? 'cliente';

$clienteEmail = $_SESSION['cliente_email'] ?? 'cliente';

// Nombre “humano” del cliente para reservas
$_SESSION['cliente_nombre'] = $_SESSION['cliente_nombre'] ?? $clienteEmail;
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Zona Cliente - BAR LOLI</title>
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
      <a class="nav-link nav-link--primary" href="panel_cliente.php">Zona cliente</a>
      <a class="nav-link" href="logout.php">Cerrar sesión</a>
    </div>
  </nav>

  <main class="container">
<section class="card">
  <div class="cliente-top">
    <div>
      <h2 class="res-title">Zona cliente</h2>
      <p class="res-sub">
        Sesión iniciada como: <strong><?php echo htmlspecialchars($clienteEmail); ?></strong>
      </p>
    </div>

    <button class="btn" id="btnResenaTop" type="button">Poner reseña</button>
  </div>
</section>

<!-- Modal reseña -->
  <section class="modal" id="modalResena" aria-hidden="true">
    <div class="modal__backdrop" id="modalResenaClose"></div>
    <div class="modal__panel card">
      <h3 style="margin-top:0;">Tu reseña</h3>
      <p class="hint">Puedes dejar el nombre vacío para salir como <strong>Anónimo</strong>.</p>

      <form id="formResena" novalidate>
        <div class="field">
          <label for="resNombre">Nombre público (opcional)</label>
          <input id="resNombre" type="text" placeholder="Ej: Paco / Anónimo" maxlength="40" />
          <small class="error" id="errResNombre"></small>
        </div>

        <div class="field">
          <label for="resPuntuacion">Puntuación (1–5)</label>
          <select id="resPuntuacion" required>
            <option value="5">5 - Excelente</option>
            <option value="4">4 - Muy bien</option>
            <option value="3">3 - Bien</option>
            <option value="2">2 - Regular</option>
            <option value="1">1 - Mal</option>
          </select>
          <small class="error" id="errResPuntuacion"></small>
        </div>

        <div class="field">
          <label for="resTexto">Texto</label>
          <textarea id="resTexto" rows="5" placeholder="Cuenta tu experiencia..." maxlength="1000" required></textarea>
          <small class="error" id="errResTexto"></small>
        </div>

        <div class="btn-row">
          <button class="btn" type="submit" id="btnResGuardar">Guardar reseña</button>
          <button class="btn btn--outline" type="button" id="btnResCancelar">Cerrar</button>
        </div>

        <p id="msgResena" class="msg" style="display:none;"></p>
      </form>
    </div>
  </section>


    <!-- RESERVAS -->
    <section class="card">
      <h2 class="res-title">Reservar mesa</h2>

      <p class="hint">
        Turnos: <strong>Comidas 14:00–16:00</strong> · <strong>Cenas 21:00–23:00</strong>.
        Máximo 50 comensales.
      </p>

      <div id="infoStock" class="stock-turnos">Cargando disponibilidad...</div>

      <form id="formReserva" novalidate>
        <div class="field">
          <label for="comensales">Número de comensales (1–50)</label>
          <input id="comensales" type="number" min="1" max="50" value="2" required />
          <small class="error" id="errComensales"></small>
        </div>

        <div class="field">
          <label for="turno">Turno</label>
          <select id="turno" required>
            <option value="comida">Comida (14:00–16:00)</option>
            <option value="cena">Cena (21:00–23:00)</option>
          </select>
          <small class="error" id="errTurno"></small>
        </div>

        <div class="field">
          <label for="fecha">Fecha</label>
          <input id="fecha" type="date" required />
          <small class="error" id="errFecha"></small>
        </div>

        <div class="btn-row">
          <button class="btn" type="submit" id="btnReservar">Reservar</button>
          <button class="btn btn--outline" type="button" id="btnCancelar" disabled>Cancelar reserva</button>
          <button class="btn btn--outline" type="button" onclick="location.href='logout.php'">Cerrar sesión</button>
        </div>

        <p id="msgReserva" class="msg" style="display:none;"></p>
      </form>

      <!-- ✅ NUEVO: tabla de reservas activas -->
      <hr class="sep" />

      <h3 class="res-title">Mis reservas activas</h3>
      <p class="res-sub">Aquí aparecen todas tus reservas activas, aunque cambies la fecha del calendario.</p>

      <div class="table-wrap">
        <table class="table">
          <thead>
            <tr>
              <th>Fecha</th>
              <th>Turno</th>
              <th>Comensales</th>
              <th>Mesas</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody id="tbodyReservas">
            <tr><td colspan="5">Cargando...</td></tr>
          </tbody>
        </table>
      </div>
    </section>
  </main>

  <footer class="site-footer">
    <div class="container footer-inner">
      <div><strong>BAR LOLI</strong><br />Calle Ejemplo 123, Madrid</div>
      <div>Tel: 600 000 000<br />Horario: 09:00 - 23:00</div>
      <div>© <span id="year"></span> BAR LOLI</div>
    </div>
  </footer>

  <script>document.getElementById("year").textContent = new Date().getFullYear();</script>

  <!-- ✅ Solo este JS para reservas (evita conflictos) -->
  <script src="assets/js/zona_cliente.js?v=4"></script>
  <script src="assets/js/resena_cliente.js?v=1"></script>
</body>
</html>
