<?php
require_once 'lib\middleware\JwtMiddleware.php';

class alergias_Ctrl
{
    protected $modelo;

    public function __construct()
    {
        $this->modelo = new M_alergias();
    }

    // Método para mostrar las alertas
    public function mostrarAlergias($f3)
{
    try {
        // Obtén el cuerpo de la solicitud como un JSON
        $data = json_decode($f3->get('BODY'), true);

        // Obtén el ID de usuario directamente del JSON
        $id_paciente = $data['id_paciente'];

        // Obtener las alertas usando el ID de usuario
        $Alergias = $this->modelo->obtenerAlergias($id_paciente);

        if ($Alergias) {
            echo json_encode(['Alergias' => $Alergias]);
        } else {
            echo json_encode(['mensaje' => 'No se encontraron Alergias']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['mensaje' => 'Error al obtener las alergias: ' . $e->getMessage()]);
    }
}




public function guardarAlergias($f3) {
    try {
        $data = json_decode($f3->get('BODY'), true);

        if (!isset($data['id_usuario'])) {
            echo json_encode(['mensaje' => 'El id_usuario es requerido']);
            return;
        }

        // Llamar al modelo para guardar el antecedente familiar
        $resultado = $this->modelo->guardarAlergias($data);

        if  ($resultado) {
            echo json_encode(['mensaje' => 'Alergia exitosamente']);
        } else {
            echo json_encode(['mensaje' => 'Error al guardar la alergia']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['mensaje' => 'Error al guardar la alerta: ' . $e->getMessage()]);
    }
}

public function editarAlergias($f3) {
    try {
        $data = json_decode($f3->get('BODY'), true);

        // Validar que se haya pasado el id_alerta
        if (!isset($data['id_alergia'])) {
            echo json_encode(['mensaje' => 'El id_alergia es requerido']);
            return;
        }

        // Llamar al modelo para editar el antecedente familiar
        $resultado = $this->modelo->editarAlergias($data);

        if ($resultado === 'actualizado') {
            echo json_encode(['mensaje' => 'Alergia actualizado exitosamente']);
        } else {
            echo json_encode(['mensaje' => 'Error al actualizar la alergia']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['mensaje' => 'Error al actualizar la alergia: ' . $e->getMessage()]);
    }
}

public function mostrartipoalergias($f3)
    {
        try {
            $tipoalergias = $this->modelo->obtenertipoalergias();

            if ($tipoalergias) {
                echo json_encode(['tipoalergias' => $tipoalergias]);
            } else {
                echo json_encode(['mensaje' => 'No se encontraron tipo alergias']);
            }
        } catch (Exception $e) {
            echo json_encode(['mensaje' => 'Error al obtener tipo de alergias: ' . $e->getMessage()]);
        }
    }

    //elimianr alergia
    public function eliminarAlergias($f3) {
        try {
            $data = json_decode($f3->get('BODY'), true);
    
            // Validar que se haya pasado el id_alergia
            if (!isset($data['id_alergia'])) {
                echo json_encode(['mensaje' => 'El id_alergia es requerido']);
                return;
            }
    
            // Llamar al modelo para eliminar la alergia
            $resultado = $this->modelo->eliminarAlergia($data['id_alergia']);
    
            if ($resultado === 'eliminado') {
                echo json_encode(['mensaje' => 'Alergia eliminada exitosamente']);
            } else {
                echo json_encode(['mensaje' => 'Error al eliminar la alergia']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['mensaje' => 'Error al eliminar la alergia: ' . $e->getMessage()]);
        }
    }
    
   
}

