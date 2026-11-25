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

$mensajeAlerta = "";
$alertaChat = "";

if (!isset($_SESSION['usuario']) && !isset($_SESSION['mensajes_invitado'])) {
    $_SESSION['mensajes_invitado'] = 5;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['descripcion_reporte'])) {
    if (!isset($_SESSION['id'])) {
        $mensajeAlerta = "Debes iniciar sesión para enviar un reporte.";
    } else {
        $descripcion = trim($_POST['descripcion_reporte']);
        if (!empty($descripcion)) {
            $stmt = $conexion->prepare("INSERT INTO reportes (descripcion, id_usuario) VALUES (?, ?)");
            $stmt->execute([$descripcion, $_SESSION['id']]);
            $mensajeAlerta = "¡Reporte enviado con éxito!";
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nuevo_mensaje_chat'])) {
    $textoChat = trim($_POST['nuevo_mensaje_chat']);
    if (!empty($textoChat)) {
        if (!isset($_SESSION['usuario'])) {
            if ($_SESSION['mensajes_invitado'] > 0) {
                $_SESSION['mensajes_invitado']--;
                $nombreUsuario = "Invitado";
                $rangoIdUsuario = 1;
                
                $stmtChat = $conexion->prepare("INSERT INTO chat (usuario, mensaje, rango_id) VALUES (?, ?, ?)");
                $stmtChat->execute([$nombreUsuario, $textoChat, $rangoIdUsuario]);
                
                if ($_SESSION['mensajes_invitado'] > 0) {
                    $alertaChat = "Mensaje enviado. Te quedan " . $_SESSION['mensajes_invitado'] . " mensajes gratis como invitado.";
                } else {
                    $alertaChat = "Has agotado tus 5 mensajes como invitado. ¡Registrate para chatear sin límites!";
                }
            } else {
                $alertaChat = "Límite alcanzado. No podés enviar más mensajes sin registrarte.";
            }
        } else {
            $nombreUsuario = $_SESSION['usuario'];
            
            // Consultamos el rango_id actual directamente de la base de datos
            $stmtRango = $conexion->prepare("SELECT rango_id FROM usuarios WHERE id = ?");
            $stmtRango->execute([$_SESSION['id']]);
            $datosUsuario = $stmtRango->fetch(PDO::FETCH_ASSOC);
            
            $rangoIdUsuario = ($datosUsuario && isset($datosUsuario['rango_id'])) ? $datosUsuario['rango_id'] : 1;
            $_SESSION['rango_id'] = $rangoIdUsuario; // Actualizamos la sesión también
            
            $stmtChat = $conexion->prepare("INSERT INTO chat (usuario, mensaje, rango_id) VALUES (?, ?, ?)");
            $stmtChat->execute([$nombreUsuario, $textoChat, $rangoIdUsuario]);
        }
        
        $_SESSION['alerta_chat'] = $alertaChat;
        header("Location: comunidad.php");
        exit();
    }
}

if (isset($_SESSION['alerta_chat'])) {
    $alertaChat = $_SESSION['alerta_chat'];
    unset($_SESSION['alerta_chat']);
}

$stmtMensajes = $conexion->query("SELECT * FROM chat ORDER BY id ASC");
$mensajesChat = $stmtMensajes->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teletubbies: El Despertar Oscuro - Comunidad</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #0b0b0e; color: #e1e1e6; }
        nav { background-color: #121216; display: flex; justify-content: space-between; align-items: center; padding: 20px 40px; border-bottom: 2px solid #2a2a35; }
        .logo { font-size: 1.5rem; font-weight: bold; color: #ff3333; letter-spacing: 1px; }
        .nav-links { display: flex; align-items: center; gap: 15px; }
        .nav-links a { text-decoration: none; font-weight: 600; padding: 8px 16px; border-radius: 4px; transition: opacity 0.3s; }
        .nav-links a:hover { opacity: 0.8; }
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
        .user-profile-wrapper { position: relative; }
        .user-badge { display: flex; align-items: center; gap: 10px; background-color: #1f1f2e; border: 1px solid #ff3333; padding: 6px 14px; border-radius: 25px; cursor: pointer; transition: background 0.3s; }
        .user-badge:hover { background-color: #2a2a3d; }
        .user-badge span { font-weight: bold; color: #ffffff; }

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
        .user-dropdown.active { display: block; }

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
        .btn-logout:hover { background-color: #cc0000; }

        .container { max-width: 1000px; margin: 40px auto; padding: 0 20px; }
        .seccion-titulo { color: #ff3333; margin-bottom: 20px; font-size: 1.8rem; border-bottom: 2px solid #2a2a35; padding-bottom: 10px; }
        .chat-box, .reporte-box { background-color: #16161c; border: 1px solid #2a2a35; border-radius: 8px; padding: 25px; margin-bottom: 40px; }
        .mensajes-container { height: 250px; background-color: #0b0b0e; border: 1px solid #2a2a35; border-radius: 5px; padding: 15px; overflow-y: auto; margin-bottom: 15px; display: flex; flex-direction: column; gap: 10px; }
        
        .mensaje { background-color: #1f1f2e; padding: 10px; border-radius: 5px; max-width: 80%; }
        .mensaje.dev { background-color: #3b1111; border-left: 4px solid #ff3333; align-self: flex-start; }

        .mensaje-input-group { display: flex; gap: 10px; }
        .mensaje-input-group input { flex: 1; background-color: #0b0b0e; border: 1px solid #2a2a35; border-radius: 5px; padding: 12px; color: white; font-size: 1rem; }
        .mensaje-input-group button { background-color: #ff3333; color: white; border: none; padding: 0 20px; border-radius: 5px; font-weight: bold; cursor: pointer; }
        .mensaje-input-group button:hover { background-color: #cc0000; }
        .mensaje-input-group button:disabled { background-color: #4a4a5a; cursor: not-allowed; }
        .reporte-box p { color: #b8b8c8; margin-bottom: 15px; }
        .reporte-box textarea { width: 100%; height: 100px; background-color: #0b0b0e; border: 1px solid #2a2a35; border-radius: 5px; padding: 12px; color: white; font-size: 1rem; margin-bottom: 15px; resize: vertical; }
        .btn-reportar { background-color: #ff3333; color: white; border: none; padding: 12px 25px; border-radius: 5px; font-weight: bold; cursor: pointer; }
        .btn-reportar:hover { background-color: #cc0000; }
        .changelog-card { background-color: #16161c; border: 1px solid #2a2a35; border-radius: 8px; padding: 25px; margin-bottom: 20px; }
        .changelog-card h3 { color: #10b981; margin-bottom: 10px; }
        .changelog-card ul { list-style-type: square; padding-left: 20px; color: #b8b8c8; line-height: 1.6; }
        .alerta-exito { color: #10b981; background: rgba(16,185,129,0.1); border: 1px solid #10b981; padding: 10px; border-radius: 5px; margin-bottom: 15px; }
        .alerta-aviso { color: #facc15; background: rgba(250,204,21,0.1); border: 1px solid #facc15; padding: 10px; border-radius: 5px; margin-bottom: 15px; }
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
        
        <h2 class="seccion-titulo">💬 Conversación con Desarrolladores</h2>
        <div class="chat-box">
            
            <?php if (!empty($alertaChat)): ?>
                <div class="alerta-aviso"><?php echo $alertaChat; ?></div>
            <?php elseif (!isset($_SESSION['usuario'])): ?>
                <div class="alerta-aviso">Aviso: Estás chateando como invitado. Tenés un límite de 5 mensajes disponibles.</div>
            <?php endif; ?>

            <div class="mensajes-container" id="chatMensajes">
                <div class="mensaje dev">
                    <strong style="color: #ff3333;">[DEV] Rodentware:</strong> ¡Hola comunidad! Dejen por acá sus consultas o sugerencias sobre el juego.
                </div>
                <div class="mensaje">
                    <strong>Usuario_67:</strong> ¿Cuándo sale el próximo mapa del laboratorio secreto?
                </div>
                <div class="mensaje dev">
                    <strong style="color: #ff3333;">[DEV] Rodentware:</strong> ¡Estamos puliendo los últimos detalles para la beta de la próxima semana!
                </div>

                <?php if (!empty($mensajesChat)): ?>
                    <?php foreach ($mensajesChat as $m): ?>
                        <?php 
                            $esDev = (isset($m['rango_id']) && $m['rango_id'] == 2);
                        ?>
                        <div class="mensaje <?php echo $esDev ? 'dev' : ''; ?>">
                            <?php if ($esDev): ?>
                                <strong style="color: #ff3333;">[DEV] <?php echo htmlspecialchars($m['usuario']); ?>:</strong>
                            <?php else: ?>
                                <strong><?php echo htmlspecialchars($m['usuario']); ?>:</strong>
                            <?php endif; ?>
                            <?php echo htmlspecialchars($m['mensaje']); ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <form method="POST" action="comunidad.php" class="mensaje-input-group">
                <input type="text" name="nuevo_mensaje_chat" placeholder="<?php echo (!isset($_SESSION['usuario']) && $_SESSION['mensajes_invitado'] <= 0) ? 'Límite alcanzado. Registrate para continuar...' : 'Escribí tu mensaje para los desarrolladores...'; ?>" <?php echo (!isset($_SESSION['usuario']) && $_SESSION['mensajes_invitado'] <= 0) ? 'disabled' : 'required'; ?> autocomplete="off">
                <button type="submit" <?php echo (!isset($_SESSION['usuario']) && $_SESSION['mensajes_invitado'] <= 0) ? 'disabled' : ''; ?>>Enviar</button>
            </form>
        </div>

        <h2 class="seccion-titulo">🐞 Reportar Fallas del Videojuego</h2>
        <div class="reporte-box">
            <?php if (!empty($mensajeAlerta)): ?>
                <div class="alerta-exito"><?php echo $mensajeAlerta; ?></div>
            <?php endif; ?>
            <p>¿Encontraste algún bug o error jugando? Envianos tu reporte (máximo 5 reportes por día por usuario).</p>
            <form method="POST" action="comunidad.php">
                <textarea name="descripcion_reporte" placeholder="Describí el error que encontraste y en qué parte del mapa ocurrió..." required></textarea>
                <button type="submit" class="btn-reportar">Enviar Reporte</button>
            </form>
        </div>

        <h2 class="seccion-titulo">🛠️ Actualizaciones y Registro de Parches</h2>
        
        <div class="changelog-card">
            <h3>Parche v1.0.4 (Última Actualización)</h3>
            <ul>
                <li>Se solucionó el bug crítico donde el personaje principal atravesaba las paredes del mapa oscuro.</li>
                <li>Optimización de texturas y reducción de tiempos de carga en el servidor local.</li>
                <li>Se ajustó la dificultad de la inteligencia artificial de los enemigos.</li>
            </ul>
        </div>

        <div class="changelog-card">
            <h3>Parche v1.0.3</h3>
            <ul>
                <li>Corrección de errores en la pantalla de inicio de sesión.</li>
                <li>Se arregló un fallo de sonido al activar la linterna dentro del juego.</li>
            </ul>
        </div>

    </div>

    <script>
        const contenedor = document.getElementById('chatMensajes');
        contenedor.scrollTop = contenedor.scrollHeight;

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