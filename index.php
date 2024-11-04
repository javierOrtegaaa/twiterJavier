<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" integrity="sha512-iBBXm8fW90+nuLcSKlbmrPcLa0OT92xO1BIsZ+ywDWZCvqsWgccV3gFoRBv0z+8dLJgyAHIhR35VZc2oM/gI1w==" crossorigin="anonymous" />
    <title>Página Principal</title>
</head>
<body class="flex items-center justify-center min-h-screen bg-cover bg-center" style="background-image: url('img/twitterFondo.jpg');">
    <div class="absolute inset-0 bg-black opacity-70"></div>

    <div class="relative z-10 bg-white bg-opacity-10 shadow-lg backdrop-blur-md p-8 rounded-lg w-80 text-center">
        <h1 class="text-white text-3xl font-semibold mb-8">Iniciar Sesión</h1>
        <form action="scripts/login.php" method="POST">
            <div class="flex items-center bg-white bg-opacity-90 mb-4 rounded overflow-hidden">
                <span class="text-gray-600 px-3"><i class="fa fa-user"></i></span>
                <input type="text" name="username" placeholder="Email o Teléfono" required class="w-full py-2 px-2 text-gray-700 focus:outline-none">
            </div>
            <div class="flex items-center bg-white bg-opacity-90 mb-4 rounded overflow-hidden relative">
                <span class="text-gray-600 px-3"><i class="fa fa-lock"></i></span>
                <input type="password" name="password" placeholder="Contraseña" required class="w-full py-2 px-2 text-gray-700 focus:outline-none">
                <span class="text-xs font-semibold absolute right-3 top-3 cursor-pointer text-gray-600">SHOW</span>
            </div>
            <div class="text-left mb-4">
                <a href="#" class="text-white text-sm hover:underline">¿Olvidaste tu contraseña?</a>
            </div>
            <button type="submit" class="w-full py-2 text-lg bg-blue-500 text-white rounded hover:bg-blue-600">INICIAR SESIÓN</button>
        </form>

        <p class="text-white mt-6">O inicia sesión con</p>
        <div class="flex justify-around mt-4">
            <div class="flex items-center justify-center bg-blue-700 w-1/2 mr-1 py-2 rounded text-white cursor-pointer hover:bg-blue-800">
                <i class="fab fa-facebook-f mr-2"></i> Facebook
            </div>
            <div class="flex items-center justify-center bg-pink-600 w-1/2 ml-1 py-2 rounded text-white cursor-pointer hover:bg-pink-700">
                <i class="fab fa-instagram mr-2"></i> Instagram
            </div>
        </div>
        <p class="text-white mt-6 text-sm">¿No tienes una cuenta? <a href="../scripts/registro.php" class="text-blue-400 hover:underline">Regístrate ahora</a></p>
    </div>

    <footer class="absolute bottom-0 w-full bg-gray-800 py-4 text-white text-center">
        <p>&copy; 2021 Todos los Derechos Reservados</p>
        <div class="flex justify-center mt-2 space-x-4">
            <a href="https://github.com/javierOrtegaaa" target="_blank" class="text-white hover:text-gray-400"><i class="fab fa-github"></i></a>
            <a href="https://www.linkedin.com/in/javier-ortega-reina-419455303?utm_source=share&utm_campaign=share_via&utm_content=profile&utm_medium=ios_app" target="_blank" class="text-white hover:text-gray-400"><i class="fab fa-linkedin"></i></a>
            <a href="https://www.instagram.com/oortega05_/profilecard/?igsh=Z2h3azF1dmV2ZmUx" target="_blank" class="text-white hover:text-gray-400"><i class="fab fa-instagram"></i></a>
           
    </footer>
</body>
</html> 
