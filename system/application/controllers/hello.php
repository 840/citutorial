<?php


class Hello extends Controller
{


    function  index()
    {

        log_message('info', 'test');

        $this->load->view('hello_view');
    }

}