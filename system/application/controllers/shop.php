<?php


class Shop extends Controller
{

    var $limit;
    var $admin;

    function Shop()
    {
        parent::Controller();

        $this->load->library('session');
        $this->load->helper(array('form', 'url'));

        $this->load->model('Shop_model');

        $this->config->load('config_shop', TRUE);

        $this->limit = $this->config->item('per_page', 'config_shop');
        $this->admin = $this->config->item('admin_email', 'config_shop');

        $this->output->set_header('Content-Type: text/html; charset=UTF-8');


        $this->load->scaffolding('product');
    }

    function index()
    {
        $data['list'] = $this->Shop_model->get_category_list();
        $data['menu'] = $this->load->view('shop_menu', $data, TRUE);


        $cat_id = (int)$this->uri->segment(3, 1);
        $offset = (int)$this->uri->segment(4, 0);


        $data['list'] = $this->Shop_model->get_product_list($cat_id, $this->limit, $offset);

        $path = '/shop/index' . $cat_id;
        $total = $this->Shop_model->get_product_count($cat_id);

        $data['category'] = $this->Shop_model->get_category_name($cat_id);

        $data['pagination'] = $this->_generate_pagination($path, $total, 4);

        if ($total > 0) {
            $data['total_item'] = $total . '点の商品が登録されています.';
        } else {
            $data['total_item'] = 'このカテゴリーには商品が登録されていません。';
        }

        $data['main'] = $this->load->view('shop_list', $data, TRUE);

        $data['item_count'] = $this->Shop_model->get_cart_item_count();
        $data['header'] = $this->load->view('shop_header', $data, TRUE);

        $this->load->view('shop_tmpl_shop', $data);


    }

    function product()
    {
        $data['list'] = $this->Shop_model->get_category_list();
        $data['menu'] = $this->load->view('shop_menu', $data, TRUE);

        $prod_id = (int)$this->uri->segment(3, 1);
        $data['item'] = $this->Shop_model->get_product_item($prod_id);
        $data['main'] = $this->load->view('shop_product', $data, TRUE);
        $data['item_count'] = $this->Shop_model->get_cart_item_count();

        $data['header'] = $this->load->view('shop_header', $data, TRUE);

        $this->load->view('shop_tmpl_shop', $data);
    }


    // ページネーションの生成
    function _generate_pagination($path, $total, $uri_segment)
    {
# ページネーションクラスをロードします。
        $this->load->library('pagination');
# リンク先のURLを指定します。
        $config['base_url'] = $this->config->site_url($path);
# 総件数を指定します。
        $config['total_rows'] = $total;
# 1ページに表示する件数を指定します。
        $config['per_page'] = $this->limit;
# ページ番号情報がどのURIセグメントに含まれるか指定します。
        $config['uri_segment'] = $uri_segment;
# 生成するリンクのテンプレートを指定します。
        $config['first_link'] = '&laquo;最初';
        $config['last_link'] = '最後&raquo;';
        $config['full_tag_open'] = '<p>';
        $config['full_tag_close'] = '</p>';
# $configでページネーションを初期化します。
        $this->pagination->initialize($config);
# 生成したリンクの文字列を返します。
        return $this->pagination->create_links();
    }


    function add()
    {
        $prod_id = (int)$this->uri->segment(3, 0);

        $qty = (int)$this->input->post('qty');
        $this->Shop_model->add_to_cart($prod_id, $qty);

        $this->cart();
    }


    function  cart()
    {
        $data['list'] = $this->Shop_model->get_category_list();
        $data['menu'] = $this->load->view('shop_menu', $data, TRUE);

        $cart = $this->Shop_model->get_cart();

        $data['total'] = $cart['total'];
        $data['cart'] = $cart['items'];
        $data['item_count'] = $cart['line'];

        $data['main'] = $this->load->view('shop_cart', $data, TRUE);
        $data['header'] = $this->load->view('shop_header', $data, TRUE);

        $this->load->view('shop_tmpl_shop', $data);
    }

