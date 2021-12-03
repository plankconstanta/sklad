<?php

function get_rediska_instance () {
    $options = array(
        'servers' => array(
            'server1' => array('host' => REDIS_SERVER_CONF, 'port' => REDIS_SERVER_PORT)
        )
    );
    $r = new Rediska($options);
    return $r;
}

?>
