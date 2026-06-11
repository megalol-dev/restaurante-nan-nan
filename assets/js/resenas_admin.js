"use strict";

document.addEventListener("DOMContentLoaded", () => {
    const $ = (id) => document.getElementById(id);

    const cont = $("adminResenas");
    const filtro = $("filtroEstado");
    const msg = $("msgAdminRes");

    function showMsg(text, ok = true) {
        msg.textContent = text;
        msg.style.display = "block";
        msg.style.color = ok ? "var(--color-blue)" : "var(--color-red)";
    }
    function clearMsg() { msg.style.display = "none"; }

    function esc(str) {
        return String(str ?? "").replace(/[&<>"']/g, (m) => ({
            "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#039;",
        }[m]));
    }

    async function api(payload) {
        const res = await fetch("api/resenas_api.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(payload),
        });
        const data = await res.json();
        if (!res.ok || !data.ok) throw new Error(data.error || "Error");
        return data;
    }

    function badgeEstado(e) {
        if (e === "oculta") return "⛔ Oculta";
        if (e === "en_revision") return "🕵️ En revisión";
        return "✅ Visible";
    }

    function render(items) {
        if (!items.length) {
            cont.textContent = "No hay reseñas con ese filtro.";
            return;
        }

        cont.innerHTML = items.map((r) => {
            const fecha = (r.fecha || r.created_at || "").slice(0, 10);
            const estrellas = "★".repeat(Number(r.puntuacion || 0)).padEnd(5, "☆");
            const estado = r.estado || "visible";

            const btnText = (estado === "oculta") ? "Desbloquear" : "Bloquear";
            const nextEstado = (estado === "oculta") ? "visible" : "oculta";

            return `
        <article class="review">
          <div class="review__top" style="display:flex; gap:10px; align-items:center; justify-content:space-between;">
            <div>
              <strong>${esc(r.nombre_publico || "Anónimo")}</strong>
              <span class="review__stars" style="margin-left:10px;">${estrellas}</span>
              <span class="hint" style="margin-left:10px;">${badgeEstado(estado)}</span>
            </div>

            <div style="display:flex; gap:8px; align-items:center;">
              <button class="btn btn--outline btn-sm" data-action="toggle" data-id="${r.id}" data-estado="${nextEstado}">
                ${btnText}
              </button>
            </div>
          </div>

          <p class="review__text">${esc(r.texto || "")}</p>

          <small class="review__date">
            ${fecha}
            · Cliente: <strong>${esc(r.cliente_nombre || r.cliente_email || "—")}</strong>
            ${r.moderada_por ? `· Moderada por: <strong>${esc(r.moderada_por)}</strong>` : ""}
          </small>
        </article>
      `;
        }).join("");
    }

    async function cargar() {
        clearMsg();
        cont.textContent = "Cargando...";

        const estado = filtro.value.trim();
        const data = await api({ action: "admin_list", estado });

        render(data.items || []);
    }

    cont.addEventListener("click", async (e) => {
        const btn = e.target.closest("button[data-action='toggle']");
        if (!btn) return;

        const id = Number(btn.dataset.id || 0);
        const estado = String(btn.dataset.estado || "");
        if (!id || !estado) return;

        const ok = confirm(`¿Cambiar estado a "${estado}"?`);
        if (!ok) return;

        try {
            await api({ action: "admin_set_estado", id, estado });
            showMsg("✅ Estado actualizado.", true);
            await cargar();
        } catch (err) {
            showMsg("❌ " + (err.message || "Error"), false);
        }
    });

    filtro.addEventListener("change", cargar);

    // init
    cargar();
});
