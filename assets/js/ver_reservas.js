"use strict";

document.addEventListener("DOMContentLoaded", () => {
  const msg = document.getElementById("msg");

  const gridComida = document.getElementById("gridMesasComida");
  const gridCena = document.getElementById("gridMesasCena");

  const kpiMesasComida = document.getElementById("kpiMesasComida");
  const kpiCapComida = document.getElementById("kpiCapacidadComida");
  const kpiOcuComida = document.getElementById("kpiOcupadasComida");

  const kpiMesasCena = document.getElementById("kpiMesasCena");
  const kpiCapCena = document.getElementById("kpiCapacidadCena");
  const kpiOcuCena = document.getElementById("kpiOcupadasCena");

  // ✅ NUEVO: selector de fecha
  const inputFecha = document.getElementById("fechaReserva");
  let fecha =
    window.__FECHA_RESERVAS__ || new Date().toISOString().slice(0, 10);

  if (inputFecha) {
    // Si quieres impedir fechas pasadas:
    // inputFecha.min = new Date().toISOString().slice(0, 10);

    // sincroniza por si acaso
    inputFecha.value = inputFecha.value || fecha;

    inputFecha.addEventListener("change", async () => {
      fecha = inputFecha.value;
      msg.style.display = "none";
      await cargarTodo();
    });
  }

  function pedirDatosReserva(turno, fecha) {
    return new Promise((resolve) => {
      const fondo = document.createElement("div");
      fondo.className = "modal-confirm-bg";

      fondo.innerHTML = `
            <div class="modal-confirm">

                <h3>Nueva reserva</h3>

                <p>
                    Turno: ${turno}<br>
                    Fecha: ${fecha}
                </p>

                <input
                    id="clienteReserva"
                    class="input"
                    placeholder="Nombre cliente"
                    style="width:100%;margin-bottom:12px;"
                >

                <input
                    id="comensalesReserva"
                    class="input"
                    type="number"
                    min="1"
                    max="50"
                    value="2"
                    placeholder="Comensales"
                    style="width:100%;margin-bottom:12px;"
                >

                <div class="btn-row">
                    <button class="btn" id="btnReservaAceptar">
                        Reservar
                    </button>

                    <button class="btn btn--outline" id="btnReservaCancelar">
                        Cancelar
                    </button>
                </div>

            </div>
        `;

      document.body.appendChild(fondo);

      document
        .getElementById("btnReservaAceptar")
        .addEventListener("click", () => {
          const cliente = document
            .getElementById("clienteReserva")
            .value.trim();

          const comensales = Number(
            document.getElementById("comensalesReserva").value,
          );

          fondo.remove();

          resolve({
            cliente,
            comensales,
          });
        });

      document
        .getElementById("btnReservaCancelar")
        .addEventListener("click", () => {
          fondo.remove();
          resolve(null);
        });
    });
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

  function pintar(grid, state) {
    const ocupadas = state.ocupadas || {};

    const kpi = state.kpi;
    if (state.turno === "comida") {
      kpiMesasComida.textContent = `${kpi.disponibles} / ${kpi.total}`;
      kpiCapComida.textContent = `${kpi.capacidad_restante} pax`;
      kpiOcuComida.textContent = `${kpi.ocupadas}`;
    } else {
      kpiMesasCena.textContent = `${kpi.disponibles} / ${kpi.total}`;
      kpiCapCena.textContent = `${kpi.capacidad_restante} pax`;
      kpiOcuCena.textContent = `${kpi.ocupadas}`;
    }

    grid.querySelectorAll("[data-mesa-id]").forEach((btn) => {
      const id = Number(btn.dataset.mesaId);
      const info = ocupadas[id];

      btn.classList.remove("mesa--libre", "mesa--ocupada", "mesa--loading");

      if (info) {
        btn.classList.add("mesa--ocupada");
        const nombreCliente =
          info.cliente_mostrar || info.cliente_nombre || "Cliente";
        btn.title = `OCUPADA | Cliente: ${nombreCliente} | Comensales: ${info.comensales} | Por: ${info.trabajador_nombre}`;
      } else {
        btn.classList.add("mesa--libre");
        btn.title = "LIBRE (clic para ocupar)";
      }
    });
  }

  async function cargarTurno(turno) {
    const data = await api({ action: "state", fecha, turno });
    if (turno === "comida") pintar(gridComida, data.state);
    else pintar(gridCena, data.state);
  }

  async function cargarTodo() {
    await Promise.all([cargarTurno("comida"), cargarTurno("cena")]);
  }

  async function onClickMesa(e) {
    const btn = e.target.closest("[data-mesa-id]");
    if (!btn) return;

    const mesaId = Number(btn.dataset.mesaId);
    const turno = btn.dataset.turno;

    msg.style.display = "none";

    const ocupada = btn.classList.contains("mesa--ocupada");

    try {
      if (!ocupada) {
        const datos = await pedirDatosReserva(turno, fecha);

        if (!datos) return;

        const cliente = datos.cliente;
        const comensales = datos.comensales;

        if (!cliente) return;

        if (!Number.isFinite(comensales) || comensales < 1 || comensales > 50) {
          mostrarAviso("❌ Comensales inválido (1-50).", true);
          return;
        }

        const data = await api({
          action: "ocupar",
          fecha,
          turno,
          mesa_id: mesaId,
          cliente_nombre: cliente.trim(),
          comensales,
        });

        if (turno === "comida") pintar(gridComida, data.state);
        else pintar(gridCena, data.state);

        mostrarAviso("✅ Reserva creada correctamente");
        return;
      }

      const ok = await confirmar(
        "Liberar reserva",
        `¿Cancelar la reserva del turno ${turno}?`,
      );
      if (!ok) return;

      const data = await api({
        action: "liberar",
        fecha,
        turno,
        mesa_id: mesaId,
      });

      if (turno === "comida") pintar(gridComida, data.state);
      else pintar(gridCena, data.state);

      mostrarAviso("✅ Reserva cancelada correctamente");
    } catch (err) {
      mostrarAviso("❌ " + (err.message || "Error"), true);
    }
  }

  gridComida.addEventListener("click", onClickMesa);
  gridCena.addEventListener("click", onClickMesa);

  // init
  cargarTodo();

  // refescar reservas en panel control
  setInterval(cargarTodo, 10000);
});



