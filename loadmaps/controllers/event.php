<?php

class Event extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('mainmodel');
    }

    /**
     * Inserta un nuevo evento mediante un POST
     */
    function insert() {
        $this->form_validation->set_rules('competitor_id', 'Competitor id', 'trim|required|xss_clean');

        $this->form_validation->set_rules('event_name', 'Event name', 'required|trim|xss_clean');
        $this->form_validation->set_rules('event_begin_date', 'Event begin date', 'xss_clean');
        $this->form_validation->set_rules('event_place', 'Event place', 'trim|xss_clean');
        $this->form_validation->set_rules('event_description', 'Competitor email', 'trim|xss_clean');

        if ($this->form_validation->run() == FALSE) {
            echo "false";
        } else {
            $event = array();
            $event['event_id'] = '';
            $event['competitor_id'] = $this->input->post('competitor_id');

            $event['event_name'] = $this->input->post('event_name');
            $event['event_begin_date'] = $this->input->post('event_begin_date');
            $event['event_place'] = $this->input->post('event_place');
            $event['event_privacity'] = $this->input->post('event_privacity');
            $event['event_description'] = $this->input->post('event_description');

            //en este caso insertamos el evento primero, para saber su id
            $event_id = $this->mainmodel->insertEvent($event);

            if ($event_id) {
                //ahora subimos la imagen al servidor
                $image = isset($_FILES['event_image']) && !empty($_FILES['event_image']['name']);
                if ($image && $event_id) {
                    $file = $this->_upload_image($event_id);
                    if (file_exists($file)) {
                        echo $event_id;
                    } else {
                        echo $file;
                    }
                } else {
                    echo $event_id;
                }
            }
        }
    }

    /**
     * Inserta un nuevo evento mediante un POST
     */
    function edit() {
        $this->form_validation->set_rules('event_id', 'Event id', 'trim|required|xss_clean');
        $this->form_validation->set_rules('competitor_id', 'Competitor id', 'trim|required|xss_clean');
        $this->form_validation->set_rules('event_old_password', 'Event old password', 'min_length[4]');
        $this->form_validation->set_rules('event_new_password', 'Event new password', 'min_length[4]');
        $this->form_validation->set_rules('event_name', 'Event name', 'required|trim|xss_clean');
        $this->form_validation->set_rules('event_begin_date', 'Event begin date', 'xss_clean');
        $this->form_validation->set_rules('event_place', 'Event place', 'trim|xss_clean');
        $this->form_validation->set_rules('event_description', 'Competitor email', 'trim|xss_clean');

        if ($this->form_validation->run() == FALSE) {
            echo "false";
        } else {
            $event_id = $this->input->post('event_id');

            $event = array();
            $event['event_id'] = $event_id;
            $event['competitor_id'] = $this->input->post('competitor_id');
            $event['event_name'] = $this->input->post('event_name');
            $event['event_begin_date'] = $this->input->post('event_begin_date');
            $event['event_place'] = $this->input->post('event_place');
            $event['event_privacity'] = $this->input->post('event_privacity');
            $event['event_description'] = $this->input->post('event_description');

            if ($event['competitor_id'] == $this->mainmodel->getEventOwner($event['event_id'])) {
                $this->mainmodel->updateEvent($event);

                //ahora subimos la imagen al servidor
                $image = isset($_FILES['event_image']) && !empty($_FILES['event_image']['name']);
                if ($image) {
                    $this->_upload_image($event_id);
                }
                echo "true";
            } else {
                echo "false";
            }
        }
    }

    /**
     * Carga el archivo de avatar del competidor
     * @return boolean True si se ha cargado correctamente. 
     * False en caso contrario.
     */
    private function _upload_image($event_id) {
        $file_name = $event_id;

        $config['upload_path'] = IMAGES_FOLDER . 'events/';
        $config['overwrite'] = TRUE; //esta es nueva
        $config['allowed_types'] = '*';
        $config['max_size'] = '2048';
        $config['max_width'] = '0';
        $config['max_height'] = '0';
        $config['max_width'] = '0';
        $config['file_name'] = $file_name;

        $this->load->library('upload', $config);
        if ($this->upload->do_upload('event_image')) {
            chmod($config['upload_path'] . $file_name, 0664);
            return true;
        } else {
            return $this->upload->display_errors();
        }
    }

    /**
     * Obtiene la imagen de un evento
     * @param type $imageName
     */
    function get_image($imageName) {
        echo img('images/events/' . $imageName);
    }

    /**
     * Devuelve los datos de un evento
     * @param type $event_id id del evento
     */
    function get($event_id) {
        $event_data = $this->mainmodel->getEvent($event_id);
        echo toJSON($event_data);
    }

    /**
     * Obtiene los eventos de un competidor
     * @param type $competitor_id ID del competidor
     */
    function get_from_competitor($competitor_id) {
        $event_data = $this->mainmodel->getCompetitorEvents($competitor_id);
        echo toJSON($event_data);
    }

    /**
     * Borra un evento tras comprobar que la acción la solicita su creador
     */
    function remove() {
        $data = array();
        $data['competitor_id'] = $this->uri->segment(3);
        $data['competitor_password'] = $this->uri->segment(4);
        $data['event_id'] = $this->uri->segment(5);

        $result = false;
        //comprobamos primero que el creador del evento es quien solicita el borrado
        if ($this->mainmodel->getEventOwner($data['event_id']) === $data['competitor_id'])
        //luego comprobamos si el par competidor/contraseña es válido
            if ($this->mainmodel->checkCompetitorCredentials($data)) {
                //y si se cumple todo, borramos el evento
                $this->mainmodel->removeEvent($data['event_id']);
                $result = true;
            }
        echo $result === true ? "true" : "false";
    }

    /**
     * Comprueba si un evento tiene entradas de coordenadas
     * @param type $event_id id del evento
     */
    function check_event_avaliability($event_id) {
        $result = $this->mainmodel->checkEventAvaliability($event_id);
        echo $result ? "true" : "false";
    }

    /**
     * Obtiene los datos de los competidores que tienen al menos una marca publicada en ese evento
     * @param type $event_id
     */
    function get_competitors_in_event($event_id) {
        $competitor_data = $this->mainmodel->getCompetitorsInEvent($event_id);
        echo toJSON($competitor_data);
    }

}

?>
