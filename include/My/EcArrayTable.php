<?php


/**
 * @filename My_EcArrayTable
 * @author daizhongwei<daizhongwei@dodoca.com>
 * @date   2015/4/20
 * @description 封装sql数组查询|安全过滤
 */
abstract class My_EcArrayTable extends My_Table
{
    protected $comparison = array('eq'=>'=','neq'=>'<>','gt'=>'>','egt'=>'>=','lt'=>'<','elt'=>'<=','notlike'=>'NOT LIKE','like'=>'LIKE');
    public $count; //总数


	 public function getDbName()
     {
         return 'weixin_sxzx_dz';
     }

    /**
     * 执行sql查询
     * @param array   $where 		查询条件[例`name`='$name']
     * @param array   $data 		需要查询的字段值[例`name`,`gender`,`birthday`]
     * @param string  $limit 		返回结果范围[例：10或10,10 默认为空]
     * @param string  $order 		排序方式	[默认按数据库默认方式排序]
     * @param string  $group 		分组方式	[默认为空]
     * @return array		        查询结果集数组
     */
    public function select($where=array(),$data=array(),$limit='',$order='',$group='')
    {
        $where = $this->parseWhere($where);
        $where && $where = ' WHERE ' . $where . ' ';
        $order = $this->parseOrder($order);
        $order && $order = ' ORDER BY ' . $order;
        $group = $group == '' ? '' : ' GROUP BY '.$group;
        if(empty($data))
        {
            $data = '*';
        }
        if (is_array($data))
        {
            array_walk($data, array($this, 'addSpecialChar'));
            $data = implode(',', $data);
        }
        $limit && $limit = ' limit ' . $limit;
        $sql = 'SELECT '.$data.' FROM `'. $this->_name . '`' . $where.$group.$order.$limit;
//         echo $sql;
        $dataList = $this->fetchAll($sql);
        return $dataList;
    }

    /**
     * 获取单条记录查询
     * @param  array $where           查询条件
     * @param  array $data            需要查询的字段值[例`name`,`gender`,`birthday`]
     * @return array /null            数据查询结果集,如果不存在，则返回空
     * @throws Exception
     * @throws Zend_Db_Exception
     */
    public function find($where,$data=array())
    {
        if(!is_array($where))
        {
            if(!$this->_primarykey)
            {
                throw new Zend_Db_Exception('没有定义主键');
            }
            $where = array($this->_primarykey => $where);
        }
        $where = $this->parseWhere($where);
        if(empty($data))
        {
            $data = '*';
        }
        if (is_array($data))
        {
            array_walk($data, array($this, 'addSpecialChar'));
            $data = implode(',', $data);
        }
        $where && $where = ' WHERE ' . $where . ' ';
        return $this->scalar($data,$where);
    }
    /**
     * 分页查询多条数据
     * @param $where
     * @param $data     //查询字段
     * @param $order    //排序
     * @param $page
     * @param $pageSize
     * @param $group
     * @return array
     */
    public function listPage($where = array(), $data = array(), $order = '', $page = 1, $pageSize = 15, $group = '')
    {
        $this->count = is_string($where)?1:$this->count($where);
        $page = max(intval($page), 1);
        $offset = $pageSize * ($page - 1);
        if($this->count > 0)
        {
            return $this->select($where, $data, "$offset, $pageSize", $order, $group);
        }
        else
        {
            return array();
        }
    }
    /**
     * 更新记录(支持数组)
     * @param array $data 数据
     * @param array $where 查询条件
     * @return bool
     */
    public function updateData($data,$where)
    {
        $where = $this->parseWhere($where);
        return $this->update($data, $where);
    }
    /**
     * 计算记录数
     * @param string|array $where 查询条件
     * @param string|array $field 字段
     * @return int
     */
    public function count($where = array(),$field='') {
        if(!$field)
        {
            if($this->_primarykey)
            {
                $field = $this->_primarykey;
            }else{
                $field = '*';
            }
        }
        $field = " COUNT($field) AS TNUM";
        $r = $this->find($where, $field);
        return $r;
    }
    /**
     * 对字段两边加反引号，以保证数据库安全
     * @param $value 数组值
     * @return string|int
     */
    public function addSpecialChar(&$value) {
        if('*' == $value || false !== strpos($value, '(') || false !== strpos($value, '.') || false !== strpos ( $value, '`')) {
            //不处理包含* 或者 使用了sql方法。
        } else {
            $value = '`'.trim($value).'`';
        }
        return $value;
    }

