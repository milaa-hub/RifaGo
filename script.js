// ================================
// BUSCADOR
// ================================

const buscador = document.getElementById("buscador");
const rifas = document.querySelectorAll(".raffle-card");

buscador.addEventListener("input", function () {

    const texto = buscador.value.toLowerCase();

    rifas.forEach(function (rifa) {

        const nombre = rifa.querySelector("h3").textContent.toLowerCase();

        if (nombre.includes(texto)) {
            rifa.style.display = "";
        } else {
            rifa.style.display = "none";
        }

    });

});


// ================================
// CATEGORÍAS
// ================================

const categorias = document.querySelectorAll(".category");

categorias.forEach(function (boton) {

    boton.addEventListener("click", function () {

        const categoriaElegida = boton.dataset.categoria;

        categorias.forEach(function (categoria) {
            categoria.classList.remove("activa");
        });

        boton.classList.add("activa");

        rifas.forEach(function (rifa) {

            const categoriaRifa = rifa.dataset.categoria;

            if (
                categoriaElegida === "todas" ||
                categoriaRifa === categoriaElegida
            ) {

                rifa.style.display = "";

            } else {

                rifa.style.display = "none";

            }

        });

    });

});


// ================================
// BOTÓN "VER RIFAS"
// ================================

const btnVerRifas = document.getElementById("btnVerRifas");

btnVerRifas.addEventListener("click", function () {

    document.querySelector(".raffles").scrollIntoView({
        behavior: "smooth"
    });

});


// ================================
// BOTÓN "VER TODAS"
// ================================

const btnVerTodas = document.getElementById("btnVerTodas");

btnVerTodas.addEventListener("click", function () {

    buscador.value = "";

    categorias.forEach(function (categoria) {
        categoria.classList.remove("activa");
    });

    document
        .querySelector('[data-categoria="todas"]')
        .classList.add("activa");

    rifas.forEach(function (rifa) {
        rifa.style.display = "";
    });

});


// ================================
// BOTÓN FILTRO
// ================================

const btnFiltro = document.getElementById("btnFiltro");

btnFiltro.addEventListener("click", function () {

    document.querySelector(".categories").scrollIntoView({
        behavior: "smooth"
    });

});


// ================================
// BOTÓN CREAR RIFA
// ================================

const btnCrearRifa = document.getElementById("btnCrearRifa");

btnCrearRifa.addEventListener("click", function () {

    window.location.href = "crear_rifa.php";

});