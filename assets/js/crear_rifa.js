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

            const nombre =
                document
                    .getElementById("nombre_premio")
                    .value
                    .trim();

            const descripcion =
                document
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


            pasoDatos.classList.remove(
                "active"
            );

            pasoConfiguracion.classList.add(
                "active"
            );


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


    resumenPrecio.textContent =
        "$" +
        precioValor.toLocaleString(
            "es-AR"
        );


    resumenCantidad.textContent =
        cantidadValor.toLocaleString(
            "es-AR"
        );


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


            if (!file) {

                return;

            }


            const allowedTypes = [

                "image/jpeg",
                "image/png",
                "image/webp"

            ];


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


            const reader =
                new FileReader();


            reader.onload =
                function(e) {

                    if (previewImage) {

                        previewImage.src =
                            e.target.result;

                    }


                    if (previewContainer) {

                        previewContainer.style.display =
                            "block";

                    }


                    if (uploadBox) {

                        uploadBox.style.display =
                            "none";

                    }


                    if (imageName) {

                        imageName.textContent =
                            file.name;

                    }

                };


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

            if (imageInput) {

                imageInput.value = "";

            }


            if (previewImage) {

                previewImage.removeAttribute(
                    "src"
                );

            }


            if (previewContainer) {

                previewContainer.style.display =
                    "none";

            }


            if (uploadBox) {

                uploadBox.style.display =
                    "flex";

            }


            if (imageName) {

                imageName.textContent =
                    "";

            }

        }
    );

}


/* ==========================================
   DETECTAR CAMBIOS Y SALIR
========================================== */

const formCrearRifa =
    document.getElementById(
        "formCrearRifa"
    );

const btnVolverCrear =
    document.getElementById(
        "btnVolverCrear"
    );

const exitModal =
    document.getElementById(
        "exitModal"
    );

const guardarBorrador =
    document.getElementById(
        "guardarBorrador"
    );

const seguirEditando =
    document.getElementById(
        "seguirEditando"
    );

const salirSinGuardar =
    document.getElementById(
        "salirSinGuardar"
    );


let hayCambios = false;


/* =========================
   DETECTAR CAMBIOS
========================= */

if (formCrearRifa) {

    const campos =
        formCrearRifa.querySelectorAll(
            "input:not([type='hidden']), textarea, select"
        );


    campos.forEach(
        function(campo) {

            campo.addEventListener(
                "input",
                function() {

                    hayCambios = true;

                }
            );


            campo.addEventListener(
                "change",
                function() {

                    hayCambios = true;

                }
            );

        }
    );

}


/* =========================
   TOCAR FLECHA
========================= */

if (btnVolverCrear) {

    btnVolverCrear.addEventListener(
        "click",
        function(event) {

            event.preventDefault();


            /* SI NO HAY CAMBIOS */

            if (!hayCambios) {

                window.location.href =
                    "mis_rifas.php";

                return;

            }


            /* MOSTRAR MODAL */

            if (exitModal) {

                exitModal.classList.add(
                    "active"
                );

            } else {

                /* SOLO PARA EVITAR
                   QUE NO PASE NADA */

                alert(
                    "Tenés cambios sin guardar."
                );

            }

        }
    );

}


/* =========================
   SEGUIR EDITANDO
========================= */

if (seguirEditando) {

    seguirEditando.addEventListener(
        "click",
        function() {

            if (exitModal) {

                exitModal.classList.remove(
                    "active"
                );

            }

        }
    );

}


/* =========================
   SALIR SIN GUARDAR
========================= */

if (salirSinGuardar) {

    salirSinGuardar.addEventListener(
        "click",
        function() {

            window.location.href =
                "mis_rifas.php";

        }
    );

}


/* =========================
   GUARDAR BORRADOR
========================= */

if (
    guardarBorrador &&
    formCrearRifa
) {

    guardarBorrador.addEventListener(
        "click",
        function() {

            let inputAccion =
                formCrearRifa.querySelector(
                    "input[name='accion']"
                );


            /* SI NO EXISTE */

            if (!inputAccion) {

                inputAccion =
                    document.createElement(
                        "input"
                    );


                inputAccion.type =
                    "hidden";

                inputAccion.name =
                    "accion";

                formCrearRifa.appendChild(
                    inputAccion
                );

            }


            inputAccion.value =
                "borrador";


            formCrearRifa.submit();

        }
    );

}


/* =========================
   INICIALIZAR RESUMEN
========================= */

actualizarResumen();