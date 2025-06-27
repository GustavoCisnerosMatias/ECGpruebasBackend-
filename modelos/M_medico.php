<?php

class M_medico extends \DB\SQL\Mapper {
    public function __construct() {
        parent::__construct(\Base::instance()->get('DB'), 'medicos');
    }

    public function getTopics(){
        $sql = "SELECT p.fecha_registro, p.estado FROM medicos p";
        return $this->db->exec($sql);
    }

    public function obtenerMedicos() {
        $sql = "SELECT medicos.id_medico, usuarios.nombre, usuarios.apellido, c.nombre_centro,  c.tipo_centro
                FROM medicos 
                JOIN usuarios ON medicos.id_usuario = usuarios.id_usuario
                JOIN centros_hospitalarios c ON c.id_centro = medicos.id_centro
                WHERE medicos.estado = 'A'";
        return $this->db->exec($sql);
    }

    public function createmedico($data){
        $this->copyFrom($data);
        return $this->save();
    }

    public function obtenerMedicoPorUsuario($id_usuario) {
        $sql = "SELECT medicos.id_medico, usuarios.nombre, usuarios.apellido, c.nombre_centro, c.tipo_centro
                FROM medicos
                JOIN usuarios ON medicos.id_usuario = usuarios.id_usuario
                JOIN centros_hospitalarios c ON c.id_centro = medicos.id_centro

                WHERE medicos.id_usuario = ? AND medicos.estado = 'A'";
        return $this->db->exec($sql, [$id_usuario]);
    }
}
