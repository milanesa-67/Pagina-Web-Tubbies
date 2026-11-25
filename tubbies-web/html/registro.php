<?php
session_start();
include 'conexion.php';

if (!isset($conexion) && isset($pdo)) { $conexion = $pdo; }
if (!isset($conexion) && isset($conn)) { $conexion = $conn; }

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['usuario']);
    $mail = trim($_POST['email']);
    $passwordPlana = $_POST['password']; 
    $fechaNacimiento = $_POST['fecha_nacimiento'];
    $avatar = isset($_POST['avatar']) ? $_POST['avatar'] : 'avatar1';

    if (!empty($nombre) && !empty($mail) && !empty($passwordPlana) && !empty($fechaNacimiento)) {
        $stmtCheck = $conexion->prepare("SELECT id FROM usuarios WHERE mail = ?");
        $stmtCheck->execute([$mail]);
        
        if ($stmtCheck->rowCount() > 0) {
            $error = "El correo electrónico ya está registrado.";
        } else {
            // Generamos el hash seguro de la contraseña con PHP
            $passwordSegura = password_hash($passwordPlana, PASSWORD_DEFAULT);

            // Inserción segura con el hash generado
            $stmt = $conexion->prepare("INSERT INTO usuarios (nombre, mail, contrasena, fecha_nacimiento, avatar, rango_id) VALUES (?, ?, ?, ?, ?, 1)");
            
            if ($stmt->execute([$nombre, $mail, $passwordSegura, $fechaNacimiento, $avatar])) {
                header("Location: login.php");
                exit();
            } else {
                $error = "Error al registrar la cuenta.";
            }
        }
    } else {
        $error = "Por favor, completa todos los campos.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teletubbies: El Despertar Oscuro - Registro</title>
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

        .container-form {
            max-width: 520px;
            margin: 40px auto;
            background-color: #16161c;
            border: 1px solid #2a2a35;
            padding: 40px;
            border-radius: 8px;
        }
        h2 { color: #ff3333; margin-bottom: 25px; text-align: center; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; color: #b8b8c8; font-weight: 600; }
        input[type="text"], input[type="email"], input[type="password"], input[type="date"] {
            width: 100%; padding: 12px; background-color: #0b0b0e; border: 1px solid #2a2a35; border-radius: 5px; color: white; font-size: 1rem;
        }

        .avatar-selector {
            display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; margin-top: 10px;
        }
        .avatar-option { cursor: pointer; text-align: center; display: inline-block; }
        .avatar-option input { display: none; }
        .avatar-option .avatar-box {
            width: 50px; height: 50px; border-radius: 50%; border: 2px solid #2a2a35; transition: all 0.3s ease;
        }
        .avatar-option input:checked + .avatar-box {
            border-color: #ff3333; transform: scale(1.1); box-shadow: 0 0 12px rgba(255, 51, 51, 0.7);
        }
        /* Colores oficiales puros sin texto */
        .av1 { background-color: #7c3aed !important; }
        .av2 { background-color: #dc2626 !important; }
        .av3 { background-color: #059669 !important; }
        .av4 { background-color: #ca8a04 !important; }
        .av5 { background-color: #374151 !important; }

        .btn-submit {
            width: 100%; background-color: #ff3333; color: white; padding: 12px; border: none; border-radius: 5px; font-size: 1.1rem; font-weight: bold; cursor: pointer; margin-top: 10px; transition: background 0.3s;
        }
        .btn-submit:hover { background-color: #cc0000; }
        .error-registro {
            color: #ff3333; background-color: rgba(255, 51, 51, 0.1); border: 1px solid #ff3333; padding: 10px; border-radius: 5px; font-size: 0.9rem; margin-bottom: 15px; text-align: center;
        }
        .link-extra { text-align: center; margin-top: 20px; color: #b8b8c8; font-size: 0.9rem; }
        .link-extra a { color: #ff3333; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>

    <nav>
        <div class="logo">RODENTWARE RESOLUTIONS</div>
        <div class="nav-links">
            <a href="index.php" class="btn-inicio">Inicio</a>
            <a href="comunidad.php" class="btn-comunidad">Comunidad</a>
            <a href="patreon.php" class="btn-patreon">Patreon</a>
            <a href="login.php" class="btn-login">Iniciar Sesión</a>
            <a href="registro.php" class="btn-registro">Registrarse</a>
        </div>
    </nav>

    <div class="container-form">
        <h2>Crear Cuenta de Jugador</h2>
        
        <?php if (!empty($error)): ?>
            <div class="error-registro"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="registro.php">
            <div class="form-group">
                <label for="usuario">Nombre de Usuario:</label>
                <input type="text" id="usuario" name="usuario" required placeholder="Ej. TinkyWinky_Dark">
            </div>

            <div class="form-group">
                <label for="email">Correo Electrónico:</label>
                <input type="email" id="email" name="email" required placeholder="correo@ejemplo.com">
            </div>

            <div class="form-group">
                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password" required placeholder="********">
            </div>

            <div class="form-group">
                <label for="fecha_nacimiento">Fecha de Nacimiento (Mayor de 13 años):</label>
                <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" required>
            </div>

            <div class="form-group">
                <label>Elegí tu Avatar Oficial:</label>
                <div class="avatar-selector">
                    <label class="avatar-option">
                        <input type="radio" name="avatar" value="avatar1" checked>
                        <div class="avatar-box av1"></div>
                    </label>
                    <label class="avatar-option">
                        <input type="radio" name="avatar" value="avatar2">
                        <div class="avatar-box av2"></div>
                    </label>
                    <label class="avatar-option">
                        <input type="radio" name="avatar" value="avatar3">
                        <div class="avatar-box av3"></div>
                    </label>
                    <label class="avatar-option">
                        <input type="radio" name="avatar" value="avatar4">
                        <div class="avatar-box av4"></div>
                    </label>
                    <label class="avatar-option">
                        <input type="radio" name="avatar" value="avatar5">
                        <div class="avatar-box av5"></div>
                    </label>
                </div>
            </div>

            <button type="submit" class="btn-submit">Registrarse</button>
        </form>
        
        <div class="link-extra">
            ¿Ya tenés cuenta? <a href="login.php">Iniciá sesión acá</a>
        </div>
    </div>
</body>
</html>