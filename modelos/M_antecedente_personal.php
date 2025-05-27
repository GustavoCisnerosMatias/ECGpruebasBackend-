<?php

class M_antecedente_personal extends \DB\SQL\Mapper {
    public function __construct() {
        parent::__construct(\Base::instance()->get('DB'), 'antecedentespersonal');
    }

    public function createAntecedentePersonal($data) {
        $this->copyFrom($data);
        $result = $this->save();
        if ($result) {
            error_log("Antecedente personal guardado con ID: " . $this->get('_id'));
        } else {
            error_log("Error al guardar el antecedente personal.");
        }
        return $result;
    }

    public function getAntecedentesPersonales($id_paciente) {
        return $this->find(['id_paciente = ?', $id_paciente]);
    }
}
