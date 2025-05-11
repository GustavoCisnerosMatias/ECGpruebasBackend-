<?php

class cantones_Ctrl
{
    protected $modelo;

    public function __construct()
    {
        $this->modelo = new M_cantones();
    }

    // Mostrar provincias
    public function mostrarCantones($f3)
    {
        // Obtener el cuerpo de la solicitud en formato JSON
        $body = $f3->get('BODY');
        $data = json_decode($body, true);

        // Obtener el parámetro id_pais desde el cuerpo de la solicitud
        $id_provincia = isset($data['id_provincia']) ? $data['id_provincia'] : null;

        try {
            // Verificar que el id_provincia esté presente
            if ($id_provincia === null) {
                throw new Exception('ID del provicia no proporcionado.');
            }

            // Obtener provincias del modelo
            $cantones = $this->modelo->obtenerCantones($id_provincia);

            if ($cantones) {
                echo json_encode(['cantones' => $cantones]);
            } else {
                echo json_encode(['mensaje' => 'No se encontraron cantones.']);
            }
        } catch (Exception $e) {
            echo json_encode(['mensaje' => 'Error al obtener cantones: ' . $e->getMessage()]);
        }
    }
}


