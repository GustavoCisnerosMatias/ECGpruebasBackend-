<?php

class M_asistente extends \DB\SQL\Mapper {
    public function __construct() {
        parent::__construct(\Base::instance()->get('DB'), 'asistente');
    }


      // Método para actualizar los datos físicos de un paciente (peso y altura)
      public function actualizarDatosFisicos($id_usuario, $peso, $estatura) {
        $sql = "
            UPDATE datos_fisicos 
            SET peso = ?, estatura = ? 
            WHERE id_usuario = ?
        ";
        return $this->db->exec($sql, [$peso, $estatura, $id_usuario]);
    }

    // Método para obtener los datos fisicos de los pacientes según el id_usuario
    public function obteneradatosfisicospaciente($id_usuario) {
        $sql = "
            SELECT e.* FROM datos_fisicos e 
            JOIN Usuarios u ON e.id_usuario = u.id_usuario 
            WHERE u.estado = 'A' AND e.id_usuario = ?
        ";
        return $this->db->exec($sql, [$id_usuario]);
    }

    // Método para obtener los datos de un médico según el id_usuario
    public function obtenerasistentePorUsuario($id_usuario) {
        $sql = "
            SELECT DISTINCT u.nombre, u.apellido,a.id_asistente,a.estado, TO_BASE64(p.foto)
            FROM medicos e 
            JOIN asistente a ON a.id_medico = e.id_medico 
            JOIN Usuarios u ON a.id_usuario = u.id_usuario 
            LEFT JOIN perfil p ON u.id_usuario = p.id_usuario 
            WHERE u.estado = 'A' AND e.id_usuario = ? 
        ";
        return $this->db->exec($sql, [$id_usuario]);
    }


     // Método para cambiar el estado del asistente
     public function cambiarEstadoAsistente($id_asistente, $estado) 
     {
         $sql = "
             UPDATE asistente
             SET estado = ?
             WHERE id_asistente = ?
         ";
         return $this->db->exec($sql, [$estado, $id_asistente]);
     }


     // Método para obtener los datos de un médico según el id_usuario
    public function obtenerpacientesasistente($id_usuario) {
        $sql = "
                        SELECT 
                u.fecha_nacimiento,
                u.id_usuario,
                u.nombre,
                u.apellido,
                u.telefono,
                u.correo_electronico,
                u.cedula,
                TO_BASE64(f.foto) AS foto_base64
            FROM Usuarios u 
            JOIN medico_paciente p ON p.id_paciente = u.id_usuario 
            JOIN medicos m ON m.id_medico = p.id_medico 
            JOIN asistente a ON a.id_medico = m.id_medico
            LEFT JOIN perfil f ON f.id_usuario = u.id_usuario
            WHERE a.id_usuario = ? AND p.estado = 'A'and  a.estado= 'A' 
        ";
        return $this->db->exec($sql, [$id_usuario]);
    }
    
}


