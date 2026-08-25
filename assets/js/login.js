const password = document.getElementById("password");
const botonMostrar = document.getElementById("mostrarPassword");

botonMostrar.addEventListener("click", function () {

    if (password.type === "password") {

        password.type = "text";

        botonMostrar.textContent = "Ocultar";

    } else {

        password.type = "password";

        botonMostrar.textContent = "Ver";

    }

});