<?php //$Id: TF_Smarty.class.php 50 2008-06-08 04:17:49Z dvaknheo $
class TF_Smarty
{
	var $vars=array();
	function assign($key) // ,...
	{
		$args=func_get_args();
		if(sizeof($args)==1){
			$this->vars=array_merge($this->vars,$args);
		}else{
			$this->vars[$args[0]]=$args[1];
		}
	}
	function display($file)
	{
		TagFeather::TemplateAndExit($file,$_SERVER['SCRIPT_FILENAME']);
	}
	function dump()
	{
		var_dump($this->vars);
	}
}

