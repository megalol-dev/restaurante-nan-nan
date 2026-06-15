<?php require_once "../components/header.php"; ?>

<nav class="site-nav">
    <div class="container nav-inner">
        <a class="nav-link" href="login.php">Reservas</a>
        <a class="nav-link" href="registro.php">Registrarse</a>
    </div>
</nav>

<main class="container">

    <section class="card hero">
        <h2 id="heroTitulo">¡Tu bar de confianza!</h2>
        <p id="heroSubtitulo">
            Tú vas confianza... lo que tú veas... Para reservar mesa, regístrate e inicia sesión.
        </p>
    </section>


    <!--Carusel de imagenes-->

    <h2 class="menu-dia__title">NUESTROS MEJORES PLATOS</h2>
    <section class="card carrusel-card">

        <div class="carrusel">
            <img id="imgA"
            src="../assets/img/carrusel/plato1.png"
            alt="Plato destacado">

            <img id="imgB"
            src="../assets/img/carrusel/plato2.png"
            alt="Plato destacado"
            style="opacity:0;">
        </div>

    </section>

    
    <!--Tarjetas de ventajas-->

    <section class="ventajas">

    <div class="ventaja-card">
        <div class="ventaja-icon">🍽️</div>

        <h3>Menú diario</h3>

        <p>
            Consulta cada día nuestro menú actualizado con
            primeros, segundos y postres.
        </p>
    </div>

    <div class="ventaja-card">
        <div class="ventaja-icon">📅</div>

        <h3>Reserva online</h3>

        <p>
            Reserva tu mesa en pocos segundos desde cualquier
            dispositivo y evita esperas.
        </p>
    </div>

    <div class="ventaja-card">
        <div class="ventaja-icon">⭐</div>

        <h3>Opiniones reales</h3>

        <p>
            Consulta las reseñas y experiencias de nuestros
            clientes antes de visitarnos.
        </p>
    </div>

    </section>


    <!--menu del dia-->

    <section class="menu-dia">
        <div class="menu-dia__head">
            <h2 class="menu-dia__title">MENÚ DEL DÍA</h2>

            <div class="menu-dia__date">
                <div class="menu-dia__date-badge" id="menuFecha">07/01/2026</div>
            </div>
        </div>

        <div class="menu-dia__wrap">
            <div class="menu-dia__box">
                <h3>Primeros</h3>
                <ul class="list" id="menuPrimeros"></ul>
            </div>

            <div class="menu-dia__box">
                <h3>Segundos</h3>
                <ul class="list" id="menuSegundos"></ul>
            </div>

            <div class="menu-dia__box">
                <h3>Postres</h3>
                <ul class="list" id="menuPostres"></ul>
            </div>

            <div class="menu-dia__box menu-dia__note">

                <p class="price-note">
                    <span class="precio1">
                        <strong>15€</strong> de lunes a viernes
                    </span>

                <span class="separador"> · </span>

                    <span class="precio2">
                        <strong>18€</strong> sábados, domingos o festivos.
                    </span>
                </p>

                <p id="menuIncluye" class="hint">
                    Incluye: Agua, vino, casera y pan.
                </p>

            </div>

            <div class="menu-dia__actions">
                <a class="btn" href="carta.php">Ver carta del restaurante</a>
            </div>
        </div>
    </section>

    <section class="reviews-home">
        <h2 class="reviews-home__title">Reseñas de nuestros clientes</h2>

        <div class="reviews-home__panel">
            <div id="homeResenas" class="reviews-list">Cargando reseñas...</div>

            <div class="reviews-home__actions">
                <a class="btn btn--outline" href="resenas.php">Ver más reseñas</a>
            </div>
        </div>
    </section>

</main>

<script>
    (async () => {
        const h2 = document.getElementById("heroTitulo");
        const p = document.getElementById("heroSubtitulo");

        const fallbackTitulo = "¡Tu bar de confianza!";
        const fallbackSubtitulo =
            "Tú vas confianza... lo que tú veas... Para reservar mesa, regístrate e inicia sesión.";

        try {
            const res = await fetch("../api/frase_web_api.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ action: "get" }),
            });

            const data = await res.json();

            if (!res.ok || !data.ok) {
                throw new Error(data.error || "Error");
            }

            if (data.frase && (data.frase.titulo || data.frase.subtitulo)) {
                h2.textContent = data.frase.titulo || fallbackTitulo;
                p.textContent = data.frase.subtitulo || fallbackSubtitulo;
            } else {
                h2.textContent = fallbackTitulo;
                p.textContent = fallbackSubtitulo;
            }
        } catch {
            h2.textContent = fallbackTitulo;
            p.textContent = fallbackSubtitulo;
        }
    })();
</script>

<script src="../assets/js/carrusel.js"></script>

<?php require_once "../components/footer.php"; ?>