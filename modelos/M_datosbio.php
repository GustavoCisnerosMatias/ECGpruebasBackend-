<?php

class M_datosbio extends \DB\SQL\Mapper
{


    public function __construct() {
        $db = Base::instance()->get('DB');
        parent::__construct($db, 'datos_manuales');
    }

    public function guardarDato($data) {
        $this->reset();
        $this->id_usuario = $data['id_usuario'];
        $this->fecha_registro = $data['fecha_registro'];
        $this->datos = $data['datos'];
        $this->id_parametro = $data['id_parametro'];
        $this->save();
    }
    public function obtenerPorUsuario($id_usuario)
    {
        $sql = "SELECT id, id_usuario, fecha_registro, datos, id_parametro FROM datos_manuales WHERE id_usuario = :id_usuario";
        return $this->db->exec($sql, [':id_usuario' => $id_usuario]);
    }

}
