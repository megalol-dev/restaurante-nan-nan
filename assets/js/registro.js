/*
Lógica del formulario para registar un cliente
*/

"use strict";

document.addEventListener("DOMContentLoaded", () => {
    const app = document.getElementById("app");
    if (!app) return;

    app.innerHTML = `
    <section class="card" style="max-width: 560px; margin: 18px auto;">
      <h2 class="registro-title">Registro de cliente</h2>
      <p class="registro-subtitle">
      Rellena tus datos. Los campos marcados son obligatorios.
      </p>

      <form id="formRegistro" novalidate>
        <div class="field">
          <label for="nombre">Nombre *</label>
          <input id="nombre" name="nombre" type="text"
            placeholder="Ej: José Luis" autocomplete="given-name" required />
          <small class="error" id="errNombre"></small>
        </div>

        <div class="field">
          <label for="apellidos">Apellidos *</label>
          <input id="apellidos" name="apellidos" type="text"
            placeholder="Ej: Escudero Polo" autocomplete="family-name" required />
          <small class="error" id="errApellidos"></small>
        </div>

        <div class="field">
          <label for="email">Email *</label>
          <input id="email" name="email" type="email"
            placeholder="Ej: nombre@correo.com" autocomplete="email" required />
          <small class="error" id="errEmail"></small>
        </div>

        <div class="field">
          <label for="telefono">Teléfono *</label>
          <input id="telefono" name="telefono" type="tel"
            placeholder="Ej: +34 600123123 o 600123123" autocomplete="tel" required />
          <small class="error" id="errTelefono"></small>
        </div>

        <div class="field">
          <label for="password">Contraseña *</label>
          <input id="password" name="password" type="password"
            placeholder="Mínimo 6 caracteres" autocomplete="new-password" required />
          <small class="error" id="errPassword"></small>
        </div>

        <div class="field">
          <label for="password2">Repite contraseña *</label>
          <input id="password2" name="password2" type="password"
            placeholder="Repite la contraseña" autocomplete="new-password" required />
          <small class="error" id="errPassword2"></small>
        </div>

        <div style="display:flex; gap:10px; margin-top: 14px; flex-wrap: wrap;">
          <button class="btn" type="submit" id="btnSubmit">Aceptar registro</button>
          <button class="btn btn--outline" type="button" id="btnVolver">Volver a inicio</button>
        </div>

        <p id="msg" style="display:none; margin-top:12px; font-weight:700;"></p>
      </form>
    </section>
  `;

    // Expresiones regulares de validación
    const reNoVacio = /\S+/;
    const reEmail = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
    const reTelefono = /^\+?\d[\d\s]{7,14}\d$/; // + opcional, dígitos y espacios

    const $ = (id) => document.getElementById(id);

    const form = $("formRegistro");
    const nombre = $("nombre");
    const apellidos = $("apellidos");
    const email = $("email");
    const telefono = $("telefono");
    const password = $("password");
    const password2 = $("password2");

    const errNombre = $("errNombre");
    const errApellidos = $("errApellidos");
    const errEmail = $("errEmail");
    const errTelefono = $("errTelefono");
    const errPassword = $("errPassword");
    const errPassword2 = $("errPassword2");

    const msg = $("msg");
    const btnSubmit = $("btnSubmit");

    /* lógica del formulario: muestra error y marca el input como inválido */
    function setError(input, errEl, message) {
        errEl.textContent = message;
        input.classList.add("is-invalid");
    }

    /* lógica del formulario: limpia el error y quita el estado inválido */
    function clearError(input, errEl) {
        errEl.textContent = "";
        input.classList.remove("is-invalid");
    }

    /* lógica del formulario: normaliza el teléfono para validarlo (quita espacios) */
    function normalizarTelefono(valor) {
        return valor.replace(/\s+/g, "");
    }

    /* lógica del formulario: valida que el nombre no esté vacío */
    function validarNombre() {
        const v = nombre.value.trim();
        if (!reNoVacio.test(v)) {
            setError(nombre, errNombre, "El nombre no puede estar en blanco.");
            return false;
        }
        clearError(nombre, errNombre);
        return true;
    }

    /* lógica del formulario: valida que los apellidos no estén vacíos */
    function validarApellidos() {
        const v = apellidos.value.trim();
        if (!reNoVacio.test(v)) {
            setError(apellidos, errApellidos, "Los apellidos no pueden estar en blanco.");
            return false;
        }
        clearError(apellidos, errApellidos);
        return true;
    }

    /* lógica del formulario: valida formato de email básico */
    function validarEmail() {
        const v = email.value.trim();
        if (!reEmail.test(v)) {
            setError(email, errEmail, "Email inválido. Ej: nombre@correo.com");
            return false;
        }
        clearError(email, errEmail);
        return true;
    }

    /* lógica del formulario: valida teléfono (solo números, + opcional, mínimo 9 dígitos) */
    function validarTelefono() {
        const vRaw = telefono.value.trim();
        const v = normalizarTelefono(vRaw);
        const soloDigitos = v.replace(/^\+/, "");

        if (!/^\d+$/.test(soloDigitos)) {
            setError(telefono, errTelefono, "Solo números (y + al inicio).");
            return false;
        }
        if (soloDigitos.length < 9) {
            setError(telefono, errTelefono, "Debe tener al menos 9 números.");
            return false;
        }
        if (!reTelefono.test(vRaw)) {
            setError(telefono, errTelefono, "Formato no válido. Ej: +34 600123123");
            return false;
        }

        clearError(telefono, errTelefono);
        return true;
    }

    /* lógica del formulario: valida contraseña mínima */
    function validarPassword() {
        const v = password.value;
        if (v.length < 6) {
            setError(password, errPassword, "Mínimo 6 caracteres.");
            return false;
        }
        clearError(password, errPassword);
        return true;
    }

    /* lógica del formulario: valida que ambas contraseñas coincidan */
    function validarPassword2() {
        const v = password2.value;
        if (v !== password.value) {
            setError(password2, errPassword2, "Las contraseñas no coinciden.");
            return false;
        }
        clearError(password2, errPassword2);
        return true;
    }

    // Validación en tiempo real
    nombre.addEventListener("input", validarNombre);
    apellidos.addEventListener("input", validarApellidos);
    email.addEventListener("input", validarEmail);
    telefono.addEventListener("input", validarTelefono);
    password.addEventListener("input", validarPassword);
    password2.addEventListener("input", validarPassword2);

    // Botón volver
    $("btnVolver").addEventListener("click", () => {
        window.location.href = "index.html";
    });

    // Envío real a PHP
    form.addEventListener("submit", async (e) => {
        e.preventDefault();
        msg.style.display = "none";

        /* lógica del formulario: validar todo antes de enviar */
        const ok =
            validarNombre() &
            validarApellidos() &
            validarEmail() &
            validarTelefono() &
            validarPassword() &
            validarPassword2();

        if (!ok) return;

        btnSubmit.disabled = true;
        btnSubmit.textContent = "Registrando...";

        try {
            /* lógica del formulario: crear payload a enviar al backend */
            const payload = {
                nombre: nombre.value.trim(),
                apellidos: apellidos.value.trim(),
                email: email.value.trim(),
                telefono: telefono.value.trim(),
                password: password.value
            };

            /* lógica del formulario: enviar por fetch a registro.php */
            const res = await fetch("registro.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(payload)
            });

            const data = await res.json();

            if (!res.ok || !data.ok) {
                msg.textContent = "❌ " + (data.error || "Error al registrar.");
                msg.style.display = "block";
                msg.style.color = "#c1121f";
                return;
            }

            msg.textContent = "✅ Registro completado. Ahora puedes iniciar sesión.";
            msg.style.display = "block";
            msg.style.color = "#003049";
            form.reset();

        } catch (err) {
            msg.textContent = "❌ Error de red o servidor.";
            msg.style.display = "block";
            msg.style.color = "#c1121f";
        } finally {
            btnSubmit.disabled = false;
            btnSubmit.textContent = "Aceptar registro";
        }
    });
});