    /**
     * 对字段值两边加引号，以保证数据库安全
     * @param $value 数组值
     * @param string|数组key $key 数组key
     * @param int $quotation
     * @return string
     */
    public function escapeString(&$value, $key='', $quotation = 1) {
        $value = @mysql_escape_string($value);
        if ($quotation) {
            $q = '\'';
        } else {
            $q = '';
        }
        $value = $q.$value.$q;
        return $value;
    }
    /**
    +----------------------------------------------------------
     * 字段名分析
    +----------------------------------------------------------
     * @access protected
    +----------------------------------------------------------
     * @param string $key
    +----------------------------------------------------------
     * @return string
    +----------------------------------------------------------
     */
    protected function parseKey(&$key) {
        return '`'.$key.'`';
    }
    /**
    +----------------------------------------------------------
     * value分析
    +----------------------------------------------------------
     * @access protected
    +----------------------------------------------------------
     * @param mixed $value
    +----------------------------------------------------------
     * @return string
    +----------------------------------------------------------
     */
    public function parseValue($value) {
        if(is_string($value)) {
            $value = $this->escapeString($value);
        }elseif(isset($value[0]) && is_string($value[0]) && strtolower($value[0]) == 'exp'){
            $value   =  $this->escapeString($value[1]);
        }elseif(is_array($value)) {
            $value   =  array_map(array($this, 'parseValue'),$value);
        }elseif(is_null($value)){
            $value   =  'null';
        }
        return $value;
    }

    /**
     * +----------------------------------------------------------
     * where分析
     * +----------------------------------------------------------
     * @access protected
     * +----------------------------------------------------------
     * @param mixed $where
     * +----------------------------------------------------------
     * @return string
     * +----------------------------------------------------------
     * @throws Exception
     */
    public function parseWhere($where) {
        $whereStr = '';
        if(is_string($where)) {
            // 直接使用字符串条件
            $whereStr = $where;
        }else{ // 使用数组或者对象条件表达式
            if(isset($where['_logic'])) {
                // 定义逻辑运算规则 例如 OR XOR AND NOT
                $operate    =   ' '.strtoupper($where['_logic']).' ';
                unset($where['_logic']);
            }else{
                // 默认进行 AND 运算
                $operate    =   ' AND ';
            }
            foreach ($where as $key=>$val){
                $whereStr .= '( ';
                if(0===strpos($key,'_')) {
                    // 解析特殊条件表达式
                    $whereStr   .= $this->parseAdsWhere($key,$val);
                }else{
                    // 查询字段的安全过滤
                    if(!preg_match('/^[A-Z_\|\&\-.a-z0-9\(\)\,]+$/',trim($key))){
                        throw new Exception('_EXPRESS_ERROR_:'.$key);
                    }
                    // 多条件支持
                    $multi = is_array($val) &&  isset($val['_multi']);
                    $key = trim($key);
                    if(strpos($key,'|')) { // 支持 name|title|nickname 方式定义查询字段
                        $array   =  explode('|',$key);
                        $str   = array();
                        foreach ($array as $m=>$k){
                            $v =  $multi?$val[$m]:$val;
                            $str[]   = '('.$this->parseWhereItem($this->parseKey($k),$v).')';
                        }
                        $whereStr .= implode(' OR ',$str);
                    }elseif(strpos($key,'&')){
                        $array   =  explode('&',$key);
                        $str   = array();
                        foreach ($array as $m=>$k){
                            $v =  $multi?$val[$m]:$val;
                            $str[]   = '('.$this->parseWhereItem($this->parseKey($k),$v).')';
                        }
                        $whereStr .= implode(' AND ',$str);
                    }else{
                        $whereStr   .= $this->parseWhereItem($this->parseKey($key),$val);
                    }
                }
                $whereStr .= ' )'.$operate;
            }
            $whereStr = substr($whereStr,0,-strlen($operate));
        }
        return $whereStr;
    }

