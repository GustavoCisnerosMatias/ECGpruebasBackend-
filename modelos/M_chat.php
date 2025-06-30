<?php

class M_chat extends \DB\SQL\Mapper {
    public function __construct() {
        parent::__construct(\Base::instance()->get('DB'), 'mensajes');
    }

    public function mostrarchatnew($id_usuario) {
        $sql = "SELECT COUNT(*) AS total FROM mensajes m WHERE m.visto = 0 AND m.id_usuario = ?";
        return $this->db->exec($sql, [$id_usuario]);
    }

    public function mostrarchatmedi($id_usuario) {
        $sql = "SELECT COUNT(*) AS total FROM mensajes m JOIN medicos me ON m.id_medico = me.id_medico WHERE m.visto = 0 AND me.id_usuario = ?";
        return $this->db->exec($sql, [$id_usuario]);
    }

    public function mostrarchatmeditodos($ids_usuarios, $ids_medicos) {
        if (empty($ids_usuarios) || empty($ids_medicos)) {
            throw new Exception("Ids de usuarios o médicos no proporcionados.");
        }

        $placeholders_usuarios = implode(',', array_fill(0, count($ids_usuarios), '?'));
        $placeholders_medicos = implode(',', array_fill(0, count($ids_medicos), '?'));

        $sql = "
            SELECT 
                m.id_usuario,
                me.id_medico,
                COUNT(*) AS chats
            FROM mensajes m
            JOIN medicos me ON m.id_medico = me.id_medico
            WHERE m.visto = 0
              AND m.id_usuario IN ($placeholders_usuarios)
              AND me.id_usuario IN ($placeholders_medicos)
            GROUP BY m.id_usuario, me.id_medico
        ";

        $params = array_merge($ids_usuarios, $ids_medicos);

        return $this->db->exec($sql, $params);
    }

    public function mostrarchat($id_usuario, $id_medico) {
        $sql = "SELECT * FROM mensajes WHERE id_usuario = ? AND id_medico = ? ORDER BY fecha_envio DESC;";
        return $this->db->exec($sql, [$id_usuario, $id_medico]);
    }

    public function enviarmensaje($id_medico, $id_usuario, $mensaje, $id_rol) {
        $this->id_medico = $id_medico;
        $this->id_usuario = $id_usuario;
        $this->mensaje = $mensaje;
        $this->id_rol = $id_rol;
        $this->save();
    }

    public function actualizarvista($id_mensaje) {
        $sql = "UPDATE mensajes SET visto = 1 WHERE id_mensaje = ?";
        return $this->db->exec($sql, [$id_mensaje]);
    }

    public function verificarmedico($id_medico) {
        $sql = "SELECT COUNT(*) as total FROM medicos WHERE id_medico = ?";
        $result = $this->db->exec($sql, [$id_medico]);
        return $result[0]['total'] > 0;
    }
}
