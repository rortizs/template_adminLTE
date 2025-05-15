<?php

// Configurar para que muestre errores durante la depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/error.log');

// validar session is started
if (session_status() === PHP_SESSION_NONE) {
  // Cargar configuración de sesión
  $sessionConfigPath = __DIR__ . '/../config/session.php';
  if (!file_exists($sessionConfigPath)) {
    error_log("Error crítico: No se encontró el archivo de configuración de sesión: " . $sessionConfigPath);
    // Manejo de error, quizás iniciar sesión con valores predeterminados o detenerse.
  } else {
    $sessionConfig = require $sessionConfigPath;

    // Asegurar que el directorio de sesión existe
    if (!is_dir($sessionConfig['save_path'])) {
      @mkdir($sessionConfig['save_path'], 0755, true);
    }

    // Configurar opciones de sesión
    foreach ($sessionConfig as $key => $value) {
      if (strpos($key, 'cookie_') === 0 || strpos($key, 'use_') === 0 || $key === 'gc_maxlifetime') {
        ini_set("session.$key", $value);
      }
    }

    // Establecer la ruta de guardado
    session_save_path($sessionConfig['save_path']);
  }

  // Asegurarnos de que el archivo DbSessionHandler.php exista antes de requerirlo
  $dbSessionHandlerPath = __DIR__ . '/DbSessionHandler.php';
  if (file_exists($dbSessionHandlerPath)) {
    require_once $dbSessionHandlerPath;
  } else {
    error_log("Error crítico: No se encontró el archivo DbSessionHandler.php en: " . $dbSessionHandlerPath);
  }

  // Asegurarnos de que el archivo connection.php exista antes de requerirlo
  $connectionPath = __DIR__ . '/../models/connection.php';
  if (file_exists($connectionPath)) {
    require_once $connectionPath;
  } else {
    error_log("Error crítico: No se encontró el archivo connection.php en: " . $connectionPath);
  }

  // Variable global para el handler de sesión
  $sessionHandler = null;

  // Obtener conexión a la base de datos e inicializar el manejador
  try {
    if (class_exists('Conexion')) {
      $dbConnection = Connet::connection();
      if (class_exists('DbSessionHandler')) {
        $sessionHandler = new DbSessionHandler($dbConnection);
        session_set_save_handler($sessionHandler, true);
      }
    }
  } catch (Exception $e) {
    error_log("[Session Bootstrap] Error al inicializar sesión con DB: " . $e->getMessage());
  }

  // Iniciar la sesión después de toda la configuración
  session_start();
  error_log("[Session Bootstrap] Sesión iniciada con ID: " . session_id());

  // Si la sesión no se pudo iniciar con el handler personalizado, registrarlo
  if (ini_get('session.save_handler') !== 'user') {
    error_log("[Session Bootstrap] Advertencia: La sesión no se inició con el manejador personalizado.");
  }
}

/**
 * Función para obtener el manejador de sesiones
 */
function getSessionHandler()
{
  global $sessionHandler;
  return $sessionHandler;
}

/**
 * Obtener el token JWT para un usuario específico
 * Esta función intenta varias estrategias para obtener un token JWT válido
 *  url --> token this user is valid --> index.php --> template ---> navigate inicio ---> modules --> inicio.php
 *  url --> token this user is valid --> index.php --> template ---> navigate inicio ---> modules --> login.php
 *  url --> token this user is valid --> index.php --> template ---> navigate inicio ---> modules --> register.php
 *  url --> token this user is valid --> index.php --> template ---> navigate inicio ---> modules --> forgot_password.php
 *  url --> token this user is valid --> index.php --> template ---> navigate inicio ---> modules --> reset_password.php
 */
