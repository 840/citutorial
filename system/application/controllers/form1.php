<?php

class Form extends Controller
{
    function Form1()
    {
        parent::Controller();

        $this->load->helper(array('form', 'url'));

        $this->load->library('session');

        $this->output->set_header('Content-Type: text/html;; charset=UTF-8');

        $this->load->library('validation');

        $this->validation->set_error_delimiters('<div class="error">', '</div>');

        $fields['name'] = '名前';

        $fields['email'] = 'メールアドレス';

        $fields['comment'] = 'コメント';

        $this->validation->set_fields($fields);

        $rules['name'] = "trim|required|max_length[20]";
        $rules['email'] = "trim|required|valid_email";
        $rules['comment'] = "required|max_length[200]";

        $this->validation->set_rules($rules);

        //$this->output->enable_profiler(TRUE);


    }

    function index()
    {

        $this->ticket = md5(uniqid(mt_rand(), TRUE));
        $this->session->set_user_data('ticket', $this->ticket);



        $this->load->view('form');
    }

}