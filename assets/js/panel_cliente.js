"use strict";

document.addEventListener("DOMContentLoaded", () => {
    const $ = (id) => document.getElementById(id);

    const form = $("formReserva");
    const comensales = $("comensales");
    const fecha = $("fecha");
    const hora = $("hora");

    const errComensales = $("errComensales");
    const errFecha = $("errFecha");
    const errHora = $("errHora");
    const msg = $("msgReserva");

    // Poner fecha mínima = hoy (opcional, pero útil)
    const hoy = new Date();
    const yyyy = hoy.getFullYear();
    const mm = String(hoy.getMonth() + 1).padStart(2, "0");
    const dd = String(hoy.getDate()).padStart(2, "0");
    fecha.min = `${yyyy}-${mm}-${dd}`;

    /* lógica del formulario: mostrar error */
    function setError(input, errEl, message) {
        errEl.textContent = message;
        input.classList.add("is-invalid");
    }

    /* lógica del formulario: limpiar error */
    function clearError(input, errEl) {
        errEl.textContent = "";
        input.classList.remove("is-invalid");
    }

    /* lógica del formulario: valida comensales entre 1 y 50 */
    function validarComensales() {
        const n = Number(comensales.value);
        if (!Number.isFinite(n) || n < 1 || n > 50) {
            setError(comensales, errComensales, "Debe ser un número entre 1 y 50.");
            return false;
        }
        clearError(comensales, errComensales);
        return true;
    }

    /* lógica del formulario: valida fecha (no vacía) */
    function validarFecha() {
        if (!fecha.value) {
            setError(fecha, errFecha, "Selecciona una fecha.");
            return false;
        }
        clearError(fecha, errFecha);
        return true;
    }

    /* lógica del formulario: valida que la hora esté dentro de 14-16 o 21-23 */
    function validarHora() {
        const v = hora.value; // "HH:MM"
        if (!v) {
            setError(hora, errHora, "Selecciona una hora.");
            return false;
        }

        const [hh, min] = v.split(":").map(Number);
        const mins = hh * 60 + min;

        const turno1Ini = 14 * 60;       // 14:00
        const turno1Fin = 16 * 60;       // 16:00
        const turno2Ini = 21 * 60;       // 21:00
        const turno2Fin = 23 * 60;       // 23:00

        const enTurno1 = mins >= turno1Ini && mins <= turno1Fin;
        const enTurno2 = mins >= turno2Ini && mins <= turno2Fin;

        if (!enTurno1 && !enTurno2) {
            setError(hora, errHora, "Hora no válida. Solo 14:00–16:00 o 21:00–23:00.");
            return false;
        }

        clearError(hora, errHora);
        return true;
    }

    comensales.addEventListener("input", validarComensales);
    fecha.addEventListener("input", validarFecha);
    hora.addEventListener("input", validarHora);

    // Botón reseña (solo placeholder por ahora)
    $("btnResena").addEventListener("click", () => {
        alert("📝 Próximamente: formulario para dejar una reseña.");
    });

    form.addEventListener("submit", (e) => {
        e.preventDefault();
        msg.style.display = "none";

        const ok = validarComensales() & validarFecha() & validarHora();
        if (!ok) return;

        // No guardamos aún, solo confirmación visual
        msg.textContent = "✅ Datos correctos. (La reserva se guardará cuando conectemos con la BD/API)";
        msg.style.display = "block";
        msg.style.color = "#003049";
    });
});
