// ==================================================
// ELEMENTOS
// ==================================================

const buscador = document.getElementById("buscador");
const rifas = document.querySelectorAll(".raffle-card");
const categorias = document.querySelectorAll(".category");

const btnFiltro = document.getElementById("btnFiltro");
const filterPanel = document.getElementById("filterPanel");
const filtros = document.querySelectorAll(".filter-option");

const sinResultados = document.getElementById("sinResultados");
const btnVerRifas = document.getElementById("btnVerRifas");
const btnCrearRifa = document.getElementById("btnCrearRifa");


// ==================================================
// BUSCADOR
// ==================================================

// El buscador NO filtra las tarjetas del index.
// Al presionar Enter, busca en busqueda.php.

if (buscador) {

    buscador.addEventListener("keydown", function (event) {

        if (event.key === "Enter") {

            event.preventDefault();

            const texto = buscador.value.trim();

            if (texto !== "") {

                window.location.href =
                    "busqueda.php?buscar=" +
                    encodeURIComponent(texto);

            }

        }

    });

}


// ==================================================
// CATEGORÍAS
// ==================================================

// Al tocar una categoría se realiza la búsqueda
// correspondiente en busqueda.php.

categorias.forEach(function (categoria) {

    categoria.addEventListener("click", function (event) {

        const categoriaSeleccionada =
            categoria.dataset.categoria;

        // Si es un enlace <a>, dejamos que funcione
        // con su href normalmente.
        if (categoria.tagName.toLowerCase() === "a") {
            return;
        }

        if (
            categoriaSeleccionada &&
            categoriaSeleccionada !== "todas"
        ) {

            window.location.href =
                "busqueda.php?buscar=" +
                encodeURIComponent(categoriaSeleccionada);

        } else {

            window.location.href =
                "busqueda.php";

        }

    });

});


// ==================================================
// BOTÓN DE FILTRO
// ==================================================

// Este botón solamente abre y cierra
// el panel de filtros.

if (btnFiltro && filterPanel) {

    btnFiltro.addEventListener("click", function () {

        filterPanel.classList.toggle("visible");

    });

}


// ==================================================
// FILTROS
// ==================================================

// Estos botones corresponden a:
// Todas / Disponibles / Próximas

filtros.forEach(function (filtro) {

    filtro.addEventListener("click", function () {

        const tipoFiltro =
            filtro.dataset.filtro;

        if (tipoFiltro === "todas") {

            window.location.href =
                "busqueda.php";

        }

        if (tipoFiltro === "disponibles") {

            window.location.href =
                "busqueda.php?disponibilidad=disponibles";

        }

        if (tipoFiltro === "proximas") {

            window.location.href =
                "busqueda.php?disponibilidad=proximas";

        }

    });

});


// ==================================================
// BOTÓN "VER RIFAS"
// ==================================================

if (btnVerRifas) {

    btnVerRifas.addEventListener("click", function () {

        const seccion =
            document.getElementById("seccionRifas");

        if (seccion) {

            seccion.scrollIntoView({
                behavior: "smooth"
            });

        }

    });

}


// ==================================================
// BOTÓN "CREAR RIFA"
// ==================================================

if (btnCrearRifa) {

    btnCrearRifa.addEventListener("click", function () {

        window.location.href =
            "pages/crear_rifa.php";

    });

}