<?php

require_once 'phpmailer/PHPMailer.php';
require_once 'phpmailer/SMTP.php';
require_once 'phpmailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

function loadEnv($path) {
    if (!file_exists($path)) return;

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue; // saltar comentarios
        list($key, $value) = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
    }
}

class User_Ctrl
{
    protected $M_Modelo;

    public function __construct()
    {
        $this->M_Modelo = new M_User();
    }



    public function listartodosusuarios($f3)
    {
        try {
            $usuarios = $this->M_Modelo->getTodosusuarios();

            if ($usuarios) {
                echo json_encode(['usuarios' => $usuarios]);
            } else {
                echo json_encode(['mensaje' => 'No se encontraron usuarios']);
            }
        } catch (Exception $e) {
            echo json_encode(['mensaje' => 'Error al obtener usuarios: ' . $e->getMessage()]);
        }
    }
    // Verificar si el correo electrónico ya está registrado
    public function verificarCorreo($f3)
    {
        // Obtener el correo electrónico del cuerpo de la solicitud
        $data = json_decode($f3->get('BODY'), true);
        $correo = $data['correo_electronico'] ?? '';

        // Validar el correo electrónico
        if (empty($correo)) {
            $f3->status(400); // Bad Request
            echo json_encode(['error' => 'El correo electrónico es requerido.']);
            return;
        }

        // Llamar al método del modelo para verificar si el correo existe
        $existe = $this->M_Modelo->checkCorreoExists($correo);

        // Devolver la respuesta
        if ($existe) {
            echo json_encode(['existe' => true, 'mensaje' => 'El correo electrónico ya está registrado por favor intente con otro.']);
        } else {
            echo json_encode(['existe' => false, 'mensaje' => 'Correo correcto continue con su registro.']);
        }
    }

    
    // Método para listar usuarios
    public function listado($f3)
    {
        $result = $this->M_Modelo->find();
        $items = array();
        foreach ($result as $datos) {
            $items[] = $datos->cast();
        }
        echo json_encode([
            'mensaje' => count($items) > 0 ? '' : 'Aún no hay registros para mostrar.',
            'total' => count($items),
            'datos' => $items
        ]);
    }

    // Método para buscar id_usuario por cédula
    public function buscarIdUsuarioPorCedula($f3)
    {
        $cedula = $f3->get('GET.cedula');

        // Validar que se haya proporcionado la cédula
        if (!$cedula) {
            echo json_encode(['mensaje' => 'Falta el parámetro: cedula']);
            return;
        }

        // Llamar al método en el modelo para buscar el id_usuario por cédula
        $id_usuario = $this->M_Modelo->getIdUsuarioByCedula($cedula);

        if ($id_usuario !== false) {
            echo json_encode(['id_usuario' => $id_usuario]);
        } else {
            echo json_encode(['mensaje' => 'No se encontró ningún usuario con esa cédula']);
        }
    }

    // Método para buscar usuario por id_usuario
    public function buscarPorCedulaedi($f3)
    {
        $id_usuario = $f3->get('GET.id_usuario');

        // Validar que se haya proporcionado el id_usuario
        if (!$id_usuario) {
            echo json_encode(['mensaje' => 'Falta el parámetro: id_usuario']);
            return;
        }

        // Crear instancia del modelo M_User
        $usuarioModel = new M_User();

        // Obtener datos del usuario por id_usuario
        $datosUsuario = $usuarioModel->getUsuarioById($id_usuario);

        if ($datosUsuario !== null) {
            echo json_encode($datosUsuario);
        } else {
            echo json_encode(['mensaje' => 'No se encontró ningún usuario con ese id_usuario']);
        }
    }

    // Método para editar usuario
    public function editarUsuario($f3)
    {
        $json = $f3->get('BODY');
        $data = json_decode($json, true);

        // Verifica si todos los campos necesarios están presentes en $data
        $requiredFields = ['id_usuario', 'correo_electronico', 'telefono'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                echo json_encode(['mensaje' => 'Faltan parámetros: ' . $field]);
                return;
            }
        }

