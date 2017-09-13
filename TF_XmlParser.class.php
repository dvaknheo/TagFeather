<?php
/**
 * TagFeather
 * A Template Engine,by PHP5 .Provider Html Editor What You See What You Get;
 * @author  Dvaknheo <dvaknheo@gmail.com>
 * @license Free For Personal , if you use it to make money ,wish you to get little me.
 * @version SVN: $Id: TF_XmlParser.class.php 78 2008-07-27 16:15:28Z dvaknheo $
 * @link	http://www.tagfeather.com 
 * @link    http://www.dvaknheo.com
 * @copyright	2006-2008 Chen Guobing E.
 * @package TagFeather
 * @since 2006.11
 *
 * SAX Mode xml parser 
 * Bug constant of TF_XmlParser is no work;
 */
class TF_XmlParser
{
	/** use for < ? PI=//Processing Instruction ? >  */
	const ATTR_FRAG_PI=1;
	/** use for < % Active Server Page % > */
	const ATTR_FRAG_ASP=2;
	/** direct property */
	const ATTR_FRAG_BOOL=4;
	/** //use for < !-- -- > */
	const ATTR_FRAG_COMMENT=8;
	/** { } */
	const ATTR_FRAG_BRACE=16;
	/** // $xxx_xxx[] */
	const ATTR_FRAG_VAR=32;	
	/** < ? + < % */
	const ATTR_FRAG_COMMON=7;
	
	const NOTATION_BEGIN="<!";
	const NOTATION_BEGIN_LENGTH=2;
	const PI_BEGIN="<\x3f";
	const PI_END="\x3f>";
	const PI_BEGIN_LENGTH=2;
	const ASP_BEGIN="<\x25";
	const ASP_END="\x25>";
	const ASP_BEGIN_LENGTH=2;
	
	public static $MAX_ATTRS_SIZE=50;
	
	/** @var object TF_Handle Instance */
	public $handle;
	/** @var string Data to parse */
	public $data;
	/** @var array tag who has no text */
    public $single_tag = array(
        'area',
        'base',
        'basefont',
        'br',
        'col',
        'frame',
        'hr',
        'img',
        'input',
		'isindex',
        'link',
        'meta',
        'param',
    );
	/** @var int config tag frag mode */
	public $tag_frag_mode=self::ATTR_FRAG_COMMON;
	/** @var string config tag frag pre */
	public $tag_frag_pre="\nfrag ";
	/** @var string */
	public $tag_tagname_key="\ntagname";
	/** @var int */
	public $timeout=25;
	/** @var int line parseed ,to point out the error line */
	public $current_line=1;
	
