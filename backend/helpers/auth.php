<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

/**
 * Verifica si el usuario está logueado
 * @return bool
 */
function isLoggedIn()
{
  // error_log("isLoggedIn - SESSION: " . print_r($_SESSION, true));
  return isset($_SESSION["user"]) && !empty($_SESSION["user"]);
}

/**
 * Obtiene el rol del usuario actual
 * @return int|null
 */
function getUserRoleId()
{
  if (!isLoggedIn()) {
    // error_log("getUserRoleId: Usuario no está logueado");
    return null;
  }

  $roleId = $_SESSION["user"]["role_id"] ?? null;

  if ($roleId !== null) {
    // error_log("getUserRoleId - Role ID: " . $roleId);
    return $roleId;
  }

  // error_log("getUserRoleId: No se encontró el rol del usuario");
  return null;
}

/**
 * Protege una vista para que solo sea accesible por ciertos roles
 * @param array $rolesPermitidos Array de IDs de roles permitidos
 * @return bool
 */
function protegerVistaPorRoles($rolesPermitidos)
{
  // error_log("protegerVistaPorRoles - Roles permitidos: " . implode(", ", $rolesPermitidos));
  // error_log("protegerVistaPorRoles - SESSION actual: " . print_r($_SESSION, true));

  if (!isLoggedIn()) {
    // error_log("protegerVistaPorRoles: Usuario no está logueado");
    header("Location: login");
    exit;
  }

  $roleId = getUserRoleId();

  // error_log("protegerVistaPorRoles - Role ID del usuario: " . ($roleId ?? 'null'));

  if ($roleId === null) {
    // error_log("protegerVistaPorRoles: No se pudo obtener el rol del usuario");
    header("Location: login");
    exit;
  }

  // Convertir los roles permitidos a números si son strings
  $rolesNumericos = array_map(function ($rol) {
    return is_numeric($rol) ? (int)$rol : $rol;
  }, $rolesPermitidos);

  // error_log("protegerVistaPorRoles - Roles numéricos permitidos: " . implode(", ", $rolesNumericos));
  // error_log("protegerVistaPorRoles - Rol del usuario (numérico): " . $roleId);

  if (!in_array($roleId, $rolesNumericos)) {
    // error_log("protegerVistaPorRoles: Usuario con rol " . $roleId . " no tiene permiso para acceder a esta vista");
    header("Location: inicio");
    exit;
  }
}

/**
 * Muestra un mensaje de error usando SweetAlert2
 */
function mostrarError()
{
  if (isset($_SESSION['error_message'])) {
    echo "<script>
      Swal.fire({
        icon: 'error',
        title: 'Error de acceso',
        text: '" . htmlspecialchars($_SESSION['error_message'], ENT_QUOTES) . "',
        showConfirmButton: true,
        confirmButtonText: 'Aceptar'
      });
    </script>";
    unset($_SESSION['error_message']);
  }
}

/**
 * Cierra la sesión del usuario y registra en bitácora
 */
function cerrarSesion()
{
  require_once "bitacora.php";

  // Registrar en bitácora antes de destruir la sesión
  if (isset($_SESSION["usuario"])) {
    registrarBitacora($_SESSION["usuario"]["id"], "Sesión cerrada manualmente por el usuario");
  }

  // Limpiar y destruir la sesión
  session_unset();
  session_destroy();

  // Redirigir al login
  echo '<script>
    window.location = "login";
  </script>';
}
