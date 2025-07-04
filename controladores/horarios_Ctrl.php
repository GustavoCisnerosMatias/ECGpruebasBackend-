<?php
require_once('lib/middleware/JwtMiddleware.php');
class horarios_Ctrl
{
    protected $modelo;

    public function __construct()
    {
        $this->modelo = new M_horarios();
    }

    // Método para crear un nuevo dispositivo
    public function crearhorarios($f3)
    {
        // Obtener el cuerpo de la solicitud y decodificar el JSON
        $json = $f3->get('BODY');
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode(['mensaje' => 'JSON inválido']);
            return;
        }

        // Verificar si todos los campos necesarios están presentes en los datos JSON
        $requiredFields = ['id_usuario', 'hora_inicio', 'hora_fin', 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'];
      
        
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                echo json_encode(['mensaje' => 'Faltan parámetros: ' . $field]);
                return;
            }
        }

        try {
            // Crear el nuevo dispositivo usando el modelo
            $result = $this->modelo->createhorarios($data);

            if ($result) {
                echo json_encode(['mensaje' => 'Horario creado exitosamente']);
            } else {
                echo json_encode(['mensaje' => 'Error al crear el horario']);
            }
        } catch (Exception $e) {
            echo json_encode(['mensaje' => 'Error al crear el horario: ' . $e->getMessage()]);
        }
    }



     // Método para actualizar un horario existente
     public function editarhorarios($f3)
     {
         // Obtener el cuerpo de la solicitud y decodificar el JSON
         $json = $f3->get('BODY');
         $data = json_decode($json, true);
 
         if (json_last_error() !== JSON_ERROR_NONE) {
             echo json_encode(['mensaje' => 'JSON inválido']);
             return;
         }
 
         // Verificar si el id del horario está presente
         if (!isset($data['id_horario'])) {
             echo json_encode(['mensaje' => 'Falta el id del horario']);
             return;
         }
 
         try {
             // Editar el horario usando el modelo
             $result = $this->modelo->updatehorarios($data);
 
             if ($result) {
                 echo json_encode(['mensaje' => 'Horario actualizado exitosamente']);
             } else {
                 echo json_encode(['mensaje' => 'Error al actualizar el horario']);
             }
         } catch (Exception $e) {
             echo json_encode(['mensaje' => 'Error al actualizar el horario: ' . $e->getMessage()]);
         }
     }





     // Método para listar los horarios de un usuario
    public function listarhorarios($f3)
    {
        // Obtener el cuerpo de la solicitud y decodificar el JSON
        $json = $f3->get('BODY');
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode(['mensaje' => 'JSON inválido']);
            return;
        }

        // Verificar que se haya enviado el id_usuario
        if (!isset($data['id_usuario'])) {
            echo json_encode(['mensaje' => 'Falta el id_usuario']);
            return;
        }

        try {
            // Obtener los horarios asociados al id_usuario usando el modelo
            $result = $this->modelo->getHorariosByUsuario($data['id_usuario']);

            if ($result) {
                echo json_encode(['horarios' => $result]);
            } else {
                echo json_encode(['mensaje' => 'No se encontraron horarios para el usuario']);
            }
        } catch (Exception $e) {
            echo json_encode(['mensaje' => 'Error al obtener los horarios: ' . $e->getMessage()]);
        }
    }

    //Listar horario telemedicina
  
public function buscarhorarioxid_medico($f3)
{
    // Obtener el cuerpo de la solicitud y decodificar el JSON
    $json = $f3->get('BODY');
    $data = json_decode($json, true);

    // Verificar que el JSON sea válido
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(['mensaje' => 'JSON inválido']);
        return;
    }

    // Verificar que se haya enviado el id_medico
    if (!isset($data['id_medico'])) {
        echo json_encode(['mensaje' => 'Falta el id_medico']);
        return;
    }

    try {
        // Obtener los horarios asociados al id_medico usando el método getHorariosxmedico
        $result = $this->modelo->getHorariosxmedico($data['id_medico']);

        if ($result) {
            echo json_encode(['horarios' => $result]);
        } else {
            echo json_encode(['mensaje' => 'No se encontraron horarios para el médico']);
        }
    } catch (Exception $e) {
        echo json_encode(['mensaje' => 'Error al obtener los horarios: ' . $e->getMessage()]);
    }
}

}


