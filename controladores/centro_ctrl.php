<?php

class centro_Ctrl
{
    protected $modelo;

    public function __construct()
    {
        $this->modelo = new M_centro();
    }

//pais
    public function mostrarCentro($f3)
    {
        try {
            $centro = $this->modelo->mostrarCentro();

            if ($centro) {
                echo json_encode(['centro' => $centro]);
            } else {
                echo json_encode(['mensaje' => 'No se encontraron los centros hospitalarios']);
            }
        } catch (Exception $e) {
            echo json_encode(['mensaje' => 'Error al obtener el centro hospitalario: ' . $e->getMessage()]);
        }
    }

   
}
?>
