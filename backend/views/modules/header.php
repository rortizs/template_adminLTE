<header class="main-header" style="background-color: #FF0000;">

  <!--=====================================
   LOGOTIPO
   ======================================-->
  <a href="inicio" class="logo" style="background-color: #FF0000;">

    <!-- logo mini -->
    <span class="logo-mini">

      <img src="<?php echo $path ?>views/assets/img/icono-logoBlanco.png" class="img-responsive" style="padding:10px">

    </span>

    <!-- logo normal -->

    <span class="logo-lg">

      <img src="<?php echo $path ?>views/assets/img/logo-blanco-lineal.png" class="img-responsive" style="padding:10px 0px; background-color: #FFFFFF">

    </span>

  </a>

  <!--=====================================
   BARRA DE NAVEGACIÓN
   ======================================-->
  <nav class="navbar navbar-static-top" role="navigation" style="background-color: #1C2751;">

    <!-- Botón de navegación -->

    <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">

      <span class="sr-only">Toggle navigation</span>

    </a>

    <!-- perfil de usuario -->

    <div class="navbar-custom-menu">

      <ul class="nav navbar-nav">

        <li class="dropdown user user-menu">

          <a href="#" class="dropdown-toggle" data-toggle="dropdown">

            <?php

            echo '<img src="views/assets/img/usuarios/default/anonymous.png" class="user-image">';


            ?>

            <span class="hidden-xs"><?php echo $_SESSION["username"]; ?></span>

          </a>

          <!-- Dropdown-toggle -->

          <ul class="dropdown-menu">

            <li class="user-body">

              <div class="pull-right">

                <a href="salir" class="btn btn-default btn-flat">Salir</a>

              </div>

            </li>

          </ul>

        </li>

      </ul>

    </div>

  </nav>

</header>