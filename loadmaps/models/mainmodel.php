<?php

class Mainmodel extends CI_Model {

    /**
     * Obtiene un elemento de la tabla competitor_event 
     * con fecha mayor que el día actual menos 60 días
     * @param type $competitor_id ID del competidor
     * @param type $event_id ID del evento
     * @return type resultado de la consulta SQL
     */
    function getCompetitorEvents($competitor_id) {
        $sql = $this->db->query("SELECT * FROM event 
            WHERE competitor_id=" . $competitor_id .
                " AND event_begin_date > DATE_SUB(CURDATE(),INTERVAL 60 DAY)" .
                " ORDER BY event_begin_date");
        $result = $sql->result();
        return $result;
    }

    /**
     * Obtiene todas las marcas de un competidor en un evento determinado
     * @param type $competitor_id id del competidor
     * @param type $event_id id del evento
     * @return type todas las marcas de un competidor para un evento
     */
    function getMarks($competitor_id, $event_id) {
        $sql = $this->db->query("SELECT mark_date, mark_latitude, mark_longitude
            FROM mark WHERE competitor_id=" . $competitor_id . " AND event_id=" . $event_id
                . " ORDER BY mark_date");
        $result = $sql->result();
        return $result;
    }

    /**
     * Obtiene todas las marcas de un evento
     * @param type $data
     */
    function getAllMarks($event_id) {
        $sql = $this->db->query("SELECT competitor_id, mark_date, mark_latitude, mark_longitude
            FROM mark WHERE event_id=" . $event_id
                . ' ORDER BY competitor_id, mark_date');
        $result = $sql->result();
        return $result;
    }

