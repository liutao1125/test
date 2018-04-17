<?php

/**
 * @author denghongmei
 * 支付宝支付
 */
class BuyAlipayApi {

    private $alipay_config = array();                                       //支付宝配置参数 数组
    private $format = "xml";                                                //返回格式  必填，不需要修改
    private $v = "2.0";                                                     //返回格式  必填，不需要修改
    private $req_id = "";                                                   //请求号 必填，须保证每次请求都是唯一
    private $notify_url = "";                                               //服务器异步通知页面路径 
    private $call_back_url = "";                                            //页面跳转同步通知页面路径
    private $merchant_url = "";                                             //操作中断返回地址
    private $seller_email = "";                                             //卖家支付宝帐户
    private $out_trade_no = "";                                             //商户订单号
    private $subject = "";                                                  //订单名称
    private $total_fee = "";                                                //付款金额

    public function __construct($data = '') {
        $this->alipay_config = array(
            'partner' => $data['partner'],
            'key' => $data['key'],
            'private_key_path' => '',
            'ali_public_key_path' => '',
            'sign_type' => 'MD5',
            'input_charset' => 'utf-8',
            'cacert' => getcwd() . '\\cacert.pem',
            'transport' => 'http'
        );
        $this->req_id = date('Ymdhis');
        $this->notify_url = WHB_DOMAIN."/buy/alipaynotify";
        $this->call_back_url = WHB_DOMAIN."/buy/alipaycallback";
        //$this->call_back_url = "";
        $this->merchant_url = "";
        $this->seller_email = $data['seller_email'];
        $this->out_trade_no = $data['out_trade_no'];
        $this->subject = $data['out_trade_no'];
        $this->total_fee = $data['total_fee'];
        //$this->total_fee = 0.01;
    }

    public function GetChkValue() {
        require_once("Alipay/alipay_submit.class.php");
        $req_data = '<direct_trade_create_req><notify_url>' . $this->notify_url . '</notify_url><call_back_url>' . $this->call_back_url . '</call_back_url><seller_account_name>' . $this->seller_email . '</seller_account_name><out_trade_no>' . $this->out_trade_no . '</out_trade_no><subject>' . $this->subject . '</subject><total_fee>' . $this->total_fee . '</total_fee><merchant_url>' . $this->merchant_url . '</merchant_url></direct_trade_create_req>';
        
        $para_token = array(
            "service" => "alipay.wap.trade.create.direct",
            "partner" => trim($this->alipay_config['partner']),
            "sec_id" => trim($this->alipay_config['sign_type']),
            "format" => $this->format,
            "v" => $this->v,
            "req_id" => $this->req_id,
            "req_data" => $req_data,
            "_input_charset" => trim(strtolower($this->alipay_config['input_charset']))
        );
        
//        s($para_token);
//        exit;

        $alipaySubmit = new AlipaySubmit($this->alipay_config);
        $html_text = $alipaySubmit->buildRequestHttp($para_token);

        //var_dump($html_text);
        //exit;
        $html_text = urldecode($html_text);
        
        
        //解析远程模拟提交后返回的信息
        $para_html_text = $alipaySubmit->parseResponse($html_text);
        
        //获取request_token
        $request_token = $para_html_text['request_token'];


        /*         * ************************根据授权码token调用交易接口alipay.wap.auth.authAndExecute************************* */

        //业务详细
        $req_data = '<auth_and_execute_req><request_token>' . $request_token . '</request_token></auth_and_execute_req>';
        //必填
        //构造要请求的参数数组，无需改动
        $parameter = array(
            "service" => "alipay.wap.auth.authAndExecute",
            "partner" => trim($this->alipay_config['partner']),
            "sec_id" => trim($this->alipay_config['sign_type']),
            "format" => $this->format,
            "v" => $this->v,
            "req_id" => $this->req_id,
            "req_data" => $req_data,
            "_input_charset" => trim(strtolower($this->alipay_config['input_charset']))
        );
        
        //var_dump($parameter);
        //exit;
        //建立请求
        $alipaySubmit = new AlipaySubmit($this->alipay_config);
        $html_text = $alipaySubmit->buildRequestForm($parameter, 'get', '提交');
        return $html_text;
    }

    public function CheckBuy() {
        require_once('Alipay/alipay_notify.class.php');
        $alipayNotify = new AlipayNotify($this->alipay_config);
        $verify_result = $alipayNotify->verifyReturn();
        return $verify_result;
    }
    
    public function NotifyBuy(){
        require_once('Alipay/alipay_notify.class.php');
        $alipayNotify = new AlipayNotify($this->alipay_config);
        $verify_result = $alipayNotify->verifyNotify();
        return $verify_result;
    }
    
   
}

?>