        // Actualiza el usuario
        $result = $this->M_Modelo->editarUsuario($data);

        if ($result) {
            echo json_encode(['mensaje' => 'Usuario actualizado exitosamente']);
        } else {
            echo json_encode(['mensaje' => 'Error al actualizar el usuario']);
        }
    }
     // Método para editar estado
     public function editarUsuarioestado($f3)
     {
         $json = $f3->get('BODY');
         $data = json_decode($json, true);
 
         // Verifica si todos los campos necesarios están presentes en $data
         $requiredFields = ['id_usuario','cedula','nombre','apellido','telefono','correo_electronico','id_rol','estado'];
         foreach ($requiredFields as $field) {
             if (!isset($data[$field])) {
                 echo json_encode(['mensaje' => 'Faltan parámetros: ' . $field]);
                 return;
             }
         }
 
         // Actualiza el usuario
         $result = $this->M_Modelo->editarestado($data);
 
         if ($result) {
             echo json_encode(['mensaje' => 'Usuario actualizado exitosamente']);
         } else {
             echo json_encode(['mensaje' => 'Error al actualizar el usuario']);
         }
     }

    // Método para editar contraseña
    public function edicontrs($f3) {
        $json = $f3->get('BODY');
        $data = json_decode($json, true);
    
        // Verificar si todos los campos necesarios están presentes en $data
        $requiredFields = ['id_usuario', 'contrasena_actual', 'nueva_contrasena'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                echo json_encode(['mensaje' => 'Faltan parámetros: ' . $field]);
                return;
            }
        }
    
        // Obtener la contraseña actual encriptada del usuario desde la base de datos
        $usuarioActual = $this->M_Modelo->obtenercontrasenaPorId($data['id_usuario']);
        if (!$usuarioActual) {
            echo json_encode(['mensaje' => 'Usuario no encontrado']);
            return;
        }
    
        // Verificar si el hash de la contraseña actual proporcionada coincide con el hash guardado
        if (!password_verify($data['contrasena_actual'], $usuarioActual['contrasena'])) {
            echo json_encode(['mensaje' => 'La contraseña actual no es válida']);
            return;
        }
    
        // Encriptar la nueva contraseña antes de actualizarla en la base de datos
        $nuevaContrasenaEncriptada = password_hash($data['nueva_contrasena'], PASSWORD_DEFAULT);
    
        // Actualizar la contraseña del usuario
        $result = $this->M_Modelo->ediucontra($data['id_usuario'], $nuevaContrasenaEncriptada);
    
        if ($result) {
            echo json_encode(['mensaje' => 'Contraseña actualizada exitosamente']);
        } else {
            echo json_encode(['mensaje' => 'Error al actualizar la contraseña']);
        }
    }
    


// Método para manejar la autenticación de usuario
public function authenticate($f3)
{
   // error_log("POST Data: " . print_r($f3->get('POST'), true));

    $cedula = $f3->get('POST.cedula');
    $password = $f3->get('POST.contrasena');

    // Verificar si los parámetros están definidos
    if (empty($cedula) || empty($password)) {
        echo json_encode([
            'mensaje' => 'Faltan parámetros: cedula y/o contrasena'
        ]);
        return;
    }

    // Limpiar las entradas
    $cedula = trim($cedula);
    $password = trim($password);

    // Cargar el usuario de la base de datos por nombre de usuario
    $this->M_Modelo->load(['cedula = ?', $cedula]);

    // Verificar si se encontró el usuario
    if ($this->M_Modelo->id_usuario) {
        // Verificar si la contraseña es correcta utilizando password_verify
        if (password_verify($password, $this->M_Modelo->contrasena)) {
            // Verificar si el usuario está activo
            if ($this->M_Modelo->estado == 'A') {
                // Obtener el menú usando el modelo
                $menu = $this->M_Modelo->getMenu($this->M_Modelo->id_usuario);
                $peso = $this->M_Modelo->getpeso($this->M_Modelo->id_usuario);
                $dispo = $this->M_Modelo->getdispo($this->M_Modelo->id_usuario);

                // Construir la respuesta JSON
                echo json_encode([
                    'mensaje' => 'Autenticación exitosa',
                    'id_rol' => $this->M_Modelo->id_rol,
                    'nombre' => $this->M_Modelo->nombre,
                    'id_usuario' => $this->M_Modelo->id_usuario,
                    'menu' => $menu,
                    'peso' => $peso,
                    'topic' => $dispo
                    
                ]);
            } else {
                echo json_encode([
                    'mensaje' => 'Usuario inactivo',
                ]);
            }
        } else {
            echo json_encode([
                'mensaje' => 'Usuario o contraseña incorrectos',
            ]);
        }
    } else {
        echo json_encode([
            'mensaje' => 'Usuario o contraseña incorrectos',
        ]);
    }
}

