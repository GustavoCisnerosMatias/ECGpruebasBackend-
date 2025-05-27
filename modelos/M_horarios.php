<?php

class M_horarios extends \DB\SQL\Mapper
{
    public function __construct()
    {
        parent::__construct(\Base::instance()->get('DB'), 'horarios');
    }

    public function createhorarios($data)
    {
        try {
            if (!isset($data['id_usuario'])) {
                throw new Exception('El id_usuario es requerido.');
            }

            $db = $this->db;
            $medicoMapper = new \DB\SQL\Mapper($db, 'medicos');
            $medicoMapper->load(['id_usuario = ?', $data['id_usuario']]);

            if ($medicoMapper->dry()) {
                throw new Exception('No se encontró un médico con el id_usuario proporcionado.');
            }

            $id_medico = $medicoMapper->id_medico;

            $data['id_medico'] = $id_medico;

            $this->copyFrom($data);

            return $this->save();
        } catch (Exception $e) {
            throw new Exception('Error al crear horarios: ' . $e->getMessage());
        }
    }

    public function updatehorarios($data)
    {
        try {
            if (!isset($data['id_horario'])) {
                throw new Exception('El id_horario es requerido.');
            }

            $this->load(['id_horario = ?', $data['id_horario']]);

            if ($this->dry()) {
                throw new Exception('No se encontró el horario con el id proporcionado.');
            }

            $this->copyFrom($data);

            return $this->save();
        } catch (Exception $e) {
            throw new Exception('Error al actualizar el horario: ' . $e->getMessage());
        }
    }

    public function getHorariosByUsuario($id_usuario)
    {
        try {
            $db = $this->db;
            $medicoMapper = new \DB\SQL\Mapper($db, 'medicos');
            $medicoMapper->load(['id_usuario = ?', $id_usuario]);

            if ($medicoMapper->dry()) {
                throw new Exception('No se encontró un médico con el id_usuario proporcionado.');
            }

            $id_medico = $medicoMapper->id_medico;

            $this->load(['id_medico = ?', $id_medico]);

            if ($this->dry()) {
                return false;
            }

            $horarios = [];
            while (!$this->dry()) {
                $horarios[] = $this->cast();
                $this->next();
            }

            return $horarios;
        } catch (Exception $e) {
            throw new Exception('Error al obtener los horarios: ' . $e->getMessage());
        }
    }

    public function getHorariosxmedico($id_medico)
    {
        try {
            $db = $this->db;

            $horarioMapper = new \DB\SQL\Mapper($db, 'horarios');
            $horarioMapper->load(['id_medico = ?', $id_medico]);

            if ($horarioMapper->dry()) {
                return false;
            }

            $horarios = [];
            while (!$horarioMapper->dry()) {
                $horarios[] = $horarioMapper->cast();
                $horarioMapper->next();
            }

            return $horarios;

        } catch (Exception $e) {
            throw new Exception('Error al obtener los horarios: ' . $e->getMessage());
        }
    }
}