	/** @var bool is parsing server attribute */
	public $is_asp_pi_frag=false;
	/** @var bool stop_parse servertag ,for more quick */
	public $stop_parse_serverfrag=false;
	/** @  current tag close by  /> .unlike >. can't insert data as child */
	public $is_current_tag_notext=false;
	public $timecost=0;
	/** @var array for this->parse_tag()*/
	public $tagnames=array();
	private $timeinit=0;
	private $tomatchtag_line=1;
	/**
	 * constructor
	 *
	 * @param TF_Handle $handle;
	 */
	public function __construct($handle)
	{
		$this->handle=$handle;
		$this->timeinit=microtime(true);
	}
	/**
	 * destructor
	 */
	public function __destruct()
	{
		unset($this->handle);
	}
	/**
	 * call $this->handle method;
	 *
	 * @param callback $handle
	 * @return mixed
	 */
	public function call($handle,$arg)
	{
		$ret=call_user_func(array(&$this->handle,$handle),$arg);
		$newtime=microtime(true);
		if( $newtime-$this->timeinit > $this->timeout){
			$this->error_info("Timeout");
		}
		return $ret;
	}
	/**
	 * insert data to parse 
	 * 
	 * @param string $ext_data data to insert
	 * @param bool $shift_line also change current line;
	 */
	public function insert_data($ext_data,$shift_line=false)
	{
		$this->data=$ext_data.$this->data;
		if($shift_line){
			$l=substr_count($ext_data,"\n");
			$this->current_line-=$l;
		}
	}
	/**
	 * Default Error Handle;
	 *
	 * @param int $line
	 * @param string $type error type
	 * @param string $info error info
	 */
	public function error_handle($line,$type,$info)
	{
		$this->is_error=true;
		if(method_exists($this->handle,'error_handle')){
			$this->handle->error_handle( array('line'=>$line,'type'=>$type,'info'=>$info) );
		}else{
			user_error(__CLASS__ . " Error at line $line ($type):'".htmlspecialchars($info)."'\n",E_USER_ERROR);
			//throw new Exception(__CLASS__ . " Error at line $line ($msg):'".htmlspecialchars($error_string)."'\n");
		}
		$this->data='';
	}
	/** Parse the $this->data */
	public function parse()
	{
		
		$p_tagheadbegin='/^<([0-9a-zA-Z_\x7f-\xff:\-]+)/s';
		
		
		while($this->data){	
			if("<"!=$this->data{0}){
				$this->parse_text();
			}else if( 0==strncmp($this->data,self::NOTATION_BEGIN,self::NOTATION_BEGIN_LENGTH) ){
				$this->parse_notation();
			}else if(0==strncmp($this->data,"</",2)){
				$this->parse_tagend();
			}else if( 0==strncmp($this->data,self::PI_BEGIN,self::PI_BEGIN_LENGTH) ){
				$this->parse_pi();
			}else if(preg_match($p_tagheadbegin,$this->data,$matches)){
				$this->parsing_attrs=$matches;
				$this->parse_tag();
			}else if( 0==strncmp($this->data,self::ASP_BEGIN,self::ASP_BEGIN_LENGTH) ){
				$this->parse_asp();
			}else if(substr($this->data,0,1)=='<'){
				$this->data=substr($this->data,1);
				$this->call('text_handle','<');
			}else{
				$this->error_info('UnmatchedData');
				return;
			}
		}
	}
	private function parse_text()
	{
		$pos=strpos($this->data,'<');
		if(FALSE!==$pos){
			$text=substr($this->data,0,$pos);
			$this->data=substr($this->data,$pos);
		}else{
			$text=$this->data;
			$this->data='';
		}
		$this->call('text_handle',$text);
		$this->current_line+=substr_count($text,"\n");
	}
	private function parse_tagend()
	{
		$p_tagend = '/^<\/([0-9a-zA-Z_\x7f-\xff:\-]+)>/';
		$flag=preg_match($p_tagend,$this->data,$matches);
		if(!$flag){
			$this->data=substr($this->data,1);
			$this->call('text_handle','<');				
		}
		$this->data=substr($this->data,strlen($matches[0]));
		$lasttagname=$matches[1];
		if(!$this->is_matchtagname($lasttagname)){
			//$this->call('text_handle',$matches[0]);
			return;
		}
		
		$this->call('tagend_handle',$lasttagname);
		array_pop($this->tagnames);		
	}
	private function parse_notation()
	{
		$data=&$this->data;
		
		if( 0==strncmp($data,"<!--",strlen("<!--")) ){
			$pos=strpos($data,"-->");
			if(FALSE!==$pos){
				$pos+=strlen("-->");
				$matchdata=substr($data,0,$pos);
				$data=substr($data,$pos);
				$this->call('comment_handle',$matchdata);
				$this->current_line+=substr_count($matchdata,"\n");
				return;
			}
		}
		if( "<![CDATA[" == strtoupper(substr($this->data,0,"<![CDATA["))){
			$pos=strpos($data,"]]>");
			if(FALSE!==$pos){
				$pos+=strlen("]]>");
				$matchdata=substr($data,0,$pos);
				$data=substr($data,$pos);
				$this->call('cdata_handle',$matchdata);
				$this->current_line+=substr_count($matchdata,"\n");
				return;
			}
		}
		if(true){
			$pos=strpos($data,">");
			if(FALSE!==$pos){
				$pos+=strlen(">");
				$matchdata=substr($data,0,$pos);
				$data=substr($data,$pos);
				$this->call('notation_handle',$matchdata);
				$this->current_line+=substr_count($matchdata,"\n");
				return;
			}
		}
		$this->data=substr($this->data,1);
		$this->call('text_handle','<');
	}
	private function parse_pi()
	{
		$PIend='?'.'>';
		$data=&$this->data;
		
		$pos=strpos($data,$PIend);
		if(FALSE===$pos){
			$this->error_info("UnclosePI");
			return;
		}
		$pos+=strlen($PIend);
		$matchdata=substr($data,0,$pos);
		$data=substr($data,$pos);
		$this->call('pi_handle',$matchdata);
		$this->current_line+=substr_count($matchdata,"\n");
	}
	private function parse_asp()
	{
		$ASPend='%'.'>';
		$data=&$this->data;
		
		$pos=strpos($data,$ASPend);
		if(FALSE===$pos){
			$this->error_info("UnclosedASP");
			return;
		}
		$pos+=strlen($ASPend);
		$matchdata=substr($data,0,$pos);
		$data=substr($data,$pos);
		$this->call('asp_handle',$matchdata);
		$this->current_line+=substr_count($matchdata,"\n");
	}
	private function parse_tag()
	{
		$p_tagheadend='/^\s*(\/)?'.'>/s';
		$matches=$this->parsing_attrs;
		
		$headdata=$matches[0];
		$tagname=$matches[1];
		
		
		//$matchdata=$matches[0];
		$this->data=substr($this->data,strlen($headdata));
		
		$attrs=array();
		$attrs=self::ToAttrs($this->data,$attrs_len,$this->tag_frag_mode,$this->tag_frag_pre);
		
		if($attrs_len>0){
			$headdata.=substr($this->data,0,$attrs_len);
			$this->data=substr($this->data,$attrs_len);
		}
		
		if(preg_match($p_tagheadend,$this->data,$matches)){
			$headdata.=$matches[0];
			
			$matchdata=$matches[0];
			$this->data=substr($this->data,strlen($matchdata));
			$attrs=$this->parse_serverattr($attrs); //for pi,asp;
			
			
			$this->tagnames[]=$tagname;
			if('/'==$matches[1]){
				$this->is_current_tag_notext=true;
			}
			$this->tomatchtag_line=$this->current_line;
			$attrs[$this->tag_tagname_key]=$tagname;
			$this->call('tagbegin_handle',$attrs);
			$this->current_line+=substr_count($headdata,"\n");
			if('/'==$matches[1]){
				$this->call('tagend_handle',$tagname);
				array_pop($this->tagnames);
				$this->is_current_tag_notext=false;
			}
			else if(in_array($tagname,$this->single_tag)){
				$this->call('tagend_handle',$tagname);
				array_pop($this->tagnames);
			}
			else if(strtolower($tagname)=='script')
			{
				$this->parse_script();
			}else {
			}
		}else{
			
			if(strlen($headdata)!=0){
				$this->error_info("UnqoutedAttribute");
				return;
			}
			$this->call('text_handle',$headdata);
		}
	}
	private function parse_script()
	{
		$data=&$this->data;
		
		$pos=stripos($data,'</script>');
		if(FALSE===$pos){
			$this->error_info("UncloseSCRIPT");
			return;
		}
		
		$text=substr($data,0,$pos);
		if( FALSE===strpos($text,self::PI_BEGIN) && FALSE===strpos($text,self::PI_BEGIN) ){
			$data=substr($data,$pos);
			$this->call('text_handle',$text);
			$this->current_line+=substr_count($text,"\n");
		}else{
			$PIpattern='<'.'\?(.*?)\?'.'>';
			$ASPpattern='<'.'\%(.*?)\%'.'>';
			$p_attr="/($PIpattern)|($ASPpattern)/s";
			
			$array=$this->preg_splitdata($p_attr,$text);
			
			$this->is_asp_pi_frag=false;
			foreach($array as $text){
				$this->data=substr($this->data,strlen($text));				
				if(self::PI_BEGIN==substr($text,0,2) && self::PI_END==substr($text,-2)){
					$this->call('pi_handle',$text);
				}else if(self::ASP_BEGIN==substr($text,0,2) && self::ASP_END==substr($text,-2)){
					$this->call('asp_handle',$text);
				}else if(FALSE!==strpos($text,self::PI_BEGIN) || FALSE!==strpos($text,self::ASP_BEGIN)){
					$this->error_info("ScriptServerBlockUnclosed");
				}else{
					$this->call('text_handle',$text);
				}
				$this->current_line+=substr_count($str,"\n");
			}
		}
		$this->data=substr($this->data,strlen("</script>"));
		
		$this->call('tagend_handle','script');
		array_pop($this->tagnames);
	}
	/**
	 * parse PI/ASP attribute
	 *
	 * @param array $attrs
	 * @return $attrs
	 */
	private function parse_serverattr($attrs)
	{
		$this->is_asp_pi_frag=true;
		foreach($attrs as $key=>$str){
			$str=$this->parse_serverfrag($str);
			$attrs[$key]=$str;
		}
		$this->is_asp_pi_frag=false;
		
		return $attrs;
	}
	private function parse_serverfrag($str)
	{
		if($this->stop_parse_serverfrag){return $str;}
		$PIpattern='<'.'\?(.*?)\?'.'>';
		$ASPpattern='<'.'\%(.*?)\%'.'>';
		
		$p_attr="/($PIpattern)|($ASPpattern)/s";
		
		if(FALSE===strpos($str,self::PI_BEGIN)|| FALSE===strpos($str,self::ASP_END)){
			return $str;
		}
		$str=preg_replace_callback ($p_attr,array(&$this,'parse_serverfrag_callback'),$str);	
		return $str;
	}
	/**
	 * callback for parse_serverattr by preg_replace_callback
	 *
	 * @param array $match
	 * @return string
	 */
	public function parse_serverfrag_callback($match){
		if($match[1]){
			$str=$this->call('pi_handle',$match[1]);
		}else if($match[3]){
			$str=$this->call('asp_handle',$match[3]);
		}
		return $str;
	}
	private function is_matchtagname($lasttagname)
	{
		$tagname=end($this->tagnames);
		if($tagname!=$lasttagname){
			$this->error_info("UnmatchTagName '$tagname' found '/$lasttagname' at line {$this->tomatchtag_line} ");
			return false;
		}
		return true;
	}
	private function error_info($info)
	{
		$this->error_handle($this->current_line,$info,substr($this->data,0,100));
	}
	private function preg_splitdata($pattern,$text)
	{
		$ret=array();
		$a=preg_split ($pattern, $text,-1,PREG_SPLIT_OFFSET_CAPTURE|PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY);
		$totaloffset=0;
		foreach($a as $capture){
			$offset=$capture[1];
			$str=$capture[0];
			if($offset!=$totaloffset){continue;}
			$totaloffset+=strlen($str);
			$ret[]=$str;
		}
		return $ret;
	}

