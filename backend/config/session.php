<?php

//=====DEBUG MODE=====
return [
  'save_path' => __DIR__ . '/../session',
  'gc_maxlifetime' => 3600, // 1 hour (60 * 60) default
  'cookie_lifetime' => 0, // until the browser is closed
  'cookie_secure' => 1, // true if using HTTPS
  'cookie_httponly' => 1, // true to prevent JavaScript access
  'use_only_cookies' => 1, // true to prevent session ID in URL
  'use_strict_mode' => 1, // true to prevent session fixation
  'cookie_samesite' => 'Lax', // 'Lax' or 'Strict'
];


//=====PRODUCTION MODE=====
//example: https://example.com/api/v1/getAllUsers (python requests,extract token from response)