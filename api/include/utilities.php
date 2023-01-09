<?php

function array_keys_exist($array,...$keys)
{
    foreach ($keys as $key) if (!array_key_exists($key,$array)) return false;
    return true;
}

function array_anykey_exists($array,...$keys)
{
    foreach ($keys as $key) if (array_key_exists($key,$array)) return true;
    return false;
}

function is_any_null(...$vars)
{
    foreach ($vars as $var) if (is_null($var)) return true;
    return false;
}

function is_any_not_null(...$vars)
{
    foreach ($vars as $var) if (!is_null($var)) return true;
    return false;
}

?>