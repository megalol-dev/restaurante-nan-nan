"use strict";

document.addEventListener("DOMContentLoaded", () => {
    const $ = (id) => document.getElementById(id);

    const sel = $("filtroPunt");
    const cont = $("listaResenas");

    if (!sel || !cont) return;

    function escapeHtml(str) {
        return String(str).replace(/[&<>"']/g, (m) => ({
            "&": "&amp;",
            "<": "&lt;",
            ">": "&gt;",
            '"': "&quot;",
            "'": "&#039;",
        }[m]));
    }

    function estrellas(p) {
        const n = Number(p || 0);
        return "★".repeat(n).padEnd(5, "☆");
    }

    async function apiList(puntuacion) {
        const payload = { action: "list_all" };
        if (puntuacion) payload.puntuacion = Number(puntuacion);

        const res = await fetch("/BarApp/api/resenas_api.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(payload),
        });

        const data = await res.json();
        if (!res.ok || !data.ok) throw new Error(data.error || "Error");

        return data.items || [];
    }

    function render(items) {
        if (!items.length) {
            cont.textContent = "Aún no hay reseñas para mostrar.";
            return;
        }

        cont.innerHTML = items.map((r) => {
            const nombre = escapeHtml(r.nombre_publico || "Anónimo");
            const txt = escapeHtml(r.texto || "");
            const fecha = escapeHtml((r.fecha || r.created_at || "").slice(0, 10));
            const stars = estrellas(r.puntuacion);

            return `
        <article class="review">
          <div class="review__top">
            <strong>${nombre}</strong>
            <span class="review__stars">${stars}</span>
          </div>
          <p class="review__text">${txt}</p>
          <small class="review__date">${fecha}</small>
        </article>
      `;
        }).join("");
    }

    async function cargar() {
        cont.textContent = "Cargando...";
        const p = sel.value.trim();

        try {
            const items = await apiList(p);
            render(items);
        } catch (e) {
            cont.textContent = "No se pudieron cargar las reseñas.";
        }
    }

    sel.addEventListener("change", cargar);

    // init
    cargar();
});

