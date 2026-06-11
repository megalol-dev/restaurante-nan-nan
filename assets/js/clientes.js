"use strict";

document.addEventListener("DOMContentLoaded", () => {
    const q = document.getElementById("q");
    const tbody = document.getElementById("tbodyClientes");
    const msg = document.getElementById("msgClientes");
    const btnBuscar = document.getElementById("btnBuscarClientes");
    const btnLimpiar = document.getElementById("btnLimpiarClientes");

    function showMsg(text, ok = true) {
        if (!msg) return;
        msg.textContent = text;
        msg.style.display = "block";
        msg.style.color = ok ? "var(--color-blue)" : "var(--color-red)";
    }
    function clearMsg() {
        if (!msg) return;
        msg.style.display = "none";
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

    function fmtFecha(val) {
        const s = String(val ?? "");
        if (!s) return "—";
        const d = s.slice(0, 10);
        if (!/^\d{4}-\d{2}-\d{2}$/.test(d)) return esc(s);
        const [y, m, dd] = d.split("-");
        return `${dd}/${m}/${y}`;
    }

    async function api(payload) {
        const res = await fetch("/BarApp/api/clientes_api.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(payload),
        });
        const data = await res.json();
        if (!res.ok || !data.ok) throw new Error(data.error || "Error");
        return data;
    }

    function render(items) {
        if (!tbody) return;

        if (!Array.isArray(items) || items.length === 0) {
            tbody.innerHTML = `<tr><td colspan="6">No hay clientes (o no coinciden con el filtro).</td></tr>`;
            return;
        }

        tbody.innerHTML = items.map((c) => `
      <tr>
        <td>${esc(c.id)}</td>
        <td>${esc(c.nombre)}</td>
        <td>${esc(c.apellidos)}</td>
        <td>${esc(c.email)}</td>
        <td>${esc(c.telefono || "—")}</td>
        <td>${fmtFecha(c.created_at)}</td>
      </tr>
    `).join("");
    }

    async function load() {
        clearMsg();
        if (tbody) tbody.innerHTML = `<tr><td colspan="6">Cargando...</td></tr>`;

        try {
            const query = (q?.value || "").trim();

            // ✅ Si hay texto -> search, si no -> list
            const payload = query
                ? { action: "search", q: query }
                : { action: "list" };

            const data = await api(payload);
            render(data.items || []);
        } catch (err) {
            if (tbody) tbody.innerHTML = `<tr><td colspan="6">No se pudieron cargar los clientes.</td></tr>`;
            showMsg("❌ " + (err.message || "Error"), false);
        }
    }

    // Botón buscar
    btnBuscar?.addEventListener("click", load);

    // Enter en el input
    q?.addEventListener("keydown", (e) => {
        if (e.key === "Enter") {
            e.preventDefault();
            load();
        }
    });

    // Limpiar
    btnLimpiar?.addEventListener("click", () => {
        if (q) q.value = "";
        load();
    });

    // Init
    load();
});



