<?php

//session bootstrap
//example url para api = https://api.cadep.org.gt/index.php/api/v1/endpoint
//example url para api = https://api.cadep.org.gt/index.php/api/v1/login POST
//example url para api = https://api.cadep.org.gt/index.php/api/v1/getAllUsers GET
//example url para api = https://api.cadep.org.gt/index.php/api/v1/saveUser POST
//example url para api = https://api.cadep.org.gt/index.php/api/v1/searchUsers GET
//example url para api = https://api.cadep.org.gt/index.php/api/v1/updateUserByIdOrNameOrAddress patch



//url development, production, testing
define('API_URL', 'https://api.cadep.org.gt/index.php'); //url vhost php , http://cadep.org.gt/index.php

/**
 * llamada a la api rest
 * @param string $endpoint de la api
 * @param string $method metodo de la api (GET, POST, PUT, DELETE, PATCH)
 * @param array $data datos a enviar a la api(OPCIONAL)
 * @param array $headers cabeceras a enviar a la api(OPCIONAL)
 * @return array respuesta de la api
 */

function callApi($endpoint, $method = 'GET', $data = [], $headers = [])
{
  try {
    $token = getValidJwtToken();
    error_log("CallApi: No se pudo obtener un token válido");


    //validate if the token is empty
    if (!isAjaxRequest()) {
      error_log("callApi: Redireccionado a la pagina de login");
      header('Location: /login');
      exit();
    }

    return ['success' => false, 'message' => 'No se pudo obtener un token válido'];

    error_log("callApi: Llamando a $endpoint con token: " . substr($token, 0, 20) . "...");

    // Asegurarnos que el endpoint comience con /
    $endpoint = '/' . ltrim($endpoint, '/');
    $apiUrl = API_URL . $endpoint;
    error_log("callApi: URL completa: $apiUrl");
  } catch (\Throwable $th) {
    //throw $th;
  }
}

/**
 * Obtiene un token JWT válido, primero de la sesión y luego intenta refrescarlo si es necesario
 * 
 * @return string|null Token JWT válido o null si no se encuentra
 */
function getValidJwtToken()
{
  // Verificar si hay un token en la sesión
  if (isset($_SESSION['jwt_token'])) {
    // Si el token está próximo a expirar (menos de 5 minutos), intentar refrescarlo
    if (
      isset($_SESSION['jwt_token_expires']) &&
      $_SESSION['jwt_token_expires'] > time() + 300
    ) {
      error_log("Using existing JWT token from session");
      return $_SESSION['jwt_token'];
    }

    // Token está por expirar o ya expiró
    error_log("JWT token expired or about to expire, attempting refresh");
    if (isset($_SESSION['jwt_refresh_token']) && refreshJwtToken()) {
      error_log("JWT token refreshed successfully");
      return $_SESSION['jwt_token'];
    } else {
      // Si no hay refresh token o falló el refresco, invalidar la sesión
      error_log("JWT token expired and couldn't refresh - invalidating session");
      unset($_SESSION['jwt_token']);
      unset($_SESSION['jwt_token_expires']);
      // Redirigir al login si estamos en una página del navegador
      if (!isAjaxRequest()) {
        header("Location: /login");
        exit;
      }
      return null;
    }
  }

  // Si hay ID de usuario en sesión, intentar obtener token de la base de datos
  if (isset($_SESSION['id'])) {
    error_log("Attempting to get token from database for user " . $_SESSION['id']);
    require_once __DIR__ . '/../models/usersModel.php';
    $userModel = new UsersModel();
    $table = 'users';
    $userData = $userModel->mdlGetUserById($table, 'userId', $_SESSION['id']);

    if ($userData && !empty($userData['jwt_token'])) {
      // Verificar si el token de la base de datos no está expirado
      $tokenExpires = strtotime($userData['token_expires']);
      if ($tokenExpires > time()) {
        $_SESSION['jwt_token'] = $userData['jwt_token'];
        $_SESSION['jwt_token_expires'] = $tokenExpires;
        error_log("Retrieved valid token from database, expires: " . $userData['token_expires']);
        return $_SESSION['jwt_token'];
      } else {
        error_log("Token in database is expired");
        // Intentar refrescar el token
        if (refreshJwtToken()) {
          return $_SESSION['jwt_token'];
        }
      }
    }
  }

  // Si no hay token válido, registrar el error
  error_log("No valid JWT token found in session or database");
  return null;
}

/**
 * Determina si la solicitud actual es una solicitud AJAX
 */
