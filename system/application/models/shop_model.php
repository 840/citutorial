<?php

class Shop_model extends Model
{

    function Shop_model()
    {
        parent::Model();
        $this->load->database();


    }


    function get_category_list()
    {
        $this->db->order_by('id');
        $query = $this->db->get('category');
        return $query->result();
    }

    function get_product_list($cat_id, $limit, $offset)
    {
        $this->db->where('category_id', $cat_id);
        $this->db->order_by('id');
        $query = $this->db->get('product', $limit, $offset);
        return $query->result();
    }

    function get_category_name($id)
    {
        $this->db->select('name');
        $this->db->where('id', $id);
        $query = $this->db->get('category');
        $row = $query->row();
        return $row->name;
    }


    function get_product_count($cat_id)
    {
        $this->db->where('category_id', $cat_id);
        $query = $this->db->get('product');
        return $query->num_rows();
    }

    function get_product_item($id)
    {
        $this->db->where('id', $id);
        $query = $this->db->get('product');
        return $query->row();

    }

    function is_available_product_item($id)
    {
        $this->db->where('id', $id);
        $query = $this->db->get('product');
        if ($query->num_rows() == 1) {
            return TRUE;
        } else {
            return FALSE;
        }
    }


    function add_to_cart($id, $qty)
    {


        if ($qty <= 0) {
            $this->session->unset_userdata('item' . $id);
        } else if ($this->is_available_product_item($id)) {
            $this->session->set_userdata('item' . $id, $qty);
        }
        error_log('INFO', 'test');


    }

    function get_cart()
    {
        $items = array();
        $total = 0;
        $line = 0;

        foreach ($this->session->all_userdata() as $key => $val) {
            if (substr($key, 0, 4) == 'item') {
                $line++;

                $id = (int)substr($key, 4);
                $item = $this->get_product_item($id);

                $amount = $item->price * $val;

                $items[$line] = array('id' => $id,
                    'qty' => $val,
                    'name' => $item->name,
                    'price' => $item->price,
                    'amount' => $amount
                );

                $total = $total + $amount;

            }

        }

        $cart['items'] = $items;
        $cart['line'] = $line;
        $cart['total'] = $total;


        return $cart;

    }

    function get_cart_item_count()
    {
        $cart = $this->get_cart();
        return $cart['line'];
    }

    function get_product_by_search($q, $limit, $offset)
    {
        $keywords = explode(" ", $q);

        foreach ($keywords as $keyword) {
            $this->db->like('name', $keyword);

        }
        $this->db->order_by('id');
        $query = $this->db->get('product', $limit, $offset);

        return $query->result();

    }

    function set_customer_info($data)
    {
        foreach ($data as $key => $val) {
            $this->session->set_userdata($key, $val);
        }
    }

    function get_customer_info()
    {
        $data['name'] = $this->session->userdata('name');
        $data['zip'] = $this->session->userdata('zip');
        $data['addr'] = $this->session->userdata('addr');
        $data['tel'] = $this->session->userdata('tel');
        $data['email'] = $this->session->userdata('email');

        return $data;
    }

    function order()
    {
        $date = date("Y/m/d H:i:s");

        $cart = $this->get_cart();
        $total = number_format($cart['total']);


        $data = $this->get_customer_info();
        $name = $data['name'];

        $zip = $data['zip'];
        $addr = $data['addr'];
        $tel = $data['tel'];
        $email = $data['email'];

        $mail['from_name'] = 'CIショップ';
        $mail['from'] = $this->admin;
        $mail['to'] = $email;
        $mail['bcc'] = $this->admin;
        $mail['subject'] = '注文メール CIショップ';

        $mail['body'] = <<< END
CIショップにご注文いただきありがとうございます。ご注文内容は以下のとおりです。

***********************************************************
 お名前とご請求金額
***********************************************************
       お名前: $name
メールアドレス: $email

     注文合計： {$total}円

***********************************************************
 注文内容
***********************************************************
注文日時: $date


END;

        foreach ($cart['items'] as $line => $item) {
            $mail['body'] .= $item['name'] . "\n";
            $mail['body'] .= '数量: ' . $item['qty'] . "\n";
            $mail['body'] .= '単価: ' . number_format($item['price']) . "円\n";
            $mail['body'] .= '金額: ' . number_format($item['amount']) . "円\n\n";
        }
        $mail['body'] .= '合計金額: ' . $total . "円\n\n";

        $mail['body'] .= <<< END
***********************************************************
 お届け先
***********************************************************
$zip
$addr
$name
$tel

***********************************************************
今後ともCIショップをよろしくお願いいたします。
-------------------------------------------------------------
CIショップ

http://codeigniter.jp
-------------------------------------------------------------
END;

//        if ($this->sendmail($mail)) {
//            return TRUE;
//        } else {
//            return FALSE;
//        }
        return TRUE;
    }


    // メール送信処理
    function sendmail($mail)
    {
# Emailクラスをロードし、初期化します。
        $this->load->library('email');
        $config['protocol'] = 'mail';
        $config['charset'] = 'ISO-2022-JP';
        $config['wordwrap'] = FALSE;
        $this->email->initialize($config);

# メールの内容を変数に代入します。
        $from_name = $mail['from_name'];
        $from = $mail['from'];
        $to = $mail['to'];
        $bcc = $mail['bcc'];
        $subject = $mail['subject'];
        $body = $mail['body'];

# メールヘッダのMIMEエンコードおよび文字エンコードの変換をします。
        $from_name = mb_encode_mimeheader($from_name, $config['charset']);
        $subject = mb_encode_mimeheader($subject, $config['charset']);

# 本文の文字エンコードを変換します。
        $body = mb_convert_encoding($body, $config['charset'], $this->config->item('charset'));

# 差出人、あて先、Bcc、件名、本文を設定します。
        $this->email->from($from, $from_name);
        $this->email->to($to);
        $this->email->bcc($bcc);
        $this->email->subject($subject);
        $this->email->message($body);

# send()メソッドで実際にメールを送信します。
        if ($this->email->send()) {
            return TRUE;
        } else {
            return FALSE;
        }
    }


}

