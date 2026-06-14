"use strict";

document.addEventListener("DOMContentLoaded", () => {
    const img = document.getElementById("carruselImg");

    if (!img) return;

    const totalFotos = 10;
    let actual = 1;

    setInterval(() => {
        actual++;

        if (actual > totalFotos) {
            actual = 1;
        }
        img.style.opacity = "0";

        setTimeout(() => {
            img.src = `../assets/img/carrusel/plato${actual}.png`;
            img.style.opacity = "1";
        }, 250);
        
    }, 2000);
    
});