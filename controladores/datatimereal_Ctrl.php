<?php

class datatimereal_Ctrl {

    protected $M_Modelo;
    protected $M_Dispositivos;

    // Constructor que inicializa los modelos necesarios
    public function __construct() {
        $this->M_Modelo = new M_datatimereal();
        $this->M_Dispositivos = new M_dispositivo();
    }



    // Método para obtener los topics activos desde la base de datos
    public function obtenerTopics() {
        // Obtener los topics usando el modelo de dispositivos
        $topics = $this->M_Dispositivos->obtenerTopics();

        // Devolver los topics como una respuesta JSON
        echo json_encode(['status' => 'success', 'topics' => $topics]);
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
    if (empty($vals)) {
        echo json_encode(['status' => 'warning', 'message' => 'No se recibieron valores']);
        return;
    }
    $vals = array_filter($vals, 'is_numeric');
    if (empty($vals)) {
        echo json_encode(['status' => 'error', 'message' => 'Todos los datos están vacíos o son inválidos']);
        return;
    }
    $normasGlobales = $this->M_Modelo->obtenerNormasGlobales($idP);    // Paso 1: Obtener normas globales
    if (!$normasGlobales) {        // Si no existen normas globales, las creamos con los valores recibidos
        $min = min($vals);
        $max = max($vals);
        $this->M_Modelo->crearNormasGlobales($idP, $min, $max);
        $minG = $min;// Actualizamos variables para el resto del flujo
        $maxG = $max;
    }else{
        $minG = (float)$normasGlobales['valor_minimo'];
        $maxG = (float)$normasGlobales['valor_maximo'];
    }
    $userStats = $this->M_Modelo->obtenerEstadisticasUsuario($idU, $idP);    // Paso 2: Obtener estadísticas del usuario
    if (!$userStats) {
        $validos = array_filter($vals, function ($x) use ($minG, $maxG) {         // Si no hay estadísticas previas, insertamos por primera vez usando todos los datos válidos globalmente
            return $x >= $minG && $x <= $maxG;
        });
        if (empty($validos)) {
            echo json_encode(['status' => 'warning', 'message' => 'Ningún dato cumple con las normas globales']);
            return;
        }
        $this->M_Modelo->guardarDatosAgrupados($idU, $idP, $validos, $dur, $disp); // Guardar datos
        $fakeStats = [  // Insertar estadísticas iniciales 
            'media' => 0,
            'desviacion_estandar' => 0,
            'count' => 0,
            'mean_welford' => 0,
            'm2_welford' => 0
        ];
        $this->M_Modelo->actualizarEstadisticas($validos, $fakeStats, $idU, $idP);
        echo json_encode(['status' => 'success', 'message' => 'Primeros datos almacenados y estadísticas inicializadas']);
        return;
    }

    // Paso 3: Definir límites personalizados del usuario
    $mu = (float)$userStats['mean_welford']; // Usamos mean_welford como media
    $sd = (float)$userStats['desviacion_estandar']; // Usamos desviacion_estandar
    $minU = $mu - $sd; // Límite inferior del usuario
    $maxU = $mu + $sd; // Límite superior del usuario

    // Paso 4: Filtrar valores según ambas normas (global y personalizada)
    list($validos, $falsos) = $this->M_Modelo->filtrarValores($vals, $minG, $maxG, $minU, $maxU);

    // Paso 5: Guardar falsos positivos si hay
    $this->M_Modelo->guardarFalsosPositivos($falsos, $idU, $idP, $dur, $disp);

    // Paso 6: Guardar válidos y actualizar estadísticas
    if (!empty($validos)) {
        $this->M_Modelo->guardarDatosAgrupados($idU, $idP, $validos, $dur, $disp);
        $this->M_Modelo->actualizarEstadisticas($validos, $userStats, $idU, $idP);

        echo json_encode(['status' => 'success', 'message' => 'Datos válidos guardados y estadísticas actualizadas']);
    } else {
        echo json_encode(['status' => 'warning', 'message' => 'Todos los datos fueron descartados por anomalías']);
    }
    }

  
}

?>





