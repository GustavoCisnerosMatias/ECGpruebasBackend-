<?php
require_once('lib/middleware/JwtMiddleware.php');class medicamentos_Ctrl
{
    protected $modelo;

    public function __construct()
    {
        $this->modelo = new M_medicamentos();
    }

//medicamentos listar todo
    public function mostrarmedicamentos($f3)
    {
        $decoded = validateJWT($f3);
        if (!$decoded) return;
        try {
            $medicamentos = $this->modelo->obtenermedicamentos();

            if ($medicamentos) {
                echo json_encode(['medicamentos' => $medicamentos]);
            } else {
                echo json_encode(['mensaje' => 'No se encontraron medicamenos']);
            }
        } catch (Exception $e) {
            echo json_encode(['mensaje' => 'Error al obtener los medicamentos: ' . $e->getMessage()]);
        }
    }


//medicaentos listar por id 
public function mostrarmedicaxid($f3)
{
    try {
        // Obtén el cuerpo de la solicitud como un JSON
        $data = json_decode($f3->get('BODY'), true);

        // Obtén el ID de usuario directamente del JSON
        $id_usuario = $data['id_usuario'];

        
        $medicamentos = $this->modelo->obtenermedicamntosxid($id_usuario);

        if ($medicamentos) {
            echo json_encode(['medicamentos' => $medicamentos]);
        } else {
            echo json_encode(['mensaje' => 'No se encontraron medicamentos']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['mensaje' => 'Error al obtener las los medicamentos: ' . $e->getMessage()]);
    }

}

//Relacion paciente medicamento 
// Relación paciente medicamento 
public function guardarRelacion($f3)
{
    $json = $f3->get('BODY');
    $data = json_decode($json, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(['mensaje' => 'JSON inválido']);
        return;
    }

    $requiredFields = ['id_medic', 'id_usuario'];
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
        $result = $this->modelo->guardarRelacion((int)$data['id_medic'], (int)$data['id_usuario']);

        if ($result) {
            echo json_encode(['mensaje' => 'Relación medicamento_usuario guardada exitosamente']);
        } else {
            echo json_encode(['mensaje' => 'Error al guardar la relación medicamento_usuario']);
        }
    } catch (Exception $e) {
        echo json_encode(['mensaje' => 'Error al guardar la relación medicamento_usuario: ' . $e->getMessage()]);
    }
}

}

