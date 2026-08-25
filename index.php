<?php

require_once "conexion.php";

echo "Conexion a RifaGo exitosa";

?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>RifaGo</title>

    <link rel="stylesheet" href="assets/css/style.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>

    <div class="app">

        <header class="header">

            <div class="logo">
                <span class="logo-blue">Rifa</span><span class="logo-red">Go</span><sup>+</sup>
            </div>

            <div class="user-icon">
                <span>SM</span>
            </div>

        </header>


        <main class="main-content">

            <div class="search-container">

                <div class="search-box">
                    <span class="search-icon">⌕</span>
                    <input
                    type="text"
                    id="buscador"
                    placeholder="Buscar rifas..."
                    aria-label="Buscar rifas"
                    >
                </div>

                <button class="filter-button" id="btnFiltro">
                    ☷
                </button>

            </div>


            <section class="hero-banner">

                <div class="hero-text">

                    <h1>
                        ¡Participá y ganá
                        <br>
                        premios increíbles!
                    </h1>

                    <button class="hero-button" id="btnVerRifas">
                        Ver rifas
                    </button>

                </div>

                <div class="hero-image">
                    <div class="gift">
                        🎁
                    </div>
                </div>

            </section>


            <section class="section">

                <div class="section-header">
                    <h2>Categorías</h2>
                </div>

                <div class="categories">

                    <button class="category activa" data-categoria="todas">
                        <div class="category-icon">◉</div>
                        <span>Todas</span>
                    </button>

                    <button class="category" data-categoria="tecnologia">
                        <div class="category-icon">▣</div>
                        <span>Tecnología</span>
                    </button>

                    <button class="category" data-categoria="hogar">
                        <div class="category-icon">⌂</div>
                        <span>Hogar</span>
                    </button>

                    <button class="category" data-categoria="viajes">
                        <div class="category-icon">✈</div>
                        <span>Viajes</span>
                    </button>

                    <button class="category" data-categoria="mas">
                        <div class="category-icon">•••</div>
                        <span>Más</span>
                    </button>

                </div>

            </section>


            <section class="section">

                <div class="section-header">
                    <h2>Rifas destacadas</h2>

                    <button class="see-more" id="btnVerTodas">
                        Ver todas
                    </button>
                </div>


                <div class="raffles">



                    <article class="raffle-card" data-categoria="tecnologia">

                        <div class="raffle-image">
                            <img
                                src="assets/img/iphone15promax.webp"
                                alt="iPhone 15 Pro Max"
                            >
                        </div>

                        <div class="raffle-info">

                            <h3>iPhone 15 Pro Max</h3>

                            <p class="raffle-price">
                                $2.000 por número
                            </p>

                            <p class="raffle-date">
                                Sorteo 30/09/2026
                            </p>

                            <div class="progress">

                                <div class="progress-bar">
                                    <span></span>
                                </div>

                                <small>750 / 1000</small>

                            </div>

                        </div>

                    </article>


                    <article class="raffle-card" data-categoria="tecnologia">

                        <div class="raffle-image">
                            <img
                                src="assets/img/ps5.webp"
                                alt="PlayStation 5"
                            >
                        </div>

                        <div class="raffle-info">

                            <h3>PlayStation 5</h3>

                            <p class="raffle-price">
                                $1.500 por número
                            </p>

                            <p class="raffle-date">
                                Sorteo 25/09/2026
                            </p>

                            <div class="progress">

                                <div class="progress-bar">
                                    <span class="progress-ps"></span>
                                </div>

                                <small>620 / 1000</small>

                            </div>

                        </div>

                    </article>


                    <article class="raffle-card" data-categoria="viajes">

                        <div class="raffle-image">
                            <img
                                src="assets/img/cancun.jpg"
                                alt="Cancún"
                            >
                        </div>

                        <div class="raffle-info">

                            <h3>Viaje a Cancún</h3>

                            <p class="raffle-price">
                                $3.000 por número
                            </p>

                            <p class="raffle-date">
                                Sorteo 24/10/2026
                            </p>

                            <div class="progress">

                                <div class="progress-bar">
                                    <span class="progress-sofa"></span>
                                </div>

                                <small>430 / 1000</small>

                            </div>

                        </div>

                    </article>

                </div>

            </section>

        </main>


        <nav class="bottom-nav">

            <a href="index.php" class="nav-item active">

                <span class="nav-icon">⌂</span>

                <span>Inicio</span>

            </a>


            <a href="mis-rifas.php" class="nav-item">

                <span class="nav-icon">▤</span>

                <span>Mis rifas</span>

            </a>


            <button class="create-button" id="btnCrearRifa">

                <span>+</span>

            </button>


            <a href="participaciones.php" class="nav-item">

                <span class="nav-icon">♧</span>

                <span>Participaciones</span>

            </a>


            <a href="perfil.php" class="nav-item">

                <span class="nav-icon">♙</span>

                <span>Perfil</span>

            </a>

        </nav>

    </div>

    <script src="assets/js/crear_rifa.js"></script>
</body>
</html>