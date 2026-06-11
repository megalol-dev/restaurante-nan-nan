"use strict";

document.addEventListener("DOMContentLoaded", () => {
    const $ = (id) => document.getElementById(id);

    /* =========================
       ELEMENTOS - CREAR
       ========================= */
    const formCrear = $("formEmpleado");
    const nombre = $("nombre");
    const apellido = $("apellido");
    const email = $("email");
    const tlf = $("tlf");
    const rol = $("rol");
    const password = $("password");
    const password2 = $("password2");

    const errNombre = $("errNombre");
    const errApellido = $("errApellido");
    const errEmail = $("errEmail");
    const errTlf = $("errTlf");
    const errRol = $("errRol");
    const errPassword = $("errPassword");
    const errPassword2 = $("errPassword2");

    const msg = $("msg");
    const btnCrear = $("btnCrear");

    /* =========================
       ELEMENTOS - EDITAR
       ========================= */
    const cardEditar = $("cardEditar");
    const formEditar = $("formEditar");
    const editId = $("editId");
    const editEmail = $("editEmail");
    const editNombre = $("editNombre");
    const editApellido = $("editApellido");
    const editTlf = $("editTlf");
    const editRol = $("editRol");

    const errEditNombre = $("errEditNombre");
    const errEditApellido = $("errEditApellido");
    const errEditTlf = $("errEditTlf");
    const errEditRol = $("errEditRol");

    const msgEdit = $("msgEdit");
    const btnGuardar = $("btnGuardar");
    const btnCancelarEdicion = $("btnCancelarEdicion");

    const tabla = $("tablaEmpleados");

    /* =========================
       REGEX
       ========================= */
    const reNombre = /^[A-Za-zÁÉÍÓÚÜÑáéíóúüñ\s'-]{2,40}$/;
    const reEmail = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
    const reTlf = /^\+?\d[\d\s]{7,14}\d$/;

    /* =========================
       HELPERS
       ========================= */
    function setError(input, errEl, msg) {
        errEl.textContent = msg;
        input.classList.add("is-invalid");
    }

    function clearError(input, errEl) {
        errEl.textContent = "";
        input.classList.remove("is-invalid");
    }

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


    /* funcion de confirmar si eliminas un empleado*/
    function confirmar(titulo, texto) {
        return new Promise((resolve) => {

            const fondo = document.createElement("div");
            fondo.className = "modal-confirm-bg";

            fondo.innerHTML = `
            <div class="modal-confirm">
                <h3>${titulo}</h3>
                <p>${texto}</p>

                <div class="btn-row">
                    <button class="btn" id="btnConfirmarSi">
                        Aceptar
                    </button>

                    <button class="btn btn--outline" id="btnConfirmarNo">
                        Cancelar
                    </button>
                </div>
            </div>
        `;

            document.body.appendChild(fondo);

            document.getElementById("btnConfirmarSi")
                .addEventListener("click", () => {
                    fondo.remove();
                    resolve(true);
                });

            document.getElementById("btnConfirmarNo")
                .addEventListener("click", () => {
                    fondo.remove();
                    resolve(false);
                });
        });
    }

    /* =========================
       VALIDACIONES CREAR
       ========================= */
    function validarNombre() {
        const v = nombre.value.trim();
        if (!reNombre.test(v)) return setError(nombre, errNombre, "Nombre inválido (2-40 letras)."), false;
        clearError(nombre, errNombre); return true;
    }

    function validarApellido() {
        const v = apellido.value.trim();
        if (!reNombre.test(v)) return setError(apellido, errApellido, "Apellido inválido."), false;
        clearError(apellido, errApellido); return true;
    }

    function validarEmail() {
        const v = email.value.trim();
        if (!reEmail.test(v)) return setError(email, errEmail, "Email inválido."), false;
        clearError(email, errEmail); return true;
    }

    function validarTlf() {
        const raw = tlf.value.trim();
        const v = raw.replace(/\s+/g, "");
        const d = v.replace(/^\+/, "");
        if (!/^\d+$/.test(d) || d.length < 9 || !reTlf.test(raw))
            return setError(tlf, errTlf, "Teléfono inválido."), false;
        clearError(tlf, errTlf); return true;
    }

    function validarRol() {
        if (rol.value !== "trabajador" && rol.value !== "encargado")
            return setError(rol, errRol, "Rol inválido."), false;
        clearError(rol, errRol); return true;
    }

    function validarPassword() {
        if (password.value.length < 8)
            return setError(password, errPassword, "Mínimo 8 caracteres."), false;
        clearError(password, errPassword); return true;
    }

    function validarPassword2() {
        if (password2.value !== password.value)
            return setError(password2, errPassword2, "No coinciden."), false;
        clearError(password2, errPassword2); return true;
    }

    /* =========================
       VALIDACIONES EDITAR
       ========================= */
    function validarEditNombre() {
        const v = editNombre.value.trim();
        if (!reNombre.test(v)) return setError(editNombre, errEditNombre, "Nombre inválido."), false;
        clearError(editNombre, errEditNombre); return true;
    }

    function validarEditApellido() {
        const v = editApellido.value.trim();
        if (!reNombre.test(v)) return setError(editApellido, errEditApellido, "Apellido inválido."), false;
        clearError(editApellido, errEditApellido); return true;
    }

    function validarEditTlf() {
        const raw = editTlf.value.trim();
        const v = raw.replace(/\s+/g, "");
        const d = v.replace(/^\+/, "");
        if (!/^\d+$/.test(d) || d.length < 9 || !reTlf.test(raw))
            return setError(editTlf, errEditTlf, "Teléfono inválido."), false;
        clearError(editTlf, errEditTlf); return true;
    }

    function validarEditRol() {
        if (editRol.value !== "trabajador" && editRol.value !== "encargado")
            return setError(editRol, errEditRol, "Rol inválido."), false;
        clearError(editRol, errEditRol); return true;
    }

    /* =========================
       EVENTOS VALIDACIÓN
       ========================= */
    nombre.addEventListener("input", validarNombre);
    apellido.addEventListener("input", validarApellido);
    email.addEventListener("input", validarEmail);
    tlf.addEventListener("input", validarTlf);
    rol.addEventListener("change", validarRol);
    password.addEventListener("input", validarPassword);
    password2.addEventListener("input", validarPassword2);

    editNombre.addEventListener("input", validarEditNombre);
    editApellido.addEventListener("input", validarEditApellido);
    editTlf.addEventListener("input", validarEditTlf);
    editRol.addEventListener("change", validarEditRol);

    /* =========================
       CREAR EMPLEADO
       ========================= */
    formCrear.addEventListener("submit", async (e) => {
        e.preventDefault();
        msg.style.display = "none";

        const ok =
            validarNombre() &&
            validarApellido() &&
            validarEmail() &&
            validarTlf() &&
            validarRol() &&
            validarPassword() &&
            validarPassword2();

        if (!ok) {
            mostrarAviso(
                "❌ No se puede crear empleado. Revisa los datos.",
                true
            );
            return;
        }

        btnCrear.disabled = true;
        btnCrear.textContent = "Creando...";

        try {
            const res = await fetch("/BarApp/api/empleados_api.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    action: "create",
                    nombre: nombre.value.trim(),
                    apellido: apellido.value.trim(),
                    email: email.value.trim(),
                    tlf: tlf.value.trim(),
                    rol: rol.value,
                    password: password.value
                })
            });

            const data = await res.json();
            if (!res.ok || !data.ok) throw new Error(data.error);

            const eNew = data.empleado;

            const tr = document.createElement("tr");
            tr.dataset.id = eNew.id;
            tr.dataset.nombre = eNew.nombre;
            tr.dataset.apellido = eNew.apellido;
            tr.dataset.email = eNew.email;
            tr.dataset.tlf = eNew.tlf;
            tr.dataset.rol = eNew.rol;

            tr.innerHTML = `
        <td class="td-nombre">${eNew.nombre} ${eNew.apellido}</td>
        <td class="td-email">${eNew.email}</td>
        <td class="td-tlf">${eNew.tlf}</td>
        <td class="td-rol"><span class="badge">${eNew.rol}</span></td>
        <td>
          <button class="btn-mini btn-mini--edit" data-action="edit">Editar</button>
          <button class="btn-mini btn-mini--danger" data-action="delete">Eliminar</button>
        </td>
      `;

            tabla.appendChild(tr);

            mostrarAviso(
                "✅ Empleado creado correctamente"
            );
            formCrear.reset();

        } catch {
            mostrarAviso(
                "❌ No se puede crear empleado. Revisa los datos.",
                true
            );
        } finally {
            btnCrear.disabled = false;
            btnCrear.textContent = "Crear empleado";
        }
    });

    /* =========================
       EDITAR / ELIMINAR
       ========================= */
    tabla.addEventListener("click", async (e) => {
        const btn = e.target.closest("button[data-action]");
        if (!btn) return;

        const tr = btn.closest("tr");
        const id = Number(tr.dataset.id);

        if (btn.dataset.action === "edit") {
            cardEditar.style.display = "block";
            editId.value = id;
            editEmail.value = tr.dataset.email;
            editNombre.value = tr.dataset.nombre;
            editApellido.value = tr.dataset.apellido;
            editTlf.value = tr.dataset.tlf;
            editRol.value = tr.dataset.rol;
            cardEditar.scrollIntoView({ behavior: "smooth" });
        }

        if (btn.dataset.action === "delete") {
            const ok = await confirmar(
                "Eliminar empleado",
                "¿Seguro que quieres eliminar este empleado?"
            );

            if (!ok) return;

            const res = await fetch("/BarApp/api/empleados_api.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ action: "delete", id })
            });

            const data = await res.json();

            if (data.ok) {
                tr.remove();

                mostrarAviso(
                    "✅ Empleado eliminado correctamente"
                );
            } else {
                mostrarAviso(
                    "❌ No se pudo eliminar el empleado",
                    true
                );
            }
        }
    });

    /* =========================
       GUARDAR EDICIÓN
       ========================= */
    formEditar.addEventListener("submit", async (e) => {
        e.preventDefault();
        msgEdit.style.display = "none";

        const ok =
            validarEditNombre() &&
            validarEditApellido() &&
            validarEditTlf() &&
            validarEditRol();

        if (!ok) {
            mostrarAviso(
                "❌ No se puede actualizar el empleado. Revisa los datos.",
                true
            );
            return;
        }

        const res = await fetch("/BarApp/api/empleados_api.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                action: "update",
                id: Number(editId.value),
                nombre: editNombre.value.trim(),
                apellido: editApellido.value.trim(),
                tlf: editTlf.value.trim(),
                rol: editRol.value
            })
        });

        const data = await res.json();
        if (!data.ok) {
            mostrarAviso(
                "❌ No se pudo actualizar el empleado",
                true
            );
            return;
        }

        const tr = tabla.querySelector(`tr[data-id="${editId.value}"]`);
        tr.dataset.nombre = data.empleado.nombre;
        tr.dataset.apellido = data.empleado.apellido;
        tr.dataset.tlf = data.empleado.tlf;
        tr.dataset.rol = data.empleado.rol;

        tr.querySelector(".td-nombre").textContent =
            `${data.empleado.nombre} ${data.empleado.apellido}`;
        tr.querySelector(".td-tlf").textContent = data.empleado.tlf;
        tr.querySelector(".td-rol").innerHTML =
            `<span class="badge">${data.empleado.rol}</span>`;

        mostrarAviso(
            "✅ Empleado actualizado correctamente"
        );

        // Cerrar automáticamente la sección de edición
        setTimeout(() => {
            cardEditar.style.display = "none";
            formEditar.reset();
        }, 800);
    });

    btnCancelarEdicion.addEventListener("click", () => {
        cardEditar.style.display = "none";
        formEditar.reset();
    });
});


