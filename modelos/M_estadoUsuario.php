<?php

class M_estadoUsuario extends \DB\SQL\Mapper
{
    public function __construct(){
        parent::__construct(\Base::instance()->get('DB'), 'estadousuarios');
    }

    public function mostrarnotas() {
        $sql = "SELECT * FROM estadousuarios p WHERE p.estado = 'A'";
        return $this->db->exec($sql);
    }

    public function createestadousuario($data){
        try {
            $this->copyFrom($data);
            $this->estado = 'A';
            return $this->save();
        } catch (Exception $e) {
            throw new Exception('Error al crear estado: ' . $e->getMessage());
        }
    }
}
