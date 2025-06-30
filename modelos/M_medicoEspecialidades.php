<?php
class M_medicoEspecialidades extends \DB\SQL\Mapper
{
    public function __construct()
    {
        parent::__construct(\Base::instance()->get('DB'), 'medico_especialidades');
    }

    public function crearEspecialidadMedico($id_medico, $id_especialidad)
    {
        $this->reset();
        $this->id_medico = $id_medico;
        $this->id_especialidad = $id_especialidad;
        $this->fecha_registro = date('Y-m-d H:i:s');
        return $this->save();
    }
}
