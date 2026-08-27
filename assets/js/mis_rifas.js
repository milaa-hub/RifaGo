const tabs = document.querySelectorAll(".tab");
const contents = document.querySelectorAll(".tab-content");


function confirmarEliminarBorrador() {

    return confirm(
        "¿Querés eliminar este borrador?\n\n" +
        "Se perderán todos los datos guardados y no podrás recuperarlos."
    );

}

tabs.forEach(function(tab) {

    tab.addEventListener("click", function() {

        const tabSeleccionada = tab.dataset.tab;

        tabs.forEach(function(item) {
            item.classList.remove("active");
        });

        contents.forEach(function(content) {
            content.classList.remove("active");
        });

        tab.classList.add("active");

        const contenido = document.getElementById(tabSeleccionada);

        if (contenido) {
            contenido.classList.add("active");
        }

    });

});