<?php
require_once('lib/middleware/JwtMiddleware.php');
class datos_fisicos_Ctrl
{
    protected $M_datos_fisicos;

    public function __construct()
    {
        $this->M_datos_fisicos = new M_datos_fisicos();
    }



public function listarDatosFisicos($f3)
{
        $decoded = validateJWT($f3);
    if (!$decoded) return;
    // Obtener el cuerpo de la solicitud y decodificar el JSON
    $json = $f3->get('BODY');
    $data = json_decode($json, true);

    // Validar que el JSON sea válido
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(['mensaje' => 'JSON inválido']);
        return;
    }

    // Verificar si se proporcionó id_usuario
    if (empty($data['id_usuario'])) {
        echo json_encode(['mensaje' => 'id_usuario es requerido']);
        return;
    }

    $id_usuario = $data['id_usuario'];

    // Buscar los datos físicos del usuario por id_usuario
    $result = $this->M_datos_fisicos->find(['id_usuario = ?', $id_usuario]);

    // Verificar si se encontraron resultados
    if ($result) {
        $response = [];
        foreach ($result as $item) {
            $response[] = $item->cast(); // Convertir el objeto a array
        }

        // Devolver los datos como JSON
        echo json_encode(['mensaje' => 'Datos encontrados', 'datos' => $response]);
    } else {
        echo json_encode(['mensaje' => 'No se encontraron datos para el id_usuario especificado']);
    }
}

    // Método para crear o actualizar datos físicos del paciente
    public function createOrUpdateDatosFisicos($f3)
    {
            $decoded = validateJWT($f3);
    if (!$decoded) return;
        // Obtener el cuerpo de la solicitud y decodificar el JSON
        $json = $f3->get('BODY');
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode(['mensaje' => 'JSON inválido']);
            return;
        }

        // Mensaje de depuración para ver el contenido de $data
        error_log(print_r($data, true));

        // Verificar si todos los campos necesarios están presentes
        $requiredFields = ['id_usuario', 'peso', 'estatura'];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                echo json_encode(['mensaje' => 'Faltan parámetros: ' . $field]);
                return;
            }
        }

        // Llamar al método de creación o actualización en M_datos_fisicos
        $id_usuario = $data['id_usuario'];
        unset($data['id_usuario']); // Eliminar `id_usuario` del arreglo para evitar conflictos en `copyFrom`

        $result = $this->M_datos_fisicos->createOrUpdateDatosFisicos($id_usuario, $data);

        if ($result) {
            echo json_encode(['mensaje' => 'Datos físicos guardados exitosamente']);
        } else {
            echo json_encode(['mensaje' => 'Error al guardar datos físicos']);
        }
    }
}

