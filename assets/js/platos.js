"use strict";

document.addEventListener("DOMContentLoaded", () => {
    const $ = (id) => document.getElementById(id);

    const msg = $("msgPlatos");
    const lista = $("listaPlatos");

    const form = $("formPlato");
    const platoId = $("platoId");
    const categoria = $("categoria");
    const nombre = $("nombre");
    const descripcion = $("descripcion");
    const precio = $("precio");

    const errCategoria = $("errCategoria");
    const errNombre = $("errNombre");
    const errPrecio = $("errPrecio");

    const btnGuardar = $("btnGuardar");
    const btnCancelarEdicion = $("btnCancelarEdicion");

    const CATS_ORDER = [
        "Ensaladas", "Carnes", "Pescados", "Pasta", "Bocadillos", "Sandwiches", "Postres", "Bebidas"
    ];


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

    async function api(payload) {
        const res = await fetch("/BarApp/api/platos_api.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(payload),
        });
        const data = await res.json();
        if (!res.ok || !data.ok) throw new Error(data.error || "Error");
        return data;
    }

    function setError(input, el, text) {
        el.textContent = text;
        input.classList.add("is-invalid");
    }
    function clearError(input, el) {
        el.textContent = "";
        input.classList.remove("is-invalid");
    }

    function validar() {
        let ok = true;

        if (!categoria.value) {
            setError(categoria, errCategoria, "Elige una categoría.");
            ok = false;
        } else clearError(categoria, errCategoria);

        if (!nombre.value.trim()) {
            setError(nombre, errNombre, "Nombre obligatorio.");
            ok = false;
        } else clearError(nombre, errNombre);

        // precio: 9.5 / 9,5 / 9.50
        const raw = precio.value.trim().replace(",", ".");
        const p = Number(raw);
        if (!Number.isFinite(p) || p <= 0 || p > 999) {
            setError(precio, errPrecio, "Precio inválido (ej: 9.50).");
            ok = false;
        } else clearError(precio, errPrecio);

        return ok;
    }

    function agrupar(items) {
        const map = {};
        for (const c of CATS_ORDER) map[c] = [];
        for (const it of items) {
            const c = it.categoria || "Otros";
            if (!map[c]) map[c] = [];
            map[c].push(it);
        }
        return map;
    }

    function esc(s) {
        return String(s ?? "").replace(/[&<>"']/g, (m) => ({
            "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#039;"
        }[m]));
    }

    function render(items) {
        const grupos = agrupar(items);

        const htmlCats = Object.keys(grupos)
            .filter((c) => grupos[c].length)
            .map((c) => {
                const cards = grupos[c].map((p) => `
          <div class="plato-row" data-id="${p.id}">
            <div class="plato-row__main">
              <div class="plato-row__top">
                <strong>${esc(p.nombre)}</strong>
                <span class="plato-row__price">${Number(p.precio).toFixed(2).replace(".", ",")} €</span>
              </div>
              ${p.descripcion ? `<div class="plato-row__desc">${esc(p.descripcion)}</div>` : ""}
            </div>
            <div class="plato-row__actions">
              <button class="btn btn--outline btn-sm" data-action="edit">Editar</button>
              <button class="btn btn--outline btn-sm" data-action="delete">Eliminar</button>
            </div>
          </div>
        `).join("");

                return `
          <section class="plato-cat">
            <h4 class="plato-cat__title">${esc(c)}</h4>
            <div class="plato-cat__list">${cards}</div>
          </section>
        `;
            }).join("");

        lista.innerHTML = htmlCats || "<p>No hay platos en la carta.</p>";
    }

    function resetForm() {
        platoId.value = "";
        form.reset();
        btnGuardar.textContent = "Guardar";
        btnCancelarEdicion.style.display = "none";
    }

    function setEditMode(p) {
        platoId.value = String(p.id);
        categoria.value = p.categoria || "Ensaladas";
        nombre.value = p.nombre || "";
        descripcion.value = p.descripcion || "";
        precio.value = String(p.precio ?? "");

        btnGuardar.textContent = "Guardar cambios";
        btnCancelarEdicion.style.display = "inline-flex";
        window.scrollTo({ top: 0, behavior: "smooth" });
    }

    let cacheItems = [];

    async function cargar() {
        const data = await api({ action: "list" });
        cacheItems = data.items || [];
        render(cacheItems);
    }

    form.addEventListener("submit", async (e) => {
        e.preventDefault();

        if (!validar()) {
            mostrarAviso(
                "❌ No se puede guardar el plato. Revisa los datos.",
                true
            );
            return;
        }

        btnGuardar.disabled = true;

        try {
            const payload = {
                categoria: categoria.value,
                nombre: nombre.value.trim(),
                descripcion: descripcion.value.trim(),
                precio: precio.value.trim(),
            };

            let data;
            if (platoId.value) {
                data = await api({ action: "update", id: Number(platoId.value), ...payload });
                mostrarAviso(
                    "✅ Plato actualizado correctamente"
                );
            } else {
                data = await api({ action: "create", ...payload });
                mostrarAviso(
                    "✅ Plato creado correctamente"
                );
            }

            cacheItems = data.items || [];
            render(cacheItems);
            resetForm();
        } catch (err) {
            mostrarAviso(
                "❌ " + (err.message || "No se pudo guardar."),
                true
            );
        } finally {
            btnGuardar.disabled = false;
        }
    });

    btnCancelarEdicion.addEventListener("click", () => {
        resetForm();
        mostrarAviso("✅ Edición cancelada");
    });

    lista.addEventListener("click", async (e) => {
        const btn = e.target.closest("button[data-action]");
        if (!btn) return;

        const row = btn.closest("[data-id]");
        if (!row) return;

        const id = Number(row.dataset.id);
        const action = btn.dataset.action;

        const item = cacheItems.find((x) => Number(x.id) === id);
        if (!item) return;

        try {
            if (action === "edit") {
                setEditMode(item);
                return;
            }

            if (action === "delete") {
                const ok = await confirmar(
                    "Eliminar plato",
                    `¿Seguro que quieres eliminar "${item.nombre}"?`
                );
                if (!ok) return;

                const data = await api({ action: "delete", id });
                cacheItems = data.items || [];
                render(cacheItems);

                // si estabas editando justo ese, limpia el form
                if (Number(platoId.value) === id) resetForm();

                mostrarAviso(
                    "✅ Plato eliminado correctamente"
                );
                return;
            }
        } catch (err) {
            mostrarAviso(
                "❌ " + (err.message || "Error"),
                true
            );
        }
    });

    // init
    cargar();
});
