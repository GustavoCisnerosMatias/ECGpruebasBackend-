<?php

class M_alergias extends \DB\SQL\Mapper {
    public function __construct() {
        parent::__construct(\Base::instance()->get('DB'), 'alergias');
    }

    public function obtenertipoalergias() {
        $sql = "SELECT * FROM tiposalergia";
        return $this->db->exec($sql);
    }

public function obtenerAlergias($id_paciente) {
    $sql = "SELECT 
                t.nombretipo AS NombreTipo, 
                t.descripcion AS DescripcionTipo, 
                a.id_alergia, 
                a.descripcion AS Descripcion, 
                a.nivel AS Nivel, 
                a.fechacreacion AS FechaCreacion, 
                m.codigo, 
                m.denominacion_comun_internacional, 
                p.foto, 
                u.fecha_nacimiento, 
                u.genero AS Genero, 
                u.nombre, 
                u.apellido, 
                u.cedula 
            FROM 
                alergias a
            INNER JOIN 
                tiposalergia t ON t.id_tipo = a.id_tipo
            INNER JOIN 
                usuarios u ON u.id_usuario = a.id_paciente
            LEFT JOIN 
                medicamentos m ON m.id_medic = a.id_medic
            LEFT JOIN 
                perfil p ON u.id_usuario = p.id_usuario
            WHERE 
                a.id_paciente = ?
            ORDER BY 
                a.fechacreacion DESC";
    
    $result = $this->db->exec($sql, [$id_paciente]);

    foreach ($result as &$row) {
        if ($row['foto']) {
            $row['foto'] = 'data:image/jpeg;base64,' . base64_encode($row['foto']);
        }
    }
    return $result;
}
    public function guardarAlergias($datos) {
        $sql = "SELECT id_medico FROM medicos WHERE id_usuario = ?";
        $medico = $this->db->exec($sql, [$datos['id_usuario']]);

        if (empty($medico) || !isset($medico[0]['id_medico'])) { 
            error_log("No se encontró id_medico para id_usuario: " . $datos['id_usuario']);
            return 'medico_no_encontrado'; 
        }

        $id_medic = isset($datos['id_medic']) && $datos['id_medic'] !== '' ? $datos['id_medic'] : null;
        $sqlInsert = "INSERT INTO alergias (id_paciente, descripcion, id_medic, id_medico, id_tipo, nivel) VALUES (?, ?, ?, ?, ?, ?)";
        $resultado = $this->db->exec($sqlInsert, [
            $datos['id_paciente'], 
            $datos['descripcion'], 
            $id_medic,
            $medico[0]['id_medico'],
            $datos['id_tipo'], 
            $datos['nivel']
        ]);

        if ($resultado) {
            return 'guardado';
        } else {
            error_log("Error al guardar la alerta: " . $datos['id_paciente']);
            return 'error_guardado';
        }
    }

    public function editarAlergias($datos) {
        $sqlUpdate = "UPDATE alergias 
                      SET descripcion = ?, 
                          nivel = ?
                      WHERE id_alergia = ?"; 
        $resultado = $this->db->exec($sqlUpdate, [
            $datos['descripcion'], 
            $datos['nivel'],     
            $datos['id_alergia'] 
        ]);

        if ($resultado) {
            return 'actualizado'; 
        } else {
            error_log("Error al actualizar alergia con id_alergia: " . $datos['id_alergia']);
            return 'error_actualizado'; 
        }
    }

    public function eliminarAlergia($id_alergia) {
        $sqlDelete = "DELETE FROM alergias WHERE id_alergia = ?";
        $resultado = $this->db->exec($sqlDelete, [$id_alergia]);

        if ($resultado) {
            return 'eliminado';
        } else {
            error_log("Error al eliminar la alergia con id_alergia: " . $id_alergia);
            return 'error_eliminado'; 
        }
    }
}
