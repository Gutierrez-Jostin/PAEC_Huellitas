document.addEventListener("DOMContentLoaded", () => {
    const btnLeerMas = document.getElementById("btn-leer-mas");
    const textoOculto = document.getElementById("texto-oculto");

    if (btnLeerMas && textoOculto) {
        btnLeerMas.addEventListener("click", () => {
            if (textoOculto.style.display === "block") {
                textoOculto.style.display = "none";
                btnLeerMas.textContent = "Read More";
            } else {
                textoOculto.style.display = "block";
                btnLeerMas.textContent = "Read Less";
            }
        });
    }
});