<?php

class M_medico extends \DB\SQL\Mapper {
    public function __construct() {
        parent::__construct(\Base::instance()->get('DB'), 'medicos');
    }


     
    public function getTopics()
    {
        $sql = "SELECT p.fecha_registro,p.estado FROM medicos p";
        return $this->db->exec($sql);
    }

    public function obtenerMedicos() {
        $sql = "SELECT medicos.id_medico, Usuarios.nombre, Usuarios.apellido, c.nombre_centro,e.nombre_esp, c.tipo_centro
                FROM medicos 
                JOIN Usuarios ON medicos.id_usuario = Usuarios.id_usuario
                JOIN centros_hospitalarios c ON c.id_centro = medicos.id_centro
                JOIN especialidades e ON e.id_especialidad = medicos.id_especialidad
                WHERE medicos.estado = 'A'";
        return $this->db->exec($sql);
    }

     // Crear un nuevo usuario
     public function createmedico($data)
     {
         $this->copyFrom($data);
         return $this->save();
     }


       // Método para obtener los datos de un médico según el id_usuario
    public function obtenerMedicoPorUsuario($id_usuario) {
        $sql = "SELECT medicos.id_medico, Usuarios.nombre, Usuarios.apellido, c.nombre_centro, e.nombre_esp, c.tipo_centro
                FROM medicos
                JOIN Usuarios ON medicos.id_usuario = Usuarios.id_usuario
                JOIN centros_hospitalarios c ON c.id_centro = medicos.id_centro
                JOIN especialidades e ON e.id_especialidad = medicos.id_especialidad
                WHERE medicos.id_usuario = ? AND medicos.estado = 'A'";
        return $this->db->exec($sql, [$id_usuario]);
    }
}

