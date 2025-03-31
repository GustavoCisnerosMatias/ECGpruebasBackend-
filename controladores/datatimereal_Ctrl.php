<?php

class datatimereal_Ctrl {

    protected $M_Modelo;
    protected $M_Dispositivos;

    // Constructor que inicializa los modelos necesarios
    public function __construct() {
        $this->M_Modelo = new M_datatimereal();
        $this->M_Dispositivos = new M_dispositivo();
    }

    // Método para guardar datos enviados a través de MQTT
    public function guardarDatos($f3) {
        // Obtener el cuerpo de la solicitud en formato JSON
        $data = json_decode($f3->get('BODY'), true);

        // Verificar si hay error en el formato del JSON recibido
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode(['status' => 'error', 'message' => 'Error en el formato JSON']);
            return;
        }

        // Iterar sobre cada elemento en el array de datos
        foreach ($data as $item) {
            // Verificar que los datos requeridos estén presentes
            if (isset($item['codigo'], $item['valor'], $item['id_parametro'])) {
                // Intentar guardar los datos usando el modelo
                if (!$this->M_Modelo->guardarDato($item['codigo'], $item['valor'], $item['id_parametro'])) {
                    echo json_encode(['status' => 'error', 'message' => 'No se pudo guardar algunos datos']);
                    return;
                }
            } else {
                // Si faltan datos, devolver un mensaje de error
                echo json_encode(['status' => 'error', 'message' => 'Datos incompletos: ' . json_encode($item)]);
                return;
            }
        }

        // Si todo está correcto, devolver un mensaje de éxito
        echo json_encode(['status' => 'success', 'message' => 'Datos guardados correctamente']);
    }

    // Método para obtener los topics activos desde la base de datos
    public function obtenerTopics() {
        // Obtener los topics usando el modelo de dispositivos
        $topics = $this->M_Dispositivos->obtenerTopics();

        // Devolver los topics como una respuesta JSON
        echo json_encode(['status' => 'success', 'topics' => $topics]);
    }
}

?>
