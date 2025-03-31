<?php

class alertas_Ctrl
{
    protected $modelo;

    public function __construct()
    {
        $this->modelo = new M_alertas();
    }
    
    
    public function mostrarAlertasfecha($f3)
{
    try {
        // Obtén el cuerpo de la solicitud como un JSON
        $data = json_decode($f3->get('BODY'), true);

        // Obtén el ID de usuario y las fechas del JSON
        $id_usuario = $data['id_usuario'];
        $fecha_ini = $data['fecha_ini'];
        $fecha_fin = $data['fecha_fin'];

        // Obtener las alertas usando el ID de usuario y las fechas
        $Alertas = $this->modelo->obtenerAlertasfecha($id_usuario, $fecha_ini, $fecha_fin);

        if ($Alertas) {
            echo json_encode(['Alertas' => $Alertas]);
        } else {
            echo json_encode(['mensaje' => 'No se encontraron Alertas']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['mensaje' => 'Error al obtener las alertas: ' . $e->getMessage()]);
    }
}


        // Método para mostrar las alertas
        public function mostrartodasAlertasmedicofiltr($f3)
        {
            try {
                // Obtén el cuerpo de la solicitud como un JSON
                $data = json_decode($f3->get('BODY'), true);
        
                // Obtén el ID de usuario directamente del JSON
                $id_usuario = $data['id_usuario'];
        
                // Obtener las alertas usando el ID de usuario
                $Alertas = $this->modelo->obtenertodasAlertasparamedico($id_usuario);
        
                if ($Alertas) {
                    echo json_encode(['Alertas' => $Alertas]);
                } else {
                    echo json_encode(['mensaje' => 'No se encontraron Alertas']);
                }
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['mensaje' => 'Error al obtener las alertas: ' . $e->getMessage()]);
            }
        }

    // Método para mostrar las alertas
    public function mostrarAlertas($f3)
{
    try {
        // Obtén el cuerpo de la solicitud como un JSON
        $data = json_decode($f3->get('BODY'), true);

        // Obtén el ID de usuario directamente del JSON
        $id_usuario = $data['id_usuario'];

        // Obtener las alertas usando el ID de usuario
        $Alertas = $this->modelo->obtenerAlertas($id_usuario);

        if ($Alertas) {
            echo json_encode(['Alertas' => $Alertas]);
        } else {
            echo json_encode(['mensaje' => 'No se encontraron Alertas']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['mensaje' => 'Error al obtener las alertas: ' . $e->getMessage()]);
    }
}
public function actualizarVistaAlerta($f3)
{
    try {
        $data = json_decode($f3->get('BODY'), true);
        $id_alertas = $data['id_alertas'];  // Asegúrate de que esta clave exista en el JSON
        $vista = $data['vista'];

        $this->modelo->actualizarVista($id_alertas, $vista);

        echo json_encode(['mensaje' => 'Estado de alerta actualizado']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['mensaje' => 'Error al actualizar el estado de alerta: ' . $e->getMessage()]);
    }
}

public function listarTipoAlertas($f3) {
    try {
        $tiposAlertas = $this->modelo->obtenerTipoAlertas();

        if ($tiposAlertas) {
            echo json_encode(['TipoAlertas' => $tiposAlertas]);
        } else {
            echo json_encode(['mensaje' => 'No se encontraron tipos de alerta']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['mensaje' => 'Error al obtener los tipos de alerta: ' . $e->getMessage()]);
    }
}


public function editarTipoAlerta($f3) {
    try {
        $data = json_decode($f3->get('BODY'), true);
        $this->modelo->editarTipoAlerta($data);

        echo json_encode(['mensaje' => 'Tipo de alerta editado con éxito']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['mensaje' => 'Error al editar el tipo de alerta: ' . $e->getMessage()]);
    }
}

public function eliminarTipoAlerta($f3) {
    try {
        $data = json_decode($f3->get('BODY'), true);
        $this->modelo->eliminarTipoAlerta($data['id_tipoalerta']);

        echo json_encode(['mensaje' => 'Tipo de alerta eliminado con éxito']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['mensaje' => 'Error al eliminar el tipo de alerta: ' . $e->getMessage()]);
    }
}

public function crearTipoAlerta($f3) {
    try {
        $data = json_decode($f3->get('BODY'), true);
        $this->modelo->crearTipoAlerta($data);

        echo json_encode(['mensaje' => 'Tipo de alerta creado con éxito']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['mensaje' => 'Error al crear el tipo de alerta: ' . $e->getMessage()]);
    }
}
   
}
?>
