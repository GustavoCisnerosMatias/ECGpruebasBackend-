<?php

class parametros_Ctrl
{
    protected $M_Modelo;

    public function __construct()
    {
        $this->M_Modelo= new M_parametros();
    }

     // Método para listar usuarios
     public function listadoparametro($f3)
     {
         $result = $this->M_Modelo->find();
         $items = array();
         foreach ($result as $datos) {
             $items[] = $datos->cast();
         }
         echo json_encode([
             'mensaje' => count($items) > 0 ? '' : 'Aún no hay registros para mostrar.',
             'total' => count($items),
             'datos' => $items
         ]);
     }


     // Método para crear un nuevo parámetro
    public function crearParametro($f3)
    {
        // Obtener los datos del parámetro del cuerpo de la solicitud
        $data = json_decode($f3->get('BODY'), true);

        // Validar los datos recibidos
        if (isset($data['nombre']) && isset($data['estado']) && isset($data['unidad_medida']) && isset($data['icono'])) {
            // Llamar al modelo para crear el parámetro
            $resultado = $this->M_Modelo->crearParametro($data);
            echo json_encode([
                'mensaje' => $resultado ? 'Parámetro creado exitosamente.' : 'Error al crear el parámetro.',
                'resultado' => $resultado
            ]);
        } else {
            echo json_encode([
                'mensaje' => 'Datos incompletos. Asegúrate de incluir nombre, estado, unidad_medida e icono.'
            ]);
        }
    }

    // Método para eliminar un parámetro
public function eliminarParametro($f3)
{
    // Obtener el id_parametro del cuerpo de la solicitud
    $data = json_decode($f3->get('BODY'), true);
    $id_parametro = $data['id_parametro'] ?? null;

    // Validar que el id_parametro esté presente
    if ($id_parametro) {
        $resultado = $this->M_Modelo->eliminarParametro($id_parametro);
        echo json_encode([
            'mensaje' => $resultado ? 'Parámetro eliminado exitosamente.' : 'Error al eliminar el parámetro. Puede que no exista.',
            'resultado' => $resultado
        ]);
    } else {
        echo json_encode([
            'mensaje' => 'ID de parámetro no proporcionado.'
        ]);
    }
}

}
?>
