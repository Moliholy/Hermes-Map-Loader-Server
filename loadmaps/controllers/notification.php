<?php

class Notification extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('mainmodel');
    }

    /**
     * Envia una notificación para añadirse a un evento
     * @param type $mainID
     * @param type $friendID
     * @param type $eventID
     */
    function send_event_invitation() {
        $mainID = $this->uri->segment(3);
        $friendID = $this->uri->segment(4);
        $eventID = $this->uri->segment(5);
        if ($mainID == $this->mainmodel->getEventOwner($eventID)) {
            $this->mainmodel->insertEventInvitation($friendID, $eventID);
            echo "true";
        }
        else
            echo "false";
    }

    /**
     * Recive las notificaciones pendientes
     * @param type $competitor_id
     */
    function receive_event_invitation($competitor_id) {
        $result = $this->mainmodel->getEventNotifications($competitor_id);
        echo toJSON($result);
    }

    /**
     * Marca una notificación de evento como vista
     */
    function set_event_notification_seen() {
        $competitor_id = $this->uri->segment(3);
        $event_id = $this->uri->segment(4);
        $result = $this->mainmodel->setEventNotificationSeen($competitor_id, $event_id);
        if (!empty($result)) {
            echo "true";
        }
        else
            echo "false";
    }

    /**
     * Envía una notificación a un amigo compartiendo una ruta
     */
    function send_route_invitation() {
        $mainID = $this->uri->segment(3);
        $friendID = $this->uri->segment(4);
        $route = $this->uri->segment(5);

        $result = $this->mainmodel->insertRouteInvitation($mainID, $friendID, $route);
        if ($result >= 0) {
            //tenemos que copiar el archivo
            $originalPath = ROUTES_FOLDER . $mainID . '/' . $route;
            $destinationPath = ROUTES_FOLDER . $friendID . '/'.$route;
            $destinationFolder = ROUTES_FOLDER . $friendID;
            if(!file_exists($destinationFolder)){
                mkdir($destinationFolder);
                chmod($destinationFolder, 0755);
            }
            copy($originalPath, $destinationPath);
            chmod($destinationPath, 0644);
            echo "true";
        }
        else
            echo "false";
    }

    /**
     * Recibe todas las invitaciones a las rutas
     * @param type $competitor_id
     */
    function receive_route_invitation($competitor_id) {
        $result = $this->mainmodel->getRouteNotifications($competitor_id);
        echo toJSON($result);
    }

    /**
     * Marca una notificación de ruta como vista
     */
    function set_route_notification_seen() {
        $owner_id = $this->uri->segment(3);
        $competitor_id = $this->uri->segment(4);
        $route = $this->uri->segment(5);
        $result = $this->mainmodel->setRouteNotificationSeen($owner_id, $competitor_id, $route);
        if (!empty($result)) {
            echo "true";
        }
        else
            echo "false";
    }

}

?>
