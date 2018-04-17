<?php
/*
 * @author maoyuhao
 * 点点客名片
 * 返回vcard字符串
 * @ arr  名片信息的二维数组
 */

class Vcard
{

	static public function get_vcard_data($arr)
	{
		$str = "BEGIN:VCARD\nVERSION:3.0\n";
		foreach ($arr as $key => $value) {
			if($value){
				switch ($key) {
					case 'name':
						$str.= "FN:";
						break;
					case 'position':
						$str.= "TITLE:";
						break;
					case 'tel':
						$str.= "TEL;CELL;VOICE:";
						break;
					case 'phone':
						$str.= "TEL;WORK;VOICE:";
						break;
					case 'fax':
						$str.= "TEL;WORK;FAX:";
						break;
					case 'url':
						$str.= "URL:";
						break;
					case 'email':
						$str.= "EMAIL;PREF;INTERNET:";
						break;
					case 'address':
						$str.= "ADR;WORK;POSTAL:";
						break;
					case 'remark':
						$str.= "NOTE:";
						break;
				}
				$str.= $value."\n";
			}
		}

		if($arr['company']){
			$str.= "ORG:".$arr['company'].";".$arr['department']."\n";
		}elseif(!$arr['company'] && $arr['department']){
			$str.= "ORG:".$arr['department']."\n";
		}

		$str.= "END:VCARD";
	}

}  