    // where子单元分析
    public function parseWhereItem($key,$val) {
        $whereStr = '';
        if(is_array($val)) {
            if(is_string($val[0])) {
                if(preg_match('/^(EQ|NEQ|GT|EGT|LT|ELT|NOTLIKE|LIKE)$/i',$val[0])) { // 比较运算
                    $whereStr .= $key.' '.$this->comparison[strtolower($val[0])].' '.$this->parseValue($val[1]);
                }elseif('exp'==strtolower($val[0])){ // 使用表达式
                    $whereStr .= ' ('.$key.' '.$val[1].') ';
                }elseif(preg_match('/IN/i',$val[0])){ // IN 运算
                    if(isset($val[2]) && 'exp'==$val[2]) {
                        $whereStr .= $key.' '.strtoupper($val[0]).' '.$val[1];
                    }else{
                        if(is_string($val[1])) {
                            $val[1] =  explode(',',$val[1]);
                        }
                        $zone   =   implode(',',$this->parseValue($val[1]));
                        $whereStr .= $key.' '.strtoupper($val[0]).' ('.$zone.')';
                    }
                }elseif(preg_match('/BETWEEN/i',$val[0])){ // BETWEEN运算
                    $data = is_string($val[1])? explode(',',$val[1]):$val[1];
                    $whereStr .=  ' ('.$key.' '.strtoupper($val[0]).' '.$this->parseValue($data[0]).' AND '.$this->parseValue($data[1]).' )';
                }else{
                    throw new Exception('_EXPRESS_ERROR_:'.$val[0]);
                }
            }else {
                $count = count($val);
                if(in_array(strtoupper(trim($val[$count-1])),array('AND','OR','XOR'))) {
                    $rule = strtoupper(trim($val[$count-1]));
                    $count   =  $count -1;
                }else{
                    $rule = 'AND';
                }
                for($i=0;$i<$count;$i++) {
                    $data = is_array($val[$i])?$val[$i][1]:$val[$i];
                    if('exp'==strtolower($val[$i][0])) {
                        $whereStr .= '('.$key.' '.$data.') '.$rule.' ';
                    }else{
                        $op = is_array($val[$i])?$this->comparison[strtolower($val[$i][0])]:'=';
                        $whereStr .= '('.$key.' '.$op.' '.$this->parseValue($data).') '.$rule.' ';
                    }
                }
                $whereStr = substr($whereStr,0,-4);
            }
        }else {
            $whereStr .= $key.' = '.$this->parseValue($val);
        }
        return $whereStr;
    }

    /**
    +----------------------------------------------------------
     * 特殊条件分析
    +----------------------------------------------------------
     * @access protected
    +----------------------------------------------------------
     * @param string $key
     * @param mixed $val
    +----------------------------------------------------------
     * @return string
    +----------------------------------------------------------
     */
    public function parseAdsWhere($key,$val) {
        $whereStr   = '';
        switch($key) {
            case '_string':
                // 字符串模式查询条件
                $whereStr = $val;
                break;
            case '_complex':
                // 复合查询条件
                $whereStr   = $this->parseWhere($val);
                break;
            case '_query':
                // 字符串模式查询条件
                parse_str($val,$where);
                if(isset($where['_logic'])) {
                    $op   =  ' '.strtoupper($where['_logic']).' ';
                    unset($where['_logic']);
                }else{
                    $op   =  ' AND ';
                }
                $array   =  array();
                foreach ($where as $field=>$data)
                    $array[] = $this->parseKey($field).' = '.$this->parseValue($data);
                $whereStr   = implode($op,$array);
                break;
        }
        return $whereStr;
    }