public function createUser($f3)
{
    // Obtener el cuerpo de la solicitud y decodificar el JSON
    $json = $f3->get('BODY');
    $data = json_decode($json, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(['mensaje' => 'El formato de los datos no es válido, por favor revise el JSON.']);
        return;
    }

    // Verificar si todos los campos necesarios están presentes
    $requiredFields = [
        'nombre' => 'Nombre', 
        'apellido' => 'Apellido', 
        'cedula' => 'Cédula', 
        'fecha_nacimiento' => 'Fecha de Nacimiento',
        'correo_electronico' => 'Correo Electrónico', 
        'telefono' => 'Teléfono', 
        'contrasena' => 'Contraseña', 
        'id_rol' => 'Rol', 
        'estado' => 'Estado',
        'id_pais_origen' => 'id_pais_origen',
        'Genero' => 'Genero'

    ];

  

   /* // Verificar si la cédula ya está registrada (descomentar cuando esté implementado)
     if ($this->M_Modelo->checkCedulaExists($data['cedula'])) {
        echo json_encode(['mensaje' => 'La cédula "' . $data['cedula'] . '" ya está registrada en el sistema.']);
        return;
    }  */

    /* // Verificar si el correo electrónico ya está registrado (descomentar cuando esté implementado)
     if ($this->M_Modelo->checkCorreoExists($data['correo_electronico'])) {
        echo json_encode(['mensaje' => 'El correo electrónico "' . $data['correo_electronico'] . '" ya está registrado, ingrese otro.']);
        return;
    }  */



    // Encriptar la contraseña antes de guardarla
    $data['contrasena'] = password_hash($data['contrasena'], PASSWORD_DEFAULT);

    // Crear el nuevo usuario
    $id_usuario = $this->M_Modelo->createUser($data);

    if ($id_usuario) {
        
        echo json_encode(['mensaje' => 'Usuario creado exitosamente', 'id_usuario' => $id_usuario]);
    } else {
        echo json_encode(['mensaje' => 'Ocurrió un error al crear el usuario, inténtelo nuevamente.']);
    }
}


// Registrar asistente
public function createUserasistente($f3)
{
    // Obtener el cuerpo de la solicitud y decodificar el JSON
    $json = $f3->get('BODY');
    $data = json_decode($json, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(['mensaje' => 'Formato de JSON no válido.']);
        return;
    }

    // Verificar si el id_medico está presente
    if (!isset($data['id_medicoss']) || empty($data['id_medicoss'])) {
        echo json_encode(['mensaje' => 'El campo "id_medico" es obligatorio y no puede estar vacío.']);
        return;
    }

    // Verificar si el id_usuario está presente
    if (!isset($data['id_usuario']) || empty($data['id_usuario'])) {
        echo json_encode(['mensaje' => 'El campo "id_usuario" es obligatorio y no puede estar vacío.']);
        return;
    }

    // Establecer el estado como "A"
    $data['estado'] = 'A';

    // Crear el asistente en la base de datos
    $id_asistente = $this->M_Modelo->createasis($data);

    // Verificar si la creación fue exitosa
    if ($id_asistente !== false) {
        echo json_encode(['mensaje' => 'Asistente creado exitosamente', 'id_asistente' => $id_asistente]);
    } else {
        echo json_encode(['mensaje' => 'Ocurrió un error al crear el asistente, inténtelo nuevamente.']);
    }
}



//crear medico 

