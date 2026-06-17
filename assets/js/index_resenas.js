"use strict";

document.addEventListener("DOMContentLoaded", () => {
  const cont = document.getElementById("homeResenas");
  if (!cont) return;

  function escapeHtml(str) {
    return String(str).replace(
      /[&<>"']/g,
      (m) =>
        ({
          "&": "&amp;",
          "<": "&lt;",
          ">": "&gt;",
          '"': "&quot;",
          "'": "&#039;",
        })[m],
    );
  }

  async function cargarResenas() {
    try {
      const res = await fetch("../api/resenas_api.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          action: "latest",
          limit: 5,
        }),
      });

      const data = await res.json();

      if (!res.ok || !data.ok) {
        throw new Error(data.error || "Error");
      }

      if (!data.items.length) {
        cont.textContent = "Aún no hay reseñas.";
        return;
      }

      cont.innerHTML = data.items
        .map((r) => {
          const fecha = (r.created_at || "").slice(0, 10);
          const estrellas = "★"
            .repeat(Number(r.puntuacion || 0))
            .padEnd(5, "☆");

          return `
                        <article class="review">
                            <div class="review__top">
                                <strong>${escapeHtml(r.nombre_publico || "Anónimo")}</strong>
                                <span class="review__stars">${estrellas}</span>
                            </div>
                            <p class="review__text">${escapeHtml(r.texto || "")}</p>
                            <small class="review__date">${fecha}</small>
                        </article>
                    `;
        })
        .join("");
    } catch {
      cont.textContent = "No se pudieron cargar las reseñas.";
    }
  }

  // Primera carga
  cargarResenas();

  // Refresco automático cada 30 segundos
  setInterval(cargarResenas, 30000);
});