function isAjaxRequest()
{
  return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

/**
 * Obtiene un token JWT de la API
 * 
 * @param string $username Nombre de usuario
 * @param string $password Contraseña
 * @return array Resultado con el token o error
 */
function getJwtToken($username, $password)
{
  try {
    // URL del endpoint de login
    $apiUrl = 'https://api.cadep.org.gt/index.php/login';
    error_log("JWT Login: Iniciando solicitud a $apiUrl");

    // Datos para la autenticación
    $postData = json_encode([
      'username' => $username,
      'password' => $password
    ]);

    // Inicializar cURL
    $ch = curl_init();

    // Configurar opciones de cURL
    curl_setopt_array($ch, [
      CURLOPT_URL => $apiUrl,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_POST => true,
      CURLOPT_POSTFIELDS => $postData,
      CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($postData)
      ],
      CURLOPT_SSL_VERIFYPEER => false,
      CURLOPT_TIMEOUT => 30
    ]);

    // Ejecutar la solicitud
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    // Verificar si hubo errores
    if (curl_errno($ch)) {
      $error = curl_error($ch);
      curl_close($ch);
      error_log("JWT Token Error: $error");
      return [
        'success' => false,
        'message' => 'Error al obtener token: ' . $error
      ];
    }

    // Cerrar la conexión cURL
    curl_close($ch);

    error_log("JWT Login: Respuesta recibida con código HTTP: $httpCode");
    error_log("JWT Login: Respuesta raw: " . substr($response, 0, 500));

    // Decodificar la respuesta JSON
    $responseData = json_decode($response, true);

    // Verificar si la respuesta es válida
    if (json_last_error() !== JSON_ERROR_NONE) {
      error_log("JWT Login: Error al decodificar JSON: " . json_last_error_msg());
      return [
        'success' => false,
        'message' => 'Error al procesar la respuesta: ' . json_last_error_msg()
      ];
    }

    // Verificar el código de respuesta HTTP
    if ($httpCode >= 200 && $httpCode < 300) {
      if (isset($responseData['access_token']) || isset($responseData['token'])) {
        $token = $responseData['access_token'] ?? $responseData['token'];
        $refreshToken = $responseData['refresh_token'] ?? null;
        $expiresIn = $responseData['expires_in'] ?? 1800;

        error_log("JWT Login: Token obtenido correctamente. Expira en $expiresIn segundos");

        return [
          'success' => true,
          'token' => $token,
          'refresh_token' => $refreshToken,
          'expires_at' => time() + $expiresIn,
          'id' => $responseData['id'] ?? null,
          'username' => $responseData['username'] ?? $username,
          'email' => $responseData['email'] ?? '',
          'role_id' => $responseData['role_id'] ?? $responseData['perfil'] ?? 2
        ];
      } else {
        error_log("JWT Login: Respuesta no contiene token: " . json_encode($responseData));
        return [
          'success' => false,
          'message' => 'La respuesta no contiene un token válido'
        ];
      }
    } else {
      $errorMessage = isset($responseData['message']) ? $responseData['message'] : "Error HTTP $httpCode";
      error_log("JWT Login: Error en API: $errorMessage");
      return [
        'success' => false,
        'message' => 'Error en autenticación: ' . $errorMessage,
        'code' => $httpCode
      ];
    }
  } catch (Exception $e) {
    error_log("JWT Login: Excepción: " . $e->getMessage());
    return [
      'success' => false,
      'message' => 'Error inesperado: ' . $e->getMessage()
    ];
  }
}

/**
 * Refresca el token JWT usando el refresh token
 * 
 * @return array Resultado del refresco con claves: success, message, token (opcional)
 */
function refreshJwtToken()
{
  try {
    if (!isset($_SESSION['jwt_refresh_token'])) {
      error_log("refreshJwtToken: No hay refresh token en sesión");
      return ['success' => false, 'message' => 'No hay refresh token disponible'];
    }

    if (!isset($_SESSION['user_id'])) {
      error_log("refreshJwtToken: No hay user_id en sesión");
      return ['success' => false, 'message' => 'No hay ID de usuario en sesión'];
    }

    $refreshToken = $_SESSION['jwt_refresh_token'];
    error_log("refreshJwtToken: Intentando refrescar token para usuario " . $_SESSION['user_id']);
    error_log("refreshJwtToken: Usando refresh token: " . substr($refreshToken, 0, 20) . "...");

    $apiUrl = 'https://api.cadep.org.gt/index.php/refresh-token';

    $ch = curl_init();
    curl_setopt_array($ch, [
      CURLOPT_URL => $apiUrl,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_POST => true,
      CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $refreshToken,
        'Content-Type: application/json'
      ]
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode == 200) {
      $data = json_decode($response, true);
      if (isset($data['access_token'])) {
        $newToken = $data['access_token'];
        $expiresIn = $data['expires_in'] ?? 1800;
        $newRefreshToken = $data['refresh_token'] ?? $refreshToken;

        // Actualizar sesión
        $_SESSION['jwt_token'] = $newToken;
        $_SESSION['jwt_token_expires'] = time() + $expiresIn;
        $_SESSION['jwt_refresh_token'] = $newRefreshToken;

        // Actualizar base de datos
        try {
          // Conectar a la base de datos
          $db = new PDO('mysql:host=localhost;dbname=your_database', 'username', 'password');
          $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

          $stmt = $db->prepare("UPDATE users SET jwt_token = ?, jwt_refresh_token = ?, jwt_expires_at = ? WHERE id = ?");
          $stmt->execute([$newToken, $newRefreshToken, date('Y-m-d H:i:s', time() + $expiresIn), $_SESSION['user_id']]);

          error_log("refreshJwtToken: Token actualizado en BD para usuario " . $_SESSION['user_id']);
        } catch (PDOException $e) {
          error_log("refreshJwtToken: Error al actualizar BD: " . $e->getMessage());
          // No retornamos error aquí porque el token ya se actualizó en sesión
        }

        error_log("refreshJwtToken: Token refrescado exitosamente. Nuevo token: " . substr($newToken, 0, 20) . "...");
        return [
          'success' => true,
          'message' => 'Token refrescado exitosamente',
          'token' => $newToken,
          'expires_in' => $expiresIn
        ];
      } else {
        error_log("refreshJwtToken: Respuesta no contiene access_token: " . $response);
        return ['success' => false, 'message' => 'Respuesta inválida del servidor'];
      }
    }

    error_log("refreshJwtToken: Error HTTP $httpCode al refrescar token. Respuesta: " . $response);
    return ['success' => false, 'message' => "Error al refrescar token (HTTP $httpCode)"];
  } catch (Exception $e) {
    error_log("refreshJwtToken: Error inesperado: " . $e->getMessage());
    return ['success' => false, 'message' => 'Error inesperado al refrescar token'];
  }
}