	///////////////////////////////////////////////////////////////////////////
	/**
	 * Get attributes array by parse string
	 * e.g. string :key1="value1" key2='value2' will return array(key1=>"value1", key2=>"value2")
	 * remark: no auto html decode;
	 *
	 * @param string $str			string to parse
	 * @param string $match_byte 	refer,how much byt parsed ,
	 * @param bool	 $flag 			parse mode
	 * @param string $frag_prefix
	 * @return array assoc
	 */
	public static function ToAttrs($str,&$match_byte,$flag=self::ATTR_FRAG_COMMON,$frag_prefix="\n")
	{
		$PIpattern='<'.'\?.*?\?'.'>';
		$ASPpattern='<'.'%.*?%'.'>';
		$bool_pattern= '[a-zA-Z:_]+';
		
		$comment_pattern='<'.'!--.*?--'.'>';
		$qoute_pattern= '\{.*?\}';
		$var_pattern='\$[0-9a-zA-Z_\x7f-\xff]+(\[.*?\])?';
		
		//can't used in some host Ex dreamhost; , will CRASH ;
		//$attr_pattern='([0-9a-zA-Z_\x7f-\xff:\-]+)\s*=\s*([\'"])(.*?('.$PIpattern.')?('.$ASPpattern.')?)*)\\2';
		
		//$attr_pattern='([0-9a-zA-Z_\x7f-\xff:\-]+)\s*=\s*([\'"])((('.$PIpattern.')|('.$ASPpattern.')|[^\\2]+)*)\\2';
		$attr_pattern_part1='([0-9a-zA-Z_\x7f-\xff:\-&;]+)\s*=([\'"])?';
		$attr_pattern_part2='((('.$PIpattern.')|('.$ASPpattern.')|[^\'"]+)*)';
		
		$patterns_array=array();
		if( $flag|self::ATTR_FRAG_PI ){
			$patterns_array[]=$PIpattern;
		}
		if( $flag|self::ATTR_FRAG_ASP ){
			$patterns_array[]=$ASPpattern;
		}
		if( $flag|self::ATTR_FRAG_BOOL ){
			$patterns_array[]=$bool_pattern;
		}
		if( $flag|self::ATTR_FRAG_COMMENT ){
			$patterns_array[]=$comment_pattern;
		}
		if( $flag|self::ATTR_FRAG_BRACE ){
			$patterns_array[]=$qoute_pattern;
		}
		if( $flag|self::ATTR_FRAG_VAR ){
			$patterns_array[]=$var_pattern;
		}


		$ret=array();
		$init_len=strlen($str);
		
		$frag_index=1;
		$l=self::$MAX_ATTRS_SIZE;
		for($i=0;$i<$l;$i++){
			$matched=false;
			$matched=preg_match('/^\s*'.$attr_pattern_part1.'/s',$str,$match);
			if($matched){
				
				$head=$match[0];
				$key=$match[1];
				$qoute=$match[2];
				if($qoute){
					$attr_pattern_part2='((('.$PIpattern.')|('.$ASPpattern.')|[^'.$qoute.']+)*)';
				}else{
					$attr_pattern_part2='([^\s\>]+)';
				}
				$p='/^.{'.strlen($head).'}'.$attr_pattern_part2.$qoute.'/s';
				$value_matched=preg_match($p,$str,$value_match);
				if($value_matched){
					$value=$value_match[1];
					$ret[$key]=$value;
					$str=substr($str,strlen($value_match[0]));
					continue;
				}
			}
			foreach($patterns_array as $pattern){
				$matched=preg_match('/^\s*'.$pattern.'/s',$str,$match);
				if($matched){
					$key=$frag_prefix.$frag_index;
					$frag_index++;
					$value=$match[0];
					
					$ret[$key]=$value;
					$str=substr($str,strlen($match[0]));
					break;
				}
			}
			if(!$matched){break;}
		}
		$match_byte=$init_len-strlen($str);
		return $ret;
	}
}
