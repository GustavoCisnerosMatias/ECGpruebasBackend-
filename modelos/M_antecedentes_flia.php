<?php

class M_antecedentes_flia extends \DB\SQL\Mapper {
    public function __construct() {
        parent::__construct(\Base::instance()->get('DB'), 'antecedentesfamiliares');
    }

    public function obtenerante_flia_xid($id_paciente) {
        $sql = "SELECT a.*, u.fecha_nacimiento, u.genero
                FROM antecedentesfamiliares a 
                JOIN usuarios u ON u.id_usuario = a.id_paciente
                WHERE a.id_paciente = ?";
        
        $result = $this->db->exec($sql, [$id_paciente]);
        
    
        return $result; 
    }

    public function verificarDuplicado($id_paciente, $relacion_familiar, $codigo_emfermedad) {
        $sql = "SELECT * FROM antecedentesfamiliares 
                WHERE id_paciente = ? 
                AND relacion_familiar = ? 
                AND codigo_emfermedad = ?";
        
        $result = $this->db->exec($sql, [$id_paciente, $relacion_familiar, $codigo_emfermedad]);

        return !empty($result);
    }

    public function guardarAntecedenteFamiliar($datos) {
        if ($this->verificarDuplicado($datos['id_paciente'], $datos['relacion_familiar'], $datos['codigo_emfermedad'])) {
            error_log("Registro duplicado encontrado para id_paciente: " . $datos['id_paciente']);
            return 'duplicado';
        }

        $sql = "SELECT id_medico FROM medicos WHERE id_usuario = ?";
        $medico = $this->db->exec($sql, [$datos['id_usuario']]);

        if (empty($medico) || !isset($medico[0]['id_medico'])) {
            error_log("No se encontró id_medico para id_usuario: " . $datos['id_usuario']);
            return 'medico_no_encontrado';
        }

        $sqlInsert = "INSERT INTO antecedentesfamiliares (id_paciente, relacion_familiar, codigo_emfermedad, edad_diagnostico, estado_actual, causa_muerte, observaciones, id_medico) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $resultado = $this->db->exec($sqlInsert, [
            $datos['id_paciente'], 
            $datos['relacion_familiar'], 
            $datos['codigo_emfermedad'], 
            $datos['edad_diagnostico'], 
            $datos['estado_actual'], 
            $datos['causa_muerte'], 
            $datos['observaciones'], 
            $medico[0]['id_medico']
        ]);

        if ($resultado) {
            return 'guardado';
        } else {
            error_log("Error al guardar el antecedente familiar para id_paciente: " . $datos['id_paciente']);
            return 'error_guardado';
        }
    }

    public function editarAntecedenteFamiliar($datos) {
        $sqlUpdate = "UPDATE antecedentesfamiliares 
                      SET relacion_familiar = ?, 
                          codigo_emfermedad = ?, 
                          edad_diagnostico = ?, 
                          estado_actual = ?, 
                          causa_muerte = ?, 
                          observaciones = ?
                      WHERE id_antecedente = ?";
        
        $resultado = $this->db->exec($sqlUpdate, [
            $datos['relacion_familiar'], 
            $datos['codigo_emfermedad'], 
            $datos['edad_diagnostico'], 
            $datos['estado_actual'], 
            $datos['causa_muerte'], 
            $datos['observaciones'], 
            $datos['id_antecedente']
        ]);

        if ($resultado) {
            return 'actualizado';
        } else {
            error_log("Error al actualizar el antecedente familiar con id_antecedente: " .$datos['id_antecedente']);
            return 'error_actualizado';
        }
    }
}
