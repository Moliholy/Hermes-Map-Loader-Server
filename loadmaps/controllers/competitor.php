<?php

class Competitor extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('mainmodel');
    }

    /**
     * Carga una ruta en el directorio
     */
    function upload_route() {
        $competitor_id = $this->input->post('competitor_id');
        $config['upload_path'] = ROUTES_FOLDER . $competitor_id . '/';
        $config['overwrite'] = TRUE; //esta es nueva
        $config['allowed_types'] = '*';
        $config['max_size'] = '256';
        $config['max_width'] = '0';
        $config['max_height'] = '0';
        $config['max_width'] = '0';

        $directory = ROUTES_FOLDER . $competitor_id;
        if (!is_dir($directory)) {
            mkdir($directory, 0755);
            chmod($directory, 0755);
        }

        $this->load->library('upload', $config);
        if ($this->upload->do_upload('route')) {
            $data = $this->upload->data();
            $complete_path = $data['full_path'];
            if (is_file($complete_path)) {
                chmod($complete_path, 0664);
            }
            echo "true";
        } else {
            echo $this->upload->display_errors();
        }
    }

    /**
     * Devuelve las rutas de un competidor
     * @param type $competitor_id ID del competidor
     */
    function get_routes($competitor_id) {
        $routes_folder = ROUTES_FOLDER . $competitor_id . '/';
        $this->load->helper('directory');
        echo toJSON(directory_map($routes_folder));
    }

    /**
     * Carga el archivo de avatar del competidor
     * @return boolean True si se ha cargado correctamente. 
     * False en caso contrario.
     */
    private function _upload_avatar($competitor_id) {
        $file_name = $competitor_id;
        $config['upload_path'] = IMAGES_FOLDER . 'competitors/';
        $config['overwrite'] = TRUE; //esta es nueva
        $config['allowed_types'] = '*';
        $config['max_size'] = '2048';
        $config['max_width'] = '0';
        $config['max_height'] = '0';
        $config['max_width'] = '0';
        $config['file_name'] = $file_name;

        $this->load->library('upload', $config);
        if ($this->upload->do_upload('avatar')) {
            if (is_file($config['upload_path'] . $file_name)) {
                chmod($config['upload_path'], 0664);
            }
            return true;
        } else {
            return $this->upload->display_errors();
        }
    }

    /**
     * Busca eventos interesantes para el usuario
     * @param type $competitor_id id del competidor
     */
    function findEvents($competitor_id) {
        $events = $this->mainmodel->findEvents($competitor_id);
        echo toJSON($events);
    }

    /**
     * Inserta un competitor en la BD, incluyendo su foto
     */
    function insert() {
//comprobamos primero que el formulario es correcto
        $this->form_validation->set_rules('competitor_name', 'Competitor name', 'trim|required|xss_clean');
        $this->form_validation->set_rules('competitor_password', 'Competitor password', 'required|min_length[4]');
        $this->form_validation->set_rules('competitor_email', 'Competitor email', 'trim|required|valid_email');

        if ($this->form_validation->run() === FALSE) {
            echo validation_errors();
        } else {
            $competitor = array();
            $competitor['competitor_id'] = '';
            $competitor['competitor_name'] = $this->input->post('competitor_name');
            $competitor['competitor_password'] = hash('sha256', $this->input->post('competitor_password'));
            $competitor['competitor_email'] = $this->input->post('competitor_email');
            $competitor['competitor_birthdate'] = $this->input->post('competitor_birthdate');
            $competitor['competitor_sex'] = $this->input->post('competitor_sex');

            //registramos al competidor
            $id = $this->mainmodel->insertCompetitor($competitor);
            if ($id) {
                //comprobamos si ha subido un avatar
                $image = isset($_FILES['avatar']) && !empty($_FILES['avatar']['name']);
                if ($image) {
                    $this->_upload_avatar($id);
                }
                //le creamos ahora su directorio para las rutas
                mkdir(ROUTES_FOLDER . $id, 0755);
                mkdir(IMAGES_FOLDER . 'competitors/' . $id, 0755);

                echo $id;
            } else {
                echo "false";
            }
        }
    }

    /**
     * Logea a un competidor
     */
    function login() {
        $competitor = array();
        $competitor['competitor_email'] = $this->uri->segment(3);
        $competitor['competitor_password'] = hash('sha256', $this->uri->segment(4));

        $result = $this->mainmodel->checkCompetitorCredentials($competitor);
        if ($result) {
            echo toJSON($result);
        } else {
            echo "false";
        }
    }

    /**
     * Obtiene los datos de un usuario
     *  @param type $competitor_id id del competidor
     */
    function get($competitor_id) {
        $data = $this->mainmodel->getCompetitorData($competitor_id);
        echo toJSON($data);
    }

    /**
     * Obtiene los datos del usuario a través de su email
     * @param type $email email del competidor
     */
    function get_by_email($email) {
        $data = $this->mainmodel->getCompetitorDataByEmail($email);
        echo toJSON($data);
    }

    /**
     * Inserta un nuevo amigo en la BD
     */
    function add_friend() {
        $originalID = $this->uri->segment(3);
        $friendID = $this->uri->segment(4);
        $success = $this->mainmodel->insertFriend($originalID, $friendID);
        if ($success) {
            echo "true";
        } else {
            echo "false";
        }
    }

    /**
     * Inserta un nuevo amigo en la BD
     */
    function delete_friend() {
        $originalID = $this->uri->segment(3);
        $friendID = $this->uri->segment(4);
        $this->mainmodel->deleteFriend($originalID, $friendID);
        echo "true";
    }

    /**
     * Sube una imagen nueva de avatar que sustituye la primera
     */
    function update_avatar() {
        $id = $this->input->post('competitor_id');
        $password = hash('sha256', $this->input->post('competitor_password'));
        $image = isset($_FILES['avatar']) && !empty($_FILES['avatar']['name']);
        $credentialsChecked = $this->mainmodel->checkCompetitorPassword($id, $password);
        if ($credentialsChecked && $image) {
            $result = $this->_upload_avatar($id);
            if ($result)
                echo "true";
            else
                echo $result;
        }else {
            echo "false";
        }
    }

    /**
     * Añade a un jugador a la lista de participantes de un evento ya creado
     */
    function add_event() {
        $competitor_event = array();
        $competitor_event['competitor_id'] = $this->uri->segment(3);
        $competitor_event['event_id'] = $this->uri->segment(4);

        $result = $this->mainmodel->insertCompetitor_event($competitor_event);
        echo $result > -1 ? "true" : "false";
    }

    /**
     * Abandona un evento
     */
    function leave_event() {
        $competitor_event = array();
        $competitor_event['competitor_id'] = $this->uri->segment(3);
        $competitor_event['event_id'] = $this->uri->segment(4);

        $this->mainmodel->removeCompetitor_event($competitor_event);
        echo "true";
    }

    /**
     * Comprueba si se ha unido a un evento
     */
    function check_event_participation() {
        $competitor_event = array();
        $competitor_event['competitor_id'] = $this->uri->segment(3);
        $competitor_event['event_id'] = $this->uri->segment(4);

        $result = $this->mainmodel->checkCompetitorEventStatus($competitor_event);
        echo $result === true ? "true" : "false";
    }

    /**
     * Obtiene los amigos de este competidor
     * @param type $competitor_id id del competidor
     */
    function friends($competitor_id) {
        $sql_result = $this->mainmodel->getCompetitorFriends($competitor_id);
        echo toJSON($sql_result);
    }

}

?>
