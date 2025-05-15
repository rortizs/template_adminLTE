<?php

// 1. first, load 
require_once __DIR__ . "/../config/session.php";

// 2. Load session configuration
require_once __DIR__ . "/../helpers/session_bootstrap.php";

// 3. load helper functions
require_once __DIR__ . "/../helpers/auth.php";
require_once __DIR__ . "/../helpers/bitacora.php";
require_once __DIR__ . "/../helpers/apiHelper.php";

// 4 load manager session and dependency
require_once __DIR__ . "/../vendor/autoload.php";

// 5. Loads Controllers
require_once __DIR__ . "/../controllers/template.controller.php";

// 6. Load models
require_once __DIR__ . "/../models/users.model.php";