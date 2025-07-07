<?php
require_once('lib/middleware/JwtMiddleware.php');
class dispositivo_Ctrl
{
    protected $modelo;

    public function __construct()
    {
        $this->modelo = new M_dispositivo();
    }
    public function listartodosdispositivos($f3)
    {
            $decoded = validateJWT($f3);
    if (!$decoded) return;
        try {
            $dispositivos = $this->modelo->getAll();

            if ($dispositivos) {
                echo json_encode(['dispositivos' => $dispositivos]);
            } else {
                echo json_encode(['mensaje' => 'No se encontraron dispositivos']);
            }
        } catch (Exception $e) {
            echo json_encode(['mensaje' => 'Error al obtener dispositivos: ' . $e->getMessage()]);
        }
    }

    // Método para crear un nuevo dispositivo
    public function crearDispositivo($f3)
    {
        // Obtener el cuerpo de la solicitud y decodificar el JSON
        $json = $f3->get('BODY');
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode(['mensaje' => 'JSON inválido']);
            return;
        }

        // Verificar si todos los campos necesarios están presentes en los datos JSON
        $requiredFields = ['id_usuario', 'nombre', 'codigo', 'estado'];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                echo json_encode(['mensaje' => 'Faltan parámetros: ' . $field]);
                return;
            }
        }

        try {
            // Crear el nuevo dispositivo usando el modelo
            $result = $this->modelo->createDispositivo($data);

            if ($result) {
                echo json_encode(['mensaje' => 'Dispositivo creado exitosamente']);
            } else {
                echo json_encode(['mensaje' => 'Error al crear el dispositivo']);
            }
        } catch (Exception $e) {
            echo json_encode(['mensaje' => 'Error al crear el dispositivo: ' . $e->getMessage()]);
        }
    }


    // Método para listar dispositivos por id_usuario
    public function listarDispositivos($f3)
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

        // Verificar si el campo id_usuario está presente
        if (!isset($data['id_usuario'])) {
            echo json_encode(['mensaje' => 'Faltan parámetros: id_usuario']);
            return;
        }

        try {
            // Listar dispositivos usando el modelo
            $result = $this->modelo->listarDispositivosPorUsuario($data['id_usuario']);

            // Retornar el resultado en formato JSON
            echo json_encode(['dispositivos' => $result]);
        } catch (Exception $e) {
            echo json_encode(['mensaje' => 'Error al listar dispositivos: ' . $e->getMessage()]);
        }
    }



    ///EDITAR Y CREAR DISPOSITIVOS 
    // Método para editar un dispositivo y crear uno nuevo
public function editarYCrearDispositivo($f3)
{
        $decoded = validateJWT($f3);
    if (!$decoded) return;
    $json = $f3->get('BODY');
    $data = json_decode($json, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(['mensaje' => 'JSON inválido']);
        return;
    }

    $requiredFields = ['id_usuario', 'nombre', 'codigo'];
    foreach ($requiredFields as $field) {
        if (!isset($data[$field])) {
            echo json_encode(['mensaje' => 'Faltan parámetros: ' . $field]);
            return;
        }
    }

    try {
        $modeloDispositivo = new M_dispositivo();

        // Verificar si el usuario ya tiene un dispositivo activo y desactivarlo
        $dispositivosActivos = $modeloDispositivo->find([
            'id_usuario = ? AND estado = ?', 
            $data['id_usuario'], 'A'
        ]);

        foreach ($dispositivosActivos as $dispositivoActivo) {
            $dispositivoActivo->estado = 'I';
            $dispositivoActivo->save();
        }

        // Buscar si el dispositivo con el código ya existe y está inactivo
        $dispositivos = $modeloDispositivo->find([
            'codigo = ? AND estado = ?', 
            $data['codigo'], 'I'
        ]);

        if (!empty($dispositivos)) {
            // Reactivar y reasignar el dispositivo
            $dispositivo = $dispositivos[0];
            $dispositivo->id_usuario = $data['id_usuario'];
            $dispositivo->estado = 'A';
            $dispositivo->save();

            echo json_encode(['mensaje' => 'Dispositivo reactivado y enlazado correctamente']);
        } else {
            // Crear nuevo dispositivo
            $nuevoDispositivo = [
                'id_usuario' => $data['id_usuario'],
                'nombre' => $data['nombre'],
                'codigo' => $data['codigo'],
                'estado' => 'A'
            ];

            $createResult = $modeloDispositivo->createDispositivo($nuevoDispositivo);

            if ($createResult) {
                echo json_encode(['mensaje' => 'Nuevo dispositivo creado y enlazado correctamente']);
            } else {
                echo json_encode(['mensaje' => 'Error al crear el nuevo dispositivo']);
            }
        }
    } catch (Exception $e) {
        echo json_encode(['mensaje' => 'Error al procesar la solicitud: ' . $e->getMessage()]);
    }
}


    // Método para desactivar un dispositivo por su ID
    public function desactivarDispositivo($f3)
    {
            $decoded = validateJWT($f3);
    if (!$decoded) return;
        $json = $f3->get('BODY');
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode(['mensaje' => 'JSON inválido']);
            return;
        }

        if (!isset($data['id_dispo'])) {
            echo json_encode(['mensaje' => 'Falta el parámetro: id_dispo']);
            return;
        }

        try {
            $result = $this->modelo->actualizarEstadoDispositivo($data['id_dispo'], 'I');

            if ($result) {
                echo json_encode(['mensaje' => 'Dispositivo desactivado correctamente']);
            } else {
                echo json_encode(['mensaje' => 'Dispositivo no encontrado o ya está inactivo']);
            }
        } catch (Exception $e) {
            echo json_encode(['mensaje' => 'Error al desactivar dispositivo: ' . $e->getMessage()]);
        }
    }

}


