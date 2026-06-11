"use strict";

document.addEventListener("DOMContentLoaded", () => {
    const $ = (id) => document.getElementById(id);

    const btnTop = $("btnResenaTop");
    const modal = $("modalResena");
    const closeBg = $("modalResenaClose");
    const btnCerrar = $("btnResCancelar");

    const form = $("formResena");
    const nombre = $("resNombre");
    const punt = $("resPuntuacion");
    const texto = $("resTexto");

    const msg = $("msgResena");
    const btnGuardar = $("btnResGuardar");

    // Si falta algo en el HTML, salimos sin romper nada.
    if (!btnTop || !modal || !form || !nombre || !punt || !texto || !msg || !btnGuardar) return;

    function openModal() {
        modal.classList.add("is-open");
        modal.setAttribute("aria-hidden", "false");
        msg.style.display = "none";
        // foco útil
        setTimeout(() => {
            (nombre.value ? texto : nombre).focus();
        }, 0);
    }

    function closeModal() {
        modal.classList.remove("is-open");
        modal.setAttribute("aria-hidden", "true");
        msg.style.display = "none";
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

    async function api(payload) {
        const res = await fetch("/BarApp/api/resenas_api.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(payload),
        });

        // Por si el PHP devuelve HTML por error, evitamos que reviente el JSON.parse
        const txt = await res.text();
        let data = null;
        try {
            data = JSON.parse(txt);
        } catch {
            throw new Error("Respuesta inválida del servidor (no es JSON).");
        }

        if (!res.ok || !data.ok) throw new Error(data.error || "Error");
        return data;
    }

    async function cargarMiResena() {
        try {
            const data = await api({ action: "my_get" });

            if (data.resena) {
                btnTop.textContent = "Reseña puesta / editar";
                nombre.value = data.resena.nombre_publico || "";
                punt.value = String(data.resena.puntuacion || 5);
                texto.value = data.resena.texto || "";
            } else {
                btnTop.textContent = "Poner reseña";
                nombre.value = "";
                punt.value = "5";
                texto.value = "";
            }
        } catch {
            // silencioso para no romper la página si aún no existe API
        }
    }

    // Eventos abrir/cerrar
    btnTop.addEventListener("click", openModal);
    closeBg?.addEventListener("click", closeModal);
    btnCerrar?.addEventListener("click", closeModal);

    // Cerrar con ESC
    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape" && modal.classList.contains("is-open")) closeModal();
    });

    // Guardar
    form.addEventListener("submit", async (e) => {
        e.preventDefault();
        msg.style.display = "none";

        const p = Number(punt.value);
        const t = texto.value.trim();

        if (!Number.isFinite(p) || p < 1 || p > 5) {
            mostrarAviso("❌ Puntuación inválida (1-5).", true);
            return;
        }
        if (t.length < 5) {
            mostrarAviso(
                "❌ Escribe un poco más (mínimo 5 caracteres).",
                true
            );
            return;
        }

        btnGuardar.disabled = true;
        btnGuardar.textContent = "Guardando...";

        try {
            const estabaEditando =
                btnTop.textContent.includes("editar");

            await api({
                action: "my_save",
                nombre_publico: nombre.value.trim(),
                puntuacion: p,
                texto: t,
            });

            await cargarMiResena();

            mostrarAviso(
                estabaEditando
                    ? "✅ Reseña actualizada correctamente"
                    : "✅ Reseña publicada correctamente"
            );

            setTimeout(closeModal, 1500);
            
        } catch (err) {
            mostrarAviso(
                "❌ " + (err.message || "No se pudo guardar."),
                true
            );
        } finally {
            btnGuardar.disabled = false;
            btnGuardar.textContent = "Guardar reseña";
        }
    });

    // init
    cargarMiResena();
});

