<?php

function base64url_encode($text){
        $base64 = base64_encode($text);
        $base64 = trim($base64, "=");
        $base64url = strtr($base64, '+/', '-_');
        return $base64url;
}

?>
