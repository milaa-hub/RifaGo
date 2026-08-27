const tabs = document.querySelectorAll(".participation-tab");
const contents = document.querySelectorAll(".participation-content");

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

        const contenido =
            document.getElementById(tabSeleccionada);

        if (contenido) {

            contenido.classList.add("active");

        }

    });

});