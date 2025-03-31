<?php

class antecedentes_flia_Ctrl
{
    protected $modelo;

    public function __construct()
    {
        $this->modelo = new M_antecedentes_flia();
    }




//medicaentos listar por id 
public function obtenerante_flia_xid($f3)
{
    try {
        // Obtén el cuerpo de la solicitud como un JSON
        $data = json_decode($f3->get('BODY'), true);

        // Obtén el ID de usuario directamente del JSON
        $id_paciente = $data['id_paciente'];

        
        $ante_flia = $this->modelo->obtenerante_flia_xid($id_paciente);

        if ($ante_flia) {
            echo json_encode(['ante_flia' => $ante_flia]);
        } else {
            echo json_encode(['mensaje' => 'No se encontraron ante_flia']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['mensaje' => 'Error al obtener las los ante_flia: ' . $e->getMessage()]);
    }

}
public function guardarAntecedenteFamiliar($f3) {
    try {
        $data = json_decode($f3->get('BODY'), true);

        if (!isset($data['id_usuario'])) {
            echo json_encode(['mensaje' => 'El id_usuario es requerido']);
            return;
        }

        // Llamar al modelo para guardar el antecedente familiar
        $resultado = $this->modelo->guardarAntecedenteFamiliar($data);

        if ($resultado === 'duplicado') {
            echo json_encode(['mensaje' => 'El antecedente familiar ya existe']);
        } elseif ($resultado) {
            echo json_encode(['mensaje' => 'Antecedente familiar guardado exitosamente']);
        } else {
            echo json_encode(['mensaje' => 'Error al guardar el antecedente familiar']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['mensaje' => 'Error al guardar el antecedente familiar: ' . $e->getMessage()]);
    }
}

public function editarAntecedenteFamiliar($f3) {
    try {
        $data = json_decode($f3->get('BODY'), true);

        // Validar que se haya pasado el id_antecedente_familiar
        if (!isset($data['id_antecedente'])) {
            echo json_encode(['mensaje' => 'El id_antecedente es requerido']);
            return;
        }

        // Llamar al modelo para editar el antecedente familiar
        $resultado = $this->modelo->editarAntecedenteFamiliar($data);

        if ($resultado === 'actualizado') {
            echo json_encode(['mensaje' => 'Antecedente familiar actualizado exitosamente']);
        } else {
            echo json_encode(['mensaje' => 'Error al actualizar el antecedente familiar']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['mensaje' => 'Error al actualizar el antecedente familiar: ' . $e->getMessage()]);
    }
}

}
?>
