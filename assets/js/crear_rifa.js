const pasoDatos = document.getElementById("step-1");
const pasoConfiguracion = document.getElementById("step-2");

const botonContinuar = document.getElementById("ir-configuracion");
const botonVolver = document.getElementById("volver-datos");

const precio = document.getElementById("precio_numero");
const cantidad = document.getElementById("cantidad_numeros");

const resumenPrecio = document.getElementById("resumen-precio");
const resumenCantidad = document.getElementById("resumen-cantidad");
const resumenTotal = document.getElementById("resumen-total");


botonContinuar.addEventListener("click", function() {

    const nombre = document.getElementById("nombre_premio").value.trim();
    const descripcion = document.getElementById("descripcion").value.trim();

    if (nombre === "" || descripcion === "") {

        alert("Completá el nombre y la descripción del premio.");

        return;
    }

    pasoDatos.classList.remove("active");
    pasoConfiguracion.classList.add("active");

    window.scrollTo({
        top: 0,
        behavior: "smooth"
    });

});


botonVolver.addEventListener("click", function() {

    pasoConfiguracion.classList.remove("active");
    pasoDatos.classList.add("active");

    window.scrollTo({
        top: 0,
        behavior: "smooth"
    });

});


function actualizarResumen() {

    const precioValor = Number(precio.value) || 0;
    const cantidadValor = Number(cantidad.value) || 0;

    resumenPrecio.textContent =
        "$" + precioValor.toLocaleString("es-AR");

    resumenCantidad.textContent =
        cantidadValor.toLocaleString("es-AR");

    const total = precioValor * cantidadValor;

    resumenTotal.textContent =
        "$" + total.toLocaleString("es-AR");

}


precio.addEventListener("input", actualizarResumen);
cantidad.addEventListener("change", actualizarResumen);