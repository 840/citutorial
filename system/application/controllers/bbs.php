<?php

class Bbs extends Controller
{

    var $limit = 5;

    function Bbs()
    {
        parent::Controller();

        $this->load->helper(array('form', 'url'));
        $this->load->database();

        $this->load->library('user_agent');

        if (count($_POST > 1 && $this->agent->is_mobile())) {
            $_POST = $this->_convert_encoding($_POST);
        }
    }

    function _convert_encoding($array)
    {
        if (is_array($array)) {
            return array_map(array($this, '_convert_encoding'), $array);

        } else {
            return mb_convert_encoding($array, 'UTF-8', 'SJIS-win');
        }
    }

    function index($offset = '')
    {
        $offset = (int)$offset;

        $this->db->order_by('id', 'desc');

        $data['query'] = $this->db->get('bbs', $this->limit, $offset);

        $this->load->library('pagination');

        $config['base_url'] = $this->config->site_url('/bbs/index');

        $config['total_rows'] = $this->db->count_all('bbs');
        $config['per_page'] = $this->limit;
        $config['first_link'] = '&laquo;最初';
        $config['last_link'] = '最後&raquo;';

        if ($this->agent->is_mobile()) {
            $config['full_tag_open'] = '<tr><td bgcolor = "#EEEEEE">';
            $config['full_tag_close'] = '</td></tr>';

        } else {
            $config['full_tag_open'] = '<p class = "pagination" > ';
            $config['full_tag_close'] = '</p>';
        }

        $this->pagination->initialize($config);
        $data['pagination'] = $this->pagination->create_links();

        $this->_load_view('bbs_show', $data);
    }

    function post(){

        $this->_set_validation();
        $this->_show_post_page();
    }

    function _set_validation(){
        $this->load->library('validation');


        if($this->agent->is_mobile()){
            $this->validation->set_error_delimiters('<div>','</div>');
        }
        else{
            $this->validation->set_error_delimiters('<div class="error">','</div>');
        }


        $fields['name'] = '名前';
        $fields['email'] = 'メールアドレス';
        $fields['subject'] = '件名';
        $fields['body'] = '内容';
        $fields['password'] = '削除パスワード';
        $fields['captcha'] = '画像認証コード';
        $fields['key'] = 'key';
        $this->validation->set_fields($fields);


        $rules['name'] = 'trim|required|max_length[16]';
        $rules['email'] = 'trim|valid_email|max_length[64]';
        $rules['subject'] = 'trim|required|max_length[32]';
        $rules['body'] = 'trim|required|max_length[200]';
        $rules['password'] = 'max_length[32]';
        $rules['captcha'] = 'trim|required|alpha_numeric|callback_captcha_check';
        $rules['key'] = 'numeric';

        $this->validation->set_rules($rules);




    }

    function _show_post_page(){

        $this->load->helper('string');

        $this->load->plugin('captcha');

        $vals = array(
            'word' => random_string('numeric',4),
            'img_path' => './captcha/',
            'img_url' => base_url() . 'captcha/',
        );

        $cap = create_captcha($vals);
        $data = array(
            'captcha_id' => '',
            'captcha_time' => $cap['time'],
            'word' => $cap['word'],
        );

        $this->db->insert('captcha' , $data);
        $key = $this->db->insert_id();

        $data['image'] = $cap['image'];
        $data['key'] = $key;

        $data['name'] = $this->validation->name;
        $data['email'] = $this->validation->email;
        $data['subject'] = $this->validation->subject;
        $data['body'] = $this->validation->body;
        $data['password'] = $this->validation->password;

        $this->_load_view('bbs_post',$data);

    }


    function captcha_check($str){
        $expiration = time()-7200;
        $this->db->delete('captcha' , array('captcha_time <' =>'$expiration'));

        $this->db->select("COUNT(*) AS count");
        $this->db->where('word' , $str);
        $this->db->where('captcha_id' , $this->input->post('key'));
        $this->db->where('captcha_time >' ,$expiration);
        $query = $this->db->get('captcha');

        $row=$query->row();

        if($row->count == 0 ){
            $this->validation->set_message('captcha_check' , '画像認証コードが一致しません');
            return FALSE;
        }
        else {
            return TRUE;
        }
    }

    function confirm(){
        $this->_set_validation();

        if($this->validation->run() ==FALSE){
            $this->_show_post_page();
        }
        else {
            $data['name'] = $this->validation->name;
            $data['email'] = $this->validation->email;
            $data['subject'] = $this->validation->subject;
            $data['body'] = $this->validation->body;
            $data['password'] = $this->validation->password;
            $data['key'] = $this->validation->key;
            $data['captcha'] = $this->validation->captcha;

            var_dump($data['key']);
            $this->_load_view('bbs_confirm', $data);


        }

    }

    function insert(){
        $this->_set_validation();

        if($this->validation->run() == FALSE){
            $this->_show_post_page();
        }
        else{
            $data['name'] = $this->validation->name;
            $data['email'] = $this->validation->email;
            $data['subject'] = $this->validation->subject;
            $data['body'] = $this->validation->body;
            $data['password'] = $this->validation->password;
            $data['ip_address'] = $this->input->server('REMOTE_ADDR');
            $this->db->insert('bbs',$data);

            redirect('/bbs');
        }
    }


    function delete($id = ''){
        $id = (int)$id;

        $password = $this->input->post('password');
        $delete = (int)$this->input->post('delete');
        if($password === ''){
            $this->_load_view('bbs_delete_error');
        }
        else{
            $this->db->where('id', $id);
            $this->db->where('password' , $password);
            $query= $this->db->get('bbs');

            if($query->num_rows() == 1){

                if($delete === 1) {
                    $this->db->where('id', $id);
                    $this->db->delete('bbs');
                    $this->_load_view('bbs_delete_finished');


                }
                else{
                    $row = $query -> row();

                    $data['id'] = $row->id;
                    $data['name'] = $row->name;
                    $data['email'] = $row->email;
                    $data['subject'] = $row->subject;
                    $data['datetime'] = $row->datetime;
                    $data['body'] = $row->body;
                    $data['password'] = $row->password;
                    $this->_load_view('bbs_delete_confirm' , $data);
                }
            }
            else{
                $this->_load_view('bbs_delete_error');
            }

        }
    }

    function _load_view($file, $data = '')
    {
        if ($this->agent->is_mobile()) {
            $this->load->view($file . '_mobile', $data);

        } else {
            $this->load->view($file, $data);
        }
    }

    function _output($output){
        if($this->agent->is_mobile()){
            header('Content-Type: text/html; charset=Shift_JIS');
            echo mb_convert_encoding($output, 'SJIS-win' , 'UTF-8');
        }
        else{
            header('Content-Type: text/html; charset=UTF-8');
            echo $output;
        }
    }

}
