<?php

class provincias_Ctrl
{
    protected $modelo;

    public function __construct()
    {
        $this->modelo = new M_Provincias();
    }

    // Mostrar provincias
    public function mostrarProvincias($f3)
    {
        // Obtener el cuerpo de la solicitud en formato JSON
        $body = $f3->get('BODY');
        $data = json_decode($body, true);

        // Obtener el parámetro id_pais desde el cuerpo de la solicitud
        $id_pais = isset($data['id_pais']) ? $data['id_pais'] : null;

        try {
            // Verificar que el id_pais esté presente
            if ($id_pais === null) {
                throw new Exception('ID del país no proporcionado.');
            }

            // Obtener provincias del modelo
            $provincias = $this->modelo->obtenerprovincias($id_pais);

            if ($provincias) {
                echo json_encode(['provincias' => $provincias]);
            } else {
                echo json_encode(['mensaje' => 'No se encontraron provincias.']);
            }
        } catch (Exception $e) {
            echo json_encode(['mensaje' => 'Error al obtener provincias: ' . $e->getMessage()]);
        }
    }
}


