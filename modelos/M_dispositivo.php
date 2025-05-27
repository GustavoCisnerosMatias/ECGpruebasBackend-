<?php

class M_dispositivo extends \DB\SQL\Mapper
{
    public function __construct(){
        parent::__construct(\Base::instance()->get('DB'), 'tab_dispositivos');
    }
    
    public function getAll(){
        $sql = "SELECT p.id_usuario, p.nombre, p.codigo FROM tab_dispositivos p WHERE estado ='A'";
        return $this->db->exec($sql);
    }

    public function getTopics(){
        $sql = "SELECT p.id_dispo, p.nombre, p.fecha_registro,p.estado FROM tab_dispositivos p;";
        return $this->db->exec($sql);
    }
    
    public function createDispositivo($data){
        try {
            $this->copyFrom($data);
            return $this->save();

        } catch (Exception $e) {
            throw new Exception('Error al crear dispositivo: ' . $e->getMessage());
        }
    }

    public function listarDispositivosPorUsuario($id_usuario) {
        $sql = "SELECT * FROM tab_dispositivos WHERE id_usuario = ? and estado='A'";
        return $this->db->exec($sql, [$id_usuario]);
    }

    public function actualizarEstadoDispositivo($id_dispo, $estado){
        try {
            $this->load(['id_dispo = ?', $id_dispo]);
            if (!$this->dry()) {
                $this->estado = $estado;
                $this->save();
                return true;
            }
            return false; 
        } catch (Exception $e) {
            throw new Exception('Error al actualizar el estado del dispositivo: ' . $e->getMessage());
        }
    }


}


