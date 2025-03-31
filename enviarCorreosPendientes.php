<?php
require_once './phpmailer/PHPMailer.php';
require_once './phpmailer/SMTP.php';
require_once './phpmailer/Exception.php';


$mail = new PHPMailer\PHPMailer\PHPMailer();

try {
    // Configuración del servidor SMTP de Gmail
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'proyectoiot64@gmail.com';
    $mail->Password = 'ch k e a p p tz k o x b d r j '; 
    $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

   

   
   // $conexion = new mysqli("127.0.0.1", "root", "", "_Proyectobiomecio");
    $conexion = new mysqli("localhost", "u692003740_Proyecto", "1234Proyecto*", "u692003740_Proyectobiome");
    $query = "SELECT * FROM CorreosPendientes WHERE enviado = 0";
    $result = $conexion->query($query);

    while ($row = $result->fetch_assoc()) {
        // Configura el remitente y destinatario
        $mail->setFrom('noreply@example.com', 'Soporte');
        $mail->addAddress($row['email_destino']); // Dirección del destinatario
        $mail->Subject = $row['asunto']; // Asunto del correo
        $mail->Body = $row['cuerpo']; // Cuerpo del correo

        // Enviar el correo
        if ($mail->send()) {
            // Actualiza el estado de enviado a 1
            $updateQuery = "UPDATE CorreosPendientes SET enviado = 1 WHERE alerta_id = " . $row['alerta_id'];
            $conexion->query($updateQuery);
        } else {
            echo 'No se pudo enviar el correo a ' . $row['email_destino'] . '. Error: ' . $mail->ErrorInfo;
        }
    }

    echo 'Correos enviados exitosamente';
} catch (Exception $e) {
    echo "Error al enviar el correo: " . $mail->ErrorInfo;  // Detalles adicionales de PHPMailer
}


?>
