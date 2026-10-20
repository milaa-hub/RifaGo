
console.log("RifaGo: quiniela.js se cargó");

document.addEventListener("DOMContentLoaded", () => {
    const boton = document.getElementById("btn-sortear");
    const estado = document.getElementById("estado-sorteo");

    console.log("Botón encontrado:", boton);
    console.log("Estado encontrado:", estado);

    if (!boton || !estado) {
        console.error("No se encontraron los elementos del sorteo.");
        return;
    }

    console.log("Elementos del sorteo encontrados correctamente");

    boton.addEventListener("click", () => {
        console.log("PRUEBA: el clic funciona");
        estado.textContent = "El clic funciona correctamente.";
    });

    boton.addEventListener("click", async () => {
        console.log("RifaGo: clic detectado");

        const idRifa = boton.dataset.idRifa;
        const csrf = boton.dataset.csrf;

        if (!idRifa || !csrf) {
            estado.textContent =
                "Error: falta el ID de la rifa o el token de seguridad.";
            console.error("ID de rifa:", idRifa, "Token:", csrf);
            return;
        }

        boton.disabled = true;
        estado.textContent = "Consultando el resultado oficial de la Quiniela...";

        try {
            const respuesta = await fetch(
                "../acciones/procesar_sorteo.php",
                {
                    method: "POST",
                    credentials: "same-origin",
                    headers: {
                        "Content-Type": "application/json",
                        "Accept": "application/json"
                    },
                    body: JSON.stringify({
                        id_rifa: idRifa,
                        csrf: csrf
                    })
                }
            );

            const texto = await respuesta.text();

            console.log("Estado HTTP:", respuesta.status);
            console.log("Respuesta del servidor:", texto);

            let datos;

            try {
                datos = JSON.parse(texto);
            } catch {
                throw new Error(
                    "El servidor no devolvió JSON válido. Revisá la consola."
                );
            }

            if (!respuesta.ok || !datos.ok) {
                throw new Error(
                    datos.mensaje || "No se pudo realizar el sorteo."
                );
            }

            estado.textContent =
                datos.mensaje +
                " Número ganador: " +
                datos.numero_ganador;

            window.location.href =
                "../pages/resultado_rifa.php?id=" +
                encodeURIComponent(idRifa);

        } catch (error) {
            console.error("Error al realizar el sorteo:", error);
            estado.textContent = error.message;
            boton.disabled = false;
        }
    });
});
