<?php
require_once('lib/middleware/JwtMiddleware.php');
class centro_Ctrl
{
    protected $modelo;

    public function __construct()
    {
        $this->modelo = new M_centro();
    }

    public function mostrarCentro($f3)
    {
        try {
            $centro = $this->modelo->mostrarCentro();

            if ($centro) {
                echo json_encode(['centro' => $centro]);
            } else {
                echo json_encode(['mensaje' => 'No se encontraron los centros hospitalarios']);
            }
        } catch (Exception $e) {
            echo json_encode(['mensaje' => 'Error al obtener el centro hospitalario: ' . $e->getMessage()]);
        }
    }

     public function buscarCentroPorId($f3) {
        $id = $f3->get('PARAMS.id');
        $datos = $this->modelo->buscarPorId($id);
        if ($datos) {
            echo json_encode(['status' => 'success', 'centro' => $datos[0]]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Centro no encontrado']);
        }
    }

    public function insertarCentro($f3) {
        $decoded = validateJWT($f3);
         if (!$decoded) return;
        $data = json_decode($f3->get('BODY'), true);
        if (!isset($data['nombre_centro'], $data['tipo_centro'])) {
            echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
            return;
        }

        $this->modelo->insertarCentro($data['nombre_centro'], $data['tipo_centro']);
        echo json_encode(['status' => 'success', 'message' => 'Centro creado']);
    }

    public function actualizarCentro($f3) {
        $decoded = validateJWT($f3);
         if (!$decoded) return;
        $id = $f3->get('PARAMS.id');
        $data = json_decode($f3->get('BODY'), true);
        if (!isset($data['nombre_centro'], $data['tipo_centro'])) {
            echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
            return;
        }

        $this->modelo->actualizarCentro($id, $data['nombre_centro'], $data['tipo_centro']);
        echo json_encode(['status' => 'success', 'message' => 'Centro actualizado']);
    }

    public function eliminarCentro($f3) {
        $decoded = validateJWT($f3);
        if (!$decoded) return;
        $id = $f3->get('PARAMS.id');
        $this->modelo->eliminarCentro($id);
        echo json_encode(['status' => 'success', 'message' => 'Centro eliminado']);
    }

   
}

