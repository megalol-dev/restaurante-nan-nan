<?php
declare(strict_types=1);
session_start();
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Iniciar sesión - BAR LOLI</title>
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
      <a class="nav-link nav-link--primary" href="login.php">Iniciar sesión</a>
      <a class="nav-link" href="registro.html">Registrarse</a>
    </div>
  </nav>

  <main class="container">
    <section class="card card-auth">
      <h2 class="res-title">Iniciar sesión</h2>
      <p class="res-sub">Accede con tu email y contraseña.</p>

      <form id="formLogin" novalidate>
        <div class="field">
          <label for="email">Email *</label>
          <input
            id="email"
            name="email"
            type="email"
            placeholder="Ej: nombre@correo.com"
            autocomplete="email"
            required
          />
          <small class="error" id="errEmail"></small>
        </div>

        <div class="field">
          <label for="password">Contraseña *</label>
          <input
            id="password"
            name="password"
            type="password"
            placeholder="Tu contraseña"
            autocomplete="current-password"
            required
          />
          <small class="error" id="errPassword"></small>
        </div>

        <p id="msg" class="msg-auth" style="display:none;"></p>

        <div class="btn-row">
          <button class="btn" type="submit" id="btnSubmit">Entrar</button>
          <button class="btn btn--outline" type="button" id="btnVolver">Volver a inicio</button>
          <button class="btn btn--outline" type="button" id="btnRegistro">Registrarse</button>
        </div>
      </form>
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
  <script src="assets/js/login.js?v=2"></script>
</body>
</html>
