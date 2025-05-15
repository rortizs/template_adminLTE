<?php

require_once "connect.php";

class UsersModel
{
  static public function mdlGetUsersAll($table, $item, $value)
  {
    if ($item != null) {
      $stmt = Connect::connection()->prepare("SELECT * FROM $table WHERE $item = :$item");

      $stmt->bindParam(":" . $item, $value, PDO::PARAM_STR);
      $stmt->execute();
      return $stmt->fetch();

      $jwt = 'SELECT jwt_user from users WHERE idUser = :idUser';
      if ($jtw === $_SESSION["jwt_user"]) {
        $stmt = Connect::connection()->prepare($jwt);
        $stmt->bindParam(":idUser", $_SESSION["idUser"], PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch();
      }
    } else {

      $stmt = Connect::connection()->prepare("SELECT * FROM $table");
      $stmt->execute();
      return $stmt->fetchAll();
    }
    $stmt->close();
    $stmt = null;
  }

  static public function mdlGetUserById($table, $item, $value)
  {
    if ($item != null) {
      $stmt = Connect::connection()->prepare("SELECT * FROM $table WHERE $item = :$item");
      $stmt->bindParam(":" . $item, $value, PDO::PARAM_STR);
      $stmt->execute();
      return $stmt->fetch();
    } else {
      return null;
    }
    $stmt->close();
    $stmt = null;
  }



  static public function mdlRegisterUser($table, $data)
  {
    $stmt = Connect::connection()->prepare("INSERT INTO $table(name, user, password, profile, image, status) VALUES (:name, :user, :password, :profile, :image, :status)");

    $stmt->bindParam(":name", $data["name"], PDO::PARAM_STR);
    $stmt->bindParam(":user", $data["user"], PDO::PARAM_STR);
    $stmt->bindParam(":password", $data["password"], PDO::PARAM_STR);
    $stmt->bindParam(":profile", $data["profile"], PDO::PARAM_STR);
    $stmt->bindParam(":image", $data["image"], PDO::PARAM_STR);
    $stmt->bindParam(":status", $data["status"], PDO::PARAM_STR);
    if ($stmt->execute()) {
      return "ok";
    } else {
      return "error";
    }
    $stmt->close();
    $stmt = null;
  }

  static public function mdlUpdateUser($table, $data)
  {
    $stmt = Connect::connection()->prepare("UPDATE $table SET name = :name, user = :user, password = :password, profile = :profile, image = :image, status = :status WHERE id = :id");

    $stmt->bindParam(":name", $data["name"], PDO::PARAM_STR);
    $stmt->bindParam(":user", $data["user"], PDO::PARAM_STR);
    $stmt->bindParam(":password", $data["password"], PDO::PARAM_STR);
    $stmt->bindParam(":profile", $data["profile"], PDO::PARAM_STR);
    $stmt->bindParam(":image", $data["image"], PDO::PARAM_STR);
    $stmt->bindParam(":status", $data["status"], PDO::PARAM_STR);
    $stmt->bindParam(":id", $data["id"], PDO::PARAM_INT);

    if ($stmt->execute()) {
      return "ok";
    } else {
      return "error";
    }
    $stmt->close();
    $stmt = null;
  }
  static public function mdlDeleteUser($table, $data)
  {
    $stmt = Connect::connection()->prepare("DELETE FROM $table WHERE id = :id");
    $stmt->bindParam(":id", $data, PDO::PARAM_INT);

    if ($stmt->execute()) {
      return "ok";
    } else {
      return "error";
    }
    $stmt->close();
    $stmt = null;
  }
  static public function mdlUpdateUserStatus($table, $data)
  {
    $stmt = Connect::connection()->prepare("UPDATE $table SET status = :status WHERE id = :id");
    $stmt->bindParam(":status", $data["status"], PDO::PARAM_STR);
    $stmt->bindParam(":id", $data["id"], PDO::PARAM_INT);

    if ($stmt->execute()) {
      return "ok";
    } else {
      return "error";
    }
    $stmt->close();
    $stmt = null;
  }
  static public function mdlUpdateUserProfile($table, $data)
  {
    $stmt = Connect::connection()->prepare("UPDATE $table SET profile = :profile WHERE id = :id");
    $stmt->bindParam(":profile", $data["profile"], PDO::PARAM_STR);
    $stmt->bindParam(":id", $data["id"], PDO::PARAM_INT);

    if ($stmt->execute()) {
      return "ok";
    } else {
      return "error";
    }
    $stmt->close();
    $stmt = null;
  }
  static public function mdlUpdateUserImage($table, $data)
  {
    $stmt = Connect::connection()->prepare("UPDATE $table SET image = :image WHERE id = :id");
    $stmt->bindParam(":image", $data["image"], PDO::PARAM_STR);
    $stmt->bindParam(":id", $data["id"], PDO::PARAM_INT);

    if ($stmt->execute()) {
      return "ok";
    } else {
      return "error";
    }
    $stmt->close();
    $stmt = null;
  }
  static public function mdlUpdateUserPassword($table, $data)
  {
    $stmt = Connect::connection()->prepare("UPDATE $table SET password = :password WHERE id = :id");
    $stmt->bindParam(":password", $data["password"], PDO::PARAM_STR);
    $stmt->bindParam(":id", $data["id"], PDO::PARAM_INT);

    if ($stmt->execute()) {
      return "ok";
    } else {
      return "error";
    }
    $stmt->close();
    $stmt = null;
  }
  static public function mdlUpdateUserProfileImage($table, $data)
  {
    $stmt = Connect::connection()->prepare("UPDATE $table SET profile = :profile, image = :image WHERE id = :id");
    $stmt->bindParam(":profile", $data["profile"], PDO::PARAM_STR);
    $stmt->bindParam(":image", $data["image"], PDO::PARAM_STR);
    $stmt->bindParam(":id", $data["id"], PDO::PARAM_INT);

    if ($stmt->execute()) {
      return "ok";
    } else {
      return "error";
    }
    $stmt->close();
    $stmt = null;
  }
}
