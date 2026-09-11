/* =========================================
   PESTAÑAS
========================================= */

const tabs =
    document.querySelectorAll(
        ".tab"
    );


const contents =
    document.querySelectorAll(
        ".tab-content"
    );


tabs.forEach(
    function(tab) {

        tab.addEventListener(
            "click",
            function() {


                const tabSeleccionada =
                    tab.dataset.tab;


                /* QUITAR ACTIVE DE TODAS */

                tabs.forEach(
                    function(item) {

                        item.classList.remove(
                            "active"
                        );

                    }
                );


                contents.forEach(
                    function(content) {

                        content.classList.remove(
                            "active"
                        );

                    }
                );


                /* ACTIVAR BOTÓN */

                tab.classList.add(
                    "active"
                );


                /* ACTIVAR CONTENIDO */

                const contenido =
                    document.getElementById(
                        tabSeleccionada
                    );


                if (contenido) {

                    contenido.classList.add(
                        "active"
                    );

                }


            }
        );

    }
);


/* =========================================
   ELIMINAR BORRADOR
========================================= */


/* MODAL */

const deleteModal =
    document.getElementById(
        "deleteModal"
    );


/* BOTÓN CANCELAR */

const cancelDelete =
    document.getElementById(
        "cancelDelete"
    );


/* BOTÓN CONFIRMAR */

const confirmDelete =
    document.getElementById(
        "confirmDelete"
    );


/* TODOS LOS BOTONES ELIMINAR */

const deleteButtons =
    document.querySelectorAll(
        ".delete-draft-button"
    );


/* FORMULARIO QUE SE VA A ELIMINAR */

let formAEliminar = null;


/* =========================================
   ABRIR MODAL
========================================= */

deleteButtons.forEach(
    function(button) {

        button.addEventListener(
            "click",
            function() {


                /* BUSCAR EL FORMULARIO
                   DEL BOTÓN PRESIONADO */

                formAEliminar =
                    this.closest(
                        ".delete-draft-form"
                    );


                /* VERIFICAR */

                if (
                    deleteModal &&
                    formAEliminar
                ) {

                    deleteModal.classList.add(
                        "active"
                    );

                }


            }
        );

    }
);


/* =========================================
   CANCELAR ELIMINACIÓN
========================================= */

if (
    cancelDelete &&
    deleteModal
) {

    cancelDelete.addEventListener(
        "click",
        function() {


            /* CERRAR MODAL */

            deleteModal.classList.remove(
                "active"
            );


            /* LIMPIAR FORMULARIO */

            formAEliminar = null;


        }
    );

}


/* =========================================
   CONFIRMAR ELIMINACIÓN
========================================= */

if (confirmDelete) {

    confirmDelete.addEventListener(
        "click",
        function() {


            /* VERIFICAR QUE HAYA
               UN FORMULARIO */

            if (formAEliminar) {


                /* ENVIAR FORMULARIO */

                formAEliminar.submit();


            }


        }
    );

}


/* =========================================
   CERRAR AL TOCAR EL FONDO
========================================= */

if (deleteModal) {

    deleteModal.addEventListener(
        "click",
        function(event) {


            if (
                event.target === deleteModal
            ) {


                deleteModal.classList.remove(
                    "active"
                );


                formAEliminar = null;


            }


        }
    );

}