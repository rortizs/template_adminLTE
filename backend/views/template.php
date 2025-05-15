<?php
//path
$path = TemplateController::path();

$inactividadMaxima = 3600; // 1 hora en segundos


// session
if (session_status() === PHP_SESSION_NONE) {
  session_start();

  // Verificar si la sesión ha expirado
  if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $inactividadMaxima)) {
    //tiempo de inactividad
    require_once __DIR__ . '../helpers/bitacora.php';
    if (isset($_SESSION['usuario']['id'])) {
      registrarBitacora($_SESSION['usuario']['id'], "Session expired");
    }
    session_unset(); // Destruir variables de sesión
    session_destroy(); // Destruir la sesión

    //asegurar redireccion
    $loginUrl = $path . 'login.php/expired=true';
    header("Location: $loginUrl");
    exit();
  }

  $_SESSION['LAST_ACTIVITY'] = time(); // Actualiza tiempo de actividad
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- title dinamic  from database -->
  <title>CADEP</title>

  <link rel="icon" href="<?php echo $path ?>views/assets/img/iconoLogo.png">

  <!-- PLUGINS CSS -->
  <!-- Bootstrap 3.3.7 -->
  <link rel="stylesheet" href="<?php echo $path ?>views/assets/plugins/bower_components/bootstrap/dist/css/bootstrap.min.css">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="<?php echo $path ?>views/assets/plugins/bower_components/font-awesome/css/font-awesome.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="<?php echo $path ?>views/assets/plugins/bower_components/Ionicons/css/ionicons.min.css">

  <!-- Theme style -->
  <link rel="stylesheet" href="<?php echo $path ?>views/assets/css/adminlte/css/AdminLTE.css">

  <!-- CSS CUSTOM TEMPLATE -->
  <link rel="stylesheet" href="<?php echo $path ?>views/assets/css/custom/custom.css">

  <!-- AdminLTE Skins -->
  <link rel="stylesheet" href="<?php echo $path ?>views/assets/css/adminlte/css/skins/_all-skins.min.css">

  <!-- Google Font -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">

  <!-- DataTables -->
  <link rel="stylesheet" href="<?php echo $path ?>views/assets/plugins/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css">
  <link rel="stylesheet" href="<?php echo $path ?>views/assets/plugins/bower_components/datatables.net-bs/css/responsive.bootstrap.min.css">

  <!-- iCheck for checkboxes and radio inputs -->
  <link rel="stylesheet" href="<?php echo $path ?>views/assets/plugins/iCheck/all.css">

  <!-- Daterange picker -->
  <link rel="stylesheet" href="<?php echo $path ?>views/assets/plugins/bower_components/bootstrap-daterangepicker/daterangepicker.css">

  <!-- Morris chart -->
  <link rel="stylesheet" href="<?php echo $path ?>views/assets/plugins/bower_components/morris.js/morris.css">

  <!-- PLUGGINS JS -->
  <!-- jQuery 3 -->
  <script src="<?php echo $path ?>views/assets/plugins/bower_components/jquery/dist/jquery.min.js"></script>

  <!-- Bootstrap 3.3.7 -->
  <script src="<?php echo $path ?>views/assets/plugins/bower_components/bootstrap/dist/js/bootstrap.min.js"></script>

  <!-- FastClick -->
  <script src="<?php echo $path ?>views/assets/plugins/bower_components/fastclick/lib/fastclick.js"></script>

  <!-- AdminLTE App -->
  <script src="<?php echo $path ?>views/assets/js/adminlte/adminlte.min.js"></script>

  <!-- DataTables -->
  <script src="<?php echo $path ?>views/assets/plugins/bower_components/datatables.net/js/jquery.dataTables.min.js"></script>
  <script src="<?php echo $path ?>views/assets/plugins/bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
  <script src="<?php echo $path ?>views/assets/plugins/bower_components/datatables.net-bs/js/dataTables.responsive.min.js"></script>
  <script src="<?php echo $path ?>views/assets/plugins/bower_components/datatables.net-bs/js/responsive.bootstrap.min.js"></script>

  <!-- SweetAlert 2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <!-- By default SweetAlert2 doesn't support IE. To enable IE 11 support, include Promise polyfill:-->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/core-js/2.4.1/core.js"></script>

  <!-- CryptoJS -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.1.1/crypto-js.min.js"></script>

  <!-- iCheck 1.0.1 -->
  <script src="<?php echo $path ?>views/assets/plugins/iCheck/icheck.min.js"></script>

  <!-- InputMask -->
  <script src="<?php echo $path ?>views/assets/plugins/input-mask/jquery.inputmask.js"></script>
  <script src="<?php echo $path ?>views/assets/plugins/input-mask/jquery.inputmask.date.extensions.js"></script>
  <script src="<?php echo $path ?>views/assets/plugins/input-mask/jquery.inputmask.extensions.js"></script>

  <!-- jQuery Number -->
  <script src="<?php echo $path ?>views/assets/plugins/jqueryNumber/jquerynumber.min.js"></script>

  <!-- daterangepicker -->
  <script src="<?php echo $path ?>views/assets/plugins/bower_components/moment/min/moment.min.js"></script>
  <script src="<?php echo $path ?>views/assets/plugins/bower_components/bootstrap-daterangepicker/daterangepicker.js"></script>

  <!-- Morris.js charts -->
  <script src="<?php echo $path ?>views/assets/plugins/bower_components/raphael/raphael.min.js"></script>
  <script src="<?php echo $path ?>views/assets/plugins/bower_components/morris.js/morris.min.js"></script>

  <!-- Librerías para exportación -->
  <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

  <!-- ChartJS -->
  <script src="<?php echo $path ?>views/assets/plugins/bower_components/Chart.js/Chart.js"></script>
