<?php
session_start();
include 'conexion.php';

// Asegurar compatibilidad con la variable de conexión ($conexion, $pdo o $conn)
if (!isset($conexion) && isset($pdo)) { $conexion = $pdo; }
if (!isset($conexion) && isset($conn)) { $conexion = $conn; }

$errorLogin = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Buscamos al usuario únicamente por el correo electrónico
    $stmt = $conexion->prepare("SELECT id, nombre, mail, contrasena, rango_id, avatar FROM usuarios WHERE mail = ?");
    $stmt->execute([$email]);
    $usuarioEncontrado = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verificamos si el usuario existe y si la contraseña coincide con el hash seguro
    if ($usuarioEncontrado && password_verify($password, $usuarioEncontrado['contrasena'])) {
        $_SESSION['usuario'] = $usuarioEncontrado['nombre'];
        $_SESSION['email'] = $usuarioEncontrado['mail'];
        $_SESSION['id'] = $usuarioEncontrado['id'];
        $_SESSION['rango_id'] = (int)$usuarioEncontrado['rango_id'];
        $_SESSION['avatar'] = !empty($usuarioEncontrado['avatar']) ? $usuarioEncontrado['avatar'] : 'avatar1';
        
        header("Location: index.php");
        exit();
    } else {
        $errorLogin = true;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teletubbies: El Despertar Oscuro - Iniciar Sesión</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }
        body { background-color: #0b0b0e; color: #e1e1e6; }
        nav { background-color: #121216; display: flex; justify-content: space-between; align-items: center; padding: 20px 40px; border-bottom: 2px solid #2a2a35; }
        .logo { font-size: 1.5rem; font-weight: bold; color: #ff3333; letter-spacing: 1px; }
        .nav-links { display: flex; align-items: center; gap: 15px; }
        .nav-links a { text-decoration: none; font-weight: 600; padding: 8px 16px; border-radius: 4px; }
        .btn-inicio { color: #ffffff; background-color: #7c3aed; }
        .btn-comunidad { color: #ffffff; background-color: #10b981; }
        .btn-patreon { color: #ffffff; background-color: #f96854; }
        .btn-login { color: #0b0b0e; background-color: #facc15; }
        .btn-registro { color: #0b0b0e; background-color: #ffffff; }
        .container-form { max-width: 400px; margin: 60px auto; background-color: #16161c; border: 1px solid #2a2a35; padding: 40px; border-radius: 8px; }
        h2 { color: #ff3333; margin-bottom: 25px; text-align: center; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; color: #b8b8c8; font-weight: 600; }
        input[type="email"], input[type="password"] { width: 100%; padding: 12px; background-color: #0b0b0e; border: 1px solid #2a2a35; border-radius: 5px; color: white; font-size: 1rem; }
        .btn-submit { width: 100%; background-color: #10b981; color: white; padding: 12px; border: none; border-radius: 5px; font-size: 1.1rem; font-weight: bold; cursor: pointer; margin-top: 10px; }
        .btn-submit:hover { background-color: #059669; }
        .error-login { color: #ff3333; background-color: rgba(255, 51, 51, 0.1); border: 1px solid #ff3333; padding: 10px; border-radius: 5px; font-size: 0.9rem; margin-bottom: 15px; text-align: center; <?php echo $errorLogin ? '' : 'display:none;'; ?> }
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
        <h2>Iniciar Sesión</h2>
        <div class="error-login">Correo o contraseña incorrectos.</div>
        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="email">Correo Electrónico:</label>
                <input type="email" id="email" name="email" required placeholder="correo@ejemplo.com">
            </div>
            <div class="form-group">
                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password" required placeholder="********">
            </div>
            <button type="submit" class="btn-submit">Entrar</button>
        </form>
        <div class="link-extra">
            ¿Todavía no tenés cuenta? <a href="registro.php">Registrate acá</a>
        </div>
    </div>
</body>
</html>