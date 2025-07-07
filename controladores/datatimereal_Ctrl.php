<?php
require_once('lib/middleware/JwtMiddleware.php');
class datatimereal_Ctrl {

    protected $M_Modelo;
    protected $M_Dispositivos;
    protected $M_alertas;


    // Constructor que inicializa los modelos necesarios
    public function __construct() {
        $this->M_Modelo = new M_datatimereal();
        $this->M_Dispositivos = new M_dispositivo();
        $this->M_alertas = new M_alertas();


    }

    public function obtenerTopics() {
        $decoded = validateJWT($f3);
        if (!$decoded) return;
        $topics = $this->M_Dispositivos->obtenerTopics();
        echo json_encode(['status' => 'success', 'topics' => $topics]);
    }
    
    public function obtenerParametrosEstadistica($f3) {
            $decoded = validateJWT($f3);
    if (!$decoded) return;
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
            $decoded = validateJWT($f3);
    if (!$decoded) return;
        $data = json_decode($f3->get('BODY'), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode(['status' => 'error', 'message' => 'Formato JSON inválido']);
            return;
        }

        if (!isset($data['id_usuario'], $data['id_parametro'], 
            $data['fechaInicio'], $data['fechaFin'])) {
            echo json_encode([
                'status' => 'error', 
                'message' => 'Faltan campos requeridos',
                'campos_faltantes' => [
                    !isset($data['id_usuario']) ? 'id_usuario' : null,
                    !isset($data['id_parametro']) ? 'id_parametro' : null,
                    !isset($data['fechaInicio']) ? 'fechaInicio' : null,
                    !isset($data['fechaFin']) ? 'fechaFin' : null
                ]
            ]);
            return;
        }

        $id_usuario = $data['id_usuario'];
        $id_parametro = $data['id_parametro'];
        $fechaInicio = $data['fechaInicio'];
        $fechaFin = $data['fechaFin'];


        // Llamada al modelo para obtener los parámetros
        $datos = $this->M_Modelo->obtenerdatosagrupados($id_usuario,$id_parametro , $fechaInicio,$fechaFin );

        // Devolver la respuesta en formato JSON
        echo json_encode([
            'status' => 'success',
            'data' => $datos
        ]);
    }

    public function guardarDatosAgrupados($f3) {
            $decoded = validateJWT($f3);
    if (!$decoded) return;
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

        // Paso 0: Obtener normas globales del parámetro
        $normasGlobales = $this->M_Modelo->obtenerNormasGlobales($idP);

        if (!$normasGlobales) {
            // No existen normas globales → calcular mínimo y máximo de los valores recibidos
            $minGlobal = min($vals);
            $maxGlobal = max($vals);

            // Guardar esas normas globales para que sirvan en el futuro
            $this->M_Modelo->guardarNormasGlobales($idP, $minGlobal, $maxGlobal);
        } else {
            $minGlobal = (float) $normasGlobales['valor_minimo'];
            $maxGlobal = (float) $normasGlobales['valor_maximo'];
        }

        // Paso 0.1: Filtrar valores usando las normas globales
        $valsLimpiosGlobales = array_filter($vals, function ($x) use ($minGlobal, $maxGlobal) {
            return $x >= $minGlobal && $x <= $maxGlobal;
        });

        if (empty($valsLimpiosGlobales)) {
            echo json_encode(['status' => 'error', 'message' => 'Todos los datos están fuera del rango global permitido']);
            return;
        }

        // Paso 1: Obtener estadísticas del usuario
        $userStats = $this->M_Modelo->obtenerEstadisticasUsuario($idU, $idP);
        if (!$userStats) {
            // Primera vez que se registran datos para este usuario y parámetro
            $cantValoresCrudos= count($vals);
            $idDatoAgrupado = $this->M_Modelo->guardarDatosAgrupados($idU, $idP, $vals, $dur, $disp, $cantValoresCrudos);

            $fakeStats = [
                'media' => 0,
                'desviacion_estandar' => 0,
                'count' => 0,
                'mean_welford' => 0,
                'm2_welford' => 0
            ];

            if ($idMedico !== null) {
                $this->M_Modelo->actualizarEstadisticas($valsLimpiosGlobales, $fakeStats, $idU, $idP);
            }
            $cantValoresNormales= count($valsLimpiosGlobales);
            $this->M_Modelo->guardardatoslimpios($valsLimpiosGlobales, $idU, $idP, $cantValoresNormales, $disp, $idDatoAgrupado);
            echo json_encode(['status' => 'success', 'message' => 'Primeros datos almacenados']);
            return;
        }

        // Paso 2: Definir límites personalizados del usuario
        $mu = (float)$userStats['mean_welford'];
        $sd = (float)$userStats['desviacion_estandar'];
        $minU = $mu - $sd;
        $maxU = $mu + $sd;

        // Paso 3: Detectar falsos positivos según límites personalizados
        $falsos = array_filter($valsLimpiosGlobales, function ($x) use ($minU, $maxU) {
            return $x < $minU || $x > $maxU;
        });

        $validos = array_filter($valsLimpiosGlobales, function ($x) use ($minU, $maxU) {
            return $x >= $minU && $x <= $maxU;
        });

        $cantValoresCrudos = count($vals);
        $idDatoAgrupado = $this->M_Modelo->guardarDatosAgrupados($idU, $idP, $vals, $dur, $disp, $cantValoresCrudos);

        if (!empty($falsos)) {
            $this->M_Modelo->guardarFalsosPositivos($falsos, $idU, $idP, count($falsos), $disp, $idDatoAgrupado);
            
            // Calcular porcentaje
            $porcentajeFalsos = (count($falsos) / count($valsLimpiosGlobales)) * 100;

            if ($porcentajeFalsos >= 25) {
                $mensaje = sprintf(
                    "En la fecha %s, se registró una sesión con %.2f%% de falsos positivos en el parámetro %d.",
                    date("Y-m-d H:i:s"),
                    $porcentajeFalsos,
                    $idP
                );
                $this->M_alertas->crearAlerta($idU, "/vizualizar-parametros", $idDatoAgrupado, $mensaje);
            }
        }


        if (!empty($validos)) {
            $this->M_Modelo->guardardatoslimpios($validos, $idU, $idP, count($validos), $disp, $idDatoAgrupado);
        }

        if ($idMedico === null) {
            echo json_encode(['status' => 'success', 'message' => 'Datos y falsos positivos guardados sin actualizar estadísticas']);
            return;
        }

        // Paso 4: Si hay médico, verificar límites globales y actualizar estadísticas
        if (!empty($validos)) {
            $nuevoMin = min($validos);
            $nuevoMax = max($validos);

            if ($nuevoMin < $minGlobal || $nuevoMax > $maxGlobal) {
                $nuevoMin = ($nuevoMin < $minGlobal) ? $nuevoMin : $minGlobal;
                $nuevoMax = ($nuevoMax > $maxGlobal) ? $nuevoMax : $maxGlobal;
                $this->M_Modelo->actualizarNormasGlobales($idP, $nuevoMin, $nuevoMax);
            }

            $this->M_Modelo->actualizarEstadisticas($validos, $userStats, $idU, $idP);
            echo json_encode(['status' => 'success', 'message' => 'Datos guardados y estadísticas actualizadas']);
        } else {
            echo json_encode(['status' => 'warning', 'message' => 'Datos guardados pero todos fueron anómalos para estadísticas']);
        }
    }

}