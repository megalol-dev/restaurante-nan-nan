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
        window.location.href = "index.html";
    });

    $("btnRegistro").addEventListener("click", () => {
        window.location.href = "registro.html";
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

    function mostrarAviso(texto, esError = false) {
        const aviso = document.createElement("div");

        aviso.textContent = texto;

        aviso.style.position = "fixed";
        aviso.style.top = "50%";
        aviso.style.left = "50%";
        aviso.style.transform = "translate(-50%, -50%)";
        aviso.style.padding = "18px 28px";
        aviso.style.borderRadius = "12px";
        aviso.style.fontWeight = "700";
        aviso.style.fontSize = "1.1rem";
        aviso.style.zIndex = "9999";
        aviso.style.boxShadow = "0 8px 20px rgba(0,0,0,.25)";
        aviso.style.backgroundColor = esError ? "#c1121f" : "#2e7d32";
        aviso.style.color = "#fff";
        aviso.style.minWidth = "320px";
        aviso.style.textAlign = "center";

        document.body.appendChild(aviso);

        setTimeout(() => {
            aviso.remove();
        }, 2000);
    }


});


