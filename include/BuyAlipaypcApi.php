<?php

/**
* @author jiahaiming
* 支付宝支付PC
*/
	class BuyAlipaypcApi {
		private $alipay_config = array();                                       //支付宝配置参数 数组
		private $notify_url = "";                                               //服务器异步通知页面路径 
		private $call_back_url = "";                                            //页面跳转同步通知页面路径
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
				'transport' => 'http'
			);
			$this->notify_url = WHB_DOMAIN."/buy/alipaypcnotify";
			$this->call_back_url = WHB_DOMAIN."/buy/alipaypccallback";
			$this->seller_email = $data['seller_email'];
			$this->out_trade_no = $data['out_trade_no'];
			$this->subject = $data['out_trade_no'];
			$this->total_fee = $data['total_fee'];
		}

		public function GetChkValue() {
			require_once("Alipaypc/alipay_submit.class.php");
			$parameter = array(
					"service" => "create_direct_pay_by_user",
					"partner" => trim($this->alipay_config['partner']),
					"payment_type"	=> 1,
					"notify_url"	=> $this->notify_url,
					"return_url"	=> $this->call_back_url,
					"seller_email"	=> $this->seller_email,
					"out_trade_no"	=> $this->out_trade_no,
					"subject"	=> $this->subject,
					"total_fee"	=> $this->total_fee,
					"body"	=> "短信套餐",
					"exter_invoke_ip"	=> get_client_ip(),
					"_input_charset"	=> trim(strtolower($this->alipay_config['input_charset']))
			);

			//建立请求
			$alipaySubmit = new AlipaySubmit($this->alipay_config);
			$html_text = $alipaySubmit->buildRequestForm($parameter,"get", "确认");
			return $html_text;
		}
		
		public function NotifyBuy(){
			require_once('Alipaypc/alipay_notify.class.php');
			$alipayNotify = new AlipayNotify($this->alipay_config);
			$verify_result = $alipayNotify->verifyNotify();
			return $verify_result;
		}
		
		public function CheckBuy() {
			require_once('Alipaypc/alipay_notify.class.php');
			$alipayNotify = new AlipayNotify($this->alipay_config);
			$verify_result = $alipayNotify->verifyReturn();
			return $verify_result;
		}

	}

?>
