<?php

    if(!defined('BASEPATH')) exit('No direct script access allowed');


/**
 * @param int $size
 * @return string
 */

function hr($size = 2){
    if($size == 2){
        return '<hr />';


    }

    else
    {
        return '<hr size=" ' . $size .' " /> ';
    }
}