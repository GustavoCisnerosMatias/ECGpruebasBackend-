<?php

class consultas_Ctrl
{
    protected $M_Modelo;

    public function __construct()
    {
        $this->M_Modelo= new M_consultas();
    }
 


    public function listarAntecedentesPersonales($f3, $params) {
        $jsonInput = file_get_contents("php://input");
        $data = json_decode($jsonInput, true);
    
        error_log("Datos recibidos para listar: " . print_r($data, true));
    
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode([
                'mensaje' => 'Error al decodificar JSON: ' . json_last_error_msg(),
                'result' => false
            ]);
            return;
        }
    
        if (!isset($data['id_paciente'])) {
            echo json_encode([
                'mensaje' => "Falta el campo requerido: id_paciente",
                'result' => false
            ]);
            return;
        }
    
        $id_paciente = $data['id_paciente'];
        $antecedentes = $this->M_Modelo->getAntecedentesPersonales($id_paciente);
    
        if ($antecedentes === null) {
            echo json_encode([
                'mensaje' => "No se pudieron obtener los antecedentes personales",
                'result' => false
            ]);
            return;
        }
    
        echo json_encode($antecedentes);
    }



    public function listarConsultas($f3) {
        try {
            // Obtener los datos en formato JSON
            $data = json_decode($f3->get('BODY'), true); // Decodificamos el cuerpo JSON como un array asociativo
            $id_usuario = $data['id_usuario'] ?? null;
            $fecha_ini = $data['fecha_ini'] ?? null;
            $fecha_fin = $data['fecha_fin'] ?? null;
    
            // Verificamos que todos los parámetros necesarios estén presentes
            if (!$id_usuario || !$fecha_ini || !$fecha_fin) {
                echo json_encode(['mensaje' => 'Parámetros incompletos']);
                return;
            }
    
            // Obtenemos el id_medico basado en id_usuario
            $id_medico = $this->M_Modelo->obtenerIdMedico($id_usuario);
            if (!$id_medico) {
                echo json_encode(['mensaje' => 'Médico no encontrado']);
                return;
            }
    
            // Listamos las consultas
            $consultas = $this->M_Modelo->listarConsultas($id_medico, $fecha_ini, $fecha_fin);
    
            echo json_encode(['consultas' => $consultas ?: 'No se encontraron consultas']);
        } catch (Exception $e) {
            echo json_encode(['mensaje' => 'Error al obtener consultas: ' . $e->getMessage()]);
        }
    }
    


    ///RECETA


   // Método para listar recetas de un paciente específico
public function listarRecetas($f3) {
    try {
        $data = json_decode($f3->get('BODY'), true); 
        $id_paciente = $data['id_paciente'] ?? null;  // Obtener id_paciente
        $anio = $data['anio'] ?? null;                // Obtener anio

        // Verificar que ambos parámetros sean válidos
        if (!$id_paciente || !$anio) {
            echo json_encode(['mensaje' => 'Parámetros incompletos']);
            return;
        }

        // Llamar al modelo con ambos parámetros
        $recetas = $this->M_Modelo->listarRecetas($id_paciente, $anio);
        echo json_encode(['recetas' => $recetas ?: 'No se encontraron recetas']);
    } catch (Exception $e) {
        echo json_encode(['mensaje' => 'Error al obtener recetas: ' . $e->getMessage()]);
    }
}


/////guardar consultas


public function guardarConsultaConRecetas($f3) {
    try {
        $data = json_decode($f3->get('BODY'), true);

        // Verifica que los datos necesarios estén presentes en el JSON
        $id_paciente = $data['id_paciente'] ?? null;
        $id_usuario = $data['id_usuario'] ?? null;
        $motivo_consulta = $data['motivo_consulta'] ?? null;
        $observaciones = $data['observaciones'] ?? null;
        $recetas = $data['recetas'] ?? [];  // Si no se envían recetas, será un array vacío

        if (!$id_paciente || !$id_usuario || !$motivo_consulta) {
            echo json_encode(['mensaje' => 'Parámetros incompletos']);
            return;
        }

        // Obtén el id_medico basado en id_usuario
        $id_medico = $this->M_Modelo->obtenerIdMedico($id_usuario);
        if (!$id_medico) {
            echo json_encode(['mensaje' => 'Médico no encontrado']);
            return;
        }

        // Guarda la consulta en la base de datos
        $consultaData = [
            'id_paciente' => $id_paciente,
            'id_medico' => $id_medico,
            'motivo_consulta' => $motivo_consulta,
            'observaciones' => $observaciones
        ];
        $id_consulta = $this->M_Modelo->crearConsultaYRetornarId($consultaData);

        // Verifica si hay recetas en el JSON y procede a guardarlas si existen
        if (!empty($recetas)) {
            foreach ($recetas as $receta) {
                $recetaData = [
                    'id_consulta' => $id_consulta,
                    'id_medic' => $receta['id_medic'],
                    'fecha_vencimiento' => $receta['fecha_vencimiento'],
                    'dosis' => $receta['dosis'],
                    'frecuencia' => $receta['frecuencia'],
                    'duracion' => $receta['duracion'],
                    'instrucciones' => $receta['instrucciones'],
                    'observaciones' => $receta['observaciones'] ?? null
                ];
                $this->M_Modelo->crearReceta($recetaData);
            }
        }

        echo json_encode(['mensaje' => 'guardadas correctamente']);
    } catch (Exception $e) {
        echo json_encode(['mensaje' => 'Error al guardar consulta y recetas: ' . $e->getMessage()]);
    }
}

}

