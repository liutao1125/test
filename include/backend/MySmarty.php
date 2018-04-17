<?php

require("Smarty/Smarty.class.php");

class MySmarty extends Zend_View_Abstract
{
    private $_smarty ;
    private $_root ;

	 private $_template ;


    public function __construct($root)
    {
        $this->initByConst($root);
		/*
		if(Zend_Registry::isRegistered('config')){
			$this->initByIni($root);
		}else{
			$this->initByConst($root);
		}
		*/
		
    }


	private function initByConst($root)
    {
      	if(defined('SYS_APP_MODULE')){
			$tplFolder = str_replace('/'.SYS_APP_MODULE, '', SYS_APP_FOLDER);
		}else{
			$tplFolder =  SYS_APP_FOLDER;
		}

		$template_dir=$tplFolder.'/views/';
		//$compile_dir = $_SERVER['SINASRV_CACHE_DIR']."/".$tplFolder;		 
		$compile_dir = $_SERVER['SINASRV_CACHE_DIR']."/".$tplFolder.(defined('CACHE_FIX')?"_".CACHE_FIX:'');
		
		if (!is_dir($compile_dir))		mkdir($compile_dir, 0777);

		
		parent::__construct(array('scriptPath'=>$template_dir));
        
		$this->_root=$root.'/';
        
        $this->_smarty = new Smarty();

		$this->_smarty->template_dir = $this->_root. $template_dir;
        $this->_smarty->compile_dir =	$compile_dir;	
        //$this->_smarty->config_dir = $this->_root.$config->smarty->config_dir;

        $this->_smarty->cache_dir = $compile_dir;
		$this->_smarty->cache_lifetime = 30;		
        $this->_smarty->caching = 0;

		$this->_smarty->cache_modified_check	=	false;

		$this->_smarty->force_complie   =   false;
		$this->_smarty->compile_check = true;
        $this->_smarty->debugging = false; 

		$this->_smarty->left_delimiter = "{{";
		$this->_smarty->right_delimiter = "}}";
		
    }
	/*
	private function initByIni($root) {
		
		$config = Zend_Registry::get('config');

		parent::__construct(array('scriptPath'=>$template_dir));
        
		$this->_root=$root.'/';

        $compile_dir = $_SERVER['SINASRV_CACHE_DIR']."/";
        
        $this->_smarty = new Smarty();

		$this->_smarty->template_dir = $this->_root. $config->smarty->template_dir;
        $this->_smarty->compile_dir =	$compile_dir;	//$this->_root. $config->smarty->compile_dir;
        $this->_smarty->config_dir = $this->_root.$config->smarty->config_dir;
        $this->_smarty->cache_dir = $compile_dir;	//$this->_root.$config->smarty->cache_dir;

		$this->_smarty->cache_lifetime = (int)$config->smarty->cache_lifetime;
		
        $this->_smarty->caching = $config->smarty->caching;

		$this->_smarty->cache_modified_check=(bool)$config->smarty->cache_modified_check;

		$this->_smarty->force_complie   =   (bool)$config->smarty->force_complie;
		$this->_smarty->compile_check = (bool)$config->smarty->compile_check;
        $this->_smarty->debugging = (bool)$config->smarty->debugging; 

	}
	*/


	

    protected function _run($template=null)
    {
		//show($template);

		//$template=basename($template);
		//if($template)
		//$this->_root.
		//$template="fabu.html";
		//s($template);

		$this->_smarty->display($template);
    }
	
	//no used
	public function display($template=null)
    {		
		$this->_smarty->display($template);
    }
    
    public function assign($var,$value=null)
    {
        if (is_string($var))
        {
            $value = @func_get_arg(1);
            
            $this->_smarty->assign($var, $value);
        }
        elseif (is_array($var))
        {
            foreach ($var as $key => $value)
            {
                $this->_smarty->assign($key, $value);
            }
        }
        else
        {
            throw new Zend_View_Exception('assign() expects a string or array, got '.gettype($var));
        }
    }

    public function escape($var)
    {
        if (is_string($var))
        {
            return parent::escape($var);
        }
        elseif (is_array($var))
        {
            foreach ($var as $key => $val)
            {
                $var[$key] = $this->escape($val);
            }

            return $var;
        }
        else
        {
            return $var;
        }
    }

	public function setTpl($template) {
		$this->_template=$template;
	}

	
	/*
	*	取得输出的内容
	*/
	public function fetch($para,$template='')
    {	
		Return $this->output($para,$template,'fetch');
    }
	/*
	*	显示
	*/
	public function render($para,$template='')
	{
		$this->output($para,$template,'display');
	}
/*
Print the output
The next method output() is a wrapper on the render() method from Zend_View_Abstract. It just sets some headers before printing the output.
*/
    public function output($para,$template,$mode='display')
    {
   		$action='';
		if($template)$this->setTpl($template);

		if($this->_template){			
			$tpl=$this->_template;
		}else{
			$tpl=$para['controller'].$action.'.tpl';
		}
		//s($tpl);
		$this->_smarty->assign('request', $para);
		switch($mode) {
			case 'display':
				echo parent::render($tpl);
				break;
			case 'fetch':
				return $this->_smarty->fetch($tpl);
				break;
		}
        
    }

	public function _script($name) {
		Return $name;
	}

    /*
    Use Smarty caching
    The last two methods were created to simply integrate the Smarty caching mechanism in the View class. With the first one you can check for cached template and with the second one you can set the caching on or of.
    */
    public function is_cached($template)
    {
        
		//var_dump($template);
		if ($this->_smarty->is_cached($template))
        {
            return true;
        }
        
        return false;
    }

    public function setCaching($caching)
    {
        $this->_smarty->caching = $caching;
    }

	public function get($var=null) {
		Return $this->_smarty->get_template_vars($var);
	}
	
	public function register_function($function_name, $callback, $cache=true, $cache_attr = null) {
		return $this->_smarty->register_function($function_name, $callback, $cache=true, $cache_attr = null);
	}
}
