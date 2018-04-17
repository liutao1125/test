<?php  
/** 
* @Author: Jiahaiming 
* @Name: Image Exif Class 
* @Version: 0.0.1 
**/  
  
class ImgExif{  
    public $imgPath;  
    public $unitFlag;  
    public $imgInfoAll;  
    public $imgInfoAllCN;  
    public $imgInfoCommon;  
    public $imgInfoBrief;  
    public $imgInfoAllCNUnit;  
  
    /*构造函数，检测exif和mbstring模块是否开启*/  
    function __construct(){  
        extension_loaded('exif')&&extension_loaded('mbstring') or 
            die('exif module was not loaded,please check it in php.ini<br>NOTICE:On Windows,php_mbstring.dll must be before php_exif.dll'); 
    } 
 
    /*获取图片格式，返回图片格式信息 
    *     如果只获取图片格式信息，建议采用此方法 
    * 
    * @param $imgPath(必填,字符串)，图片路径，不可为url。 
    * @param $MimeOrExifOrExtension(可选,字符串)，获取图片格式为Mime类型或Exif类型或图片类型文件后缀。 
    *      如果为字符串'Mime'，则获取Mime图片类型。 
    *      如果为字符串'Exif'，则获取Exif图片类型。 
    *      如果为字符串'Extension'，则获取图片类型的文件后缀。 
    *      如果填写参数异常或缺省，则默认获取Mime图片类型。 
    */ 
 
    function getImgtype($imgPath,$MimeOrExifOrExtension = null){ 
        $exifImgtype = array( 
            'IMAGETYPE_GIF' => 1, 
            'IMAGETYPE_JPEG' => 2, 
            'IMAGETYPE_PNG' => 3, 
            'IMAGETYPE_SWF' => 4, 
            'IMAGETYPE_PSD' => 5, 
            'IMAGETYPE_BMP' => 6, 
            'IMAGETYPE_TIFF_II' => 7, //（Intel 字节顺序） 
            'IMAGETYPE_TIFF_MM' => 8, //（Motorola 字节顺序） 
            'IMAGETYPE_JPC' => 9, 
            'IMAGETYPE_JP2' => 10, 
            'IMAGETYPE_JPX' => 11, 
            'IMAGETYPE_JB2' => 12, 
            'IMAGETYPE_SWC' => 13, 
            'IMAGETYPE_IFF' => 14, 
            'IMAGETYPE_WBMP' => 15, 
            'IMAGETYPE_XBM' => 16 
            ); 
        $exifType = array_search(exif_imagetype($imgPath),$exifImgtype); 
        $mimeType = image_type_to_mime_type(exif_imagetype($imgPath)); 
        $extension = substr(image_type_to_extension(exif_imagetype($imgPath)),1); 
        if($MimeOrExifOrExtension){ 
            if($MimeOrExifOrExtension === 'Mime'){ 
                return $mimeType; 
            } 
            elseif($MimeOrExifOrExtension === 'Exif'){ 
                return $exifType; 
            } 
            elseif($MimeOrExifOrExtension === 'Extension'){ 
                return $extension; 
            } 
            else{ 
                return $mimeType; 
            } 
        } 
        else{ 
            return $mimeType; 
        } 
    } 
 
