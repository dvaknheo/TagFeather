<?php
namespace TagFeather;

class Helper
{
	const PI_BEGIN="<\x3f";
	const PI_END="\x3f>";
	const ASP_BEGIN="<\x25";
	const ASP_END="\x25>";
	const PHP_BEGIN="<\x3fphp";
	const PHP_BEGIN_SHOW="<\x3fphp echo";
	const PHP_ENDBLOCK="<\x3fphp }\x3f>";
	
	/** //DECRAPED Get php code from attribute .if no < ? ? >; wrap decode html ,else use the data inside wrap
	 *
	 * @param string $str
	 * @return string
	 */
	public static function GetPhp($str)
	{
		if(substr($str,0,1)!='<'){
			$str=str_replace('&nbsp;',' ',$str);
			//because: the '&nbsp;' entity is not ASCII code 32 but ASCII code 160 (0xa0) in the default ISO 8859-1
			$str=html_entity_decode($str);
			return $str;
		}
		if(substr($str,0,5)=="<\x3fphp"){
			return substr($str,5,-2);
		}
		if(substr($str,0,2)=="<\x3f" || substr($str,0,2)=='<%'){
			return substr($str,2,-2);
		}
	}
	/**
	 *  Get the attibute bool value  incasetive
	 *  NULL,'','no','false','off' ,'disabled' will be false ,other is true;
	 *
	 * @param string $str
	 * @return bool
	 */
	public static function GetBool($str)
	{
		if(!$str)return false;
		$str=strtolower($str);	
		if( !$str || in_array($str,array('no','false','off','disabled')) ){
			return false;
		}else{
			return true;
		}
	}
	/** //DECRAPED 
	 * wrap php block
	 * 
	 * @param string $str $string to wrap
	 * @param string $use_decode to htmldecode the string ?
	 * @param string $is_show  start  with < ?php  or < ? php echo
	 * @return string wrapped string;
	 */
	public static function WrapPhp($str,$use_decode=false,$is_show=false)
	{
		if($use_decode){
			$str=str_replace('&nbsp;',' ',$str);
			//because: the '&nbsp;' entity is not ASCII code 32 but ASCII code 160 (0xa0) in the default ISO 8859-1
			$str=html_entity_decode($str);
		}
		if($is_show){
			return "<\x3fphp echo $str;\x3f>";
		}else{
			return "<\x3fphp $str;\x3f>";
		}
	}
	/**
	 * split class by space include pi/asp
	 * e.g "a" => array('a');"a b " => array('a','b') "a b < ? c ? >" => array('a','b','< ?c? >')
	 *
	 * @param string $class  class text
	 * @return array 
	 */
	public static function SplitClass($class)
	{
		if(FALSE===strpos($class,' ')){
			$rc=array($class);
			return $rc;
		}
		if(FALSE===strpos($class,'<')){
			return explode(' ',$class);
		}else{
			$pattern='/\S+|<.*?>/s';
			preg_match_all($pattern,$class,$matches);
			return $matches[0];
		}
	}
	/*
	 * check is a cssclass in a html class texts can include pi/asp
	 * @param stirng $class  
	 * @param stirng $subclass
	 */
	public static function MatchClass($class,$subclass)
	{
		return in_array($subclass,self::SplitClass($class));
	}
	/**
	 *
	 */
	public static function SliceCut($data,$str1,$str2)
	{
		$pos_begin=strpos($data,$str1);
		$pos_begin=$pos_begin+strlen($str1);
		$pos_end=strpos($data,$str2,$pos_begin);
		
		return substr($data,$pos_begin,$pos_end-$pos_begin);
	}
	/**
	 * 
	 */
	public static function SliceReplace($data,$replacement,$str1,$str2,$is_outside=false,$wrap=false)
	{
		$pos_begin=strpos($data,$str1);
		$extlen=($pos_begin===false)?0:strlen($str1);
		$pos_end=strpos($data,$str2,$pos_begin+$extlen);
		
		if( $pos_begin===false || $pos_end===false ){
			if(!$wrap)	return  $data;
		}
		if($is_outside){
			$pos_begin=($pos_begin===false)?0:$pos_begin;
			$pos_end=($pos_end===false)?strlen($data):$pos_end+strlen($str2);
		}else{
			$pos_begin=($pos_begin===false)?0:$pos_begin+strlen($str1);;
			$pos_end=($pos_end===false)?strlen($data):$pos_end;
		}
		
		return substr_replace($data,$replacement,$pos_begin,$pos_end-$pos_begin);
	}
	/**
	 * call tf_{$str_func} at tagbegin and tagend;
	 * call tfbegin_{$str_func} at tagbegin;
	 * call tfend_{$str_func} at tagend;
	 * 
	 * @param $str_func
	 * @param $attrs
	 * @param $tf TagFeather Object
	 * @param $type  'tagbegin'/'tagend'
	 * @return array new $attrs;
	 */
	public static function call_tagblock_hook($str_func,$attrs,$tf,$type)
	{
		
		if(is_callable('tf_'.$str_func)){
			$rc=call_user_func('tf_'.$str_func,$attrs,$tf,$type);
		}else if($type=='tagbegin' && is_callable('tf_tagbegin_'.$str_func)){
			$rc=call_user_func('tf_tagbegin_'.$str_func,$attrs,$tf,$type);
		}else if($type=='tagend' && is_callable( 'tf_tagend_'.$str_func)){
			$rc=call_user_func('tf_tagend_'.$str_func,$attrs,$tf,$type);
		}else{
			$rc=$attrs;
		}
		return $rc;
	}
	/**
	 */
	public static function ToBlankAttrs($attrs)
	{
		$attrs=array('tf:notag'=>true,'tf:notext'=>true ,'tf:pretag'=>'','tf:posttag'=>'',
				'tf:pretext'=>'','tf:posttext'=>'',	);
		return $attrs;
	}
	/**
	 */
	public static function ToHiddenAttrs($attrs,$go=true)
	{
		if(!$go)return $attrs;
		$ext_attrs=array(
				'tf:notag'=>true,'tf:notext'=>true ,
				'tf:pretag'=>'','tf:posttag'=>'','tf:pretext'=>'','tf:posttext'=>'',
			);
		$attrs=array_merge($attrs,$ext_attrs);
		unset($array["\ntext"]);
		return $attrs;
	}
	/**
	 */
	public static function MergeAttrs($attrs,$ext_attrs)
	{
		$newattrs=array_merge($attrs,$ext_attrs);
		if(array_key_exists('tf:pretag',$attrs)){
			$newattrs['tf:pretag']=$newattrs['tf:pretag'].$attrs['tf:pretag'];
		}
		if(array_key_exists('tf:pretext',$attrs)){
			$newattrs['tf:pretext']=$attrs['tf:pretext'].$newattrs['tf:pretext'];
		}
		if(array_key_exists('tf:posttext',$attrs)){
			$newattrs['tf:posttext']=$newattrs['tf::posttext'].$attrs['tf::posttext'];
		}
		if(array_key_exists('tf:posttag',$attrs)){
			$newattrs['tf:posttag']=$attrs['tf:posttag'].$newattrs['tf:posttag'];
		}
		if(array_key_exists('tf:lastfrag',$attrs)){
			$newattrs['tf:lastfrag']=$attrs['tf:lastfrag']." ".$newattrs['tf:lastfrag'];
		}
		return $newattrs;
	}
	/**
	 */
	public static function AttrsPrepareAssign($attrs,$extkey)
	{
		$extkeys=func_get_args();
		array_shift($extkeys);
		
		unset($attrs["\ntagname"]);
		unset($attrs["\ntf index"]);
		unset($attrs["\ntf children"]);
		foreach($extkeys as $extkey){
			unset($attrs[$extkey]);
		}
		return $attrs;
	}
	/**
	 *
	 */
	public static function DumpTagStackString($tf)
	{
		$l=sizeof($tf->tagStack);
		if($l==1)return '';
		$str='';
		foreach($tf->tagStack as $tag){
			$id='';
			$class='';
			if($tag['id']){$id="#".$tag['id'];}
			if($tag['class']){
				$class=".".str_replace(' ','.',$tag['class']);
				//$class=implode(".",self::SplitClass($tag['class']));
			}
			if($tag["\ntagname"])$index="<sup>".$tag["\ntf index"]."</sup>";
			$str.="$index".$tag["\ntagname"]."$id$class";
		}
		return $str;
	}

