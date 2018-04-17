<?php
/**
 * 
 * CSV文件操作类
 * @author zhangle@dodoca.com
 *
 */
class CsvApi
{
    
    /**
     *
     *   根据文件路径读取csv文件内容
     *
     *   @method getDataFromCsv
     *   @param $targetFile  文件地址
     *   @date   2016年3月16日
     *   @author rongxiang<rongxiang@dodoca.cn>
     *
     */
     static public function getDataFromCsv($targetFile){
         if (is_file($targetFile)) {
            $handle = fopen($targetFile, 'r');    //读取文件
            $out = array ();
            $n = 0;
            while ($data = fgetcsv($handle, 10000)) {
                $num = count($data);
                for ($i = 0; $i < $num; $i++) {
                    $out[$n][$i] = $data[$i];
                }
                $n++;
            }
            return $out;
         }
         return false;
     }

    /**
     *
     *   导出数据到csv文件
     *   @method ExportDataToCsv
     *   @param string  $filename		设置文件名
     *   @param string  $data		    csv文件数据
     *   @date   2016年3月23日
     *   @author liutao<liutao@dodoca.com>
     *
     */
    static public function ExportDataToCsv($filename,$data){
        header("Content-type:text/csv");
        header("Content-Disposition:attachment;filename=".$filename);
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        echo $data;
    }


}

?>