<?php

class M_datos_fisicos extends \DB\SQL\Mapper
{
    public function __construct()
    {
        parent::__construct(\Base::instance()->get('DB'), 'datos_fisicos');
    }
    
    
    // Método para listar datos físicos de un usuario específico
    public function listarDatosFisicos($id_usuario)
    {
        // Validar que se proporcione un id_usuario
        if (!$id_usuario) {
            return null; // o lanza una excepción si prefieres manejarlo así
        }

        // Buscar los datos físicos del usuario por id_usuario
        $resultado = $this->find(['id_usuario = ?', $id_usuario]);

        // Retornar el resultado si existe, o null si no hay datos
        return $resultado;
    }

    // Verificar si ya existen datos físicos para un usuario
    public function datosFisicosExisten($id_usuario)
    {
        return $this->count(['id_usuario = ?', $id_usuario]) > 0;
    }

    // Crear o actualizar datos físicos
    public function createOrUpdateDatosFisicos($id_usuario, $data)
    {
        // Intentar cargar los datos existentes
        $this->load(['id_usuario = ?', $id_usuario]);

        if (!$this->dry()) {
            // Si ya existen, actualizar
            $this->copyFrom($data);
        } else {
            // Si no existen, crear nuevos
            $this->reset(); // Limpia el estado previo del objeto
            $this->id_usuario = $id_usuario; // Asignar id_usuario manualmente
            $this->copyFrom($data);
        }

        // Guardar cambios
        return $this->save();
    }
}
?>
