<aside class="main-sidebar" style="background-color: #1C2751;">

  <section class="sidebar">

    <ul class="sidebar-menu" data-widget="tree">
      <?php
      // Debug detallado
      error_log("=== Navbar Debug Start ===");
      error_log("SESSION completa: " . print_r($_SESSION, true));
      error_log("Navbar Debug - Session username: " . (isset($_SESSION["username"]) ? $_SESSION["username"] : "no set"));
      error_log("Navbar Debug - Session perfil: " . (isset($_SESSION["perfil"]) ? $_SESSION["perfil"] : "no set"));

      if (isset($_SESSION["username"])) {
        error_log("Usuario está en sesión");

        //Roles para los menus que debe mostrar 
        //en el navbar 
        //secretaria = 3, terapueta = 4, admin = 2, superadmin = 1

        // Siempre mostramos el menú de inicio
        echo '<li>
					<a href="inicio">
						<i class="fa fa-home"></i>
						<span>Inicio</span>
					</a>
				</li>';

        $role_id = intval($_SESSION["perfil"]);
        error_log("Role ID extraído: " . $role_id);

        // Menú para SuperAdmin (role_id = 1) y Admin (role_id = 2)
        if (in_array($role_id, [1, 2])) {
          echo '<li class="header">GESTIÓN DE USUARIOS</li>';
          error_log("Mostrando menú de Usuarios");
          echo '<li>
						<a href="usuarios">
							<i class="fa fa-users"></i>
							<span>Usuarios</span>
						</a>
					</li>
					<li>
						<a href="roles">
							<i class="fa fa-key"></i>
							<span>Roles</span>
						</a>
					</li>';

          if ($role_id == 1) {
            error_log("Mostrando menú de SuperAdmin");
            echo '<li>
							<a href="bitacora">
								<i class="fa fa-history"></i>
								<span>Bitácora</span>
							</a>
						</li>';
          }
        }

        // Menú para SuperAdmin, Admin y Secretaria (roles 1, 2 y 3)
        if (in_array($role_id, [1, 2, 3])) {
          echo '<li class="header">GESTIÓN DE Pacientes</li>';
          error_log("mostrando menú de Pacientes");
          echo '<li class="treeview">
						<a href="#">
							<i class="fa fa-list-ul"></i>
							<span>Pacientes</span>
							<span class="pull-right-container">
								<i class="fa fa-angle-left pull-right"></i>
							</span>
						</a>
						<ul class="treeview-menu">
							<li>
								<a href="listarPacientes">
									<i class="fa fa-circle-o"></i>
									<span>Listar Pacientes</span>
								</a>
							</li>
							<li>
								<a href="agregarPacientes">
									<i class="fa fa-circle-o"></i>
									<span>Agregar Pacientes</span>
								</a>
							</li>
						</ul>
					</li>';
        }

        // Menú de Citas  role 3 secretaria 
        error_log("Mostrando menú de Citas");
        echo '<li class="header">CADEP</li>';
        echo '<li class="treeview">
					<a href="#">
						<i class="fa fa-list-ul"></i>
						<span>Info. CITAS</span>
						<span class="pull-right-container">
							<i class="fa fa-angle-left pull-right"></i>
						</span>
					</a>
					<ul class="treeview-menu">
						<li>
							<a href="listaCitas">
								<i class="fa fa-circle-o"></i>
								<span>Info Lista Citas</span>
							</a>
						</li>
					</ul>
				</li>';

        // Menú de Terapuestas role 4 (accesible para todos los roles)
        error_log("Mostrando menú de Terapias");
        echo '<li class="header">CADEP</li>';
        echo '<li class="treeview">
					<a href="#">
						<i class="fa fa-list-ul"></i>
						<span>Lista de Terapias</span>
						<span class="pull-right-container">
							<i class="fa fa-angle-left pull-right"></i>
						</span>
					</a>
					<ul class="treeview-menu">
						<li>
							<a href="listaTerapias">
								<i class="fa fa-circle-o"></i>
								<span>Info Lista Terapias</span>
							</a>
						</li>
					</ul>
				</li>';
      } else {
        error_log("Usuario NO está en sesión");
      }
      error_log("=== Navbar Debug End ===");
      ?>

    </ul>

  </section>

</aside>