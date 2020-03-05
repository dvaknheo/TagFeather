<?php
namespace TagFeather;

class Selector
{
	public $objtable=array();
	private $compiled_selector=array();
	public function addSelectorHash($selector_string,$obj)
	{
		$css=trim($selector_string);
		$this->objtable[$selector_string]=$obj;
		$this->compiled_selector[$selector_string]=self::SelectorFromString($selector_string);
	}
	public function removeSelectorHash($selector_string)
	{
		unset($this->table[$selector_string]);
		unset($this->compiled_selector[$selector_string]);
	}
	public function objectsFromStack(&$stack)
	{
		$ret=array();
		foreach($this->compiled_selector as $selector_name => $selector){
			if(self::MatchSelector($selector,$stack)){
				$ret[]=$this->objtable[$selector_name];
			}
		}
		return $ret;
	}
	
	public function objectsFromAttrs($stack,$attrs)
	{
		$stack[]=$attrs;
		return $this->objectsFromStack($stack);
	}
	///////////////////////////////////////////////////////////////////////////
	public static function SelectorFromString($text)
	{
		//$pattern='/(([A-Za-z0-9#\.@!\*]+)(\[.*?\])?(:[A-Za-z0-9\-]+)?)(\z|\s*[\s\+>]?\s*)/sA';
		$pattern='/([A-Za-z0-9\x7f-\xff#\.@!\*_^]+)(\[.*?\])?(:[A-Za-z0-9\-]+)?(\s*([,\s\+~;>]?)\s*)/sA';
		$p_props='/[#\.@!\*_^]?[A-Za-z0-9_]+/sA';
		$selector=array();
		$text=trim($text).";";
		
		preg_match_all($pattern,$text,$matches,PREG_SET_ORDER);
		foreach($matches as $match){
			$node=array();
			
			//make props
			preg_match_all($p_props,$match[1],$arr_prop);
			
			$props=$arr_prop[0];
			$newprops=array();
			foreach($props as $prop){
				if('*'!=$prop){$newprops[]=$prop;}
			}
			$node['props']=$newprops;
			
			//make attrs
			$node['attrs']=$match[2];
			$node['pseudo']=$match[3];
			
			//make operator
			$node['op']=$match[5];
			
			$selector[]=$node;
		}
		$selector=array_reverse($selector);
		return $selector;
	}

	public static function MatchSelector($selector,$stack)
	{
		$matched=false;
		$index=sizeof($stack)-1;
		if($index<0){return false;}
		$l=sizeof($selector);
		for($i=0;$i<$l;$i++){
			$node=$selector[$i];
			switch($node['op']){
			case '':
				$theindex=$index;
				for(true;$theindex>=0;$theindex--){
					//TODO adjust for pseudo
					$matched=self::MatchSelectorNode($node,$stack[$theindex]);
					if($matched){
						$index=$theindex;
						break;
					}
				}
			break;
			case '>':
				--$index;
				//TODO adjust for pseudo
				$matched=self::MatchSelectorNode($node,$stack[$index]);
			break;
			case '+':
				$index=sizeof($stack)-1;
			case ',':
			case ';':
				$matched=self::MatchSelectorNode($node,$stack[$index]);
			break;
			default:
				return false;
			}
			if(!$matched){ return false; }
			/* TODO "," opeartor
			if(!$matched){
				$return_now=true;
				for(++$i;$i<$l;++$i){
					if($selector[$i]['op']==','){
						$index=sizeof($stack)-1;
						$return_now=false;
						break;
					}
				}
				if($return_now){
					return false;
				}
				
			}
			*/
		}
		return $matched;
		
	}
	public static function MatchSelectorNode($selector,$attrs)
	{
		$props=array();
		$props[]=$attrs["\ntagname"];
		$props[]='#'.$attrs['id'];
		$props[]='@'.$attrs['name'];
		
		$props[]='^'.$attrs["\ntf index"];
		
		$classes=self::SplitClass($attrs['class']);
		foreach($classes as $class){
			$props[]='.'.$class;
		}
		$extprops=array_diff($selector['props'],$props);
		if($extprops){
			return false;
		}
		//TODO 	 attrs;
		return true;
	}
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
}