function getTokenForUser($userId)
{
  global $sessionHandler, $dbConnection;

  try {
    error_log("[getTokenForUser] Iniciando búsqueda de token para usuario $userId");

    // Primer intento: buscar en la sesión activa
    error_log("[getTokenForUser] SESSION actual: " . print_r($_SESSION, true));
    if (isset($_SESSION['jwt_token']) && !empty($_SESSION['jwt_token'])) {
      error_log("[getTokenForUser] Token encontrado en sesión actual para usuario $userId");
      return $_SESSION['jwt_token'];
    }
    error_log("[getTokenForUser] No se encontró token en la sesión actual");

    // Asegurarnos de que $dbConnection está disponible si no se usó el handler
    if (!isset($dbConnection) && class_exists('Conexion')) {
      error_log("[getTokenForUser] Creando nueva conexión a la base de datos");
      $dbConnection = Conexion::conectar();
    }

    // Segundo intento: buscar en la tabla de sesiones (si el handler está disponible)
    if (isset($sessionHandler)) {
      error_log("[getTokenForUser] Buscando token en tabla de sesiones");
      $token = $sessionHandler->getJwtTokenByUserId($userId);
      if (!empty($token)) {
        error_log("[getTokenForUser] Token encontrado en tabla de sesiones para usuario $userId");
        $_SESSION['jwt_token'] = $token;
        $_SESSION['jwt_token_expires'] = time() + 3600;
        return $token;
      }
      error_log("[getTokenForUser] No se encontró token en tabla de sesiones");
    } else {
      error_log("[getTokenForUser] Session handler no está disponible");
    }

    // Tercer intento: buscar directamente en la tabla de usuarios
    if (isset($dbConnection)) {
      error_log("[getTokenForUser] Buscando token en tabla de usuarios");
      $stmt = $dbConnection->prepare("SELECT jwt_token, jwt_token_expires FROM users WHERE id = :userId");
      $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
      $stmt->execute();
      $result = $stmt->fetch(PDO::FETCH_ASSOC);
      error_log("[getTokenForUser] Resultado de búsqueda en users: " . print_r($result, true));

      if ($result && !empty($result['jwt_token'])) {
        error_log("[getTokenForUser] Token encontrado en tabla de usuarios para usuario $userId");
        $_SESSION['jwt_token'] = $result['jwt_token'];
        $_SESSION['jwt_token_expires'] = strtotime($result['jwt_token_expires'] ?? '+1 hour');
        return $result['jwt_token'];
      }
      error_log("[getTokenForUser] No se encontró token en tabla de usuarios");
    } else {
      error_log("[getTokenForUser] Conexión a base de datos no está disponible");
    }

    // Cuarto intento: obtener un nuevo token desde la API
    error_log("[getTokenForUser] Intentando obtener nuevo token desde API para usuario $userId");
    $token = refreshTokenFromApi($userId);
    if (!empty($token)) {
      error_log("[getTokenForUser] Token obtenido exitosamente desde API");
      return $token;
    }
    error_log("[getTokenForUser] No se pudo obtener token desde API");

    error_log("[getTokenForUser] No se pudo obtener token para usuario $userId después de agotar todas las opciones");
    return null;
  } catch (Exception $e) {
    error_log("[getTokenForUser] Error obteniendo token para usuario $userId: " . $e->getMessage());
    error_log("[getTokenForUser] Stack trace: " . $e->getTraceAsString());
    return null;
  }
}

/**
 * Refrescar el token desde la API
 */
function refreshTokenFromApi($userId)
{
  try {
    // Asegurarnos de que $dbConnection está disponible
    global $dbConnection;
    if (!isset($dbConnection) && class_exists('Conexion')) {
      $dbConnection = Conexion::conectar();
    }

    // URL de la API para obtener el token
    $tokenUrl = 'https://api.capep.org.gt/index.php/getTokenAndExpiresIn/' . $userId;
    $ch = curl_init($tokenUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Considerar eliminar en producción

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
      throw new Exception("Error CURL: " . curl_error($ch));
    }

    curl_close($ch);

    $data = json_decode($response, true);
    if (isset($data['access_token'])) { // Corregido: buscar 'access_token' como en la API
      $newToken = $data['access_token'];
      error_log("[refreshTokenFromApi] Token obtenido exitosamente para usuario $userId");
      // Guardar el token en la sesión
      $_SESSION['jwt_token'] = $newToken;

      // Actualizar también en la base de datos
      if (isset($dbConnection)) {
        $stmt = $dbConnection->prepare("UPDATE users SET jwt_token = :token WHERE id = :userId");
        $stmt->bindParam(':token', $newToken);
        $stmt->bindParam(':userId', $userId);
        $stmt->execute();
        error_log("[refreshTokenFromApi] Token actualizado en base de datos para usuario $userId");
      }

      return $newToken;
    }

    error_log("[refreshTokenFromApi] API no devolvió access_token para usuario $userId. Respuesta: " . $response);
    return null;
  } catch (Exception $e) {
    error_log("[refreshTokenFromApi] Error al refrescar token desde API para usuario $userId: " . $e->getMessage());
    return null;
  }
}

/**
 * Verificar si el usuario tiene un rol específico
 */
function userHasRole($requiredRole)
{
  if (!isset($_SESSION['user']) || !isset($_SESSION['user']['role_id'])) {
    return false;
  }
  return $_SESSION['user']['role_id'] == $requiredRole;
}