	/**
	 *
	 */
	public static function UniqString($id='')
	{
		if(!$id)$id=uniqid();
		$id=md5($id);
		return 'TF_{'.$id.'}';
	}
	/**
	 *
	 */
	public static function InsertDataAndFile($tf,$data,$filename)
	{
		if($filename){
			$filename=$tf->get_abspath($filename,true);
			$data=file_get_contents($filename);
		}else{
			$tf->parser->current_line-=substr_count($data,"\n");
		}
		$tf->parser->insert_data($data);
	}
	/**
	 *
	 */
	public static function GetTextMap($text)
	{
		$ret=array();
		$PIpattern='<'.'\?(.*?)\?'.'>';
		$ASPpattern='<'.'\%(.*?)\%'.'>';
		$p_attr="/($PIpattern)|($ASPpattern)/s";
		preg_match_all("/(\S+)\s*(($PIpattern)|($ASPpattern)|\S+)/s",$text,$matches,PREG_SET_ORDER);
		foreach($matches as $match){
			$key=trim($match[1]);
			$value=$match[2];
			$ret[$key]=$value;
		}
		return $ret;
	}
	/**
	 *
	 */
	public static function ToServerOrNormalAttrs($attrs,$only_server)
	{
		$flag=false;
		$keys=array_keys($attrs);
		foreach($keys as $key){
			if( 0==strncmp($key,"tf:",strlen("tf:")) ){
				$flag=true;
			}else{
				$flag=false;
			}
			if(!$only_server)$flag=!$flag;
			if($flag)unset($attrs[$key]);
		}
	}
}
