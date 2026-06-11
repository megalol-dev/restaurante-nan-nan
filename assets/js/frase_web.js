"use strict";

document.addEventListener("DOMContentLoaded", () => {
    const titulo = document.getElementById("titulo");
    const subtitulo = document.getElementById("subtitulo");
    const form = document.getElementById("formFraseWeb");
    const msg = document.getElementById("msgFraseWeb");
    const btnGuardar = document.getElementById("btnGuardarFrase");

    function showMsg(text, ok = true) {
        if (!msg) return;
        msg.textContent = text;
        msg.style.display = "block";
        msg.style.color = ok ? "var(--color-blue)" : "var(--color-red)";
    }
    function hideMsg() {
        if (!msg) return;
        msg.style.display = "none";
    }

    async function api(payload) {
        const res = await fetch("api/frase_web_api.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(payload),
        });
        const data = await res.json();
        if (!res.ok || !data.ok) throw new Error(data.error || "Error");
        return data;
    }

    async function cargar() {
        hideMsg();
        try {
            const data = await api({ action: "get" });
            if (data.frase) {
                titulo.value = data.frase.titulo || "";
                subtitulo.value = data.frase.subtitulo || "";
            }
        } catch (e) {
            showMsg("❌ " + (e.message || "No se pudo cargar la frase."), false);
        }
    }

    form?.addEventListener("submit", async (e) => {
        e.preventDefault();
        hideMsg();

        const t = String(titulo?.value || "").trim();
        const s = String(subtitulo?.value || "").trim();

        if (t.length < 3) return showMsg("❌ El título es demasiado corto.", false);
        if (s.length < 3) return showMsg("❌ El subtítulo es demasiado corto.", false);

        btnGuardar.disabled = true;
        const old = btnGuardar.textContent;
        btnGuardar.textContent = "Guardando...";

        try {
            await api({ action: "set", titulo: t, subtitulo: s });
            showMsg("✅ Frase guardada. Ya se verá en el index.", true);
        } catch (e2) {
            showMsg("❌ " + (e2.message || "No se pudo guardar."), false);
        } finally {
            btnGuardar.disabled = false;
            btnGuardar.textContent = old || "Guardar frase";
        }
    });

    cargar();
});
