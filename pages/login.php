<?php
declare(strict_types=1);
session_start();
?>

<?php require_once "../components/header.php"; ?>

<nav class="site-nav">
    <div class="container nav-inner">
        <a class="nav-link" href="index.php">Inicio</a>
        <a class="nav-link nav-link--primary" href="login.php">Iniciar sesión</a>
        <a class="nav-link" href="registro.php">Registrarse</a>
    </div>
</nav>

  <main class="container">
    <section class="card card-auth">
      <h2 class="res-title">Iniciar sesión</h2>
      <p class="res-sub">Accede con tu email y contraseña.</p>

      <form id="formLogin" novalidate autocomplete="off">
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

<script src="/BarApp/assets/js/components/ui.js"></script>
<script src="../assets/js/login.js?v=2"></script>

<?php require_once "../components/footer.php"; ?>
