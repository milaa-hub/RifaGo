// ================================
// ELEMENTOS
// ================================

const buscador = document.getElementById("buscador");
const rifas = document.querySelectorAll(".raffle-card");
const categorias = document.querySelectorAll(".category");


// ================================
// FILTRAR RIFAS
// ================================

function filtrarRifas() {

    const texto = buscador
        ? buscador.value.toLowerCase().trim()
        : "";

    const categoriaActiva = document.querySelector(".category.activa");

    const categoriaElegida = categoriaActiva
        ? categoriaActiva.dataset.categoria
        : "todas";


    rifas.forEach(function (rifa) {

        // Buscamos el nombre de la rifa
        const titulo = rifa.querySelector("h3");

        const nombre = titulo
            ? titulo.textContent.toLowerCase()
            : "";


        // Categoría de la rifa
        const categoriaRifa =
            rifa.dataset.categoria || "todas";


        // Comprobar búsqueda
        const coincideBusqueda =
            nombre.includes(texto);


        // Comprobar categoría
        const coincideCategoria =
            categoriaElegida === "todas" ||
            categoriaRifa === categoriaElegida;


        // Mostrar u ocultar
        if (coincideBusqueda && coincideCategoria) {

            rifa.style.display = "";

        } else {

            rifa.style.display = "none";

        }

    });

}


// ================================
// BUSCADOR
// ================================

if (buscador) {

    buscador.addEventListener("input", function () {

        filtrarRifas();

    });

}


// ================================
// CATEGORÍAS
// ================================

categorias.forEach(function (boton) {

    boton.addEventListener("click", function () {

        const categoriaElegida =
            boton.dataset.categoria;


        // Quitar activa de todas
        categorias.forEach(function (categoria) {

            categoria.classList.remove("activa");

        });


        // Activar la elegida
        boton.classList.add("activa");


        // Aplicar filtros
        filtrarRifas();

    });

});


// ================================
// BOTÓN "VER RIFAS"
// ================================

const btnVerRifas =
    document.getElementById("btnVerRifas");


if (btnVerRifas) {

    btnVerRifas.addEventListener("click", function () {

        const seccionRifas =
            document.querySelector(".raffles");


        if (seccionRifas) {

            seccionRifas.scrollIntoView({
                behavior: "smooth"
            });

        }

    });

}


// ================================
// BOTÓN "VER TODAS"
// ================================

const btnVerTodas =
    document.getElementById("btnVerTodas");


if (btnVerTodas) {

    btnVerTodas.addEventListener("click", function () {

        // Limpiar buscador
        if (buscador) {

            buscador.value = "";

        }


        // Activar "Todas"
        categorias.forEach(function (categoria) {

            categoria.classList.remove("activa");

        });


        const categoriaTodas =
            document.querySelector(
                '[data-categoria="todas"]'
            );


        if (categoriaTodas) {

            categoriaTodas.classList.add("activa");

        }


        // Mostrar todas
        rifas.forEach(function (rifa) {

            rifa.style.display = "";

        });

    });

}


// ================================
// BOTÓN FILTRO
// ================================

const btnFiltro =
    document.getElementById("btnFiltro");


if (btnFiltro) {

    btnFiltro.addEventListener("click", function () {

        const categoriasContainer =
            document.querySelector(".categories");


        if (categoriasContainer) {

            categoriasContainer.scrollIntoView({
                behavior: "smooth"
            });

        }

    });

}


// ================================
// BOTÓN CREAR RIFA
// ================================

const btnCrearRifa =
    document.getElementById("btnCrearRifa");


if (btnCrearRifa) {

    btnCrearRifa.addEventListener("click", function () {

        window.location.href =
            "crear_rifa.php";

    });

}