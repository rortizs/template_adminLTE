<?php

function registrarBitacora($userId, $action)
{
  try {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'Desconocida';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'NO DEFINIDO';

    $conexion = Connect::connection();
    $stmt = $conexion->prepare("INSERT INTO bitacora (user_id, action, ip_address, user_agent) VALUES (?,?,?,?)");
    $stmt->execute([$userId, $action, $ip, $userAgent]);
  } catch (Exception $e) {
    // Manejo de errores
    error_log("Error al registrar en la bitácora: " . $e->getMessage());
  }
}
