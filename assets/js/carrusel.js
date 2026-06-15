"use strict";

document.addEventListener("DOMContentLoaded", () => {

    const imgA = document.getElementById("imgA");
    const imgB = document.getElementById("imgB");

    if (!imgA || !imgB) return;

    const totalFotos = 10;

    let actual = 1;
    let visibleA = true;

    setInterval(() => {

        let siguiente = actual + 1;

        if (siguiente > totalFotos) {
            siguiente = 1;
        }

        if (visibleA) {

            imgB.src = `../assets/img/carrusel/plato${siguiente}.png`;

            imgB.style.opacity = "1";
            imgA.style.opacity = "0";

        } else {

            imgA.src = `../assets/img/carrusel/plato${siguiente}.png`;

            imgA.style.opacity = "1";
            imgB.style.opacity = "0";
        }

        visibleA = !visibleA;
        actual = siguiente;

    }, 3000);

});