<?php

if(! function_exists('render') ) {
    function render($response) {
        if( is_string($response) ) {
            $response = $response;
        } else {
            $response = 'Something went wrong!';
        }
    
        echo $response;
        return $response;
    }
}

if(! function_exists('getIfExist') ) {
    function getIfExist($name, $method = null) {
    
        if( $method === null ) {
            $method = $_GET;
        }
    
        if( isset($method[$name]) ) {
            return $method[$name];
        }
    
        return false;
    }
}

if(! function_exists('___') ) {
    function ___(string $string) {
        return htmlspecialchars($string);
    }
}

?>