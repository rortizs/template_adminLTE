<?php

/**
 * Manejador de sesiones personalizado que almacena las sesiones en la base de datos
 */
class DbSessionHandler implements SessionHandlerInterface
{
  private $dbConnection;
  private $tablePrefix;
  private $sessionLifetime;

  public function __construct($dbConnection, $tablePrefix = 'session_', $sessionLifetime = 3600)
  {
    $this->dbConnection = $dbConnection;
    $this->tablePrefix = $tablePrefix;
    $this->sessionLifetime = $sessionLifetime;
    $this->ensureTableExists();
  }

  /**
   * Asegurarse de que la tabla de sesiones existe
   */
  private function ensureTableExists()
  {
    try {
      $sql = "CREATE TABLE IF NOT EXISTS {$this->tablePrefix}data (
                session_id VARCHAR(128) NOT NULL PRIMARY KEY,
                session_data TEXT NOT NULL,
                last_activity TIMESTAMP NOT NULL,
                user_id INT DEFAULT NULL,
                ip_address VARCHAR(45) DEFAULT NULL,
                user_agent TEXT DEFAULT NULL,
                INDEX (last_activity),
                INDEX (user_id)
            )";
      $this->dbConnection->exec($sql);
    } catch (PDOException $e) {
      error_log("Error al crear tabla de sesiones: " . $e->getMessage());
    }
  }

  /**
   * Abre la sesión
   */
  public function open($savePath, $sessionName): bool
  {
    return true;
  }

  /**
   * Cierra la sesión
   */
  public function close(): bool
  {
    return true;
  }

  /**
   * Lee los datos de la sesión
   */
  public function read($id): string|false
  {
    try {
      $stmt = $this->dbConnection->prepare("SELECT session_data FROM {$this->tablePrefix}data WHERE session_id = :id");
      $stmt->bindParam(':id', $id);
      $stmt->execute();
      $result = $stmt->fetch(PDO::FETCH_ASSOC);

      return $result ? $result['session_data'] : ''; //validacion ternaria example: 
    } catch (PDOException $e) {
      error_log("Error al leer sesión: " . $e->getMessage());
      return '';
    }
  }

  /**
   * Escribe los datos de la sesión
   */
  public function write($id, $data): bool
  {
    try {
      // Intentar extraer el ID de usuario de los datos de sesión
      $userId = null;
      if (isset($_SESSION['id'])) {
        $userId = $_SESSION['id'];
      }

      $stmt = $this->dbConnection->prepare("REPLACE INTO {$this->tablePrefix}data (
                session_id, session_data, last_activity, user_id, ip_address, user_agent
            ) VALUES (
                :id, :data, NOW(), :userId, :ipAddress, :userAgent
            )");

      $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
      $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;

      $stmt->bindParam(':id', $id);
      $stmt->bindParam(':data', $data);
      $stmt->bindParam(':userId', $userId);
      $stmt->bindParam(':ipAddress', $ipAddress);
      $stmt->bindParam(':userAgent', $userAgent);

      return $stmt->execute();
    } catch (PDOException $e) {
      error_log("Error al escribir sesión: " . $e->getMessage());
      return false;
    }
  }

  /**
   * Destruye una sesión
   */
  public function destroy($id): bool
  {
    try {
      $stmt = $this->dbConnection->prepare("DELETE FROM {$this->tablePrefix}data WHERE session_id = :id");
      $stmt->bindParam(':id', $id);
      return $stmt->execute();
    } catch (PDOException $e) {
      error_log("Error al destruir sesión: " . $e->getMessage());
      return false;
    }
  }

  /**
   * Recolección de basura - elimina sesiones caducadas
   */
  public function gc($maxlifetime): int|false
  {
    try {
      $expired = date('Y-m-d H:i:s', time() - $this->sessionLifetime);
      $stmt = $this->dbConnection->prepare("DELETE FROM {$this->tablePrefix}data WHERE last_activity < :expired");
      $stmt->bindParam(':expired', $expired);
      $stmt->execute();
      return $stmt->rowCount();
    } catch (PDOException $e) {
      error_log("Error en recolección de basura de sesiones: " . $e->getMessage());
      return false;
    }
  }

  /**
   * Obtiene los datos de sesión por ID de usuario
   */
  public function getUserSession($userId)
  {
    try {
      $stmt = $this->dbConnection->prepare("SELECT * FROM {$this->tablePrefix}data WHERE user_id = :userId ORDER BY last_activity DESC LIMIT 1");
      $stmt->bindParam(':userId', $userId);
      $stmt->execute();
      return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      error_log("Error al obtener sesión de usuario: " . $e->getMessage());
      return null;
    }
  }

  /**
   * Obtiene el token JWT almacenado en la sesión
   */
  public function getJwtTokenByUserId($userId)
  {
    $sessionData = $this->getUserSession($userId);
    if ($sessionData) {
      $data = @unserialize($sessionData['session_data']);
      return isset($data['jwt_token']) ? $data['jwt_token'] : null;
    }
    return null;
  }
}
