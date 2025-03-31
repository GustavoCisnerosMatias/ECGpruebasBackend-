<?php

class M_medicamentos extends \DB\SQL\Mapper {
    public function __construct() {
        parent::__construct(\Base::instance()->get('DB'), 'medicamentos');
    }

    public function obtenermedicamentos() {
        $sql = "SELECT *FROM medicamentos ";
        return $this->db->exec($sql);
    }

    public function obtenermedicamntosxid($id_usuario) {
        $sql = "SELECT r.id_usuario,m.*,u.nombre, u.apellido,u.cedula,u.fecha_nacimiento,u.correo_electronico,u.telefono, r.fecha_registro
        FROM medicamentos m JOIN rel_usuario_medica r ON m.id_medic=r.id_medic 
        JOIN Usuarios u ON u.id_usuario =r.id_usuario WHERE u.id_usuario = ?";
        return $this->db->exec($sql, [$id_usuario]);
    }

    // Método para guardar la relación medicamento_paciente///////
    public function guardarRelacion($id_medic, $id_paciente,$descripcion,$dosis,$frecuencia,$duracion_tratamiento,$via_administracion) {
        $sql = "INSERT INTO rel_usuario_medica (id_medic, id_paciente,descripcion,dosis,frecuencia,duracion_tratamiento,via_administracion) VALUES (?, ?)";
    
        try {
            return $this->db->exec($sql, [$id_medic, $id_usuario]); // Ejecutar la inserción
        } catch (\Exception $e) {
            error_log('Error al guardar relación medicamento_usuario: ' . $e->getMessage());
            return false; // Error al guardar
        }
    }
    

     
}
?>