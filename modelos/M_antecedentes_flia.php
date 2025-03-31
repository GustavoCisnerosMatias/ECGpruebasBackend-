<?php

class M_antecedentes_flia extends \DB\SQL\Mapper {
    public function __construct() {
        parent::__construct(\Base::instance()->get('DB'), 'AntecedentesFamiliares');
    }


    public function obtenerante_flia_xid($id_paciente) {
        $sql = "SELECT a.*, u.fecha_nacimiento, u.Genero, p.foto
                FROM AntecedentesFamiliares a 
                JOIN Usuarios u ON u.id_usuario = a.id_paciente
                JOIN perfil p ON u.id_usuario = p.id_usuario 
                WHERE a.id_paciente = ?";
        
        $result = $this->db->exec($sql, [$id_paciente]);
        
        if ($result === false) {
            error_log("Error al obtener antecedentes familiares: " . $this->db->error);
            return null; // O maneja el error de otra manera
        }
        
        // Convierte el BLOB a una cadena base64
        foreach ($result as &$row) {
            if ($row['foto']) {
                $row['foto'] = 'data:image/jpeg;base64,' . base64_encode($row['foto']);
            }
        }
    
        return $result; 
    }
    
   ///////medico////

   // Método para verificar si hay un registro duplicado
public function verificarDuplicado($id_paciente, $relacion_familiar, $Codigo_emfermedad) {
    $sql = "SELECT * FROM AntecedentesFamiliares 
            WHERE id_paciente = ? 
            AND relacion_familiar = ? 
            AND Codigo_emfermedad = ?";
    
    $result = $this->db->exec($sql, [$id_paciente, $relacion_familiar, $Codigo_emfermedad]);

    return !empty($result); // Devuelve true si se encontró un duplicado, de lo contrario false
}

public function guardarAntecedenteFamiliar($datos) {
    // Verifica si ya existe un registro duplicado por id_paciente, relacion_familiar y Codigo_emfermedad
    if ($this->verificarDuplicado($datos['id_paciente'], $datos['relacion_familiar'], $datos['Codigo_emfermedad'])) {
        error_log("Registro duplicado encontrado para id_paciente: " . $datos['id_paciente']);
        return 'duplicado'; // Regresa 'duplicado' si se encuentra un registro exacto
    }

    // Obtener id_medico usando el id_usuario relacionado
    $sql = "SELECT id_medico FROM medicos WHERE id_usuario = ?";
    $medico = $this->db->exec($sql, [$datos['id_usuario']]);

    // Verifica si se encontró el médico
    if (empty($medico) || !isset($medico[0]['id_medico'])) { // Cambiar a $medico[0] para acceder correctamente al resultado
        error_log("No se encontró id_medico para id_usuario: " . $datos['id_usuario']);
        return 'medico_no_encontrado'; // Regresa un mensaje si no se encuentra el médico
    }

    // Guardar nuevo antecedente familiar
    $sqlInsert = "INSERT INTO AntecedentesFamiliares (id_paciente, relacion_familiar, Codigo_emfermedad,edad_diagnostico,estado_actual,causa_muerte,observaciones, id_medico) VALUES (?, ?, ?, ?,?,?,?,?)";
    
    // Ejecutar la consulta de inserción
    $resultado = $this->db->exec($sqlInsert, [
        $datos['id_paciente'], 
        $datos['relacion_familiar'], 
        $datos['Codigo_emfermedad'], 
        $datos['edad_diagnostico'], 
        $datos['estado_actual'], 
        $datos['causa_muerte'], 
        $datos['observaciones'], 
        $medico[0]['id_medico'] // Acceder al id_medico correctamente
    ]);

    // Verificar si la inserción fue exitosa
    if ($resultado) {
        return 'guardado'; // Devuelve un mensaje de éxito
    } else {
        error_log("Error al guardar el antecedente familiar para id_paciente: " . $datos['id_paciente']);
        return 'error_guardado'; // Devuelve un mensaje de error
    }
}
public function editarAntecedenteFamiliar($datos) {
    // Guardar nuevo antecedente familiar
    $sqlUpdate = "UPDATE AntecedentesFamiliares 
                  SET relacion_familiar = ?, 
                      Codigo_emfermedad = ?, 
                      edad_diagnostico = ?, 
                      estado_actual = ?, 
                      causa_muerte = ?, 
                      observaciones = ?
                  WHERE id_antecedente = ?"; // Asegúrate de que el nombre de la columna sea correcto
    
    // Ejecutar la consulta de actualización
    $resultado = $this->db->exec($sqlUpdate, [
        $datos['relacion_familiar'], 
        $datos['Codigo_emfermedad'], 
        $datos['edad_diagnostico'], 
        $datos['estado_actual'], 
        $datos['causa_muerte'], 
        $datos['observaciones'], 
        $datos['id_antecedente'] // Debes pasar el ID del antecedente a actualizar
    ]);

    // Verificar si la actualización fue exitosa
    if ($resultado) {
        return 'actualizado'; // Devuelve un mensaje de éxito
    } else {
        error_log("Error al actualizar el antecedente familiar con id_antecedente: " . $datos['id_antecedente_familiar']);
        return 'error_actualizado'; // Devuelve un mensaje de error
    }
}


}
?>