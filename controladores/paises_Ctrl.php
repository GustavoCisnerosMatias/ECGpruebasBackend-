<?php
require_once('lib/middleware/JwtMiddleware.php');
class paises_Ctrl
{
    protected $modelo;

    public function __construct()
    {
        $this->modelo = new M_paises();
    }

//pais
    public function mostrarPais($f3)
    {
        try {
            $pais = $this->modelo->obtenerpais();

            if ($pais) {
                echo json_encode(['pais' => $pais]);
            } else {
                echo json_encode(['mensaje' => 'No se encontraron pais']);
            }
        } catch (Exception $e) {
            echo json_encode(['mensaje' => 'Error al obtener pais: ' . $e->getMessage()]);
        }
    }

   
}

