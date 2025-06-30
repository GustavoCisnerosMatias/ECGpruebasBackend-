<?php

class alertas_Ctrl
{
    protected $modelo;

    public function __construct()
    {
        $this->modelo = new M_alertas();
    }

     public function obtenerAlertasPorUsuario($f3)
    {
        try {
            $id_usuario = $f3->get('PARAMS.id');
            $alertas = $this->modelo->obtenerAlertasPorUsuario($id_usuario);

            if ($alertas) {
                echo json_encode(['alertas' => $alertas]);
            } else {
                echo json_encode(['mensaje' => 'No se encontraron alertas']);
            }
        } catch (Exception $e) {
            echo json_encode(['mensaje' => 'Error al obtener alertas: ' . $e->getMessage()]);
        }
    }

    // Marcar alerta como vista (estado = 0)
    public function marcarAlertaComoVista($f3)
    {
        try {
            $id_alerta = $f3->get('PARAMS.id');
            $this->modelo->marcarComoVista($id_alerta);
            echo json_encode(['success' => true, 'mensaje' => 'Alerta marcada como vista']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'mensaje' => 'Error al actualizar alerta: ' . $e->getMessage()]);
        }
    }

}

