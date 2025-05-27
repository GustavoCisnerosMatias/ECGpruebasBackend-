<?php

class M_especialidad extends \DB\SQL\Mapper {
    public function __construct() {
        parent::__construct(\Base::instance()->get('DB'), 'especialidades');
    }

    public function mostrarEspecialidad(){
        $sql = "SELECT *FROM especialidades p WHERE p.estado = 'A'";
        return $this->db->exec($sql);
    }


}
