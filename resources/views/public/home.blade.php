<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>FC Cogote Salado</title>

    <!-- Tailwind -->
    @vite('resources/css/app.css')
</head>

<body class="bg-gray-900 text-white">

<!-- =========================
     NAVBAR
========================= -->
<nav class="fixed top-0 w-full z-50 bg-black/70 backdrop-blur">
    <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
        <!-- Logo -->
        <div class="flex items-center gap-3">
            <img src="/storage/fotos/logo_con_fondo.png" class="h-10" alt="Logo FC Cogote Salado">
            <span class="font-bold text-lg">FC Cogote Salado</span>
        </div>

        <!-- Menu -->
        <ul class="hidden md:flex gap-6 font-semibold">
            <li><a href="#" class="hover:text-yellow-400">Inicio</a></li>
            <li><a href="#" class="hover:text-yellow-400">Temporadas</a></li>
            <li><a href="#" class="hover:text-yellow-400">Plantel</a></li>
            <li><a href="#" class="hover:text-yellow-400">Noticias</a></li>
            <li><a href="#" class="hover:text-yellow-400">Fotos</a></li>
        </ul>
    </div>
</nav>

<!-- =========================
     HERO / CARRUSEL (ESTÁTICO)
========================= -->
<section class="h-screen relative">

    <!-- Imagen fondo -->
    <div class="absolute inset-0 bg-cover bg-center"
         style="background-image: url('/storage/fotos/hero.jpg');">
    </div>

    <!-- Overlay -->
    <div class="absolute inset-0 bg-black/60"></div>

    <!-- Contenido -->
    <div class="relative z-10 h-full flex items-center">
        <div class="max-w-5xl mx-auto px-6">
            <h1 class="text-5xl md:text-6xl font-extrabold mb-6">
                SUPERAMOS LOS MIL ABONADOS
            </h1>

            <p class="text-xl mb-8 max-w-xl">
                Pasión, fútbol y familia. FC Cogote Salado sigue creciendo junto a su gente.
            </p>

            <a href="#"
               class="inline-block bg-purple-600 hover:bg-purple-700 px-8 py-4 rounded font-bold">
                Leer más
            </a>
        </div>
    </div>

</section>

<!-- =========================
     AVISOS (CARRUSEL SIMPLE)
========================= -->
<section class="bg-gray-800 py-20">
    <div class="max-w-7xl mx-auto px-6">

        <h2 class="text-3xl font-bold mb-10">
            Avisos
        </h2>

        <!-- Carrusel (estático por ahora) -->
        <div class="grid md:grid-cols-3 gap-6">

            <div class="bg-gray-700 rounded p-6">
                <h3 class="text-xl font-bold mb-2">Aviso 1</h3>
                <p class="text-sm text-gray-300">
                    Información importante del club.
                </p>
            </div>

            <div class="bg-gray-700 rounded p-6">
                <h3 class="text-xl font-bold mb-2">Aviso 2</h3>
                <p class="text-sm text-gray-300">
                    Próximo partido y horarios.
                </p>
            </div>

            <div class="bg-gray-700 rounded p-6">
                <h3 class="text-xl font-bold mb-2">Aviso 3</h3>
                <p class="text-sm text-gray-300">
                    Noticias internas del plantel.
                </p>
            </div>

        </div>

    </div>
</section>

<!-- =========================
     FOOTER
========================= -->
<footer class="bg-black py-10">
    <div class="max-w-7xl mx-auto px-6 text-center text-gray-400 text-sm">
        © {{ date('Y') }} FC Cogote Salado — Todos los derechos reservados
    </div>
</footer>

</body>
</html>
