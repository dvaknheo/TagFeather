<?php
/**
 * TagFeather
 * A Template Engine,by PHP5 .Provider Html Editor What You See What You Get;
 * @author  Dvaknheo <dvaknheo@gmail.com>
 * @license Free For Personal , if you use it to make money ,wish you to get little me.
 * @version SVN: $Id: TF_Builder.class.php 78 2008-07-27 16:15:28Z dvaknheo $
 * @link	http://www.tagfeather.com 
 * @link    http://www.dvaknheo.com
 * @copyright	2006-2008 Chen Guobing E.
 * @package TagFeather
 * @since 2006.11
 */
class TF_Builder //extends TF_Hookmanager //implements TF_IHandleCallback
{
	/** @var array Important to extends ,tag stack */
	public $tagStack=array(array("\ntext"=>''));
	/** @var string data to parse */
	public $data='';
	
	/** @var array this is the config array ,use it freely */
	public $config=array();
	/** @var array this array is for your hook ,use it freely */
	public $runtime=array();

	/** @var bool flag to data is builded */
	public $has_build=false;
	/** @var bool is error ?  */
	public $is_build_error=false;
	/** @var string error infomation  */
	public $build_error_msg='Build Error';

	/** @var TF_XmlParser the parser*/
	public $parser=null;
	/** @var TF_HookManager the hookmanager */
	//public $hookmanager=null;
	/** @var TF_Handle the handle call by parser */
	public $handle=null;
	public $builder_callback=null;
	/** */
	private $key_text="\ntext";
	/** Constructor */
	public function __construct()
	{
		//parent::__construct();
		//$this->handle=new TF_Handle($this);
		$this->parser=new TF_XmlParser($this);
	}
	/** Destructor */
	public function __destruct()
	{
		$this->parser->__destruct();
    }
	/** Build $this->data */
	public function build()
	{
		error_reporting(error_reporting() & ( ~E_NOTICE));
		$this->data=$this->callHooksByType('prebuild',$this->data,true);
		
		$this->parser->data=$this->data;
		$this->parser->parse();
		
		$this->data=$this->tagStack[0]["\ntext"];
		$this->data=$this->callHooksByType('postbuild',$this->data,false);
		
		$this->has_build=true;
		return $this->is_build_error;
	}
	///////////////////////////////////////////////////////////////////////////
	public function addLastTagText($str)
	{
		if($str!==''){
			$this->tagStack[sizeof($this->tagStack)-1][$this->key_text].=$str;
		}
	}
	/**
	 * implement IHandleCallback
	 */
	public function callHooksByType($hooktype,$arg,$queque_mode=false)
	{
		if(NULL!==$this->builder_callback){
			return call_user_func($this->builder_callback,$hooktype,$arg,$queque_mode,$this);
		}else{
			return $arg;
		}
	}
	/**
	 * implement IHandleCallback
	 */
	public function needReturnAspPi()
	{
		$to_returntext=$this->parser->is_asp_pi_frag;
		return $to_returntext;
	}
	/**
	 * implement IHandleCallback
	 */
	public function errorFinalHandle($e)
	{
		if(!$e)return;
		$error_msg=
			"TF_Builder Parser Error: <br />\n".
			"Line: {$e['line']} <br />\n".
			"Type:{$e['type']}<br />\n".
			"Message:'".htmlspecialchars($e['info'])."\n".
			"Stack Dump:";
		
		$str='';
		foreach($this->tagStack as $tag){
			if($tag["\ntagname"])$index="<sup>".$tag["\ntf index"]."</sup>";
			$str.="/".$tag["\ntagname"];
		}
		$error_msg.=$str;
		$this->parser->data=""; // to stop next paser;
		$this->is_build_error=true;
		$this->build_error_msg=$error_msg;
		return array();
	}
	/**
	 * implement IHandleCallback
	 */
	public function endTagFinalHandle($attrs)
	{
		if(!$attrs)return ;
		//if( !array_key_exists("\ntagname",$attrs) ){
		//	$attrs["\ntagname"]=end($this->parser->tagnames);
		//}
		$keeptext=(!in_array($attrs["\ntagname"],$this->parser->single_tag))?true:false;
		$text=self::TagToText($attrs,"\nfrag",$keeptext);
		$this->addLastTagText($text);
		return array();
	}
	///////////////////////////////////////////////////////////////////////////
	/**
	 *  asp handle for TF_XMLParser
	 *
	 * @param string $str include < %
	 * @return string if $to_returntext;
	 */
	public function asp_handle($str)
	{
		$to_returntext=$this->needReturnAspPi();
		$str=$this->callHooksByType('asp',$str);
		if($to_returntext){
			return $str;
		}else{
			$this->addLastTagText($str);
			return;
		}
	}
	/**
	 * cdata handle for TF_XMLParser 
	 *
	 * @param string $str include < ![cdata[ ]] >
	 */
	public function cdata_handle($str)
	{
		$str=$this->callHooksByType('cdata',$str);
		$this->addLastTagText($str);
	}
	/**
	 * comment handle for TF_XMLParser 
	 * include < !-- -- >
	 * @param string $str
	 */
	public function comment_handle($str)
	{
		$str=$this->callHooksByType('comment',$str);
		$this->addLastTagText($str);
	}
	/**
	 * notation handle for TF_XMLParser 
	 *
	 * @param string $str include < !  > 
	 */
	public function notation_handle($str)
	{
		$str=$this->callHooksByType('notation',$str);
		$this->addLastTagText($str);
	}
	/**
	 * error handle for TF_XMLParser 
	 *
	 * @param int $line the error line
	 * @param string $name the error name
	 * @param string $info more info about this error;
	 */
	public function error_handle($error_info)
	{
		$file=$this->tf->source;
		$error_array=array(
			'source'=>'parser',
			//'file'=>$file,
			'line'=>$error_info['line'],
			'type'=>$error_info['type'],
			'info'=>$error_info['info'],
			//'level'=>$level,
		);
		$error_array=$this->callHooksByType('error',$error_array);
		$this->errorFinalHandle($error_array);
	}
	/**
	 *  pi handle for TF_XMLParser
	 *
	 * @param string $str include < ?
	 * @param false $to_returntext is in attribute
	 * @return string if $to_returntext;
	 */
	public function pi_handle($str)
	{
		$to_returntext=$this->needReturnAspPi();
		$str=$this->callHooksByType('pi',$str);
		if($to_returntext){
			return $str;
		}else{
			$this->addLastTagText($str);
			return;
		}
	}
	/**
	 *  tagbegin handle for TF_XMLParser
	 *
	 * @param string $tagname 
	 * @param array $attrs the attributes
	 */
	public function tagbegin_handle($attrs)
	{
		$this->tagStack[]=$this->callHooksByType('tagbegin',$attrs,true);
	}
	/**
	 *  tagend handle for TF_XMLParser
	 *
	 * @param string $tagname 
	 */
	public function tagend_handle($tagname)
	{
		$lasttag=array_pop($this->tagStack);
		$tag=$this->callHooksByType('tagend',$lasttag);
		$this->endTagFinalHandle($tag);
	}

