<?php
require_once('lib/middleware/JwtMiddleware.php');
class chat_Ctrl
{
    protected $modelo;

    public function __construct()
    {
        $this->modelo = new M_chat();
    }


    // Mostrar chat filtrado por id_usuario e id_medico
    public function mostrarchatnuevo($f3)
    {
        // Obteniendo el cuerpo de la solicitud en formato JSON
        $data = json_decode($f3->get('BODY'), true);
        $id_usuario = $data['id_usuario'] ?? null;  // Usar null si no está presente
        

        try {
            // Asegúrate de que se han recibido los IDs
            if (is_null($id_usuario) ) {
                echo json_encode(['mensaje' => 'Error: id_usuario  es requerido.']);
                return;
            }

            $chat = $this->modelo->mostrarChatnew($id_usuario);

            if ($chat) {
                echo json_encode(['chat' => $chat]);
            } else {
                echo json_encode(['mensaje' => 'No se encontraron mensajes']);
            }
        } catch (Exception $e) {
            echo json_encode(['mensaje' => 'Error al obtener los mensajes: ' . $e->getMessage()]);
        }
    }
    // Mostrar chat nuevo de medico
    public function mostrarchatnuevomedi($f3)
    {
        // Obteniendo el cuerpo de la solicitud en formato JSON
        $data = json_decode($f3->get('BODY'), true);
        $id_usuario = $data['id_usuario'] ?? null;  // Usar null si no está presente
        

        try {
            // Asegúrate de que se han recibido los IDs
            if (is_null($id_usuario) ) {
                echo json_encode(['mensaje' => 'Error: id_usuario  es requerido.']);
                return;
            }

            $chat = $this->modelo->mostrarChatmedi($id_usuario);

            if ($chat) {
                echo json_encode(['chat' => $chat]);
            } else {
                echo json_encode(['mensaje' => 'No se encontraron mensajes']);
            }
        } catch (Exception $e) {
            echo json_encode(['mensaje' => 'Error al obtener los mensajes: ' . $e->getMessage()]);
        }
    }
     // Mostrar chat nuevo de medico todos 
    public function mostrarchatnuevomeditodos($f3)
{
    // Obteniendo el cuerpo de la solicitud en formato JSON
    $data = json_decode($f3->get('BODY'), true);

    $ids_usuarios = $data['ids_usuarios'] ?? [];  // Lista de id_usuario
    $ids_medicos = $data['ids_medicos'] ?? [];    // Lista de id_medico

    try {
        // Validar que ambas listas no estén vacías
        if (empty($ids_usuarios) || empty($ids_medicos)) {
            echo json_encode(['mensaje' => 'Error: se requieren ids_usuarios e ids_medicos.']);
            return;
        }

        // Obtener los chats no vistos para los usuarios y médicos proporcionados
        $chats = $this->modelo->mostrarChatmeditodos($ids_usuarios, $ids_medicos);

        if (!empty($chats)) {
            // Devolver el resultado en el formato solicitado
            echo json_encode($chats);
        } else {
            echo json_encode(['mensaje' => 'No se encontraron mensajes']);
        }
    } catch (Exception $e) {
        echo json_encode(['mensaje' => 'Error al obtener los mensajes: ' . $e->getMessage()]);
    }
}

   
   

    // Mostrar chat filtrado por id_usuario e id_medico
    public function mostrarchat($f3)
    {
        // Obteniendo el cuerpo de la solicitud en formato JSON
        $data = json_decode($f3->get('BODY'), true);
        $id_usuario = $data['id_usuario'] ?? null;  // Usar null si no está presente
        $id_medico = $data['id_medico'] ?? null;    // Usar null si no está presente

        try {
            // Asegúrate de que se han recibido los IDs
            if (is_null($id_usuario) || is_null($id_medico)) {
                echo json_encode(['mensaje' => 'Error: id_usuario e id_medico son requeridos.']);
                return;
            }

            $chat = $this->modelo->mostrarChat($id_usuario, $id_medico);

            if ($chat) {
                echo json_encode(['chat' => $chat]);
            } else {
                echo json_encode(['mensaje' => 'No se encontraron mensajes']);
            }
        } catch (Exception $e) {
            echo json_encode(['mensaje' => 'Error al obtener los mensajes: ' . $e->getMessage()]);
        }
    }

    public function enviarMensaje($f3)
    {
        // Obteniendo el cuerpo de la solicitud en formato JSON
        $data = json_decode($f3->get('BODY'), true);
        $id_medico = $data['id_medico'] ?? null;
        $id_usuario = $data['id_usuario'] ?? null;
        $mensaje = $data['mensaje'] ?? null;
        $id_rol = $data['id_rol'] ?? null;
        // Verificar que se han recibido todos los datos necesarios
        if (is_null($id_medico) || is_null($id_usuario) || is_null($mensaje)|| is_null($id_rol)) {
            echo json_encode(['mensaje' => 'Error: id_medico, id_usuario y mensaje son requeridos.']);
            return;
        }

        // Verificar que el médico existe
        $medicoExiste = $this->modelo->verificarMedico($id_medico);
        error_log("id_medico: " . $id_medico . ", médico existe: " . ($medicoExiste ? 'sí' : 'no')); // Para depurar

        if (!$medicoExiste) {
            echo json_encode(['mensaje' => 'Error: El médico no existe.']);
            return;
        }

        try {
            $this->modelo->enviarMensaje($id_medico, $id_usuario, $mensaje,$id_rol);
            echo json_encode(['mensaje' => 'Mensaje enviado con éxito']);
        } catch (Exception $e) {
            echo json_encode(['mensaje' => 'Error al enviar el mensaje: ' . $e->getMessage()]);
        }
    }

    // Actualizar la vista de un mensaje
    public function actualizarVista($f3)
    {
        // Obteniendo el cuerpo de la solicitud en formato JSON
        $data = json_decode($f3->get('BODY'), true);
        $id_mensaje = $data['id_mensaje'] ?? null; // Usar null si no está presente

        // Verificar que se ha recibido id_mensaje
        if (is_null($id_mensaje)) {
            echo json_encode(['mensaje' => 'Error: id_mensaje es requerido.']);
            return;
        }

        try {
            $this->modelo->actualizarVista($id_mensaje);
            echo json_encode(['mensaje' => 'Mensaje marcado como visto con éxito']);
        } catch (Exception $e) {
            echo json_encode(['mensaje' => 'Error al actualizar el estado de visto: ' . $e->getMessage()]);
        }
    }


}
