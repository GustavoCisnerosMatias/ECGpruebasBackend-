<?php

class M_estadousurio_alertas extends \DB\SQL\Mapper
{
    public function __construct()
    {
        parent::__construct(\Base::instance()->get('db'), 'alerta_estadousuario');
    }

    public function mostrarnotas() {
        $sql = "SELECT * FROM estadousuarios";
        return $this->db->exec($sql);
    }

    public function createestadoUsuario($data){
        try {
            $this->copyFrom($data);
            return $this->save();
        } catch (Exception $e) {
            throw new Exception('Error al crear estado: ' . $e->getMessage());
        }
    }
}
