<?php

class M_centro extends \DB\SQL\Mapper {
    public function __construct() {
        parent::__construct(\Base::instance()->get('DB'), 'centros_hospitalarios');
    }

    public function mostrarCentro() {
        $sql = "SELECT *FROM centros_hospitalarios p WHERE p.estado = 'A'";
        return $this->db->exec($sql);
    }
    
    public function buscarPorId($id) {
        return $this->db->exec("SELECT * FROM centros_hospitalarios WHERE id_centro = ? AND estado = 'A'", [$id]);
    }

    public function insertarCentro($nombre, $tipo) {
        return $this->db->exec("INSERT INTO centros_hospitalarios (nombre_centro, tipo_centro, estado) VALUES (?, ?, 'A')", [$nombre, $tipo]);
    }

    public function actualizarCentro($id, $nombre, $tipo) {
        return $this->db->exec("UPDATE centros_hospitalarios SET nombre_centro = ?, tipo_centro = ? WHERE id_centro = ? AND estado = 'A'", [$nombre, $tipo, $id]);
    }

    public function eliminarCentro($id) {
        // Eliminación lógica (opcional: puedes hacer un DELETE físico si prefieres)
        return $this->db->exec("UPDATE centros_hospitalarios SET estado = 'I' WHERE id_centro = ?", [$id]);
    }
}
