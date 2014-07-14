<?php

function toJSON($sql_result) {
    $json= json_encode($sql_result);
    if($json === FALSE)
        return "false";
    return $json;
}

?>