    /**
     * order排序数组处理
     * @param $order
     * @return string
     */
    public function parseOrder($order) {
        if(is_array($order)) {
            $array   =  array();
            foreach ($order as $key=>$val){
                if(is_numeric($key)) {
                    $array[] =  $val;
                }else{
                    $array[] =  '`' . $key . '` '.$val;
                }
            }
            $order   =  implode(',',$array);
        }
        return !empty($order)?  $order:'';
    }
    
    
    /**
     * insertAll 批量插入操作
     * @param $insert_data
     * @return boolean
     */
    public function insertAll($insert_data) {
        if (empty($insert_data) || !is_array($insert_data)) {
            return false;
        }
        
        $data_str = '';
        $keys_data = false;
        $jude_num = $this->getArrayLevel($insert_data); //判断数组维度
        if ($jude_num == 2) {
            foreach ((array)$insert_data as $row){
                if (!$keys_data) {
                    $keys_data = array_keys($row);
                }
                $data_str .=  ' (';
                foreach ($row as $val){
                    $data_str .=  "'" . mysql_escape_string($val) . "',";
//                     $data_str .=  is_numeric($val)? mysql_escape_string($val).",":"'" . mysql_escape_string($val) . "',";
                }
                $data_str = $this->removeCommas($data_str);// 去掉逗号;
                $data_str .=  ' ),';
            }
        } else if ($jude_num == 1) {
            if (!$keys_data) {
                $keys_data = array_keys($insert_data);
            }
            $data_str .=  ' (';
            foreach ($insert_data as $val){
                $data_str .=  is_numeric($val)? mysql_escape_string($val) : "'" . mysql_escape_string($val) . "',";
            }
            $data_str = $this->removeCommas($data_str);// 去掉逗号;
            $data_str .=  ' ),';
        }
        
        $end_str = substr($data_str, -1);
        if ($end_str === ',') {
            $data_str = substr($data_str, 0, -1);  // 去掉逗号;
            $data_str .= ";";  // 添加;
        }
        
        $filed_str = '';
        foreach ((array)$keys_data as $row){
            $filed_str .=  ' `' . mysql_escape_string($row) . '`,';
        }
        $filed_str = $this->removeCommas($filed_str);// 去掉逗号;
        
        $sql = "INSERT INTO `". $this->_name ."` ($filed_str ) VALUES $data_str";
        ini_set('max_execution_time', '0'); //防止数据过大程序未执行
        return $this->execute($sql);
    }
    
    
    /**
     * 去掉字符串最后的逗号
     * @param $order
     * @return string
     */
    function removeCommas($string){
        $end_str = substr($string, -1);
        if ($end_str === ',') {
            $string = substr($string, 0, -1);  // 去掉逗号;
        }
        return $string;
    }
    
    
    /**
     * 判断是一维数组还是二维数组
     * @param $order
     * @return num
     */
    function getArrayLevel($array){
        if (! is_array($array)) {
            return false;
        }
        
        $s = 1;
        foreach($array as $value){
            if (is_array($value)) {
                $s=2;
                return $s;
                break;
            }
        }
        return $s;
    }
    
    
    /**
     * 获取数据库中组大的id
     * @param $order
     * @return num
     */
    function getMaxId($id = 'id'){
        $sql = "SELECT `$id` FROM  ". $this->_name ." WHERE  $id=(SELECT MAX(`$id`) from ". $this->_name.");";
       return $this->fetchAll($sql);
    }

}
