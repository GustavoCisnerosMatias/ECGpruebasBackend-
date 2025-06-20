<?php

class datatimereal_Ctrl {

    protected $M_Modelo;
    protected $M_Dispositivos;

    // Constructor que inicializa los modelos necesarios
    public function __construct() {
        $this->M_Modelo = new M_datatimereal();
        $this->M_Dispositivos = new M_dispositivo();
    }

    public function obtenerTopics() {
        $topics = $this->M_Dispositivos->obtenerTopics();
        echo json_encode(['status' => 'success', 'topics' => $topics]);
    }
    
    public function obtenerParametrosEstadistica($f3) {
        $data = json_decode($f3->get('BODY'), true);

        // Validar JSON y existencia del id_usuario
        if (json_last_error() !== JSON_ERROR_NONE || !isset($data['id_usuario'])) {
            echo json_encode(['status' => 'error', 'message' => 'Formato JSON inválido o id_usuario no proporcionado']);
            return;
        }

        $id_usuario = $data['id_usuario'];

        // Llamada al modelo para obtener los parámetros
        $datos = $this->M_Modelo->obtenerParametrosEstadistica($id_usuario);

        // Devolver la respuesta en formato JSON
        echo json_encode([
            'status' => 'success',
            'data' => $datos
        ]);
    }



  public function obtenerdatosagrupados($f3) {
        $data = json_decode($f3->get('BODY'), true);

        // Validar JSON y existencia del id_usuario
        if (json_last_error() !== JSON_ERROR_NONE || !isset($data['id_usuario'])) {
            echo json_encode(['status' => 'error', 'message' => 'Formato JSON inválido o id_usuario no proporcionado']);
            return;
        }

        $id_usuario = $data['id_usuario'];

        // Llamada al modelo para obtener los parámetros
        $datos = $this->M_Modelo->obtenerdatosagrupados($id_usuario);

        // Devolver la respuesta en formato JSON
        echo json_encode([
            'status' => 'success',
            'data' => $datos
        ]);
    }




    public function guardarDatosAgrupados($f3) {
        $data = json_decode($f3->get('BODY'), true);
        if (
            json_last_error() !== JSON_ERROR_NONE ||
            !isset($data['id_usuario'], $data['id_parametro'], $data['valores'], $data['duracion'], $data['id_dispo'])
        ) {
            echo json_encode(['status' => 'error', 'message' => 'Formato JSON inválido o datos incompletos']);
            return;
        }

        $idU = $data['id_usuario'];
        $idP = $data['id_parametro'];
        $vals = is_array($data['valores']) ? $data['valores'] : [];
        $dur = $data['duracion'];
        $disp = $data['id_dispo'];
        $idMedico = $data['idMedico'] ?? null;
        if (empty($vals)) {
            echo json_encode(['status' => 'warning', 'message' => 'No se recibieron valores']);
            return;
        }

        $vals = array_filter($vals, 'is_numeric');
        if (empty($vals)) {
            echo json_encode(['status' => 'error', 'message' => 'Todos los datos están vacíos o son inválidos']);
            return;
        }

        // Paso 1: Obtener estadísticas del usuario
        $userStats = $this->M_Modelo->obtenerEstadisticasUsuario($idU, $idP);
        if (!$userStats) {
            // Primera vez que se registran datos para este usuario y parámetro
            $this->M_Modelo->guardarDatosAgrupados($idU, $idP, $vals, $dur, $disp);

            $fakeStats = [
                'media' => 0,
                'desviacion_estandar' => 0,
                'count' => 0,
                'mean_welford' => 0,
                'm2_welford' => 0
            ];
            if ($idMedico !== null) {
                //Si es medico se crean estadisticas
                $this->M_Modelo->actualizarEstadisticas($vals, $fakeStats, $idU, $idP);
            }

            echo json_encode(['status' => 'success', 'message' => 'Primeros datos almacenados']);
            return;
        }

        // Paso 2: Definir límites personalizados del usuario
        $mu = (float)$userStats['mean_welford'];
        $sd = (float)$userStats['desviacion_estandar'];
        $minU = $mu - $sd;
        $maxU = $mu + $sd;

        // Paso 3: Detectar falsos positivos según límites personalizados
        $falsos = array_filter($vals, function ($x) use ($minU, $maxU) {
            return $x < $minU || $x > $maxU;
        });

        // Paso 4: Guardar todos los datos recibidos
        $this->M_Modelo->guardarDatosAgrupados($idU, $idP, $vals, $dur, $disp);

        // Paso 5: Guardar los falsos positivos
        if (!empty($falsos)) {
            $cant = count($falsos); 
            $this->M_Modelo->guardarFalsosPositivos($falsos, $idU, $idP, $cant, $disp);
        }

        // Si no hay médico, no actualizar estadísticas ni normas
        if ($idMedico === null) {
            echo json_encode(['status' => 'success', 'message' => 'Datos y falsos positivos guardados sin actualizar estadísticas']);
            return;
        }

        $validos = array_filter($vals, function ($x) use ($minU, $maxU) {
            return $x >= $minU && $x <= $maxU;
        });

        if (!empty($validos)) {
            // Paso extra: Verificar si hay nuevos extremos para el parámetro global
            $normasGlobales = $this->M_Modelo->obtenerNormasGlobales($idP); // trae valor_minimo y valor_maximo

            if ($normasGlobales) {
                $minG = (float)$normasGlobales['valor_minimo'];
                $maxG = (float)$normasGlobales['valor_maximo'];

                $nuevoMin = min($validos);
                $nuevoMax = max($validos);

                $actualizar = false;

                if ($nuevoMin < $minG || $nuevoMax > $maxG) {
                    $nuevoMin = ($nuevoMin < $minG) ? $nuevoMin : $minG;
                    $nuevoMax = ($nuevoMax > $maxG) ? $nuevoMax : $maxG;
                    $this->M_Modelo->actualizarNormasGlobales($idP, $nuevoMin, $nuevoMax);
                }
            } else {
                // Si no hay normas, las crea con los valores actuales
                $this->M_Modelo->crearNormasGlobales($idP, min($validos), max($validos));
            }

            // Actualizar estadísticas del usuario
            $this->M_Modelo->actualizarEstadisticas($validos, $userStats, $idU, $idP);
            echo json_encode(['status' => 'success', 'message' => 'Datos guardados y estadísticas actualizadas']);
        } else {
            echo json_encode(['status' => 'warning', 'message' => 'Datos guardados pero todos fueron anómalos para estadísticas']);
        }

    }

}