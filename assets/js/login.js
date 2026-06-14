"use strict";

document.addEventListener("DOMContentLoaded", () => {
    const $ = (id) => document.getElementById(id);

    const form = $("formLogin");
    const email = $("email");
    const password = $("password");

    const errEmail = $("errEmail");
    const errPassword = $("errPassword");

    const btnSubmit = $("btnSubmit");

    const reEmail = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;

    function setError(input, errEl, message) {
        errEl.textContent = message;
        input.classList.add("is-invalid");
    }

    function clearError(input, errEl) {
        errEl.textContent = "";
        input.classList.remove("is-invalid");
    }

    function validarEmail() {
        const v = email.value.trim();

        if (!reEmail.test(v)) {
            setError(email, errEmail, "Email inválido. Ej: nombre@correo.com");
            return false;
        }

        clearError(email, errEmail);
        return true;
    }

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

    $("btnVolver").addEventListener("click", () => {
        window.location.href = "index.php";
    });

    $("btnRegistro").addEventListener("click", () => {
        window.location.href = "registro.php";
    });

    form.addEventListener("submit", async (e) => {
        e.preventDefault();

        const ok = validarEmail() && validarPassword();

        if (!ok) return;

        btnSubmit.disabled = true;
        btnSubmit.textContent = "Entrando...";

        try {
            const payload = {
                email: email.value.trim(),
                password: password.value
            };

            const res = await fetch("/BarApp/api/login_api.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify(payload)
            });

            const data = await res.json();

            if (!res.ok || !data.ok) {
                mostrarAviso(
                    "❌ " + (data.error || "Error al iniciar sesión."),
                    true
                );
                return;
            }

            if (data.redirect) {

                mostrarAviso(
                    "✅ Inicio de sesión correcto"
                );

                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 2000);
            }

        } catch (err) {
            console.error(err);

            mostrarAviso(
                "❌ Error de red o servidor.",
                true
            );
        } finally {
            btnSubmit.disabled = false;
            btnSubmit.textContent = "Entrar";
        }
    });




});


