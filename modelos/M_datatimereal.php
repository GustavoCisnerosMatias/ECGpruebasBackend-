<?php

class M_datatimereal extends \DB\SQL\Mapper {
    public function __construct() {
        parent::__construct(\Base::instance()->get('DB'), 'd_realtime');
    }

    public function obtenerTopicsActivos() {
        // Ejecuta la consulta para obtener los topics activos
        $result = \Base::instance()->get('DB')->exec('SELECT nombre FROM tab_dispositivos WHERE estado = ?', ['A']);
        return array_map(function($item) {
            return $item['nombre'];
        }, $result);
    }
    public function guardarDato($codigo, $valor, $id_parametro) {
        try {
            $db = \Base::instance()->get('DB'); // Obtener la instancia de la base de datos
            $sql = "INSERT INTO d_realtime (codigo, valor, id_parametro, fecha) VALUES (?, ?, ?, NOW())";
            $db->exec($sql, [$codigo, $valor, $id_parametro]);
            return true;
        } catch (\Exception $e) {
            \Base::instance()->get('LOGGER')->write('Error al guardar dato: ' . $e->getMessage()); // Loguear error si existe
            return false;
        }
    }
}


?>
