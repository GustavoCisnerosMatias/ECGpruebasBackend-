<?php

class med_pacie_Ctrl
{
    protected $modelo;

    public function __construct()
    {
        $this->modelo = new M_med_pacie();
    }

   // Método para guardar relación entre médico y paciente
   public function guardarRelacion($f3)
   {
       $json = $f3->get('BODY');
       $data = json_decode($json, true);
   
       if (json_last_error() !== JSON_ERROR_NONE) {
           echo json_encode(['mensaje' => 'JSON inválido']);
           return;
       }
   
       $requiredFields = ['id_medico', 'id_usuario'];
       foreach ($requiredFields as $field) {
           if (!isset($data[$field])) {
               echo json_encode(['mensaje' => 'Faltan parámetros: ' . $field]);
               return;
           }
           if (!is_numeric($data[$field])) {
               echo json_encode(['mensaje' => 'Parámetro inválido: ' . $field]);
               return;
           }
       }
   
       try {
           $result = $this->modelo->guardarRelacion((int)$data['id_medico'], (int)$data['id_usuario']);
   
           if ($result) {
               echo json_encode(['mensaje' => 'Relación medico_paciente guardada exitosamente']);
           } else {
               echo json_encode(['mensaje' => 'Error al guardar la relación medico_paciente']);
           }
       } catch (Exception $e) {
           // Mostrar mensaje de error específico
           echo json_encode(['mensaje' => 'Error al guardar la relación medico_paciente: ' . $e->getMessage()]);
       }
   }
   

    // Método para obtener los datos del médico-paciente basado en el ID del usuario
public function obtenerDatosMedicoPaciente($f3) {
    $id_usuario = $f3->get('GET.id_usuario');  // Obtener el parámetro de la URL

    if (!$id_usuario) {
        echo json_encode(['mensaje' => 'Parámetro id_usuario es requerido']);
        return;
    }

    try {
        $datos = $this->modelo->obtenerDatosMedicoPaciente($id_usuario);

        if ($datos) {
            // Envía los datos en formato JSON
            echo json_encode($datos);
        } else {
            echo json_encode(['mensaje' => 'No se encontraron datos']);
        }
    } catch (Exception $e) {
        echo json_encode(['mensaje' => 'Error al obtener datos: ' . $e->getMessage()]);
    }
}

    

    // Método para obtener los pacientes del medico basado en el ID del usuario
    public function obtenerDatosPaciente($f3) {
        $id_usuario = $f3->get('GET.id_usuario');  // Obtener el parámetro de la URL

        if (!$id_usuario) {
            echo json_encode(['mensaje' => 'Parámetro id_usuario es requerido']);
            return;
        }

        try {
            $datos = $this->modelo->obtenerDatosPaciente($id_usuario);

            if ($datos) {
                echo json_encode($datos);
            } else {
                echo json_encode(['mensaje' => 'No se encontraron datos']);
            }
        } catch (Exception $e) {
            echo json_encode(['mensaje' => 'Error al obtener datos: ' . $e->getMessage()]);
        }
    }


    public function eliminarRelacion($f3) {
        // Obtener el cuerpo de la solicitud (POST)
        $json = $f3->get('BODY');
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode(['mensaje' => 'JSON inválido']);
            return;
        }

        // Verificar si el campo id_medpaci está presente en los datos JSON
        if (!isset($data['id_medpaci'])) {
            echo json_encode(['mensaje' => 'Parámetro id_medpaci es requerido']);
            return;
        }

        $id_medpaci = $data['id_medpaci'];

        try {
            $result = $this->modelo->eliminarRelacion($id_medpaci);

            if ($result) {
                echo json_encode(['mensaje' => 'Relación medico_paciente eliminada exitosamente']);
            } else {
                echo json_encode(['mensaje' => 'Error al eliminar la relación medico_paciente']);
            }
        } catch (Exception $e) {
            echo json_encode(['mensaje' => 'Error al eliminar la relación medico_paciente: ' . $e->getMessage()]);
        }
    }

    public function actualizarEstado($f3)
    {
        $json = $f3->get('BODY');
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode(['mensaje' => 'JSON inválido']);
            return;
        }

        $requiredFields = ['id_medpaci', 'estado'];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                echo json_encode(['mensaje' => 'Faltan parámetros: ' . $field]);
                return;
            }
        }

        try {
            $this->modelo->actualizarEstado($data['id_medpaci'], $data['estado']);
            echo json_encode(['mensaje' => 'Estado actualizado exitosamente']);
        } catch (Exception $e) {
            echo json_encode(['mensaje' => 'Error al actualizar el estado: ' . $e->getMessage()]);
        }
    }



    /*  // Método para obtener los pacientes del medico basado en el ID del usuario
     public function obtenertotalpacientes($f3) {
        $id_usuario = $f3->get('GET.id_usuario');  // Obtener el parámetro de la URL

        if (!$id_usuario) {
            echo json_encode(['mensaje' => 'Parámetro id_usuario es requerido']);
            return;
        }

        try {
            $datos = $this->modelo->total_paceintes($id_usuario);

            if ($datos) {
                echo json_encode($datos);
            } else {
                echo json_encode(['mensaje' => 'No se encontraron datos']);
            }
        } catch (Exception $e) {
            echo json_encode(['mensaje' => 'Error al obtener datos: ' . $e->getMessage()]);
        }
    } */
}
?>