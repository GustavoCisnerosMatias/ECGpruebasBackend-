<?php
require_once 'lib\middleware\JwtMiddleware.php';

class asistente_Ctrl
{
    protected $modelo;

    public function __construct()
    {
        $this->modelo = new M_asistente();
    }
   

    // Método para actualizar los datos físicos del paciente (peso y altura)
    public function actualizarDatosFisicos($f3)
    {
        $json = $f3->get('BODY');
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode(['mensaje' => 'JSON inválido']);
            return;
        }

        // Verificar que los parámetros necesarios existan
        if (!isset($data['id_usuario']) || !isset($data['peso']) || !isset($data['estatura'])) {
            echo json_encode(['mensaje' => 'Faltan parámetros: id_usuario, peso o estatura']);
            return;
        }

        try {
            // Actualizar los datos físicos (peso y altura)
            $resultado = $this->modelo->actualizarDatosFisicos($data['id_usuario'], $data['peso'], $data['estatura']);

            if ($resultado) {
                echo json_encode(['mensaje' => 'Datos físicos actualizados correctamente']);
            } else {
                echo json_encode(['mensaje' => 'No se pudo actualizar los datos físicos']);
            }
        } catch (Exception $e) {
            echo json_encode(['mensaje' => 'Error al actualizar los datos físicos: ' . $e->getMessage()]);
        }
    }

    // Método para obtener los datos los pacientes del medico por el id_uaurio de asistente 
    public function metodoparaoctenerdatosfisicos($f3)
    {
        $json = $f3->get('BODY');
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode(['mensaje' => 'JSON inválido']);
            return;
        }

        if (!isset($data['id_usuario'])) {
            echo json_encode(['mensaje' => 'Falta parámetro: id_usuario']);
            return;
        }

        try {
            $datosfisicos = $this->modelo->obteneradatosfisicospaciente($data['id_usuario']);

            if ($datosfisicos) {
                echo json_encode(['datosfisicos' => $datosfisicos]);
            } else {
                echo json_encode(['mensaje' => 'No se encontró el datosfisicos']);
            }
        } catch (Exception $e) {
            echo json_encode(['mensaje' => 'Error al obtener el datosfisicos: ' . $e->getMessage()]);
        }
    }





    // Método para obtener los datos de un médico por id_usuario
    public function obtenerasisitentePorUsuario($f3)
    {
        $json = $f3->get('BODY');
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode(['mensaje' => 'JSON inválido']);
            return;
        }

        if (!isset($data['id_usuario'])) {
            echo json_encode(['mensaje' => 'Falta parámetro: id_usuario']);
            return;
        }

        try {
            $asistentes = $this->modelo->obtenerasistentePorUsuario($data['id_usuario']);

            if ($asistentes) {
                echo json_encode(['asistentes' => $asistentes]);
            } else {
                echo json_encode(['mensaje' => 'No se encontró el asistente']);
            }
        } catch (Exception $e) {
            echo json_encode(['mensaje' => 'Error al obtener el asistente: ' . $e->getMessage()]);
        }
    }



    // Método para inactivar asistente
    public function inactivarAsistente($f3)
    {
        $json = $f3->get('BODY');
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode(['mensaje' => 'JSON inválido']);
            return;
        }

        if (!isset($data['id_asistente'])) {
            echo json_encode(['mensaje' => 'Falta parámetro: id_asistente']);
            return;
        }

        if (!isset($data['estado'])) {
            echo json_encode(['mensaje' => 'Falta parámetro: estado']);
            return;
        }

        try {
            // Cambia la línea aquí, asegurándote de pasar el estado correctamente
            $resultado = $this->modelo->cambiarEstadoAsistente($data['id_asistente'], $data['estado']);

            if ($resultado) {
                echo json_encode(['mensaje' => 'Asistente inactivado correctamente']);
            } else {
                echo json_encode(['mensaje' => 'No se pudo inactivar el asistente']);
            }
        } catch (Exception $e) {
            echo json_encode(['mensaje' => 'Error al inactivar el asistente: ' . $e->getMessage()]);
        }
    }


    // Método para obtener los datos los pacientes del medico por el id_uaurio de asistente 
    public function obtenerpacientesmedicoasisitente($f3)
    {
        $json = $f3->get('BODY');
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode(['mensaje' => 'JSON inválido']);
            return;
        }

        if (!isset($data['id_usuario'])) {
            echo json_encode(['mensaje' => 'Falta parámetro: id_usuario']);
            return;
        }

        try {
            $pacientes = $this->modelo->obtenerpacientesasistente($data['id_usuario']);

            if ($pacientes) {
                echo json_encode(['pacientes' => $pacientes]);
            } else {
                echo json_encode(['mensaje' => 'No se encontró el pacientes']);
            }
        } catch (Exception $e) {
            echo json_encode(['mensaje' => 'Error al obtener el pacientes: ' . $e->getMessage()]);
        }
    }



     

}