public function createUsermedico($f3)
{
    // Obtener el cuerpo de la solicitud y decodificar el JSON
    $json = $f3->get('BODY');
    $data = json_decode($json, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        //echo json_encode(['mensaje' => 'El formato de los datos no es válido, por favor revise el JSON.']);
        return;
    }

    // Verificar si todos los campos necesarios están presentes
    $requiredFields = [
        'nombre' => 'Nombre', 
        'apellido' => 'Apellido', 
        'cedula' => 'Cédula', 
        'fecha_nacimiento' => 'Fecha de Nacimiento',
        'correo_electronico' => 'Correo Electrónico', 
        'telefono' => 'Teléfono', 
        'id_pais' => 'País', 
        'id_provincia' => 'Provincia',
        'id_canton' => 'Cantón',  
        'contrasena' => 'Contraseña', 
        'id_rol' => 'Rol', 
        'estado' => 'Estado',
        'id_pais_origen' => 'id_pais_origen',
        'Genero' => 'Genero'

    ];

    foreach ($requiredFields as $field => $displayName) {
        if (!isset($data[$field]) || empty($data[$field])) {
            echo json_encode(['mensaje' => 'El campo "' . $displayName . '" es obligatorio y no puede estar vacío.']);
            return;
        }
    }

    // Verificar si la cédula ya está registrada (descomentar cuando esté implementado)
    if ($this->M_Modelo->checkCedulaExists($data['cedula'])) {
        echo json_encode(['mensaje' => 'El usuario con la cedula "' . $data['cedula'] . '" Ya esta registrado en el sistema.']);
        return;
    }

    // Verificar si el correo electrónico ya está registrado (descomentar cuando esté implementado)
     if ($this->M_Modelo->checkCorreoExists($data['correo_electronico'])) {
        echo json_encode(['mensaje' => 'El correo electrónico "' . $data['correo_electronico'] . '" ya está registrado, ingrese otro.']);
        return;
    } 



    // Encriptar la contraseña antes de guardarla
    $data['contrasena'] = password_hash($data['contrasena'], PASSWORD_DEFAULT);

    // Crear el nuevo usuario
    $id_usuario = $this->M_Modelo->createUser($data);

    if ($id_usuario) {
        echo json_encode(['mensaje' => 'Usuario creado exitosamente', 'id_usuario' => $id_usuario]);
    } else {
        echo json_encode(['mensaje' => 'Ocurrió un error al crear el usuario, inténtelo nuevamente.']);
    }
}


//////////////Recuperar contraseña////////////////////////////////////////
  
 
    // Método para recuperar la contraseña
    public function recuperarContrasena($f3)
    {
         // Obtiene el correo electrónico del cuerpo de la solicitud (JSON)
        $data = json_decode($f3->get('BODY'), true);
        $correo = isset($data['correo']) ? $data['correo'] : null;

        // Validar que el correo no sea nulo y tenga un formato correcto
        if (empty($correo) || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['mensaje' => 'Correo electrónico inválido.']);
            return;
        }

        // Verificar si el correo existe
        $usuario = $this->M_Modelo->getIdUsuarioByCorreo($correo);
        if (!$usuario) {
            echo json_encode(['mensaje' => 'Correo no encontrado.']);
            return;
        }

        // Generar un token de recuperación único de 6 dígitos
        $token = random_int(100000, 999999); // Genera un número aleatorio entre 100000 y 999999

        $tokenExpiracion = date('Y-m-d H:i:s', strtotime('+10 minutes')); // Token válido por 10 minutos
        

        // Almacenar el token en la base de datos
        $this->M_Modelo->guardarToken($usuario, $token, $tokenExpiracion);

        // Enviar el correo
        $mail = new PHPMailer(true);
        try {
            // Configuración del servidor SMTP
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // Para Gmail
 
            $mail->SMTPAuth = true;
            $mail->Username = 'proyectoiot64@gmail.com'; // Tu dirección de correo
            $mail->Password = 'ch k e a p p tz k o x b d r j​ '; // Tu contraseña
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587; // Puerto del servidor SMTP

            // Remitente y destinatario
            $mail->setFrom('noreply@example.com', 'Soporte'); // Cambia la dirección y nombre
            $mail->addAddress($correo); // Agrega el destinatario

            // Contenido del correo
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = 'Recuperación de credenciales ';
            $mail->Body = "Aquí tienes tu código de recuperación: <strong>$token</strong>";
            $mail->AltBody = "Aquí tienes tu código de recuperación: $token";

            // Enviar el correo
            $mail->send();
            echo json_encode(['mensaje' => 'Correo enviado.']);
        } catch (Exception $e) {
            echo json_encode(['mensaje' => "Error al enviar el correo: {$mail->ErrorInfo}"]);
        }
    }