</head>

<body class="hold-transition skin-blue sidebar-collapse sidebar-mini login-page">

  <?php
  if (isset($_SESSION['iniciarSesion']) && $_SESSION['iniciarSesion'] == 'ok') {
    // wrapper
    echo '<div class="wrapper">';
    /**header */
    include "modules/header.php";
    /**navbar */
    include "modules/navbar.php";
    /**content */

    if (isset($_GET["ruta"])) {
      $ruta = $_GET["ruta"];

      if (
        $ruta == 'inicio' ||
        $ruta == 'usuarios' ||
        $ruta == 'bitacora' ||
        $ruta == 'roles' ||
        $ruta == 'listarPacientes' ||
        $ruta == 'agregarPacientes' ||
        $ruta == 'listaCitas' ||
        $ruta == 'listaTerapias' ||
        $ruta == 'salir'
      ) {
        if ($ruta == 'salir') {
          // La lógica de salir ahora está en helpers/auth.php -> cerrarSesion()
          // pero la incluimos aquí por si acaso la ruta es llamada directamente
          require_once __DIR__ . '../helpers/bitacora.php';
          cerrarSesion();
        }
        include "pages/" . $ruta . ".php";
      } else {
        include "modules/404.php";
      }
    } else {
      include "pages/inicio.php";
    }

    /**footer */
    include "modules/footer.php";

    echo '</div>';
  } else {
    // Si no hay sesión iniciada
    if (isset($_GET["ruta"]) && $_GET["ruta"] == 'login') {
      // Si la ruta es explícitamente login, incluir login.php
      include "pages/login.php";
    } else {
      // Para cualquier otra ruta o sin ruta, incluir login.php
      include "pages/login.php";
    }
  }

  ?>

  <script src="<?php echo $path ?>views/assets/js/roles/roles.js"></script>
  <script src="<?php echo $path ?>views/assets/js/template/template.js"></script>
  <script src="<?php echo $path ?>views/assets/js/users/users.js"></script>
  <script src="<?php echo $path ?>views/assets/js/categories/categories.js"></script>
  <script src="<?php echo $path ?>views/assets/js/categories/category.js"></script>
  <script src="<?php echo $path ?>views/assets/js/files/files.js"></script>
  <script src="<?php echo $path ?>views/assets/js/bitacora/bitacora.js"></script>

</body>

</html>