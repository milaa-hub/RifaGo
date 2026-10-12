const buscador = document.getElementById("buscador");
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

// ==================================================
// BUSCADOR
// ==================================================

if (buscador) {

    buscador.addEventListener("keydown", function (event) {

        if (event.key === "Enter") {

            event.preventDefault();

            const texto =
                this.value.trim();

            window.location.href =
                "pages/busqueda.php?busqueda=" +
                encodeURIComponent(texto);

        }

    });

}


// ==================================================
// CATEGORÍAS
// ==================================================

categorias.forEach(function (categoria) {

    categoria.addEventListener("click", function () {

        const categoriaSeleccionada =
            this.dataset.categoria;


        // TODAS

        if (categoriaSeleccionada === "todas") {

            window.location.href =
                "pages/busqueda.php";

            return;

        }


        // MÁS

        if (categoriaSeleccionada === "mas") {

            window.location.href =
                "pages/busqueda.php?categoria=mas";

            return;

        }


        // CATEGORÍAS NORMALES

        window.location.href =
            "pages/busqueda.php?categoria=" +
            encodeURIComponent(
                categoriaSeleccionada
            );

    });

});


// ==================================================
// BOTÓN DE FILTRO
// ==================================================

if (btnFiltro && filterPanel) {

    btnFiltro.addEventListener("click", function () {

        filterPanel.classList.toggle("visible");

    });

}


// ==================================================
// FILTROS
// ==================================================

filtros.forEach(function (filtro) {

    filtro.addEventListener("click", function () {

        const tipoFiltro =
            filtro.dataset.filtro;


        if (tipoFiltro === "todas") {

            window.location.href =
                "pages/busqueda.php";

        }


        if (tipoFiltro === "disponibles") {

            window.location.href =
                "pages/busqueda.php?disponibilidad=disponibles";

        }


        if (tipoFiltro === "proximas") {

            window.location.href =
                "pages/busqueda.php?disponibilidad=proximas";

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