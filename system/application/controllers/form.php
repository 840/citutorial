<?php

class Form extends Controller
{

    function Form()
    {
# 親クラスのコンストラクタを呼び出します。コントローラにコンストラクタを
# 記述する場合は、忘れずに記述してください。
        parent::Controller();

# 必要なヘルパーをロードします。
        $this->load->helper(array('form', 'url'));

# セッションクラスをロードすることで、セッションを開始します。
        $this->load->library('session');

# 出力クラスのset_header()メソッドでHTTPヘッダのContent-Typeヘッダを指定
# します。
        $this->output->set_header('Content-Type: text/html; charset=UTF-8');

# バリデーション(検証)クラスをロードし、バリデーションの設定をします。
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

    function confirm()
    {
        $this->ticket = $this->session->userdata('ticket');
        if (!$this->input->post('ticket')
            || $this->input->post('ticket') !== $this->ticket
        ) {
            echo 'クッキー不正';
            exit;
        }

        if ($this->validation->run() == TRUE) {
            $this->load->view('form_confirm');
        } else {
            $this->load->view('form');
        }
    }

    function send()
    {
        $this->ticket = $this->session->userdata('ticket');
        if (!$this->input->post('ticket') || $this->input->post('ticket' !== $this->ticket)) {
            echo 'クッキー不正';
            exit;
        } else {
            if ($this->validation->run() == TRUE) {
                $mail['from_name'] = $this->validation->name;
                $mail['from'] = $this->validation->email;
                $mail['to'] = 'nyarlatlohotep@gmail.com';
                $mail['subject'] = 'コンタクトフォーム';
                $mail['body'] = $this->validation->comment;

                if ($this-> _sendmail($mail)) {
                    $this->load->view('form_end');
                    $this->session->sess_destroy();

                } else {

                    echo 'メール送信エラー';
                }
            } else {
                $this->load->view('form');
            }
        }
    }


    function _sendmail($mail){

        $this->load->library('email');

        $config['protocol'] = 'mail';

        $config['charset'] = 'ISO-2022-JP';

        $config['wordwrap'] = FALSE;


        $this->email->initialize($config);



        $from_name = $mail['from_name'];
        $from = $mail['from'];
        $to = $mail['to'];
        $subject = $mail['subject'];
        $body = $mail['body'];

        $from_name = mb_encode_mimeheader($from_name , 'ISO-2022-JP' , 'UTF-8');
        $subject = mb_encode_mimeheader($subject, 'ISO-2022-JP' , 'UTF-8' );

        $body = mb_convert_encoding($body, 'ISO-2022-JP' , 'UTF-8');



        $this->email->from($from,$from_name);

        $this->email->to($to);
        $this->email->subject($subject);
        $this->email->message($body);

        log_message('info','test');



        if($this->email->send()){
            return TRUE;
        }
        else {
            return FALSE;
        }

    }

    function index()
    {
# ランダムなチケットを生成し、セッションに保存します。
        $this->ticket = md5(uniqid(mt_rand(), TRUE));
        $this->session->set_userdata('ticket', $this->ticket);

# 入力ページ(form)のビューをロードし表示します。
        $this->load->view('form');
    }

}