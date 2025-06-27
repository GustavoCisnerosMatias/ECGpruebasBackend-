<?php

class M_asistente extends \DB\SQL\Mapper {
    public function __construct() {
        parent::__construct(\Base::instance()->get('DB'), 'asistente');
    }

    // public function actualizardatosfisicos($id_usuario, $peso, $estatura) {
    //     $sql = "
    //         INSERT INTO datos_fisicos (id_usuario, peso, estatura) 
    //         VALUES (?, ?, ?)
    //         ON DUPLICATE KEY UPDATE 
    //             peso = VALUES(peso),
    //             estatura = VALUES(estatura)
    //     ";
    //     $stmt = $this->db->prepare($sql);
    //     return $stmt->execute([$id_usuario, $peso, $estatura]);
    // }
    public function actualizardatosfisicos($id_usuario, $peso, $estatura) {
    // 1. Verificar si existe el registro
    $sqlCheck = "SELECT COUNT(*) as count FROM datos_fisicos WHERE id_usuario = ?";
    $stmt = $this->db->prepare($sqlCheck);
    $stmt->execute([$id_usuario]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result['count'] > 0) {
        // Actualizar
        $sqlUpdate = "UPDATE datos_fisicos SET peso = ?, estatura = ? WHERE id_usuario = ?";
        $stmt = $this->db->prepare($sqlUpdate);
        return $stmt->execute([$peso, $estatura, $id_usuario]);
    } else {
        // Insertar
        $sqlInsert = "INSERT INTO datos_fisicos (id_usuario, peso, estatura) VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($sqlInsert);
        return $stmt->execute([$id_usuario, $peso, $estatura]);
    }
}
    public function obteneradatosfisicospaciente($id_usuario) {
        $sql = "
            SELECT e.* FROM datos_fisicos e 
            JOIN usuarios u ON e.id_usuario = u.id_usuario 
            WHERE u.estado = 'A' AND e.id_usuario = ?
        ";
        return $this->db->exec($sql, [$id_usuario]);
    }

    public function obtenerasistenteporusuario($id_usuario) {
        $sql = "
            SELECT DISTINCT u.nombre, u.apellido, a.id_asistente, a.estado, TO_BASE64(p.foto)
            FROM medicos e 
            JOIN asistente a ON a.id_medico = e.id_medico 
            JOIN usuarios u ON a.id_usuario = u.id_usuario 
            LEFT JOIN perfil p ON u.id_usuario = p.id_usuario 
            WHERE u.estado = 'A' AND e.id_usuario = ?
        ";
        return $this->db->exec($sql, [$id_usuario]);
    }

    public function cambiarestadoasistente($id_asistente, $estado) {
        $sql = "
            UPDATE asistente
            SET estado = ?
            WHERE id_asistente = ?
        ";
        return $this->db->exec($sql, [$estado, $id_asistente]);
    }

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
            FROM usuarios u 
            JOIN medico_paciente p ON p.id_paciente = u.id_usuario 
            JOIN medicos m ON m.id_medico = p.id_medico 
            JOIN asistente a ON a.id_medico = m.id_medico
            LEFT JOIN perfil f ON f.id_usuario = u.id_usuario
            WHERE a.id_usuario = ? AND p.estado = 'A' AND a.estado = 'A'
        ";
        return $this->db->exec($sql, [$id_usuario]);
    }
}
