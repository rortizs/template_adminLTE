<?php

require_once __DIR__ . '/../../helpers/session_bootstrap.php';

class LoginController
{
  static public function login()
  {
    // Obtener ID de usuario
    $userId = isset($_GET['userId']) ? $_GET['userId'] : (isset($_SESSION['id']) ? $_SESSION['id'] : 1);
    // Obtener token JWT para la API
    $token = getTokenForUser($userId);

    $curl = 'https://api.cadep.org.gt/index.php/api/v1/login';
    $ch = curl_init($curl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
      'Content-Type: application/json',
      'Authorization: Bearer ' . $token
    ]);

    $username = $_POST['username'];
    $password = $_POST['password'];

    $result = callApi('login', 'POST', [
      'username' => $username,
      'password' => $password
    ]);

    if ($result['success']) {
      echo json_encode([
        'success' => true,
        'message' => 'Login successful',
        'data' => $result['data']
      ]);
    } else {
      echo json_encode([
        'success' => false,
        'message' => 'Login failed',
        'data' => $result['data']
      ]);
    }
  }
}
