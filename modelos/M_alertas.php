<?php

class M_alertas extends \DB\SQL\Mapper {
    public function __construct() {
        parent::__construct(\Base::instance()->get('DB'), 'alertas');
    }
    public function crearAlerta($id_usuario, $vista, $id_datos, $contenido) {
        $fecha_alerta = date('Y-m-d H:i:s');
        $sql = "INSERT INTO alertas (id_usuario, vista, estado_alerta, fecha_alerta, id_datos, contenido)
                VALUES (?, ?, ?, ?, ?, ?)";
        $this->db->exec($sql, [
            $id_usuario,
            $vista,
            1, // 1 = por ver
            $fecha_alerta,
            $id_datos,
            $contenido
        ]);


    }

}
