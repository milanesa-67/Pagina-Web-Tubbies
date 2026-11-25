<?php
session_start();
include 'conexion.php';

if (!isset($conexion) && isset($pdo)) { $conexion =$pdo; }
if (!isset($conexion) && isset($conn)) { $conexion =$conn; }

if (isset($_SESSION['id']) && isset($conexion)) {
    $stmtSync =$conexion->prepare("SELECT rango_id, avatar FROM usuarios WHERE id = ?");
    $stmtSync->execute([$_SESSION['id']]);
    $datosSync =$stmtSync->fetch(PDO::FETCH_ASSOC);
    if ($datosSync) {$_SESSION['rango_id'] = (int)$datosSync['rango_id'];$_SESSION['avatar'] = !empty($datosSync['avatar']) ?$datosSync['avatar'] : 'avatar1';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teletubbies: El Despertar Oscuro - Inicio</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #0b0b0e;
            color: #e1e1e6;
        }

        nav {
            background-color: #121216;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 40px;
            border-bottom: 2px solid #2a2a35;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: bold;
            color: #ff3333;
            letter-spacing: 1px;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .nav-links a {
            text-decoration: none;
            font-weight: 600;
            padding: 8px 16px;
            border-radius: 4px;
            transition: opacity 0.3s;
        }

        .nav-links a:hover {
            opacity: 0.8;
        }

        .btn-inicio { color: #ffffff; background-color: #7c3aed; }
        .btn-comunidad { color: #ffffff; background-color: #10b981; }
        .btn-patreon { color: #ffffff; background-color: #f96854; }
        .btn-login { color: #0b0b0e; background-color: #facc15; }
        .btn-registro { color: #0b0b0e; background-color: #ffffff; }

        /* Colores oficiales para los avatares dinámicos (Puro color sin texto) */
        .av1 { background-color: #7c3aed !important; } /* Morado (Tinky Winky) */
        .av2 { background-color: #dc2626 !important; } /* Rojo (Po) */
        .av3 { background-color: #059669 !important; } /* Verde (Dipsy) */
        .av4 { background-color: #ca8a04 !important; } /* Amarillo (Laa-Laa) */
        .av5 { background-color: #374151 !important; } /* Oscuro / Gris */

        /* Perfil de usuario logueado */
        .user-profile-wrapper {
            position: relative;
        }

        .user-badge {
            display: flex;
            align-items: center;
            gap: 10px;
            background-color: #1f1f2e;
            border: 1px solid #ff3333;
            padding: 6px 14px;
            border-radius: 25px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .user-badge:hover {
            background-color: #2a2a3d;
        }

        .user-badge span {
            font-weight: bold;
            color: #ffffff;
        }

        .user-dropdown {
            display: none;
            position: absolute;
            right: 0;
            top: 52px;
            background-color: #16161c;
            border: 1px solid #2a2a35;
            border-radius: 8px;
            width: 220px;
            padding: 10px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.85);
            z-index: 2000;
        }

        .user-dropdown.active {
            display: block;
        }

        .dropdown-item {
            display: block;
            width: 100%;
            padding: 10px 12px;
            color: #b8b8c8;
            background-color: transparent;
            border: none;
            border-radius: 5px;
            text-align: left;
            text-decoration: none;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: not-allowed;
            margin-bottom: 5px;
        }

        .dropdown-item.config {
            color: #e1e1e6;
            background-color: #1f1f2e;
            cursor: pointer;
            transition: background 0.3s;
        }

        .dropdown-item.config:hover {
            background-color: #2a2a3d;
        }

        .btn-logout {
            display: block;
            text-align: center;
            width: 100%;
            padding: 10px;
            background-color: #ff3333;
            color: white;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            text-decoration: none;
            cursor: pointer;
            transition: background 0.3s;
        }

        .btn-logout:hover {
            background-color: #cc0000;
        }

        .banner {
            background: linear-gradient(135deg, rgba(18, 18, 22, 0.9), rgba(42, 10, 10, 0.8));
            text-align: center;
            padding: 100px 20px;
            border-bottom: 4px solid #ff3333;
        }

        .banner h1 {
            font-size: 3rem;
            color: #ffffff;
            margin-bottom: 15px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.8);
        }

        .banner p {
            font-size: 1.2rem;
            color: #b8b8c8;
            max-width: 700px;
            margin: 0 auto 30px auto;
        }

        .btn-descargar {
            background-color: #10b981;
            color: white;
            padding: 12px 30px;
            font-size: 1.1rem;
            font-weight: bold;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
            box-shadow: 0 4px 10px rgba(16, 185, 129, 0.3);
            transition: background 0.3s;
        }

        .btn-descargar:hover {
            background-color: #059669;
        }

        .container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .card {
            background-color: #16161c;
            border: 1px solid #2a2a35;
            padding: 30px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .card h2 {
            color: #ff3333;
            margin-bottom: 15px;
        }

        .card p {
            color: #b8b8c8;
            line-height: 1.6;
        }

        /* Modal de verificación de edad */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.9);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 3000;
        }

        .modal-content {
            background-color: #16161c;
            border: 2px solid #ff3333;
            padding: 35px;
            border-radius: 10px;
            max-width: 450px;
            width: 90%;
            text-align: center;
        }

        .modal-content h2 {
            color: #ff3333;
            margin-bottom: 15px;
            font-size: 1.5rem;
        }

        .modal-content p {
            color: #b8b8c8;
            margin-bottom: 20px;
            font-size: 0.95rem;
            line-height: 1.5;
        }

        .selectores-fecha {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-bottom: 25px;
        }

        .selectores-fecha select {
            background-color: #0b0b0e;
            color: white;
            border: 1px solid #2a2a35;
            padding: 10px;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
        }

        .btn-modal {
            background-color: #ff3333;
            color: white;
            border: none;
            padding: 12px 25px;
            font-size: 1rem;
            font-weight: bold;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            transition: background 0.3s;
        }

        .btn-modal:hover {
            background-color: #cc0000;
        }

        .error-edad {
            color: #ff3333;
            font-size: 0.85rem;
            margin-top: 10px;
            display: none;
        }
    </style>
</head>
<body>

    <div class="modal-overlay" id="edadModal">
        <div class="modal-content">
            <h2>⚠️ VERIFICACIÓN DE EDAD</h2>
            <p>Este juego independiente contiene material de terror y gore. Por favor, introduce tu fecha de nacimiento para continuar (Debes ser mayor de 13 años).</p>
            
            <div class="selectores-fecha">
                <select id="selectDia">
                    <option value="" disabled selected>Día</option>
                    <?php for($d = 1; $d <= 31; $d++): ?>
                        <option value="<?php echo $d; ?>"><?php echo $d; ?></option>
                    <?php endfor; ?>
                </select>

                <select id="selectMes">
                    <option value="" disabled selected>Mes</option>
                    <option value="1">Enero</option>
                    <option value="2">Febrero</option>
                    <option value="3">Marzo</option>
                    <option value="4">Abril</option>
                    <option value="5">Mayo</option>
                    <option value="6">Junio</option>
                    <option value="7">Julio</option>
                    <option value="8">Agosto</option>
                    <option value="9">Septiembre</option>
                    <option value="10">Octubre</option>
                    <option value="11">Noviembre</option>
                    <option value="12">Diciembre</option>
                </select>

                <select id="selectAnio">
                    <option value="" disabled selected>Año</option>
                    <?php 
                        $anioActual = date('Y');
                        for($a = $anioActual; $a >= $anioActual - 100; $a--): 
                    ?>
                        <option value="<?php echo $a; ?>"><?php echo $a; ?></option>
                    <?php endfor; ?>
                </select>
            </div>

            <button class="btn-modal" onclick="verificarEdad()">Verificar Edad</button>
            <p class="error-edad" id="errorEdad">Debes ser mayor de 13 años para acceder al sitio.</p>
        </div>
    </div>

    <nav>
        <div class="logo">RODENTWARE RESOLUTIONS</div>
        <div class="nav-links">
            <a href="index.php" class="btn-inicio">Inicio</a>
            <a href="comunidad.php" class="btn-comunidad">Comunidad</a>
            <a href="patreon.php" class="btn-patreon">Patreon</a>
            
            <?php if (isset($_SESSION['usuario'])): ?>
                <?php 
                    $rangoActual = isset($_SESSION['rango_id']) ?$_SESSION['rango_id'] : 1;
                    $esDev = ($rangoActual == 2);
                    
                    $avatarUser = isset($_SESSION['avatar']) ? $_SESSION['avatar'] : 'avatar1';$avatarClass = 'av1';
                    if ($avatarUser == 'avatar2') {$avatarClass = 'av2'; }
                    if ($avatarUser == 'avatar3') {$avatarClass = 'av3'; }
                    if ($avatarUser == 'avatar4') {$avatarClass = 'av4'; }
                    if ($avatarUser == 'avatar5') {$avatarClass = 'av5'; }

                    $borderColor =$esDev ? '#ff3333' : '#10b981';
                ?>
                <div class="user-profile-wrapper" id="userProfileWrapper">
                    <div class="user-badge" onclick="toggleDropdown()" style="display: flex; align-items: center; gap: 10px; background-color: #1f1f2e; border: 1px solid <?php echo $borderColor; ?>; padding: 6px 14px; border-radius: 25px; cursor: pointer;">
                        <div class="avatar-box <?php echo $avatarClass; ?>" style="width: 34px; height: 34px; border-radius: 50%; border: 2px solid <?php echo $borderColor; ?>;"></div>
                        <span style="font-weight: bold; color: #ffffff;"><?php echo ($esDev ? '🤖 ' : '👤 '); ?><?php echo htmlspecialchars($_SESSION['usuario']); ?></span>
                    </div>
                    <div class="user-dropdown" id="userDropdown">
                        <?php if ($esDev): ?>
                            <a href="admin.php" class="dropdown-item config" style="color: #ff3333; font-weight: bold;">🛠️ Panel Admin</a>
                        <?php endif; ?>
                        <a href="#" class="dropdown-item config">⚙️ Configuración</a>
                        <div class="dropdown-item" style="font-size: 0.8rem; color: #6b6b80; padding-top: 0; margin-bottom: 8px;">(Próximamente)</div>
                        <a href="logout.php" class="btn-logout">Cerrar Sesión</a>
                    </div>
                </div>
            <?php else: ?>
                <a href="login.php" class="btn-login">Iniciar Sesión</a>
                <a href="registro.php" class="btn-registro">Registrarse</a>
            <?php endif; ?>
        </div>
    </nav>

    <header class="banner">
        <h1>Teletubbies: El Despertar Oscuro</h1>
        <p>La plataforma oficial de la comunidad y gestión del videojuego de terror independiente. Descargá el juego, reportá fallas y enterate de las últimas novedades.</p>
        <a href="#" class="btn-descargar">Descargar Videojuego</a>
    </header>

    <div class="container">
        <div class="card">
            <h2>Acerca del Proyecto</h2>
            <p>Un grupo de desarrolladores independientes está creando este videojuego de terror basado en la temática de los Teletubbies bajo el sello de Rodentware Resolutions. Esta plataforma web centraliza el registro de jugadores, la descarga oficial, el sistema de reportes de errores y la gestión comunitaria.</p>
        </div>
    </div>

    <script>
        window.onload = function() {
            if (sessionStorage.getItem('edadVerificada') === 'true') {
                document.getElementById('edadModal').style.display = 'none';
            }
        };

        function verificarEdad() {
            const dia = document.getElementById('selectDia').value;
            const mes = document.getElementById('selectMes').value;
            const anio = document.getElementById('selectAnio').value;
            const errorMsg = document.getElementById('errorEdad');

            if(!dia || !mes || !anio) {
                errorMsg.style.display = 'block';
                errorMsg.innerText = "Por favor, completa todos los campos de la fecha.";
                return;
            }

            const fechaNacimiento = new Date(anio, mes - 1, dia);
            const hoy = new Date();
            let edad = hoy.getFullYear() - fechaNacimiento.getFullYear();
            const m = hoy.getMonth() - fechaNacimiento.getMonth();
            
            if (m < 0 || (m === 0 && hoy.getDate() < fechaNacimiento.getDate())) {
                edad--;
            }

            if (edad >= 13) {
                sessionStorage.setItem('edadVerificada', 'true');
                document.getElementById('edadModal').style.display = 'none';
            } else {
                errorMsg.style.display = 'block';
                errorMsg.innerText = "Lo sentimos, debes ser mayor de 13 años para ingresar.";
            }
        }

        function toggleDropdown() {
            const dropdown = document.getElementById('userDropdown');
            if (dropdown) {
                dropdown.classList.toggle('active');
            }
        }

        window.onclick = function(event) {
            if (!event.target.closest('.user-profile-wrapper')) {
                const dropdown = document.getElementById('userDropdown');
                if (dropdown && dropdown.classList.contains('active')) {
                    dropdown.classList.remove('active');
                }
            }
        };
    </script>
</body>
</html>