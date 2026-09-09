// ==================================================
// ELEMENTOS
// ==================================================

const buscador = document.getElementById("buscador");

const rifas = document.querySelectorAll(".raffle-card");

const categorias = document.querySelectorAll(".category");

const btnFiltro = document.getElementById("btnFiltro");

const filterPanel = document.getElementById("filterPanel");

const filtros = document.querySelectorAll(".filter-option");

const sinResultados =
    document.getElementById("sinResultados");

const btnVerRifas =
    document.getElementById("btnVerRifas");

const btnCrearRifa =
    document.getElementById("btnCrearRifa");


// ==================================================
// ESTADO DE LOS FILTROS
// ==================================================

let categoriaActual = "todas";

let filtroActual = "todas";


// ==================================================
// FUNCIÓN PRINCIPAL DE FILTRADO
// ==================================================

function filtrarRifas() {

    const texto =
        buscador.value
            .toLowerCase()
            .trim();


    let cantidadVisibles = 0;


    rifas.forEach(function (rifa) {


        // ------------------------------------------
        // BUSCADOR
        // ------------------------------------------

        const titulo =
            rifa.dataset.titulo || "";

        const descripcion =
            rifa.dataset.descripcion || "";

        const premio =
            rifa.dataset.premio || "";


        const coincideBusqueda =
            titulo.includes(texto) ||
            descripcion.includes(texto) ||
            premio.includes(texto);



        // ------------------------------------------
        // CATEGORÍA
        // ------------------------------------------

        const categoria =
            rifa.dataset.categoria;


        const coincideCategoria =
            categoriaActual === "todas" ||
            categoria === categoriaActual;



        // ------------------------------------------
        // FILTRO
        // ------------------------------------------

        let coincideFiltro = true;


        if (filtroActual === "disponibles") {

            const vendidos =
                Number(
                    rifa.dataset.vendidos
                );

            const cantidad =
                Number(
                    rifa.dataset.cantidad
                );


            coincideFiltro =
                vendidos < cantidad;

        }


        if (filtroActual === "proximas") {

            const fecha =
                rifa.dataset.fecha;


            if (fecha) {

                const fechaRifa =
                    new Date(fecha);

                const hoy =
                    new Date();


                coincideFiltro =
                    fechaRifa >= hoy;

            } else {

                coincideFiltro = false;

            }

        }



        // ------------------------------------------
        // MOSTRAR / OCULTAR
        // ------------------------------------------

        if (
            coincideBusqueda &&
            coincideCategoria &&
            coincideFiltro
        ) {

            rifa.style.display = "";

            cantidadVisibles++;

        } else {

            rifa.style.display = "none";

        }

    });


    // ------------------------------------------
    // MENSAJE SIN RESULTADOS
    // ------------------------------------------

    if (
        cantidadVisibles === 0 &&
        rifas.length > 0
    ) {

        sinResultados.style.display = "block";

    } else {

        sinResultados.style.display = "none";

    }

}


// ==================================================
// BUSCADOR
// ==================================================

if (buscador) {

    buscador.addEventListener(
        "input",
        function () {

            /*
             * Se ejecuta inmediatamente al escribir.
             * NO necesita tocar el botón de filtro.
             */

            filtrarRifas();

        }
    );

}


// ==================================================
// CATEGORÍAS
// ==================================================

categorias.forEach(function(categoria) {

    categoria.addEventListener(
        "click",
        function() {


            // Cambiar categoría actual

            categoriaActual =
                categoria.dataset.categoria;


            // Cambiar apariencia

            categorias.forEach(
                function(item) {

                    item.classList.remove(
                        "activa"
                    );

                }
            );


            categoria.classList.add(
                "activa"
            );


            // Aplicar filtro

            filtrarRifas();

        }
    );

});


// ==================================================
// BOTÓN DE FILTRO
// ==================================================

if (btnFiltro) {

    btnFiltro.addEventListener(
        "click",
        function() {

            /*
             * El botón solamente abre/cierra
             * las opciones de filtro.
             *
             * NO realiza la búsqueda.
             */

            filterPanel.classList.toggle(
                "visible"
            );

        }
    );

}


// ==================================================
// OPCIONES DEL FILTRO
// ==================================================

filtros.forEach(function(filtro) {

    filtro.addEventListener(
        "click",
        function() {


            filtroActual =
                filtro.dataset.filtro;


            // Actualizar apariencia

            filtros.forEach(
                function(item) {

                    item.classList.remove(
                        "active"
                    );

                }
            );


            filtro.classList.add(
                "active"
            );


            // Aplicar filtro

            filtrarRifas();

        }
    );

});


// ==================================================
// BOTÓN "VER RIFAS"
// ==================================================

if (btnVerRifas) {

    btnVerRifas.addEventListener(
        "click",
        function() {

            const seccion =
                document.getElementById(
                    "seccionRifas"
                );


            if (seccion) {

                seccion.scrollIntoView({
                    behavior: "smooth"
                });

            }

        }
    );

}


// ==================================================
// BOTÓN CREAR RIFA
// ==================================================

if (btnCrearRifa) {

    btnCrearRifa.addEventListener(
        "click",
        function() {

            window.location.href =
                "../../pages/crear_rifa.php";

        }
    );

}


// ==================================================
// FILTRAR AL CARGAR
// ==================================================

filtrarRifas();