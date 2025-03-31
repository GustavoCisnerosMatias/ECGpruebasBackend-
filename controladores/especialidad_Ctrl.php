<?php

class especialidad_Ctrl
{
    protected $modelo;

    public function __construct()
    {
        $this->modelo = new M_especialidad();
    }

//especialidad
    public function mostrarEspecialidad($f3)
    {
        try {
            $especialidad = $this->modelo->mostrarEspecialidad();

            if ($especialidad) {
                echo json_encode(['especialidad' => $especialidad]);
            } else {
                echo json_encode(['mensaje' => 'No se encontraron especialidad']);
            }
        } catch (Exception $e) {
            echo json_encode(['mensaje' => 'Error al obtener especialidad: ' . $e->getMessage()]);
        }
    }

   
}
?>
