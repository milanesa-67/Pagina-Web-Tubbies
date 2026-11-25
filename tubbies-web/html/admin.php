<?php
session_start();
include 'conexion.php';

// Asegurar compatibilidad con la variable de conexión
if (!isset($conexion) && isset($pdo)) { $conexion = $pdo; }
if (!isset($conexion) && isset($conn)) { $conexion = $conn; }

// Verificar si el usuario es Administrador / Desarrollador (rango_id == 2)
$esAdmin = (isset($_SESSION['rango_id']) && (int)$_SESSION['rango_id'] === 2);

// Lógica para eliminar usuario si se envía una petición con ID
if ($esAdmin && isset($_GET['eliminar'])) {
    $idEliminar = (int)$_GET['eliminar'];
    
    // Evitar que el admin se borre a sí mismo por accidente
    if ($idEliminar !== (int)$_SESSION['id']) {
        $stmtDel = $conexion->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmtDel->execute([$idEliminar]);
        header("Location: admin.php");
        exit();
    }
}

// Obtener la lista de usuarios si es admin
$usuariosBD = [];
if ($esAdmin) {
    $stmtUsuarios = $conexion->query("SELECT id, nombre, mail, rango_id FROM usuarios");
    $usuariosBD = $stmtUsuarios->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - Base de Datos</title>
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
        }

        .container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .admin-card {
            background-color: #16161c;
            border: 1px solid #ff3333;
            padding: 30px;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        h2 {
            color: #ff3333;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #2a2a35;
        }

        th {
            background-color: #1f1f2e;
            color: #ff3333;
        }

        .btn-eliminar {
            background-color: #ff3333;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
        }

        .btn-eliminar:hover {
            background-color: #cc0000;
        }

        .acceso-denegado {
            text-align: center;
            margin-top: 100px;
            color: #ff3333;
            font-size: 1.5rem;
        }
    </style>
</head>
<body>

    <nav>
        <div class="logo">RODENTWARE - PANEL ADMIN</div>
        <div><a href="index.php" style="color: white; text-decoration: none; font-weight: bold;">Volver al Inicio</a></div>
    </nav>

    <?php if ($esAdmin): ?>
        <div class="container" id="panelAdmin">
            <div class="admin-card">
                <h2>Administrador Web de Base de Datos (Usuarios)</h2>
                <p>Desde este panel web conectado a MySQL puedes visualizar los registros actuales y gestionar las cuentas de la comunidad.</p>
                
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Usuario</th>
                            <th>Correo Electrónico</th>
                            <th>Rango ID</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuariosBD as $u): ?>
                            <tr>
                                <td><?php echo $u['id']; ?></td>
                                <td><?php echo htmlspecialchars($u['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($u['mail']); ?></td>
                                <td>
                                    <?php if ((int)$u['rango_id'] === 2): ?>
                                        <span style="color: #ff3333; font-weight: bold;">2. Desarrollador 🤖</span>
                                    <?php else: ?>
                                        <span style="color: #10b981; font-weight: bold;"><?php echo $u['rango_id']; ?>. Usuario 👤</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ((int)$u['id'] !== (int)$_SESSION['id']): ?>
                                        <a href="admin.php?eliminar=<?php echo $u['id']; ?>" class="btn-eliminar" onclick="return confirm('¿Estás seguro de eliminar este usuario?');">Eliminar</a>
                                    <?php else: ?>
                                        <span style="color: #facc15; font-weight: bold;">(Tu cuenta)</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else: ?>
        <div class="container" id="accesoDenegado">
            <div class="acceso-denegado">
                <h2>ACCESO DENEGADO</h2>
                <p>No tienes permisos de Administrador (Rango Desarrollador) para ver esta sección.</p>
                <p style="margin-top: 20px;"><a href="index.php" style="color: #10b981; text-decoration: none; font-weight: bold;">Volver al inicio</a></p>
            </div>
        </div>
    <?php endif; ?>

</body>
</html>