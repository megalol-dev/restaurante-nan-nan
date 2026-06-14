"use strict";

document.addEventListener("DOMContentLoaded", () => {
    const $ = (id) => document.getElementById(id);

    const fechaMenu = $("fechaMenu");
    const msg = $("msgMenu");

    const gridPrimero = $("gridPrimero");
    const gridSegundo = $("gridSegundo");
    const gridPostre = $("gridPostre");

    const btnGuardar = $("btnGuardarMenu");
    const btnLimpiar = $("btnLimpiarMenu");

    // ✅ Aplicar colores a los botones del final
    if (btnGuardar) btnGuardar.classList.add("btn-success");
    if (btnLimpiar) btnLimpiar.classList.add("btn-danger");

    const CATS = [
        "Ensaladas",
        "Carnes",
        "Pescados",
        "Pasta",
        "Bocadillos",
        "Sandwiches",
        "Postres",
        "Bebidas",
    ];

    let platos = []; // [{id,categoria,nombre,descripcion,precio}]
    let sel = { primero: {}, segundo: {}, postre: {} };

  
    function clearMsg() {
        if (!msg) return;
        msg.style.display = "none";
    }


    async function api(payload) {
        const res = await fetch("/BarApp/api/menu_diario_api.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(payload),
        });
        const data = await res.json();
        if (!res.ok || !data.ok) throw new Error(data.error || "Error");
        return data;
    }

    function esc(s) {
        return String(s ?? "").replace(/[&<>"']/g, (m) => ({
            "&": "&amp;",
            "<": "&lt;",
            ">": "&gt;",
            '"': "&quot;",
            "'": "&#039;",
        }[m]));
    }

    function platoById(id) {
        return platos.find((p) => Number(p.id) === Number(id)) || null;
    }

    function fillPlatosSelect(selectEl, categoria, selectedId = 0) {
        if (!selectEl) return;

        if (!categoria) {
            selectEl.innerHTML = `<option value="">-- elige categoría primero --</option>`;
            return;
        }

        const items = platos.filter((p) => p.categoria === categoria);

        // ✅ Sin precios
        selectEl.innerHTML =
            `<option value="">-- elegir plato --</option>` +
            items.map((p) => `<option value="${p.id}">${esc(p.nombre)}</option>`).join("");

        if (selectedId) selectEl.value = String(selectedId);
    }

    function setPreview(slot, p, modo = "vacio") {
        const preview = slot.querySelector(".menu-slot__preview");
        if (!preview) return;

        if (p) {
            // ✅ Sin precio en preview
            preview.innerHTML = `
        <div><strong>${esc(p.nombre)}</strong></div>
        ${p.descripcion ? `<div class="hint">${esc(p.descripcion)}</div>` : ""}
      `;
            return;
        }

        if (modo === "elige_plato") {
            preview.innerHTML = `<div class="hint">Elige un plato (si lo dejas vacío no se mostrará en el index)</div>`;
            return;
        }

        preview.innerHTML = `<div class="hint">Vacío (no se mostrará en el index)</div>`;
    }

    function setSlotEmpty(slot) {
        const selCat = slot.querySelector(".slot-cat");
        const selPlato = slot.querySelector(".slot-plato");

        if (selCat) selCat.value = "";
        if (selPlato) fillPlatosSelect(selPlato, "", 0);

        setPreview(slot, null, "vacio");
    }

    function renderGrid(tipo, cont) {
        if (!cont) return;

        const html = Array.from({ length: 10 }).map((_, i) => {
            const orden = i + 1;

            return `
        <div class="menu-slot menu-slot--card" data-tipo="${tipo}" data-orden="${orden}">
          <div class="menu-slot__head">
            <strong>${tipo.toUpperCase()} ${orden}</strong>
            <button type="button" class="btn btn-danger btn-sm" data-action="clear">Quitar</button>
          </div>

          <div class="menu-slot__row">
            <label>Categoría</label>
            <select class="slot-cat">
              <option value="">-- elegir --</option>
              ${CATS.map((c) => `<option value="${esc(c)}">${esc(c)}</option>`).join("")}
            </select>
          </div>

          <div class="menu-slot__row">
            <label>Plato</label>
            <select class="slot-plato">
              <option value="">-- elige categoría primero --</option>
            </select>
          </div>

          <div class="menu-slot__preview"></div>
        </div>
      `;
        }).join("");

        cont.innerHTML = html;

        // Aplicar estado guardado (si existe)
        cont.querySelectorAll(".menu-slot").forEach((slot) => {
            const orden = Number(slot.dataset.orden);
            const pid = sel[tipo][orden] || 0;
            const p = pid ? platoById(pid) : null;

            const selCat = slot.querySelector(".slot-cat");
            const selPlato = slot.querySelector(".slot-plato");

            if (p) {
                selCat.value = p.categoria;
                fillPlatosSelect(selPlato, p.categoria, pid);
                setPreview(slot, p);
            } else {
                selCat.value = "";
                fillPlatosSelect(selPlato, "", 0);
                setPreview(slot, null, "vacio");
            }
        });
    }

    function renderAll() {
        renderGrid("primero", gridPrimero);
        renderGrid("segundo", gridSegundo);
        renderGrid("postre", gridPostre);
    }

    async function cargarPlatos() {
        const data = await api({ action: "platos_list" });
        platos = data.items || [];
    }

    async function cargarMenuFecha() {
        clearMsg();
        const f = fechaMenu.value;
        const data = await api({ action: "get_menu", fecha: f });

        sel = { primero: {}, segundo: {}, postre: {} };
        for (const tipo of ["primero", "segundo", "postre"]) {
            for (const it of (data.menu?.[tipo] || [])) {
                sel[tipo][Number(it.orden)] = Number(it.plato_id);
            }
        }
        renderAll();
    }

    function onGridChange(e) {
        const slot = e.target.closest(".menu-slot");
        if (!slot) return;

        const tipo = slot.dataset.tipo;
        const orden = Number(slot.dataset.orden);

        const selCat = slot.querySelector(".slot-cat");
        const selPlato = slot.querySelector(".slot-plato");

        // ✅ cambio categoría (sin repintar todo)
        if (e.target.classList.contains("slot-cat")) {
            const cat = e.target.value;

            delete sel[tipo][orden];
            fillPlatosSelect(selPlato, cat, 0);
            setPreview(slot, null, cat ? "elige_plato" : "vacio");
            return;
        }

        // ✅ cambio plato (sin repintar todo)
        if (e.target.classList.contains("slot-plato")) {
            const pid = Number(e.target.value || 0);

            if (!pid) {
                delete sel[tipo][orden];
                setPreview(slot, null, selCat.value ? "elige_plato" : "vacio");
                return;
            }

            sel[tipo][orden] = pid;

            const p = platoById(pid);
            if (p) {
                selCat.value = p.categoria;
                fillPlatosSelect(selPlato, p.categoria, pid);
                setPreview(slot, p);
            }
            return;
        }

        // ✅ botón quitar
        const btn = e.target.closest("button[data-action]");
        if (btn && btn.dataset.action === "clear") {
            delete sel[tipo][orden];
            setSlotEmpty(slot);
        }
    }

    async function guardarMenu() {
        clearMsg();
        btnGuardar.disabled = true;
        btnGuardar.textContent = "Guardando...";

        try {
            const f = fechaMenu.value;

            const items = [];
            for (const tipo of ["primero", "segundo", "postre"]) {
                for (let orden = 1; orden <= 10; orden++) {
                    const pid = sel[tipo][orden];
                    if (pid) items.push({ tipo, orden, plato_id: pid });
                }
            }

            await api({ action: "save_menu", fecha: f, items });
            mostrarAviso("✅ Menú guardado correctamente");
        } catch (err) {
            mostrarAviso("❌ " + (err.message || "No se pudo guardar."), true);
        } finally {
            btnGuardar.disabled = false;
            btnGuardar.textContent = "Guardar menú";
        }
    }

    async function limpiarMenu() {
        const ok = await confirmar(
            "Limpiar menú",
            "¿Seguro que quieres borrar el menú de esta fecha?"
        );
        if (!ok) return;

        try {
            await api({ action: "clear_menu", fecha: fechaMenu.value });
            sel = { primero: {}, segundo: {}, postre: {} };
            renderAll();
            mostrarAviso("✅ Menú eliminado correctamente");
        } catch (err) {
            mostrarAviso("❌ " + (err.message || "Error."), true);
        }
    }

    // Events
    gridPrimero?.addEventListener("change", onGridChange);
    gridSegundo?.addEventListener("change", onGridChange);
    gridPostre?.addEventListener("change", onGridChange);

    // ✅ CLAVE: escuchar clicks para que funcione el botón "Quitar"
    gridPrimero?.addEventListener("click", onGridChange);
    gridSegundo?.addEventListener("click", onGridChange);
    gridPostre?.addEventListener("click", onGridChange);

    btnGuardar?.addEventListener("click", guardarMenu);
    btnLimpiar?.addEventListener("click", limpiarMenu);

    fechaMenu?.addEventListener("change", cargarMenuFecha);

    // init
    (async () => {
        await cargarPlatos();
        await cargarMenuFecha();
    })();
});




