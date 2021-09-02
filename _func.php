<?php

/**
 * Debug/Stop function
 *
 * Breaks the execution and dumps any given arguments
 */
function s()
{
    if (func_num_args() > 0) {
        $args = func_get_args();
        call_user_func_array('var_dump', $args);
    }
    die;
}

function t($string, $lang = null)
{
    echo _::translate($string, $lang);
}