// Método para verificar el token y actualizar la contraseña
public function verificarTokenYActualizarContrasena($f3)
{
    $data = json_decode($f3->get('BODY'), true);
    $correo = isset($data['correo']) ? $data['correo'] : null;
    $token = isset($data['token']) ? $data['token'] : null;
    $nuevaContrasena = isset($data['nueva_contrasena']) ? $data['nueva_contrasena'] : null;

    // Validar que el correo y el token no sean nulos
    if (empty($correo) || empty($token) || empty($nuevaContrasena)) {
        echo json_encode(['mensaje' => 'Todos los campos son obligatorios.']);
        return;
    }

    // Verificar si el usuario existe
    $usuarioId = $this->M_Modelo->getIdUsuarioByCorreo($correo);
    if (!$usuarioId) {
        echo json_encode(['mensaje' => 'Correo no encontrado.']);
        return;
    }

    // Verificar el token
    if (!$this->M_Modelo->verificarToken($usuarioId, $token)) {
        echo json_encode(['mensaje' => 'Token inválido o ya utilizado.']); // Mensaje actualizado
        return;
    }

    // Actualizar la contraseña
    if ($this->M_Modelo->actualizarContrasena($usuarioId, $nuevaContrasena)) {
        echo json_encode(['mensaje' => 'Contraseña actualizada con éxito.']);
    } else {
        echo json_encode(['mensaje' => 'Error al actualizar la contraseña.']);
    }
}



public function verificarTokenYActualizarUsername($f3)
{
    $data = json_decode($f3->get('BODY'), true);
    $correo = isset($data['correo']) ? $data['correo'] : null;
    $token = isset($data['token']) ? $data['token'] : null;


    // Validar que el correo, el token y el nuevo nombre de usuario no sean nulos
    if (empty($correo) || empty($token) ) {
        echo json_encode(['mensaje' => 'Todos los campos son obligatorios.']);
        return;
    }

    // Verificar si el usuario existe
    $usuarioId = $this->M_Modelo->getIdUsuarioByCorreo($correo);
    if (!$usuarioId) {
        echo json_encode(['mensaje' => 'Correo no encontrado.']);
        return;
    }

    // Verificar el token
    if (!$this->M_Modelo->verificarToken($usuarioId, $token)) {
        echo json_encode(['mensaje' => 'Token inválido o ya utilizado.']);
        return;
    }

}
public function bloquearusuariod($f3){
    $data = json_decode($f3->get('BODY'), true);
    $username = isset($data['username']) ? $data['username'] : null;
    $bloqueado = isset($data['bloqueado']) ? $data['bloqueado'] : null;

    if (empty($username) || empty($bloqueado)) {
        echo json_encode(['mensaje' => 'Todos los campos son obligatorios.']);
        return;
    }

    // bloquear
   
    if ($this->M_Modelo->bloquearus($username, $bloqueado)) {
        echo json_encode(['mensaje' => 'usuario bloqueado.']);
    } else {
        echo json_encode(['mensaje' => 'Error de bloqueo.']);
    }

}