    /**
     * Obtiene los datos de un competidor por su ID
     * @param type $competitor_id id del competidor
     * @return type consulta SQL
     */
    function getCompetitorData($competitor_id) {
        $sql = $this->db->query("SELECT *
            FROM competitor WHERE competitor_id=" . $competitor_id);
        $result = $sql->result();
        return $result;
    }

    /**
     * Obtiene los datos de un competidor por su email
     * @param type $email email del competidor
     * @return type consulta SQL
     */
    function getCompetitorDataByEmail($email) {
        $sql = $this->db->query("SELECT *
            FROM competitor WHERE competitor_email LIKE '" . $email . "'");
        $result = $sql->result();
        return $result;
    }

    /**
     * Inserta una marca en la BD
     * @param type $mark marca tipo latitud/longitud/tiempo
     */
    function insertMark($mark) {
        $this->db->insert('mark', $mark);
        return $this->db->insert_id();
    }

    /**
     * Añade un amigo a la BD
     * @param type $id1 id original
     * @param type $id2 id amigo
     */
    function insertFriend($id1, $id2) {
        $sql = $this->db->query("INSERT INTO competitor_competitor
            VALUES( '" . $id1 . "', '" . $id2 . "')");
        return $sql;
    }

    /**
     * Borra un amigo de la BD
     * @param type $id1 id original
     * @param type $id2 id amigo
     */
    function deleteFriend($id1, $id2) {
        $sql = $this->db->query("DELETE FROM competitor_competitor
            WHERE competitor_id_1=" . $id1 . " AND competitor_id_2=" . $id2);
        return $sql;
    }

    /**
     * Inserta un competidor
     * @param type $competitor
     */
    function insertCompetitor($competitor) {
        $this->db->insert('competitor', $competitor);
        return $this->db->insert_id('competitor', $competitor);
    }

    /**
     * Obtiene al competidor que creó el evento
     * @param type $event_id
     * @return type
     */
    function getEventOwner($event_id) {
        $sql = $this->db->query("SELECT competitor_id
            FROM event WHERE event_id=" . $event_id);
        $result = $sql->result();
        return $result[0]->competitor_id;
    }

    /**
     * Comprueba si el par email/contraseña del competidor es válido
     * @param type $data
     * @return type
     */
    function checkCompetitorCredentials($data) {
        $sql = $this->db->query("SELECT competitor_id, competitor_name, 
             competitor_email, competitor_sex, competitor_birthdate
            FROM competitor WHERE competitor_email LIKE '" . $data['competitor_email']
                . "' AND competitor_password LIKE '" . $data['competitor_password'] . "'");
        $result = $sql->result();
        return $result;
    }

    /**
     * Comprueba que el par id-password del competidor es correcto
     * @param type $competitor_id
     * @param type $competitor_password
     * @return type
     */
    function checkCompetitorPassword($competitor_id, $competitor_password) {
        $sql = $this->db->query("SELECT *
            FROM competitor WHERE competitor_id=" . $competitor_id
                . " AND competitor_password LIKE '" . $competitor_password . "'");
        return $sql->num_rows() == 1;
    }

    /**
     * Borra un evento
     * @param type $event_id
     */
    function removeEvent($event_id) {
        $this->db->query("DELETE FROM event
            WHERE event_id=" . $event_id);
    }

    /**
     * Obtiene un evento identificado por su id único
     * @param type $event_id
     */
    function getEvent($event_id) {
        $sql = $this->db->query("SELECT event_id, event_name, event_begin_date, 
            event_place, envent_privacity, event_description
            FROM event WHERE event_id=" . $event_id);
        return $sql->result();
    }

    /**
     * Inserta un evento
     * @param type $event
     */
    function insertEvent($event) {
        $this->db->insert('event', $event);
        return $this->db->insert_id();
    }

    /**
     * Consulta la BD para obtener los eventos interesantes para un usuario
     * @param type $competitor_id
     */
    function findEvents($competitor_id) {
        $sql = $this->db->query("SELECT * FROM event
            WHERE (event_privacity=1  AND
            competitor_id IN (SELECT competitor_id_2 FROM competitor_competitor 
            WHERE competitor_id_1=" . $competitor_id . "))" .
                " OR event_id IN (SELECT event_id FROM event_invitation 
                    WHERE competitor_id=" . $competitor_id . ")" .
                " OR (event_privacity=2 AND competitor_id <> " . $competitor_id .
                ") ORDER BY event_begin_date LIMIT 30");
        return $sql->result();
    }

    /**
     * Edita un evento
     * @param type $event nuevos parámetros del evento
     */
    function updateEvent($event) {
        $this->db->where('event_id', $event['event_id']);
        $this->db->update('event', $event);
    }

    /**
     * Obtiene todos los amigos de un competidor
     * @param type $competitor_id
     * @return type
     */
    function getCompetitorFriends($competitor_id) {
        $sql = $this->db->query("SELECT competitor_id, competitor_name, 
             competitor_email, competitor_sex, competitor_birthdate 
            FROM competitor WHERE competitor_id IN 
            (SELECT competitor_id_2 FROM competitor_competitor
             WHERE competitor_id_1=" . $competitor_id . ")");
        $result = $sql->result();
        return $result;
    }

    /**
     * Comprueba si en la BD existe el par competidor-evento
     * @param type $competitor_id
     * @param type $event_id
     * @return type true si existe, false en caso contrario
     */
    function checkCompetitorEventStatus($competitor_event) {
        $sql = $this->db->query("SELECT * FROM competitor_event
            WHERE competitor_id=" . $competitor_event['competitor_id'] .
                " AND event_id=" . $competitor_event['event_id']);
        return $sql->num_rows() == 1 ? true : false;
    }

    /**
     * Inserta un elemento en la table competitor_event
     * @param type $competitor_event datos del competidor
     * @return type id del elemento insertado
     */
    function insertCompetitor_event($competitor_event) {
        $this->db->insert('competitor_event', $competitor_event);
        return $this->db->insert_id();
    }

    /**
     * Borra un elemento de la tabla competitor_event
     * @param type $competitor_event datos del competidor
     */
    function removeCompetitor_event($competitor_event) {
        $this->db->query("DELETE FROM competitor_event
            WHERE event_id=" . $competitor_event['event_id'] .
                " AND competitor_id=" . $competitor_event['competitor_id']);
    }

    /**
     * Comprueba si hay entradas en la tabla marks para ese evento
     * @param type $event_id id del evento en cuestión
     * @return type true si hay entradas, false en caso contrario
     */
    function checkEventAvaliability($event_id) {
        $sql = $this->db->query("SELECT * FROM mark 
            WHERE event_id=" . $event_id . " ORDER BY mark_date");
        return $sql->num_rows() > 0;
    }

    /**
     * Obtiene los datos de los competidores que participan en un evento y que tienen al menos una marca
     * @param type $event_id id del evento
     */
    function getCompetitorsInEvent($event_id) {
        $sql = $this->db->query("SELECT competitor_id, competitor_name, competitor_email 
            FROM competitor WHERE competitor_id IN 
            (SELECT DISTINCT competitor_id FROM mark WHERE event_id=" . $event_id . ") 
                ORDER BY 1");
        return $sql->result();
    }

    /**
     * Inserta una invitación a un evento
     * @param type $mainID
     * @param type $friendID
     * @param type $eventID
     */
    function insertEventInvitation($friendID, $eventID) {
        $data = array(
            'event_id' => $eventID,
            'competitor_id' => $friendID
        );
        $this->db->insert('event_invitation', $data);
        return $this->db->insert_id();
    }

    /**
     * Obtiene las notificaciones pendientes para una ID de usuario
     * @param type $competitor_id ID del competidor
     */
    function getEventNotifications($competitor_id) {
        $sql = $this->db->query("SELECT e.*
            FROM event e, event_invitation i
            WHERE " . $competitor_id . "=i.competitor_id AND
                e.event_id=i.event_id AND i.notification_seen=0");
        return $sql->result();
    }

    /**
     * Marca una invitación a un evento como vista
     * @param type $competitor_id
     * @param type $event_id
     */
    function setEventNotificationSeen($competitor_id, $event_id) {
        $data = array(
            'competitor_id' => $competitor_id,
            'event_id' => $event_id,
            'notification_seen' => 1
        );
        $this->db->where('event_id', $event_id);
        $this->db->where('competitor_id', $competitor_id);
        $this->db->update('event_invitation', $data);
        return true;
    }

    /**
     * Inserta una invitación a una ruta
     * @param type $competitor_id
     * @param type $friend_id
     * @param type $route
     */
    function insertRouteInvitation($owner_id, $friend_id, $route) {
        $data = array(
            'owner_id' => $owner_id,
            'competitor_id' => $friend_id,
            'route' => $route
        );
        $this->db->insert('route_invitation', $data);
        return $this->db->insert_id();
    }

    /**
     * Obtiene las notificaciones para las rutas
     * @param type $competitor_id ID del competidor que hace la consulta
     */
    function getRouteNotifications($competitor_id) {
        $sql = $this->db->query("SELECT c.competitor_name, r.owner_id, r.route
            FROM competitor c, route_invitation r
            WHERE r.competitor_id=" . $competitor_id .
                " AND c.competitor_id=r.owner_id AND r.notification_seen=0");
        return $sql->result();
    }

    /**
     * Marca una notificación de ruta como vista
     * @param type $owner_id id del creador de la ruta
     * @param type $competitor_id id del receptor de la ruta
     * @param type $route nombre del archivo de la ruta en el servidor
     */
    function setRouteNotificationSeen($owner_id, $competitor_id, $route) {
        $data = array(
            'competitor_id' => $competitor_id,
            'owner_id' => $owner_id,
            'route' => $route,
            'notification_seen' => 1
        );
        $this->db->where('owner_id', $owner_id);
        $this->db->where('competitor_id', $competitor_id);
        $this->db->where('route', $route);
        $this->db->update('route_invitation', $data);
        return true;
    }

}

?>
