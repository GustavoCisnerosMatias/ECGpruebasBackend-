<?php
require_once 'lib\middleware\JwtMiddleware.php';

class antecedente_personal_Ctrl
{
    protected $M_Modelo;

    public function __construct()
    {
        $this->M_Modelo= new M_antecedente_personal();
    }

    public function crearAntecedentePersonal($f3, $params)
{
    // Leer el cuerpo de la solicitud y decodificar el JSON
    $jsonInput = file_get_contents("php://input");
    $data = json_decode($jsonInput, true);

    // Registrar los datos recibidos en el log
    error_log("Datos recibidos: " . print_r($data, true));

    // Verificar que la decodificación fue exitosa
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode([
            'mensaje' => 'Error al decodificar JSON: ' . json_last_error_msg(),
            'result' => false
        ]);
        return;
    }

    // Confirmar que los datos necesarios están presentes
    $requiredFields = ['id_paciente', 'id_medico', 'codigo_enfermedad', 'estado_actual', 'observaciones'];
    foreach ($requiredFields as $field) {
        if (!isset($data[$field])) {
            echo json_encode([
                'mensaje' => "Falta el campo requerido: $field",
                'result' => false
            ]);
            return;
        }
    }

    // Intentar guardar los datos
    $result = $this->M_Modelo->createAntecedentePersonal($data);

    echo json_encode([
        'mensaje' => $result ? 'Antecedente personal creado con éxito' : 'Error al crear el antecedente personal',
        'result' => $result
    ]);
}

 // Método para listar antecedentes personales de un paciente específico
 public function listarAntecedentesPersonales($f3, $params) {
    // Leer el cuerpo de la solicitud y decodificar el JSON
    $jsonInput = file_get_contents("php://input");
    $data = json_decode($jsonInput, true);

    // Registrar los datos recibidos en el log
    error_log("Datos recibidos para listar: " . print_r($data, true));

    // Verificar que la decodificación fue exitosa
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode([
            'mensaje' => 'Error al decodificar JSON: ' . json_last_error_msg(),
            'result' => false
        ]);
        return;
    }

    // Confirmar que el campo id_paciente está presente
    if (!isset($data['id_paciente'])) {
        echo json_encode([
            'mensaje' => "Falta el campo requerido: id_paciente",
            'result' => false
        ]);
        return;
    }

    $id_paciente = $data['id_paciente'];  // Obtener el id_paciente del JSON
    $antecedentes = $this->M_Modelo->getAntecedentesPersonales($id_paciente);
    $result = [];

    foreach ($antecedentes as $antecedente) {
        $result[] = $antecedente->cast();
    }

    echo json_encode($result);
}

}

