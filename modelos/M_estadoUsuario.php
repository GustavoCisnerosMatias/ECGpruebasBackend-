<?php

class M_estadoUsuario extends \DB\SQL\Mapper
{
    public function __construct()
    {
        parent::__construct(\Base::instance()->get('DB'), 'EstadoUsuarios');
    }

//listar
    public function mostrarnotas() {
        $sql = "SELECT *FROM EstadoUsuarios p WHERE p.estado = 'A'";
        return $this->db->exec($sql);
    }

    // Método para crear un nuevo dispositivo
    public function createestadoUsuario($data)
    {
        try {
            // Crear instancia del modelo y copiar datos
            $this->copyFrom($data);

            // Establecer el estado a 'A'
            $this->estado = 'A'; // Asignar 'A' al campo estado

            // Guardar en la base de datos
            return $this->save();
        } catch (Exception $e) {
            throw new Exception('Error al crear estado: ' . $e->getMessage());
        }
    }

}

?>
