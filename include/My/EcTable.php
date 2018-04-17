<?php


/**
 * 该类仅用于操作电商库中的表
 *
 */
abstract class My_EcTable extends My_Table
{

	 public function getDbName() {

		return 'shop_'.CITY_EN;


	}

}