public function desbloquearuser($f3) {
    header('Content-Type: application/json');

    try {
        // Cargar variables de entorno
        loadEnv(__DIR__ . '/../.env'); // Ajusta la ruta según tu estructura

        $data = json_decode($f3->get('BODY'), true);
        $cedula = isset($data['cedula']) ? trim($data['cedula']) : null;
        $correo = isset($data['correo']) ? trim($data['correo']) : null;

        if (empty($cedula) || empty($correo)) {
            echo json_encode(['mensaje' => 'Todos los campos son obligatorios.']);
            return;
        }

        // Cargar usuario por cédula
        $this->M_Modelo->load(['cedula = ?', $cedula]);

        if ($this->M_Modelo->dry()) {
            echo json_encode(['mensaje' => 'Usuario no encontrado.']);
            return;
        }

        // Verificar si el correo coincide
        if ($this->M_Modelo->correo_electronico !== $correo) {
            echo json_encode(['mensaje' => 'El correo no coincide con la cédula ingresada.']);
            return;
        }

        // Verificar si el usuario NO está bloqueado
        if ($this->M_Modelo->estado !== 'B') {
            echo json_encode(['mensaje' => 'El usuario no está bloqueado.']);
            return;
        }
        // Obtener el ID del usuario
        $id_usuario = $this->M_Modelo->id_usuario;

        // Generar token de recuperación
        $token = random_int(100000, 999999);
        $tokenExpiracion = date('Y-m-d H:i:s', strtotime('+5 minutes'));

        // Guardar token en la base de datos

        $this->M_Modelo->guardarToken($id_usuario, $token, $tokenExpiracion);


        // Enviar el correo con PHPMailer
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = $_ENV['SMTP_USER'];
            $mail->Password = $_ENV['SMTP_PASS'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('noreply@example.com', 'Soporte');
            $mail->addAddress($correo);
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = 'Recuperación de credenciales ViTECED';
            $mail->Body = "Aquí tienes tu código de recuperación: <strong>$token</strong>";
            $mail->AltBody = "Aquí tienes tu código de recuperación: $token";

            $mail->send();
            echo json_encode(['mensaje' => 'Código de recuperación enviado']);
        } catch (Exception $e) {
            echo json_encode(['mensaje' => "Error al enviar el correo: {$mail->ErrorInfo}"]);
        }

    } catch (Exception $e) {
        error_log("Error en desbloquearuser: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}
    


// public function obtenerTopicforUser($f3)
// {
//     // Obtener el id_usuario desde la petición POST
//     $idUsuario = $f3->get('POST.id_usuario');

//     // Validar que el parámetro esté presente
//     if (empty($idUsuario)) {
//         echo json_encode([
//             'mensaje' => 'Falta el parámetro id_usuario'
//         ]);
//         return;
//     }

//     // Limpiar el valor
//     $idUsuario = trim($idUsuario);

//     // Cargar los datos del usuario
//     $this->M_Modelo->load(['id_usuario = ?', $idUsuario]);

//     // Verificar si el usuario existe
//     if ($this->M_Modelo->id_usuario) {
//         // Obtener el topic/dispositivo
//         $dispo = $this->M_Modelo->getdispo($idUsuario);

//         echo json_encode([
//             'mensaje' => 'Topic obtenido correctamente',
//             'topic' => $dispo
//         ]);
//     } else {
//         echo json_encode([
//             'mensaje' => 'Usuario no encontrado'
//         ]);
//     }
// }
public function obtenerTopicforUser($f3)
{
    // Obtener el cuerpo de la solicitud y decodificar el JSON
    $json = $f3->get('BODY');
    $data = json_decode($json, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(['mensaje' => 'JSON inválido']);
        return;
    }

    // Verificar que el parámetro exista
    if (!isset($data['id_usuario'])) {
        echo json_encode(['mensaje' => 'Falta el parámetro id_usuario']);
        return;
    }

    $idUsuario = trim($data['id_usuario']);

    // Cargar los datos del usuario
    $this->M_Modelo->load(['id_usuario = ?', $idUsuario]);

    // Verificar si el usuario existe
    if ($this->M_Modelo->id_usuario) {
        $dispo = $this->M_Modelo->getdispo($idUsuario);

        echo json_encode([
            'mensaje' => 'Topic obtenido correctamente',
            'topic' => $dispo
        ]);
    } else {
        echo json_encode(['mensaje' => 'Usuario no encontrado']);
    }
}

}


