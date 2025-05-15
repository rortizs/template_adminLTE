<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class TemplateController
{
  // Load the main template
  public function index()
  {
    include 'views/template.php';
  }

   public static function path()
  {
    $host = $_SERVER['HTTP_HOST'];
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";

    // Obtener la ruta base del proyecto
    $basePath = dirname($_SERVER['SCRIPT_NAME']);

    // Asegurarse de que la ruta base termine con /
    if ($basePath !== '/') {
      $basePath = rtrim($basePath, '/') . '/';
    }

    $fullPath = $protocol . $host . $basePath;

    //error_log("Path Debug - Host: " . $host);
    //error_log("Path Debug - Protocol: " . $protocol);
    //error_log("Path Debug - Base Path: " . $basePath);
    //error_log("Path Debug - Full Path: " . $fullPath);

    return $fullPath;
  }

  //send email
  static public function sendEmail($subject, $email, $recipient_email, $title, $message)
  {
    date_default_timezone_set('America/Guatemala');

    $mail = new PHPMailer(true);
    $mail->CharSet = 'UTF-8';
    //todo:habilitar cuando este en produccion -->//$mail->Encoding = 'base64'; //habilitar al subir a un hosting, no usar en local
    $mail->isMail();
    $mail->UseSendmailOptions = 0;
    $mail->setFrom("noreply@cadept.org.gt", "Cadep El Progreso");

    if ($email == null) {
      $email = "admin@cadept.org.gt";
    }

    $mail->Subject = $subject;
    $mail->addAddress($email);
    $mail->addCC($recipient_email);
    $mail->msgHTML('<div style="width:100%; background:#eee; position:relative; font-family:sans-serif; padding-top:40px; padding-bottom: 40px;">
		
			<div style="position:relative; margin:auto; width:600px; background:white; padding:20px">
				
				<center>
					
					<img src="' . TemplateController::path() . 'views/assets/img/LogoCadep.png"" style="padding:20px; width:30%">

					<h3 style="font-weight:100; color:#999">' . $title . '</h3>

					<hr style="border:1px solid #ccc; width:80%">

					' . $message . '

					<br>

					<hr style="border:1px solid #ccc; width:80%">

					<h5 style="font-weight:100; color:#999">Si necesita cambiar la fecha de su cita o cancelarla, comuniquese con nosotros de inmediato. Al correo admin@cadep.org.gt</h5>

				</center>

			</div>

		</div>');

    $send = $mail->send();

    if (!$send) {
      return $mail->ErrorInfo;
    } else {
      return "ok";
    }
  }
}
