<?php

class UserController
{
  static public function ctrloginUser()
  {
    if (isset($_POST["ingUser"])) {
      if (preg_match('/^[a-zA-Z0-9]+$/', $_POST['ingUser'])) {

        $encripter = crypt($_POST["ingPassword"], '$2a$07$');
        $table = "users";
        $item = "userId";
        $value = $_POST["ingUser"];

        $response = UsersModel::mdlGetUsersAll($table, $item, $value);

        if (is_array($response) && $response["user"] == $_POST["ingUser"] && $response["password"] == $encripter) {
          if ($response["status"] == 1) {
            $_SESSION["iniciarSesion"] = "ok";
            $_SESSION["id"] = $response["id"];
            $_SESSION["name"] = $response["name"];
            $_SESSION["user"] = $response["user"];
            $_SESSION["profile"] = $response["profile"];
            $_SESSION["image"] = $response["image"];
            $_SESSION["status"] = $response["status"];

            //===REGISTER DATE FROM LAST LOGIN===
            $date = date("Y-m-d");
            $time = date("H:i:s");

            $dateNow = $date . " " . $time;

            $item1 = "last_login";
            $value1 = $dateNow;
            $item2 = "id";
            $value2 = $response["id"];

            $lastLogin = UsersModel::mdlUpdateUser($table, $item1, $value1, $item2, $value2);

            if ($lastLogin == "ok") {

              echo '<script>

								window.location = "inicio";

							</script>';
            }
          } else {
            echo '<div class="alert alert-danger">El usuario se encuentra desactivado</div>';
          }
        } else {
          echo '<div class="alert alert-danger">Error al ingresar, vuelve a intentarlo</div>';
        }
      }
    }
  }

  static public function ctrRegisterUser()
  {
    if (isset($_POST["registerUser"])) {
      if (preg_match('/^[a-zA-Z0-9]+$/', $_POST['registerUser'])) {
        $table = "users";
        $data = array(
          "name" => $_POST["registerName"],
          "user" => $_POST["registerUser"],
          "password" => crypt($_POST["registerPassword"], '$2a$07$'),
          "profile" => $_POST["registerProfile"],
          "image" => $_POST["registerImage"],
          "status" => $_POST["registerStatus"]
        );

        $response = UsersModel::mdlRegisterUser($table, $data);

        if ($response == "ok") {
          echo '<script>

            window.location = "inicio";

          </script>';
        } else {
          echo '<div class="alert alert-danger">Error al registrar el usuario</div>';
        }
      }
    }
  }
}
