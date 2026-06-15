"use strict";

document.addEventListener("DOMContentLoaded", async () => {
    const ulP = document.getElementById("menuPrimeros");
    const ulS = document.getElementById("menuSegundos");
    const ulPo = document.getElementById("menuPostres");
    const txtIncluye = document.getElementById("menuIncluye");

    if (!ulP || !ulS || !ulPo) return;

    const hoy = new Date().toISOString().slice(0, 10);

    function li(text) {
        const el = document.createElement("li");
        el.textContent = text;
        return el;
    }

    function reset() {
        ulP.innerHTML = "";
        ulS.innerHTML = "";
        ulPo.innerHTML = "";
    }

    try {
        const res = await fetch("../api/menu_diario_api.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ action: "public_get", fecha: hoy }),
        });

        const data = await res.json();
        if (!res.ok || !data.ok) throw new Error(data.error || "Error");

        reset();

        if (!data.menu) {
            ulP.appendChild(
                li("El menú del día para hoy aún no ha sido publicado. Puedes consultar nuestra carta completa pulsando el botón inferior.")
            );

            return;
        }

        const items = data.menu.items || [];
        const primeros = items.filter((x) => x.tipo === "primero");
        const segundos = items.filter((x) => x.tipo === "segundo");
        const postres = items.filter((x) => x.tipo === "postre");

        if (!primeros.length) ulP.appendChild(li("—"));
        else primeros.forEach((p) => ulP.appendChild(li(p.nombre)));

        if (!segundos.length) ulS.appendChild(li("—"));
        else segundos.forEach((p) => ulS.appendChild(li(p.nombre)));

        if (!postres.length) ulPo.appendChild(li("—"));
        else postres.forEach((p) => ulPo.appendChild(li(p.nombre)));

    } catch {
        reset();
        ulP.appendChild(li("No se pudo cargar el menú del día."));
        ulS.appendChild(li("—"));
        ulPo.appendChild(li("—"));
    }
});

