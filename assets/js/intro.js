const intro = document.querySelector(".intro");


setTimeout(function () {

    intro.classList.add("salida");


    setTimeout(function () {

        window.location.href = "login.php";

    }, 500);


}, 1500);