    /*处理Exif信息*/ 
    function imgInfo(){ 
        $imgPath = $this->imgPath; 
 
        $imgInfoAll = exif_read_data($imgPath,0,1); 
        foreach($imgInfoAll as $section => $arrValue){ 
            foreach($arrValue as $key => $value){ 
                $infoAllKey[] = $key; 
                $infoAllValue[] = $value; 
            } 
        } 
        $infoAll = array_combine($infoAllKey,$infoAllValue); 
 
        $translate = array( 
            'Orientation' => 'Orientation', 
            ); 
 
        @$translate_unit = array( 
            'Orientation' => array_search($infoAll['Orientation'],array( 
                '0' => 1, 
                '0' => 2, 
                '180' => 3, 
                '0' => 4, 
                '0' => 5, 
                '270' => 6, 
                '0' => 7, 
                '90' => 8 
            ))
            ); 
 
        $infoAllCNKey = array_keys($translate); 
        $infoAllCNName = array_values($translate); 
        foreach($infoAllCNKey as $value){ 
            @$infoAllCNValue[] = $infoAll[$value]; 
        } 
        $infoAllCNUnit = array_combine($infoAllCNName,array_values($translate_unit)); 
        $infoAllCN = array_combine($infoAllCNName,$infoAllCNValue); 
        $infoCommon = array( 
            $translate['DateTimeOriginal'] => $infoAll['DateTimeOriginal'], 
            $translate['MimeType'] => $infoAll['MimeType'], 
            $translate['Width'] => $infoAll['Width'], 
            $translate['Height'] => $infoAll['Height'], 
            $translate['Comments'] => $infoAll['Comments'], 
            $translate['Author'] => $infoAll['Author'], 
            $translate['Make'] => $infoAll['Make'], 
            $translate['Model'] => $infoAll['Model'], 
            $translate['CompressedBitsPerPixel'] => $infoAll['CompressedBitsPerPixel'], 
            $translate['ExposureBiasValue'] => $infoAll['ExposureBiasValue'], 
            $translate['MaxApertureValue'] => $infoAll['MaxApertureValue'], 
            $translate['MeteringMode'] => $infoAll['MeteringMode'], 
            $translate['LightSource'] => $infoAll['LightSource'], 
            $translate['Flash'] => $infoAll['Flash'], 
            $translate['FocalLength'] => $infoAll['FocalLength'], 
            $translate['SceneType'] => $infoAll['SceneType'], 
            $translate['CFAPattern'] => $infoAll['CFAPattern'], 
            $translate['CustomRendered'] => $infoAll['CustomRendered'], 
            $translate['ExposureMode'] => $infoAll['ExposureMode'], 
            $translate['WhiteBalance'] => $infoAll['WhiteBalance'], 
            $translate['DigitalZoomRatio'] => $infoAll['DigitalZoomRatio'], 
            $translate['FocalLengthIn35mmFilm'] => $infoAll['FocalLengthIn35mmFilm'], 
            $translate['SceneCaptureType'] => $infoAll['SceneCaptureType'], 
            $translate['GainControl'] => $infoAll['GainControl'], 
            $translate['Contrast'] => $infoAll['Contrast'], 
            $translate['Saturation'] => $infoAll['Saturation'], 
            $translate['Sharpness'] => $infoAll['Sharpness'], 
            $translate['SubjectDistanceRange'] => $infoAll['SubjectDistanceRange'], 
            $translate['Software'] => $infoAll['Software'], 
            $translate['DateTime'] => $infoAll['DateTime'], 
            $translate['FileSize'] => $infoAll['FileSize'] 
            ); 
        foreach($infoCommon as $cKey => $cKalue){ 
            $infoCommonUnitKeys[] = $cKey; 
            $infoCommonUnitValues[] = $translate_unit[$cKey]; 
        } 
        $infoCommonUnit = array_combine($infoCommonUnitKeys,$infoCommonUnitValues); 
 
        $infoBrief = array( 
            $translate['FileName'] => $infoAll['FileName'], 
            $translate['Width'] => $infoAll['Width'], 
            $translate['Height'] => $infoAll['Height'], 
            $translate['DateTimeOriginal'] => $infoAll['DateTimeOriginal'], 
            $translate['Make'] => $infoAll['Make'], 
            $translate['Model'] => $infoAll['Model'], 
            $translate['MimeType'] => $infoAll['MimeType'] 
            ); 
        foreach($infoBrief as $bKey => $bValue){ 
            $infoBriefUnitKeys[] = $bKey; 
            $infoBriefUnitValues[] = $translate_unit[$bKey]; 
        } 
        $infoBriefUnit = array_combine($infoBriefUnitKeys,$infoBriefUnitValues); 
 
        $this->imgInfoAll = $infoAll; 
        $this->imgInfoAllCN = $infoAllCN; 
        $this->imgInfoAllCNUnit = $infoAllCNUnit; 
        $this->imgInfoCommon = $this->unitFlag ? $infoCommonUnit : $infoCommon; 
        $this->imgInfoBrief = $this->unitFlag ? $infoBriefUnit : $infoBrief; 
    } 
 
    /*获取图片Exif信息，返回Exif信息一维数组 
    * 
    * @param $imgPath(必填,字符串)，图片路径，不可为url。 
    * @param $iChoice(可选,字符串或一维数组) 
    *    此参数内置了三种模式： 
    *      如果为字符串'All'，则获取所有信息； 
    *      如果为字符串'Common'，则获取常用信息； 
    *      如果为字符串'Brief'，则获取简要信息。 
    *    用户可以自定义数组获取精确的信息，如array('图片宽度','图片高度')，则获取图片宽度和高度。 
    *    对于异常输入或缺省，则自动获取简要信息。 
    * @param $showUnit(可选，字符串)，只要不为null，则获取已经转换后的值，否则获取未转换后的值。 
    */ 
    function getImgInfo($imgPath,$iChoice = null,$showUnit = null){ 
        $this->imgPath = $imgPath; 
        $this->unitFlag = $showUnit; 
        $this->imgInfo(); 
        $this->imgInfoAllCN = $showUnit ? $this->imgInfoAllCNUnit : $this->imgInfoAllCN; 
        if($iChoice){ 
            if(is_string($iChoice)){ 
                if($iChoice === 'All'){ 
                    return $this->imgInfoAllCN; 
                } 
                elseif($iChoice === 'AllUnit'){ 
                    return $this->imgInfoAllCN; 
                } 
                elseif($iChoice === 'Common'){ 
                    return $this->imgInfoCommon; 
                } 
                elseif($iChoice === 'Brief'){ 
                    return $this->imgInfoBrief; 
                } 
                else{ 
                    return $this->imgInfoBrief; 
                } 
            } 
            elseif(is_array($iChoice)){ 
                foreach($iChoice as $value){ 
                    $arrCustomValue[] = $this->imgInfoAllCN[$value]; 
                } 
                $arrCustom = array_combine($iChoice,$arrCustomValue) or die('Ensure the array $iChoice values match $infoAll keys!'); 
                return $arrCustom; 
            } 
            else{ 
                return $this->imgInfoBrief; 
            } 
        } 
        else{ 
            return $this->imgInfoBrief; 
        } 
    } 
}
?> 