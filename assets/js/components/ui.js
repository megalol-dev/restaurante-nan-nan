"use strict";

function mostrarAviso(texto, esError = false) {
    const aviso = document.createElement("div");

    aviso.textContent = texto;

    aviso.className = esError
        ? "aviso aviso--error"
        : "aviso aviso--ok";

    document.body.appendChild(aviso);

    setTimeout(() => {
        aviso.remove();
    }, 2000);
}

function confirmar(titulo, texto) {
    return new Promise((resolve) => {
        const fondo = document.createElement("div");
        fondo.className = "modal-confirm-bg";

        fondo.innerHTML = `
            <div class="modal-confirm">
                <h3>${titulo}</h3>
                <p>${texto}</p>

                <div class="btn-row">
                    <button class="btn" id="btnConfirmarSi">
                        Aceptar
                    </button>

                    <button class="btn btn--outline" id="btnConfirmarNo">
                        Cancelar
                    </button>
                </div>
            </div>
        `;

        document.body.appendChild(fondo);

        document.getElementById("btnConfirmarSi")
            .addEventListener("click", () => {
                fondo.remove();
                resolve(true);
            });

        document.getElementById("btnConfirmarNo")
            .addEventListener("click", () => {
                fondo.remove();
                resolve(false);
            });
    });
}