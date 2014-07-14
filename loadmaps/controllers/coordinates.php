<?php

class Coordinates extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('mainmodel');
        date_default_timezone_set('Europe/Madrid');
    }

    /**
     * Esta función recibe las coordenadas de los jugadores en un instante y lo almacena en la BD
     */
    function send() {
        $data = array();
        $data['competitor_id'] = $this->uri->segment(3);
        $competitor_password = hash('sha256', $this->uri->segment(4));
        $data['event_id'] = $this->uri->segment(5);
        $data['mark_latitude'] = $this->uri->segment(6);
        $data['mark_longitude'] = $this->uri->segment(7);
        $data['mark_date'] = date('Y-m-d H:i:s', time());

        $result = false;
        if ($this->mainmodel->checkCompetitorPassword($data['competitor_id'], $competitor_password)) {
            $result = $this->mainmodel->insertMark($data);
        }

        echo $result ? "true" : "false";
    }

    /**
     * Obtiene las coordenadas de un competidor en un evento
     */
    function get_competitor_coordinates() {
        $coordinates = array();
        $coordinates['competitor_id'] = $this->uri->segment(3);
        $coordinates['event_id'] = $this->uri->segment(4);

        $data = $this->mainmodel->getMarks($coordinates);
        echo toJSON($data);
    }

    /**
     * Obtiene todas las marcas
     * @param type $event_id id del evento
     */
    function get_all_marks($event_id) {
        $data = $this->mainmodel->getAllMarks($event_id);
        echo toJSON($data);
    }

//prototipo:
    //http://localhost/ci/index.php/coordinates/send/0/0/20130812230005/123.4352334/-34.13454332
}

?>
