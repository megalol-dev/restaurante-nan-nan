"use strict";

document.addEventListener("DOMContentLoaded", () => {
    const $ = (id) => document.getElementById(id);

    const form = $("formLogin");
    const email = $("email");
    const password = $("password");

    const errEmail = $("errEmail");
    const errPassword = $("errPassword");

    const msg = $("msg");
    const btnSubmit = $("btnSubmit");

    const reEmail = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;

    /* lógica del formulario: muestra error */
    function setError(input, errEl, message) {
        errEl.textContent = message;
        input.classList.add("is-invalid");
    }

    /* lógica del formulario: limpia error */
    function clearError(input, errEl) {
        errEl.textContent = "";
        input.classList.remove("is-invalid");
    }

    /* lógica del formulario: valida email */
    function validarEmail() {
        const v = email.value.trim();
        if (!reEmail.test(v)) {
            setError(email, errEmail, "Email inválido. Ej: nombre@correo.com");
            return false;
        }
        clearError(email, errEmail);
        return true;
    }

    /* lógica del formulario: valida contraseña no vacía */
    function validarPassword() {
        const v = password.value;
        if (v.length < 1) {
            setError(password, errPassword, "La contraseña es obligatoria.");
            return false;
        }
        clearError(password, errPassword);
        return true;
    }

    email.addEventListener("input", validarEmail);
    password.addEventListener("input", validarPassword);

    $("btnVolver").addEventListener("click", () => (window.location.href = "index.html"));
    $("btnRegistro").addEventListener("click", () => (window.location.href = "registro.html"));

    form.addEventListener("submit", async (e) => {
        e.preventDefault();
        msg.style.display = "none";

        const ok = validarEmail() & validarPassword();
        if (!ok) return;

        btnSubmit.disabled = true;
        btnSubmit.textContent = "Entrando...";

        try {
            const payload = {
                email: email.value.trim(),
                password: password.value
            };

            const res = await fetch("api/login_api.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(payload)
            });

            const data = await res.json();

            if (!res.ok || !data.ok) {
                msg.textContent = "❌ " + (data.error || "Error al iniciar sesión.");
                msg.style.display = "block";
                msg.style.color = "#c1121f";
                return;
            }

            if (data.redirect) window.location.href = data.redirect;

        } catch (err) {
            msg.textContent = "❌ Error de red o servidor.";
            msg.style.display = "block";
            msg.style.color = "#c1121f";
        } finally {
            btnSubmit.disabled = false;
            btnSubmit.textContent = "Entrar";
        }
    });
});