    function search()
    {
        $q = '';
        $q_disp = '';
        $q_uri = '';

        $data['list'] = $this->Shop_model->get_category_list();
        $data['menu'] = $this->load->view('shop_menu', $data, TRUE);

        if ($this->input->post('q')) {
            $q = $this->input->post('q');
        }

        $offset = (int)$this->uri->segment(4, 0);

        $q = trim(mb_convert_kana($q, "s"));

        if (strpos($q, '/') !== FALSE) {
            $q_disp = $q;
            $q_uri = str_replace('/', '／', $q);
        }

        if ($q == '-' || $q == '') {
            $q = '';
            $q_disp = '全商品';
            $q_uri = '-';


        } else {
            $q_disp = $q;
            $q_uri = $q;
        }

        $data['list'] = $this->Shop_model->get_product_by_search($q, $this->limit, $offset);

        $total = $this->Shop_model->get_count_by_search($q);

        $path = '/shop/search/' . rawurlencode($q_uri);
        $data['pagination'] = $this->_generate_pagination($path, $total, 4);

        $data['q'] = $q_disp;

        if ($total) {
            $data['total_item'] = $total . '点の商品がヒットしました';
        } else {
            $data['total_item'] = '"' . $q_disp . '"の検索に一致する商品はありませんでした。';
        }

        $data['main'] = $this->load->view('shop_search', $data, TRUE);
        $data['item_count'] = $this->Shop_model->get_cart_item_count();
        $data['header'] = $this->load->view('shop_header', $data, TRUE);

        $this->load->view('shop_tmpl_shop', $data);


    }

    function _set_validation()
    {
        $this->load->library('validation');
        $this->validation->set_error_delimiters('<div class = "error">', '</div>');

        $fields['name'] = '名前';
        $fields['zip'] = '郵便番号';
        $fields['addr'] = '住所';
        $fields['tel'] = '電話番号';
        $fields['email'] = 'メールアドレス';

        $this->validation->set_fields($fields);

        $rules['name'] = 'trim|required|max_length[64]';
        $rules['zip'] = 'trim|max_length[8]';
        $rules['addr'] = 'trim|required|max_length[128]';
        $rules['tel'] = 'trim|required|max_length[12]';
        $rules['email'] = 'trim|required|valid_email|max_length[64]';

        $this->validation->set_rules($rules);

    }

    function customer_info()
    {
        $this->_set_validation();

        $data = $this->Shop_model->get_customer_info();
        $this->validation->name = $data['name'];
        $this->validation->addr = $data['addr'];
        $this->validation->zip = $data['zip'];
        $this->validation->tel = $data['tel'];
        $this->validation->email = $data['email'];

        $data['action'] = 'お客様情報の入力';
        $data['main'] = $this->load->view('shop_customer_info', '', TRUE);

        $this->load->view('shop_tmpl_checkout', $data);
    }

    function confirm()
    {

        $this->_set_validation();

        if ($this->validation->run() == TRUE) {
            $data['name'] = $this->validation->name;
            $data['zip'] = $this->validation->zip;
            $data['addr'] = $this->validation->addr;
            $data['tel'] = $this->validation->tel;
            $data['email'] = $this->validation->email;
            $this->Shop_model->set_customer_info($data);

            $cart = $this->Shop_model->get_cart();

            $data['total'] = $cart['total'];
            $data['cart'] = $cart['items'];


            $this->ticket = md5(uniqid(mt_rand(), TRUE));
            $this->session->set_userdata('ticket', $this->ticket);

            $data['action'] = '注文内容の確認';
            $data['main'] = $this->load->view('shop_confirm', $data, TRUE);


        } else {
            $data['action'] = 'お客様情報の入力';
            $data['main'] = $this->load->view('shop_customer_info', '', TRUE);
        }

        $this->load->view('shop_tmpl_checkout', $data);

    }

    function order()
    {
        $this->ticket = $this->session->userdata('ticket');

        if (!$this->input->post('ticket') || $this->input->post('ticket') !== $this->ticket) {
            echo '不正な操作';
            exit;
        }


        if ($this->Shop_model->get_cart_item_count() == 0) {
            echo '買い物かごには何も入っていません.';

        } else if ($this->Shop_model->order()) {
            $data['action'] = '注文の完了';
            $data['main'] = $this->load->view('shop_thankyou', '', TRUE);

            $this->load->view('shop_tmpl_checkout', $data);

            $this->session->sess_destroy();
        } else {
            echo "システムエラー";
        }

    }

}