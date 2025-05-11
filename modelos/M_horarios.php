<?php

class M_horarios extends \DB\SQL\Mapper
{
    public function __construct()
    {
        parent::__construct(\Base::instance()->get('DB'), 'horarios');
    }

    // Método para crear un nuevo horario
    public function createhorarios($data)
    {
        try {
            // Validar que exista el id_usuario en los datos enviados
            if (!isset($data['id_usuario'])) {
                throw new Exception('El id_usuario es requerido.');
            }

            // Buscar el id_medico correspondiente al id_usuario
            $db = $this->db; // Obtener instancia de la base de datos
            $medicoMapper = new \DB\SQL\Mapper($db, 'medicos');
            $medicoMapper->load(['id_usuario = ?', $data['id_usuario']]);

            // Verificar si se encontró un médico con el id_usuario proporcionado
            if ($medicoMapper->dry()) {
                throw new Exception('No se encontró un médico con el id_usuario proporcionado.');
            }

            // Obtener el id_medico
            $id_medico = $medicoMapper->id_medico;

            // Agregar el id_medico a los datos que se van a guardar en la tabla horarios
            $data['id_medico'] = $id_medico;

            // Crear instancia del modelo y copiar datos
            $this->copyFrom($data);

            // Guardar en la base de datos
            return $this->save();
        } catch (Exception $e) {
            throw new Exception('Error al crear horarios: ' . $e->getMessage());
        }
    }



    // Método para actualizar un horario existente
    public function updatehorarios($data)
    {
        try {
            // Verificar que exista el id_horario
            if (!isset($data['id_horario'])) {
                throw new Exception('El id_horario es requerido.');
            }

            // Cargar el horario existente
            $this->load(['id_horario = ?', $data['id_horario']]);

            // Verificar si el horario existe
            if ($this->dry()) {
                throw new Exception('No se encontró el horario con el id proporcionado.');
            }

            // Actualizar los datos del horario
            $this->copyFrom($data);

            // Guardar los cambios en la base de datos
            return $this->save();
        } catch (Exception $e) {
            throw new Exception('Error al actualizar el horario: ' . $e->getMessage());
        }
    }




    // Método para obtener los horarios por id_usuario
    public function getHorariosByUsuario($id_usuario)
    {
        try {
            // Buscar el id_medico correspondiente al id_usuario
            $db = $this->db; // Obtener instancia de la base de datos
            $medicoMapper = new \DB\SQL\Mapper($db, 'medicos');
            $medicoMapper->load(['id_usuario = ?', $id_usuario]);

            // Verificar si se encontró un médico con el id_usuario proporcionado
            if ($medicoMapper->dry()) {
                throw new Exception('No se encontró un médico con el id_usuario proporcionado.');
            }

            // Obtener el id_medico
            $id_medico = $medicoMapper->id_medico;

            // Buscar todos los horarios asociados al id_medico
            $this->load(['id_medico = ?', $id_medico]);

            // Verificar si se encontraron horarios
            if ($this->dry()) {
                return false;
            }

            // Convertir los resultados en un array
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
    //Listar horarios con id_medico se utiliza en telemedicna del peicnete
   
    public function getHorariosxmedico($id_medico)
    {
        try {
            // Obtener instancia de la base de datos
            $db = $this->db;
    
            // Buscar todos los horarios asociados al id_medico
            $horarioMapper = new \DB\SQL\Mapper($db, 'horarios');
            $horarioMapper->load(['id_medico = ?', $id_medico]);
    
            // Verificar si se encontraron horarios
            if ($horarioMapper->dry()) {
                return false; // No se encontraron horarios
            }
    
            // Convertir los resultados en un array
            $horarios = [];
            while (!$horarioMapper->dry()) {
                $horarios[] = $horarioMapper->cast();
                $horarioMapper->next();
            }
    
            return $horarios; // Retornar el array de horarios
    
        } catch (Exception $e) {
            throw new Exception('Error al obtener los horarios: ' . $e->getMessage());
        }
    }
    


}

