<?php
require_once('lib/middleware/JwtMiddleware.php');
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
    
    public function crearEspecialidad($f3)
    {
        try {
            $data = json_decode($f3->get('BODY'), true);
            $resultado = $this->modelo->crearEspecialidad($data);
            echo json_encode(['success' => true, 'mensaje' => 'Especialidad creada', 'data' => $resultado]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'mensaje' => 'Error al crear especialidad: ' . $e->getMessage()]);
        }
    }

    public function actualizarEspecialidad($f3)
    {
        try {
            $data = json_decode($f3->get('BODY'), true);
            $resultado = $this->modelo->actualizarEspecialidad($data);
            echo json_encode(['success' => true, 'mensaje' => 'Especialidad actualizada']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'mensaje' => 'Error al actualizar: ' . $e->getMessage()]);
        }
    }

    public function eliminarEspecialidad($f3)
    {
        try {
            $id = $f3->get('PARAMS.id');
            $this->modelo->eliminarEspecialidad($id);
            echo json_encode(['success' => true, 'mensaje' => 'Especialidad eliminada']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'mensaje' => 'Error al eliminar: ' . $e->getMessage()]);
        }
    }

    public function buscarEspecialidadPorId($f3)
    {
        try {
            $id = $f3->get('PARAMS.id');
            $especialidad = $this->modelo->buscarEspecialidadPorId($id);
            echo json_encode(['especialidad' => $especialidad]);
        } catch (Exception $e) {
            echo json_encode(['mensaje' => 'Error al buscar especialidad: ' . $e->getMessage()]);
        }
    }
   
}

