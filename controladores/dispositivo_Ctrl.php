<?php

class dispositivo_Ctrl
{
    protected $modelo;

    public function __construct()
    {
        $this->modelo = new M_dispositivo();
    }
    public function listartodosdispositivos($f3)
    {
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
        // Obtener el cuerpo de la solicitud y decodificar el JSON
        $json = $f3->get('BODY');
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode(['mensaje' => 'JSON inválido']);
            return;
        }

        // Verificar si todos los campos necesarios están presentes en los datos JSON
        $requiredFields = ['id_usuario', 'id_dispo', 'nombre', 'codigo'];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                echo json_encode(['mensaje' => 'Faltan parámetros: ' . $field]);
                return;
            }
        }

        try {
            $updateResult = $this->modelo->actualizarEstadoDispositivo($data['id_dispo'], 'I');
            if ($updateResult) {
                // Crear una nueva instancia del modelo para evitar problemas de sobreescritura
                $nuevoModeloDispositivo = new M_dispositivo();

                // Crear un nuevo dispositivo con el estado 'A' (Activo)
                $nuevoDispositivo = [
                    'id_usuario' => $data['id_usuario'],
                    'nombre' => $data['nombre'],
                    'codigo' => $data['codigo'],
                    'estado' => 'A'
                ];

                $createResult = $nuevoModeloDispositivo->createDispositivo($nuevoDispositivo);

                if ($createResult) {
                    echo json_encode(['mensaje' => 'Nuevo dispositivo actualizado correctamente']);
                    // Actualizar el estado del dispositivo existente a 'I' (Inactivo)
                    $updateResult = $this->modelo->actualizarEstadoDispositivo($data['id_dispo'], 'I');
                } else {
                    echo json_encode(['mensaje' => 'Error al crear el nuevo dispositivo']);
                }
            } else {
                echo json_encode(['mensaje' => 'Error al actualizar el estado del dispositivo']);
            }
        } catch (Exception $e) {
            echo json_encode(['mensaje' => 'Error al procesar la solicitud: ' . $e->getMessage()]);
        }
    }

    
}


