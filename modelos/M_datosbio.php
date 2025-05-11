<?php

class M_datosbio extends \DB\SQL\Mapper
{
    public function __construct()
    {
        parent::__construct(\Base::instance()->get('DB'), 'd_realtime');
    }

    public function obtenerDatos($id_usuario, $id_parametro, $fecha_ini, $fecha_fin)
    {
        $sql = "
                            SELECT r.fecha, p.nombre, p.unidad_medida, r.valor, t.codigo
            FROM d_realtime r
            JOIN tab_dispositivos t ON r.codigo = t.codigo
            JOIN parametros p ON p.id_parametro = r.id_parametro
            JOIN Usuarios u ON u.id_usuario = t.id_usuario
            WHERE u.id_usuario = ?
            and r.id_parametro = ?
            AND r.fecha BETWEEN ? AND ?
        ";

        // Ejecutar la consulta pasando los parámetros
        return $this->db->exec($sql, [$id_usuario, $id_parametro, $fecha_ini, $fecha_fin]);
    }
}

