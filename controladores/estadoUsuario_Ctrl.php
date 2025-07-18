<?php
require_once('lib/middleware/JwtMiddleware.php');
class estadoUsuario_Ctrl
{
    protected $modelo;

    public function __construct()
    {
        $this->modelo = new M_estadoUsuario();
    }


    //especialidad
    public function mostrarnota($f3)
    {
            $decoded = validateJWT($f3);
    if (!$decoded) return;
        try {
            $nota = $this->modelo->mostrarnotas();

            if ($nota) {
                echo json_encode(['nota' => $nota]);
            } else {
                echo json_encode(['mensaje' => 'No se encontraron notas']);
            }
        } catch (Exception $e) {
            echo json_encode(['mensaje' => 'Error al obtener notas: ' . $e->getMessage()]);
        }
    }

    // Método para crear un nuevo dispositivo
    public function crearestadoUsuarios($f3)
    {
        // Obtener el cuerpo de la solicitud y decodificar el JSON
        $json = $f3->get('BODY');
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode(['mensaje' => 'JSON inválido']);
            return;
        }

        // Verificar si todos los campos necesarios están presentes en los datos JSON
        $requiredFields = ['id_alertas', 'descripcion'];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                echo json_encode(['mensaje' => 'Faltan parámetros: ' . $field]);
                return;
            }
        }

        try {
            // Crear el nuevo dispositivo usando el modelo
            $result = $this->modelo->createestadoUsuario($data);

            if ($result) {
                echo json_encode(['mensaje' => 'Estado creado exitosamente']);
            } else {
                echo json_encode(['mensaje' => 'Error al crear el estado']);
            }
        } catch (Exception $e) {
            echo json_encode(['mensaje' => 'Error al crear el estado: ' . $e->getMessage()]);
        }
    }
}


