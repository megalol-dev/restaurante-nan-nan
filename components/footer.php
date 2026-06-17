    <!-- FOOTER -->
    <footer class="site-footer">
        <div class="container footer-inner">
            <div>
                <strong>RESTAURANTE ÑAN ÑAN</strong><br />
                Calle Ejemplo 123, Madrid
            </div>

            <div>
                Tel: 600 000 000<br />
                Horario: 09:00 - 23:00
            </div>

            <div>
                © <span id="year">2026</span> RESTAURANTE ÑAN ÑAN
            </div>
        </div>
    </footer>

    <script>
        const now = new Date();
        const d = String(now.getDate()).padStart(2, "0");
        const m = String(now.getMonth() + 1).padStart(2, "0");
        const y = now.getFullYear();

        const fechaEl = document.getElementById("menuFecha");
        if (fechaEl) fechaEl.textContent = `${d}/${m}/${y}`;

        const yearEl = document.getElementById("year");
        if (yearEl) yearEl.textContent = y;
    </script>

    <script src="../assets/js/index_resenas.js?v=4"></script>
    <script src="../assets/js/index_menu.js?v=1"></script>

</body>
</html>