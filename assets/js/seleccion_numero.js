const numeros = document.querySelectorAll(
    '.number-item input[type="checkbox"]'
);

const cantidad = document.getElementById(
    'cantidadSeleccionada'
);

const total = document.getElementById(
    'totalSeleccionado'
);

const boton = document.getElementById(
    'btnContinuar'
);

function actualizarSeleccion() {

    const seleccionados = document.querySelectorAll(
        '.number-item input[type="checkbox"]:checked'
    );

    const cantidadSeleccionados = seleccionados.length;

    let precio = 0;

    seleccionados.forEach(numero => {

        precio += Number(
            numero.dataset.precio
        );

    });

    cantidad.textContent =
        cantidadSeleccionados;

    total.textContent =
        '$' + precio.toLocaleString('es-AR');

    boton.disabled =
        cantidadSeleccionados === 0;
}


numeros.forEach(numero => {

    numero.addEventListener(
        'change',
        function () {

            this.parentElement.classList.toggle(
                'number-selected',
                this.checked
            );

            actualizarSeleccion();

        }
    );

});