<?php

class M_estadousurio_alertas extends \DB\SQL\Mapper
{
    public function __construct()
    {
        parent::__construct(\Base::instance()->get('DB'), 'Alerta_EstadoUsuario');
    }

//listar
    public function mostrarnotas() {
        $sql = "SELECT *FROM EstadoUsuarios ";
        return $this->db->exec($sql);
    }

    // Método para crear un nuevo dispositivo
    public function createestadoUsuario($data)
    {
        try {
            // Crear instancia del modelo y copiar datos
            $this->copyFrom($data);

            

            // Guardar en la base de datos
            return $this->save();
        } catch (Exception $e) {
            throw new Exception('Error al crear estado: ' . $e->getMessage());
        }
    }

}

