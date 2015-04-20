<?php


class Sample16 extends Controller {
    function index(){

        $this->load->helper('sample');
        echo '1';
        echo hr(5);
        echo '2';
    }
}