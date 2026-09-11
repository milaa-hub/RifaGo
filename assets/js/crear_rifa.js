/* =========================
   ELEMENTOS
========================= */

const pasoDatos = document.getElementById("step-1");
const pasoConfiguracion = document.getElementById("step-2");

const botonContinuar = document.getElementById("ir-configuracion");
const botonVolver = document.getElementById("volver-datos");

const precio = document.getElementById("precio_numero");
const cantidad = document.getElementById("cantidad_numeros");

const resumenPrecio = document.getElementById("resumen-precio");
const resumenCantidad = document.getElementById("resumen-cantidad");
const resumenTotal = document.getElementById("resumen-total");


/* =========================
   PASO 1 → PASO 2
========================= */

if (botonContinuar) {

    botonContinuar.addEventListener(
        "click",
        function() {

            const nombre = document
                .getElementById("nombre_premio")
                .value
                .trim();


            const descripcion = document
                .getElementById("descripcion")
                .value
                .trim();


            if (
                nombre === "" ||
                descripcion === ""
            ) {

                alert(
                    "Completá el nombre y la descripción del premio."
                );

                return;

            }


            /* OCULTAR PASO 1 */

            pasoDatos.classList.remove(
                "active"
            );


            /* MOSTRAR PASO 2 */

            pasoConfiguracion.classList.add(
                "active"
            );


            /* VOLVER ARRIBA */

            window.scrollTo({
                top: 0,
                behavior: "smooth"
            });

        }
    );

}


/* =========================
   VOLVER AL PASO 1
========================= */

if (botonVolver) {

    botonVolver.addEventListener(
        "click",
        function() {

            /* OCULTAR PASO 2 */

            pasoConfiguracion.classList.remove(
                "active"
            );


            /* MOSTRAR PASO 1 */

            pasoDatos.classList.add(
                "active"
            );


            /* VOLVER ARRIBA */

            window.scrollTo({
                top: 0,
                behavior: "smooth"
            });

        }
    );

}


/* =========================
   RESUMEN
========================= */

function actualizarResumen() {

    /* VERIFICAR QUE EXISTAN */

    if (
        !precio ||
        !cantidad ||
        !resumenPrecio ||
        !resumenCantidad ||
        !resumenTotal
    ) {

        return;

    }


    const precioValor =
        Number(precio.value) || 0;


    const cantidadValor =
        Number(cantidad.value) || 0;


    /* PRECIO */

    resumenPrecio.textContent =
        "$" +
        precioValor.toLocaleString(
            "es-AR"
        );


    /* CANTIDAD */

    resumenCantidad.textContent =
        cantidadValor.toLocaleString(
            "es-AR"
        );


    /* TOTAL */

    const total =
        precioValor * cantidadValor;


    resumenTotal.textContent =
        "$" +
        total.toLocaleString(
            "es-AR"
        );

}


/* =========================
   ESCUCHAR PRECIO
========================= */

if (precio) {

    precio.addEventListener(
        "input",
        actualizarResumen
    );

}


/* =========================
   ESCUCHAR CANTIDAD
========================= */

if (cantidad) {

    cantidad.addEventListener(
        "change",
        actualizarResumen
    );

}


/* =========================
   SUBIDA DE IMAGEN
========================= */

const imageInput =
    document.getElementById("imagen");


const previewContainer =
    document.getElementById(
        "imagePreviewContainer"
    );


const previewImage =
    document.getElementById(
        "imagePreview"
    );


const uploadBox =
    document.getElementById(
        "uploadBox"
    );


const imageName =
    document.getElementById(
        "imageName"
    );


const removeImage =
    document.getElementById(
        "removeImage"
    );


/* =========================
   VERIFICAR IMAGEN EXISTENTE
========================= */

if (
    previewImage &&
    previewImage.getAttribute("src") &&
    previewImage.getAttribute("src").trim() !== ""
) {

    if (previewContainer) {

        previewContainer.style.display =
            "block";

    }


    if (uploadBox) {

        uploadBox.style.display =
            "none";

    }

}


/* =========================
   SELECCIONAR IMAGEN
========================= */

if (imageInput) {

    imageInput.addEventListener(
        "change",
        function(event) {

            const file =
                event.target.files[0];


            /* SI NO HAY ARCHIVO */

            if (!file) {

                return;

            }


            /* TIPOS PERMITIDOS */

            const allowedTypes = [

                "image/jpeg",
                "image/png",
                "image/webp"

            ];


            /* VALIDAR TIPO */

            if (
                !allowedTypes.includes(
                    file.type
                )
            ) {

                alert(
                    "Solo se permiten imágenes JPG, PNG o WEBP."
                );


                imageInput.value = "";


                return;

            }


            /* LEER IMAGEN */

            const reader =
                new FileReader();


            reader.onload =
                function(e) {

                    /* PONER IMAGEN */

                    if (previewImage) {

                        previewImage.src =
                            e.target.result;

                    }


                    /* MOSTRAR PREVIEW */

                    if (previewContainer) {

                        previewContainer.style.display =
                            "block";

                    }


                    /* OCULTAR UPLOAD */

                    if (uploadBox) {

                        uploadBox.style.display =
                            "none";

                    }


                    /* MOSTRAR NOMBRE */

                    if (imageName) {

                        imageName.textContent =
                            file.name;

                    }

                };


            /* ERROR */

            reader.onerror =
                function() {

                    alert(
                        "No se pudo cargar la imagen."
                    );

                };


            /* LEER ARCHIVO */

            reader.readAsDataURL(
                file
            );

        }
    );

}


/* =========================
   ELIMINAR IMAGEN
========================= */

if (removeImage) {

    removeImage.addEventListener(
        "click",
        function() {

            /* LIMPIAR INPUT */

            if (imageInput) {

                imageInput.value = "";

            }


            /* QUITAR IMAGEN */

            if (previewImage) {

                previewImage.removeAttribute(
                    "src"
                );

            }


            /* OCULTAR PREVIEW */

            if (previewContainer) {

                previewContainer.style.display =
                    "none";

            }


            /* MOSTRAR SUBIDA */

            if (uploadBox) {

                uploadBox.style.display =
                    "flex";

            }


            /* LIMPIAR NOMBRE */

            if (imageName) {

                imageName.textContent =
                    "";

            }

        }
    );

}


/* =========================
   INICIALIZAR RESUMEN
========================= */

actualizarResumen();