	/**
	 * text handle for TF_XMLParser
	 *
	 * @param string $str string to parse
	 */
	public function text_handle($str)
	{
		$str=$this->callHooksByType('text',$str);
		$this->addLastTagText($str);
	}
	///////////////////////////////////////////////////////////////////////////
	/**
	 * 
	 */
	public static function GetTagName($attrs)
	{
		return $attrs["\ntagname"];
	}
	/**
	 * 
	 */
	public static function SetTagText(&$attrs,$text)
	{
		$attrs["\ntext"]=$text;
	}
	/**
	 * 
	 */
	public static function AddTagText(&$attrs,$text)
	{
		$attrs["\ntext"].=$text;
	}
	/**
	 * 
	 */
	public static function GetTagText($attrs)
	{
		return $attrs["\ntext"];
	}
	/**
	 * 
	 */
	public static function ClearTagText(&$attrs)
	{
		unset($attrs["\ntext"]);
		return $attrs;
	}
	/**
	 * Get Tag Text
	 *
	 * @param array $attrs server attributes
	 * @param array $reserve_prefixs
	 * @return string
	 */
	public static function TagToText($attrs,$pre_frag="\nfrag",$keeptext=true)
	{
		$tagname=$attrs["\ntagname"];
		$ret='';
		$text=$attrs["\ntext"];
		if($tagname){
			$pre_frag_len=strlen($pre_frag);
			
			$ret="<$tagname";
			$len=strlen($ret);
			$headdata=array();
			foreach( $attrs as $key => $value ){
				if( 0===strncmp($key,$pre_frag,$pre_frag_len) ){
					$headdata[]="$value";
					continue;
				}
				if($key{0}!="\n"){
					$headdata[]="$key=\"$value\"";
				}
			}
			if($headdata){
				//if(!is_array($headdata)){var_dump($headdata);die;}
				$ret.=" ".implode(" ",$headdata);
			}
			if( !$keeptext && $tagname && (NULL===$text || ""===$text) ){
				$ret.=" />";
			}else{
				$ret.=">$text</$tagname>";
			}
			
		}else{
			$ret=$attrs["\ntext"];
		}
		if(array_key_exists("\nposttag",$attrs)){$ret=$ret.$attrs["\nposttag"];}
		if(array_key_exists("\npretag",$attrs)){$ret=$attrs["\npretag"].$ret;}
		return $ret;
		
	}
}
