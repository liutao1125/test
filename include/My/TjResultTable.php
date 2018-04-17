<?php


/**
 * 该类仅用于操作统计表
 *
 */
abstract class My_TjResultTable extends My_Table
{
	 public function getDbName() {
		return 'weixin_tj_result';
	}
}
