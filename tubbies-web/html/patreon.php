<?php
session_start();
include 'conexion.php';

// Si tu conexión usa otro nombre (como $pdo o $conn), la unificamos aquí:
if (!isset($conexion) && isset($pdo)) { $conexion = $pdo; }
if (!isset($conexion) && isset($conn)) { $conexion = $conn; }

// Sincronización automática del rango y avatar en tiempo real
if (isset($_SESSION['id'])) {
    $stmtSync = $conexion->prepare("SELECT rango_id, avatar FROM usuarios WHERE id = ?");
    $stmtSync->execute([$_SESSION['id']]);
    $datosSync = $stmtSync->fetch(PDO::FETCH_ASSOC);
    if ($datosSync) {
        $_SESSION['rango_id'] = (int)$datosSync['rango_id'];
        $_SESSION['avatar'] = !empty($datosSync['avatar']) ? $datosSync['avatar'] : 'avatar1';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teletubbies: El Despertar Oscuro - Patreon</title>
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

        /* Perfil de usuario logueado en la barra de navegación */
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

        .container {
            max-width: 800px;
            margin: 60px auto;
            padding: 40px;
            background-color: #16161c;
            border: 1px solid #2a2a35;
            border-radius: 8px;
            text-align: center;
        }

        h1 {
            color: #f96854;
            margin-bottom: 20px;
        }

        p {
            color: #b8b8c8;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .btn-apoyar {
            background-color: #f96854;
            color: white;
            padding: 12px 30px;
            font-size: 1.1rem;
            font-weight: bold;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
            transition: background 0.3s;
        }

        .btn-apoyar:hover {
            background-color: #e04f3d;
        }
    </style>
</head>
<body>

    <nav>
        <div class="logo">RODENTWARE RESOLUTIONS</div>
        <div class="nav-links">
            <a href="index.php" class="btn-inicio">Inicio</a>
            <a href="comunidad.php" class="btn-comunidad">Comunidad</a>
            <a href="patreon.php" class="btn-patreon">Patreon</a>
            
            <?php if (isset($_SESSION['usuario'])): ?>
                <?php 
                    $rangoActual = isset($_SESSION['rango_id']) ? $_SESSION['rango_id'] : 1;
                    $esDev = ($rangoActual == 2);
                    
                    $avatarUser = isset($_SESSION['avatar']) ? $_SESSION['avatar'] : 'avatar1';
                    
                    $avatarClass = 'av1';
                    if ($avatarUser == 'avatar2') { $avatarClass = 'av2'; }
                    if ($avatarUser == 'avatar3') { $avatarClass = 'av3'; }
                    if ($avatarUser == 'avatar4') { $avatarClass = 'av4'; }
                    if ($avatarUser == 'avatar5') { $avatarClass = 'av5'; }

                    // Definimos el color del borde dinámico: Rojo para Dev (#ff3333), Verde para Usuario (#10b981)
                    $borderColor = $esDev ? '#ff3333' : '#10b981';
                ?>
                <div class="user-profile-wrapper" id="userProfileWrapper">
                    <div class="user-badge" onclick="toggleDropdown()" style="display: flex; align-items: center; gap: 10px; background-color: #1f1f2e; border: 1px solid <?php echo $borderColor; ?>; padding: 6px 14px; border-radius: 25px; cursor: pointer;">
                        <!-- Círculo de avatar dinámico (Solo color puro, sin texto) -->
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

    <div class="container">
        <h1>Apoyanos en Patreon</h1>
        <p>¡Convertite en mecenas del desarrollo independiente! Con tu aporte nos ayudás a mantener el proyecto de Rodentware Resolutions, mejorar los diseños y sumar contenido exclusivo al videojuego de terror.</p>
        <a href="#" class="btn-apoyar">Unirse a Patreon</a>
    </div>

    <script>
        function toggleDropdown() {
            const dropdown = document.getElementById('userDropdown');
            if (dropdown) { dropdown.classList.toggle('active'); }
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