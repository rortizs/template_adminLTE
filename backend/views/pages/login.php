<div id="back"></div>

<div class="login-box">

  <div class="login-logo">   

    <img src="<?php echo $path ?>views/assets/img/icono-blanco.png" class="img-responsive" style="padding:30px 100px 0px 100px">

  </div>

  <div class="login-box-body">

    <p class="login-box-msg">Ingresar al sistema</p>

    <form method="post">

      <div class="form-group has-feedback">

        <input type="text" class="form-control" placeholder="Usuario" name="ingUsuario" required>
        <span class="glyphicon glyphicon-user form-control-feedback"></span>

      </div>

      <div class="form-group has-feedback">

        <input type="password" class="form-control" placeholder="Contraseña" name="ingPassword" required>
        <span class="glyphicon glyphicon-lock form-control-feedback"></span>

      </div>

      <div class="row">

        <div class="col-xs-4">

          <button type="submit" class="btn btn-primary btn-block btn-flat">Ingresar</button>

        </div>

      </div>
      <?php
      //validate if the user is logged in
      if (isset($_SESSION['iniciarSesion']) && $_SESSION['iniciarSesion'] == 'ok') {
        echo '<script>
          window.location = "inicio";
        </script>';
      }
      //validate if the user is logged in
      $login = new UsersController();
      $login->ctrLoginUser();
      ?>

      <?php if (isset($_GET['expired'])): ?>
        <script>
          Swal.fire({
            icon: 'info',
            title: 'Sesión finalizada',
            text: 'Tu sesión ha expirado por inactividad. Por favor, inicia sesión nuevamente.',
            confirmButtonText: 'Aceptar'
          });
        </script>
      <?php endif; ?>

    </form>

  </div>

</div>