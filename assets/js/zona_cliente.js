"use strict";

document.addEventListener("DOMContentLoaded", () => {
    const $ = (id) => document.getElementById(id);

    // =========================================================
    // ✅ RESEÑAS (modal + estado bloqueada/oculta)
    // =========================================================
    const btnResTop = $("btnResenaTop");
    const modalRes = $("modalResena");
    const closeBgRes = $("modalResenaClose");
    const btnCerrarRes = $("btnResCancelar");

    const formRes = $("formResena");
    const resNombre = $("resNombre");
    const resPunt = $("resPuntuacion");
    const resTexto = $("resTexto");
    const msgRes = $("msgResena");
    const btnResGuardar = $("btnResGuardar");

    const TEXTO_BLOQUEO = (motivo = "") => {
        const m = String(motivo || "").trim();
        const extra = m ? `\n\nMotivo: ${m}` : "";
        return (
            "⚠️ Tu reseña ha sido bloqueada y ocultada por moderación.\n" +
            "Si crees que es un error o quieres que se revise, contacta con el gerente o escribe a: contacto@barloli.es" +
            extra
        );
    };

    function openResModal() {
        if (!modalRes) return;
        modalRes.classList.add("is-open");
        modalRes.setAttribute("aria-hidden", "false");
    }

    function closeResModal() {
        if (!modalRes) return;
        modalRes.classList.remove("is-open");
        modalRes.setAttribute("aria-hidden", "true");
        if (msgRes) msgRes.style.display = "none";
    }

    function showResMsg(text, ok = true) {
        if (!msgRes) return;
        msgRes.textContent = text;
        msgRes.style.display = "block";
        msgRes.style.color = ok ? "var(--color-blue)" : "var(--color-red)";
    }

    function setResFormEnabled(enabled) {
        if (!formRes) return;

        const dis = !enabled;
        if (resNombre) resNombre.disabled = dis;
        if (resPunt) resPunt.disabled = dis;
        if (resTexto) resTexto.disabled = dis;

        if (btnResGuardar) {
            btnResGuardar.disabled = dis;
            btnResGuardar.style.display = enabled ? "" : "none";
        }
    }

    async function apiResenas(payload) {
        const res = await fetch("/BarApp/api/resenas_api.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(payload),
        });
        const data = await res.json();
        if (!res.ok || !data.ok) throw new Error(data.error || "Error");
        return data;
    }

    let miResena = null;

    async function cargarMiResenaUI() {
        if (!btnResTop) return;

        try {
            const data = await apiResenas({ action: "my_get" });
            miResena = data.resena || null;

            if (!miResena) {
                btnResTop.textContent = "Poner reseña";
                setResFormEnabled(true);
                if (resNombre) resNombre.value = "";
                if (resPunt) resPunt.value = "5";
                if (resTexto) resTexto.value = "";
                return;
            }

            const estado = String(miResena.estado || "visible");

            if (estado !== "visible") {
                btnResTop.textContent = "Reseña bloqueada y ocultada";
                setResFormEnabled(false);

                if (resNombre) resNombre.value = miResena.nombre_publico || "";
                if (resPunt) resPunt.value = String(miResena.puntuacion || 5);
                if (resTexto) resTexto.value = miResena.texto || "";
                return;
            }

            btnResTop.textContent = "Reseña puesta / editar";
            setResFormEnabled(true);
            if (resNombre) resNombre.value = miResena.nombre_publico || "";
            if (resPunt) resPunt.value = String(miResena.puntuacion || 5);
            if (resTexto) resTexto.value = miResena.texto || "";

        } catch {
            // silencioso
        }
    }

    btnResTop?.addEventListener("click", async () => {
        await cargarMiResenaUI();

        if (miResena && String(miResena.estado || "visible") !== "visible") {
            openResModal();
            showResMsg(TEXTO_BLOQUEO(miResena.motivo_moderacion || ""), false);
            return;
        }

        openResModal();
        if (msgRes) msgRes.style.display = "none";
    });

    closeBgRes?.addEventListener("click", closeResModal);
    btnCerrarRes?.addEventListener("click", closeResModal);

    formRes?.addEventListener("submit", async (e) => {
        e.preventDefault();
        if (!btnResGuardar) return;

        if (msgRes) msgRes.style.display = "none";

        if (miResena && String(miResena.estado || "visible") !== "visible") {
            showResMsg(TEXTO_BLOQUEO(miResena.motivo_moderacion || ""), false);
            return;
        }

        const p = Number(resPunt?.value || 0);
        const t = String(resTexto?.value || "").trim();

        if (!Number.isFinite(p) || p < 1 || p > 5) {
            showResMsg("❌ Puntuación inválida (1-5).", false);
            return;
        }

        if (t.length < 5) {
            showResMsg("❌ Escribe un poco más (mínimo 5 caracteres).", false);
            return;
        }

        btnResGuardar.disabled = true;
        const old = btnResGuardar.textContent;
        btnResGuardar.textContent = "Guardando...";

        try {
            await apiResenas({
                action: "my_save",
                nombre_publico: String(resNombre?.value || "").trim(),
                puntuacion: p,
                texto: t,
            });

            showResMsg("✅ Reseña guardada.", true);
            await cargarMiResenaUI();
            setTimeout(closeResModal, 450);

        } catch (err) {
            showResMsg("❌ " + (err.message || "No se pudo guardar."), false);
        } finally {
            btnResGuardar.disabled = false;
            btnResGuardar.textContent = old || "Guardar reseña";
        }
    });

    // =========================================================
    // ✅ RESERVAS
    // =========================================================
    const form = $("formReserva");
    const comensales = $("comensales");
    const turno = $("turno");
    const fecha = $("fecha");

    const errCom = $("errComensales");
    const errTur = $("errTurno");
    const errFec = $("errFecha");

    const infoStock = $("infoStock");
    const msg = $("msgReserva");

    const btnReservar = $("btnReservar");
    const btnCancelar = $("btnCancelar"); // se elimina del DOM

    const tbodyReservas = $("tbodyReservas");

    if (btnReservar) btnReservar.classList.add("btn-reservar");

    const btnLogout = document.querySelector("button[onclick*='logout.php']");
    if (btnLogout) btnLogout.classList.add("btn-logout");

    if (btnCancelar) btnCancelar.remove();

    // Helpers fecha
    const hoy = new Date().toISOString().slice(0, 10);

    function esFechaValidaYYYYMMDD(s) {
        return /^\d{4}-\d{2}-\d{2}$/.test(String(s || ""));
    }

    function esFechaPasada(yyyyMMdd) {
        // Comparación lexicográfica sirve en formato YYYY-MM-DD
        if (!esFechaValidaYYYYMMDD(yyyyMMdd)) return false;
        return String(yyyyMMdd) < hoy;
    }

    if (fecha) {
        fecha.value = hoy;
        fecha.min = hoy; // ayuda visual, pero NO evita el bug por escritura
    }

    function showMsg(text, ok = true) {
        if (!msg) return;
        msg.textContent = text;
        msg.style.display = "block";
        msg.style.color = ok ? "var(--color-blue)" : "var(--color-red)";
    }

    function setError(input, el, text) {
        if (!input || !el) return;
        el.textContent = text;
        input.classList.add("is-invalid");
    }

    function clearError(input, el) {
        if (!input || !el) return;
        el.textContent = "";
        input.classList.remove("is-invalid");
    }

    function validar() {
        let ok = true;

        const c = Number(comensales?.value);
        if (!Number.isFinite(c) || c < 1 || c > 50) {
            setError(comensales, errCom, "Comensales debe estar entre 1 y 50.");
            ok = false;
        } else clearError(comensales, errCom);

        if (turno?.value !== "comida" && turno?.value !== "cena") {
            setError(turno, errTur, "Turno inválido.");
            ok = false;
        } else clearError(turno, errTur);

        const f = String(fecha?.value || "");
        if (!esFechaValidaYYYYMMDD(f)) {
            setError(fecha, errFec, "Fecha inválida.");
            ok = false;
        } else if (esFechaPasada(f)) {
            setError(fecha, errFec, "No se puede reservar para un día que ya pasó.");
            showMsg("❌ No se puede reservar para un día que ya pasó.", false);
            ok = false;
        }
        else {
            clearError(fecha, errFec);
        }
        return ok;
    }

    async function api(payload) {
        const res = await fetch("/BarApp/api/reservas_api.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(payload),
        });
        const data = await res.json();
        if (!res.ok || !data.ok) throw new Error(data.error || "Error");
        return data;
    }

    function pintarResumen(resumen) {
        if (!infoStock || !fecha) return;
        const c = resumen.comida;
        const n = resumen.cena;

        const txt = `Disponibilidad para ${fecha.value}:
        🍽️ Comida: ${c.disponibles} mesas (${c.capacidad_restante} personas)
        🌙 Cena: ${n.disponibles} mesas (${n.capacidad_restante} personas)`;

        infoStock.textContent = txt;

        if (c.disponibles <= 0 && n.disponibles <= 0) {
            showMsg("Lo sentimos, ese día está todo reservado ya.", false);
        }
    }

    async function cargarResumen() {
        if (msg) msg.style.display = "none";
        if (!fecha) return;

        // Si han metido una fecha pasada, no “refrescamos” como si fuese válida
        if (esFechaPasada(fecha.value)) {
            setError(fecha, errFec, "No se puede reservar para un día que ya pasó.");
            if (infoStock) infoStock.textContent = "";
            return;
        }

        const data = await api({ action: "resumen_dia", fecha: fecha.value });
        pintarResumen(data.resumen);
    }

    function fmtFecha(yyyyMMdd) {
        const [y, m, d] = String(yyyyMMdd).split("-");
        return `${d}/${m}/${y}`;
    }

    function labelTurno(t) {
        return t === "comida" ? "Comida (14:00–16:00)" : "Cena (21:00–23:00)";
    }

    function renderTablaReservas(reservas) {
        if (!tbodyReservas) return;

        if (!Array.isArray(reservas) || reservas.length === 0) {
            tbodyReservas.innerHTML =
                `<tr><td colspan="5">No tienes reservas activas.</td></tr>`;
            return;
        }
        
        tbodyReservas.innerHTML = reservas
            .map((r) => {
                const rid = Number(r.id);

                return `
<tr>
    <td data-label="Fecha">${fmtFecha(r.fecha)}</td>
    <td data-label="Turno">${labelTurno(r.turno)}</td>
    <td data-label="Comensales">${Number(r.comensales)}</td>
    <td data-label="Mesas">${Number(r.mesas_usadas)}</td>
    <td data-label="Acciones">
        <button
            class="btn btn--outline btn-sm"
            type="button"
            data-cancelar-reserva="${rid}">
            Cancelar
        </button>
    </td>
</tr>`;
            })
            .join("");
    }


    async function cargarTablaReservas() {
        if (!tbodyReservas) return;

        tbodyReservas.innerHTML =
            `<tr><td colspan="5">Cargando...</td></tr>`;

        try {
            const data = await api({ action: "cliente_listar" });

            const reservasFuturas = (data.reservas || []).filter(
                (r) => String(r.fecha) >= hoy
            );

            renderTablaReservas(reservasFuturas);

        } catch {
            tbodyReservas.innerHTML =
                `<tr><td colspan="5">No se pudieron cargar tus reservas.</td></tr>`;
        }
    }


    if (tbodyReservas) {
        tbodyReservas.addEventListener("click", async (e) => {
            const btn = e.target.closest("[data-cancelar-reserva]");
            if (!btn) return;

            const rid = Number(btn.dataset.cancelarReserva);
            if (!rid) return;

            const ok = await confirmar(
                "Cancelar reserva",
                "¿Seguro que quieres cancelar esta reserva?"
            );
            if (!ok) return;

            const oldText = btn.textContent;
            btn.disabled = true;
            btn.textContent = "Cancelando...";

            try {
                const data = await api({ action: "cliente_cancelar", reserva_id: rid });
                renderTablaReservas(data.reservas || []);
                await cargarResumen();
                showMsg("✅ Reserva cancelada.", true);
            } catch (err) {
                showMsg("❌ " + (err.message || "No se pudo cancelar."), false);
            } finally {
                btn.disabled = false;
                btn.textContent = oldText;
            }
        });
    }

    async function cargarMiReserva() {
        try {
            if (!fecha || !turno) return;

            // Si fecha pasada, no pedimos mi_reserva (evita “confusiones”)
            if (esFechaPasada(fecha.value)) return;

            const data = await api({
                action: "cliente_mi_reserva",
                fecha: fecha.value,
                turno: turno.value,
            });

            pintarResumen(data.resumen);

            if (data.reserva) {
                showMsg(
                    `✅ Reserva activa en ${labelTurno(data.reserva.turno)} para ${data.reserva.comensales} comensales(mesas: ${data.reserva.mesas_usadas}).`,
                    true
                );
            }
        } catch {
            // silencioso
        }
    }

    fecha?.addEventListener("change", async () => {
        // Si meten pasado, mostramos mensaje claro y no seguimos
        if (esFechaPasada(String(fecha.value || ""))) {
            setError(fecha, errFec, "No se puede reservar para un día que ya pasó.");
            showMsg("❌ No se puede reservar para un día que ya pasó.", false);
            return;
        }

        await cargarResumen();
        await cargarMiReserva();
        await cargarTablaReservas();
    });

    turno?.addEventListener("change", async () => {
        await cargarMiReserva();
        await cargarTablaReservas();
    });

    form?.addEventListener("submit", async (e) => {
        e.preventDefault();
        if (msg) msg.style.display = "none";

        // ✅ aquí bloqueamos el submit si fecha pasada
        if (!validar()) return;

        if (btnReservar) {
            btnReservar.disabled = true;
            btnReservar.textContent = "Reservando...";
        }

        try {
            const c = Number(comensales.value);

            const data = await api({
                action: "cliente_reservar",
                fecha: fecha.value,
                turno: turno.value,
                comensales: c,
            });

            pintarResumen(data.resumen);

            showMsg(
                `✅ Reserva confirmada(${labelTurno(turno.value)
                }).Mesas asignadas: ${data.mesas_asignadas.join(", ")}.`,
                true
            );

            await cargarMiReserva();
            await cargarTablaReservas();
        } catch (err) {
            showMsg("❌ " + (err.message || "No se pudo reservar."), false);
        } finally {
            if (btnReservar) {
                btnReservar.disabled = false;
                btnReservar.textContent = "Reservar";
            }
        }
    });

    // =========================================================
    // INIT
    // =========================================================
    (async () => {
        await cargarMiResenaUI();
        await cargarResumen();
        await cargarMiReserva();
        await cargarTablaReservas();
    })();

});
