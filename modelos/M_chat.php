<?php

class M_chat extends \DB\SQL\Mapper {
    public function __construct() {
        parent::__construct(\Base::instance()->get('DB'), 'mensajes');
    }

    public function mostrarChatnew($id_usuario) {
        $sql = "SELECT COUNT(*) AS total FROM mensajes m WHERE m.visto = 0 AND m.id_usuario = ?";
        return $this->db->exec($sql, [$id_usuario]);
    }
    public function mostrarChatmedi($id_usuario ) {
        $sql = "SELECT COUNT(*) AS total FROM mensajes m JOIN medicos ME ON m.id_medico=ME.id_medico WHERE m.visto = 0 AND ME.id_usuario = ?";
        return $this->db->exec($sql, [$id_usuario]);
    }
    
    
    
 public function mostrarChatmeditodos($ids_usuarios, $ids_medicos)
{
    // Verifica que ambas variables estén definidas
    if (empty($ids_usuarios) || empty($ids_medicos)) {
        throw new Exception("Ids de usuarios o médicos no proporcionados.");
    }

    // Construir los placeholders dinámicos
    $placeholders_usuarios = implode(',', array_fill(0, count($ids_usuarios), '?'));
    $placeholders_medicos = implode(',', array_fill(0, count($ids_medicos), '?'));

    // Consulta SQL para obtener los chats
    $sql = "
        SELECT 
            m.id_usuario,
            ME.id_medico,
            COUNT(*) AS chats
        FROM mensajes m
        JOIN medicos ME ON m.id_medico = ME.id_medico
        WHERE m.visto = 0
          AND m.id_usuario IN ($placeholders_usuarios)
          AND ME.id_usuario IN ($placeholders_medicos)
        GROUP BY m.id_usuario, ME.id_medico
    ";

    // Combina las variables de usuarios y médicos en un solo array para pasar a la consulta
    $params = array_merge($ids_usuarios, $ids_medicos);

    // Ejecutar la consulta
    return $this->db->exec($sql, $params);
}





    // Mostrar chat filtrado por id_usuario e id_medico
    public function mostrarChat($id_usuario, $id_medico) {
        $sql = "SELECT * FROM mensajes WHERE id_usuario = ? AND id_medico = ?";
        return $this->db->exec($sql, [$id_usuario, $id_medico]);
    }

    // Enviar un nuevo mensaje
    public function enviarMensaje($id_medico, $id_usuario, $mensaje, $id_rol) {
        $this->id_medico = $id_medico;
        $this->id_usuario = $id_usuario;
        $this->mensaje = $mensaje;
        $this->id_rol = $id_rol;
        $this->save();
    }

    // Actualizar el estado de visto de un mensaje
    public function actualizarVista($id_mensaje) {
        $sql = "UPDATE mensajes SET visto = 1 WHERE id_mensaje = ?";
        return $this->db->exec($sql, [$id_mensaje]);
    }
    public function verificarMedico($id_medico) {
        $sql = "SELECT COUNT(*) as total FROM medicos WHERE id_medico = ?";
        $result = $this->db->exec($sql, [$id_medico]);
        return $result[0]['total'] > 0; // Devuelve verdadero si existe
    }
    
}

