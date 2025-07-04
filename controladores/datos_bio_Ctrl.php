<?php
require_once 'lib\middleware\JwtMiddleware.php';

class datos_bio_Ctrl
{
    protected $modelo;

    public function __construct()
    {
        $this->modelo = new M_datosbio();
    }

    // Método para obtener datos en tiempo real basados en id_usuario, id_parametro y rango de fechas
    public function obtenerDatospara($f3)
    {
        // Verificar que la solicitud sea de tipo POST
        if ($f3->get('VERB') !== 'POST') {
            echo json_encode(['mensaje' => 'Método no permitido, use POST']);
            return;
        }

        // Obtener los parámetros desde el cuerpo de la solicitud (asumiendo que es un JSON)
        $data = json_decode($f3->get('BODY'), true);

        // Validar que el cuerpo de la solicitud sea un JSON válido
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode(['mensaje' => 'Solicitud malformada']);
            return;
        }

        // Obtener y validar los parámetros
        $id_usuario = isset($data['id_usuario']) ? (int)$data['id_usuario'] : null;
        $id_parametro = isset($data['id_parametro']) ? (int)$data['id_parametro'] : null;
        $fecha_ini = isset($data['fecha_ini']) ? $data['fecha_ini'] : null;
        $fecha_fin = isset($data['fecha_fin']) ? $data['fecha_fin'] : null;

        if (!$id_usuario || !$id_parametro || !$fecha_ini || !$fecha_fin) {
            echo json_encode(['mensaje' => 'Parámetros incompletos']);
            return;
        }

        // Verificar si las fechas tienen el formato correcto (YYYY-MM-DD)
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_ini) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_fin)) {
            echo json_encode(['mensaje' => 'Formato de fecha incorrecto']);
            return;
        }

        try {
            // Obtener los datos del modelo
            $datos = $this->modelo->obtenerDatos($id_usuario, $id_parametro, $fecha_ini, $fecha_fin);

            if ($datos) {
                echo json_encode($datos);
            } else {
                echo json_encode(['mensaje' => 'No se encontraron datos']);
            }
        } catch (Exception $e) {
            echo json_encode(['mensaje' => 'Error al obtener datos: ' . $e->getMessage()]);
        }
    }
}


