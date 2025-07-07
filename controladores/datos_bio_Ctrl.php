<?php
require_once('lib/middleware/JwtMiddleware.php');
class datos_bio_Ctrl
{
    protected $modelo;

    public function __construct()
    {
        $this->modelo = new M_datosbio();
    }
    public function obtenerDatosPorUsuario($f3)
        {
        $decoded = validateJWT($f3);
        if (!$decoded) return;

        // Validar método POST
        if ($f3->get('VERB') !== 'POST') {
            echo json_encode(['mensaje' => 'Método no permitido, use POST']);
            return;
        }

        // Obtener datos del body
        $data = json_decode($f3->get('BODY'), true);
        if (json_last_error() !== JSON_ERROR_NONE || !isset($data['id_usuario'])) {
            echo json_encode(['mensaje' => 'Solicitud malformada o falta id_usuario']);
            return;
        }

        $id_usuario = (int)$data['id_usuario'];

        try {
            $datos = $this->modelo->obtenerPorUsuario($id_usuario);

            if ($datos) {
                echo json_encode(['datos_manuales' => $datos]);
            } else {
                echo json_encode(['mensaje' => 'No se encontraron datos para el usuario']);
            }
        } catch (Exception $e) {
            echo json_encode(['mensaje' => 'Error al obtener datos: ' . $e->getMessage()]);
        }
    }


    // POST: Crear un nuevo dato manual
    public function crearDatoManual($f3)
    {
        $decoded = validateJWT($f3);
        if (!$decoded) return;

        try {
            $data = json_decode($f3->get('BODY'), true);
            $resultado = $this->modelo->guardarDato($data);
            echo json_encode(['success' => true, 'mensaje' => 'Dato manual creado', 'data' => $resultado]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'mensaje' => 'Error al crear dato: ' . $e->getMessage()]);
        }
    }

}


