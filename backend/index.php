<?php

//=====DEBUG MODE=====
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . 'error.log');


// Cargar la configuración y todas las dependencias
require_once __DIR__ . '/config/bootstrap.php';

// Instanciar el controlador de plantilla principal
$template = new TemplateController();
$template->index();
