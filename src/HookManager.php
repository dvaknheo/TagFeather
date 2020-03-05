<?php
namespace TagFeather;

class HookManager
{
	/** @ use in  {@link add_parsehook() } */
	const PARSEHOOK_EXIST=2;
	/** use in  @see reg_parsephook */
	const PARSEHOOK_NEW=1;
	/** use in  reg_parsephook */
	const PARSEHOOK_TYPEERROR=-1;
	/** use in  reg_parsephook */
	const PARSEHOOK_FAIL=-2;
	
	/** @var callback $callback  $foo($org,$callback,$hooktype) */
	public $manager_callback=null;
	
	/** @var array arriable hook types  */
	public $parsehooks=array(
		'unreg'=>array(),
	);

	public $lasthookname='';
	/** @var int for uniq hookname */
	private $objectcount=0;
	/** @var array for disableparsehook*/
	private $disabledparsehooks=array();
	private $stop_hooking=false;
	/** Constructor */
	public function __construct()
	{
	}
	/** Destructor */
	public function __destruct()
	{
		$arg=true;
		$this->call_parsehooksbytype('unreg',$arg,false);
	}
	/**
	 * get hookname by a callback; serialize callback
	 * 
	 * @param callback $callback callback
	 * @param bool $overfollow  if a object callback ,overfollow the old hook?
	 * @return string hookname;
	 */
	public function get_hooknamebycallback($callback,$overfollow=true)
	{
		if( !is_array($callback) ){
			$hookname=$callback;
		}else{
			if( is_string($callback[0]) ){
				$hookname=$callback[0]."::".$callback[1];
			}else{
				$hookname='$'.get_class($callback[0]).'->'.$callback[1];
				if(!$overfollow){
					$this->objectcount++;
					$hookname=$hookname.'#'.$this->objectcount;
				}
			}
		}
		return $hookname;
	}
	/**
	 * add a parse hook
	 *
	 * @param callback $callback callback
	 * @param string $hooktype hooktype
	 * @param bool $overfollow is overfollow the old hook?
	 * @param string $hookname a new hookname,not default;
	 * @return int the const of this class
	 */
	public function add_parsehook($callback,$hooktype='',$overfollow=false,$hookname='')
	{
		
		if(!array_key_exists($hooktype,$this->parsehooks)){
			return self::PARSEHOOK_TYPEERROR;
		}
		if(!is_callable($callback)){
			return self::PARSEHOOK_FAIL;
		}
		if(!$hookname){
			$hookname=$this->get_hooknamebycallback($callback,$overfollow);
		}
		
		if($overfollow){
			if(array_key_exists($hookname,$this->parsehooks[$hooktype])){
				$this->parsehooks[$hooktype][$hookname]=$callback;
				return self::PARSEHOOK_EXIST;
			}
		}
		$this->parsehooks[$hooktype][$hookname]=$callback;
		return self::PARSEHOOK_NEW;
	}
	/**
	 * insert a parse hook,if hookname exists ,overfollow
	 *
	 * @param callback $callback callback
	 * @param string $hooktype hooktype
	 * @param string $insertbefore insert before the hookname ,by default is the first;
	 * @param string $hookname a new hookname,not default;
	 * @return int the const of this class
	 */
	public function insert_parsehook($callback,$hooktype,$insertbefore='',$hookname='')
	{
		if(!$hookname){	$hookname=$this->get_hooknamebycallback($callback);}
		$newhooks=array();
		if(!$insertbefore){$newhooks[$hookname]=$callback;}
		$keys=array_keys($this->parsehooks[$hooktype]);
		foreach($keys as $key){
			if($insertbefore==$key && $insertbefore){
				$newhooks[$hookname]=$callback;
			}
			$newhooks[$key]=$this->parsehooks[$hooktype][$key];
		}
		$this->parsehooks[$hooktype]=$newhooks;
		return self::PARSEHOOK_NEW;
	}
	/**
	 * append a parse hook,if hookname exists ,overfollow
	 *
	 * @param callback $callback callback
	 * @param string $hooktype hooktype
	 * @param string $append append from the hookname ,by default is the last;
	 * @param string $hookname a new hookname,not default;
	 * @return int the const of this class
	 */
	public function append_parsehook($callback,$hooktype,$appendafter='',$hookname='')
	{
		if(!$hookname){	$hookname=$this->get_hooknamebycallback($callback);}
		if(!$appendafter){$this->parsehooks[$hooktype][$hookname]=$callback;}
		$newhooks=array();
		$keys=array_keys($this->parsehooks[$hooktype]);
		foreach($keys as $key){
			$newhooks[$key]=$this->parsehooks[$hooktype][$key];
			if($appendafter==$key){
				$newhooks[$hookname]=$callback;
			}
		}
		
		$this->parsehooks[$hooktype]=$newhooks;	
		return self::PARSEHOOK_NEW;
	}
	/**
	 * remove a parsehook by hookname
	 *
	 * @param string $hookname 
	 * @param string $hooktype if default search each hook type and remove all
	 * @return int how many to remove
	 */
	public function remove_parsehook($hookname,$hooktype=false)
	{
		$ret=0;
		if($hooktype ){
			if(isset($this->parsehooks[$hooktype][$hookname])){
				$ret++;
				unset($this->parsehooks[$hooktype][$hookname]);
			}
		}else{
			foreach($this->parsehooks as $hooktype =>$hooks){
				if(isset($hooks[$hookname])){
					$ret++;
					unset($this->parsehooks[$hooktype][$hookname]);
				}
			}
		}
		
		return $ret;
	}
	/**
	 * get ther parsehook callback  by hookname
	 *
	 * @param string $hookname 
	 * @param string $hooktype if default search each hook type and unregist all
	 * @return callback the handle
	 */	
	public function get_parsehook($hookname,$hooktype=false)
	{
		if($hooktype){
			return $this->parsehooks[$hooktype][$hookname];
		}else{
			foreach($this->parsehooks as $hooktype =>$hooks){
				if(isset($hooks[$hookname])){
					return $this->parsehooks[$hooktype][$hookname];
				}
			}
		}
		return NULL;
	}

	/** stop to parse next hook ,use in parsehooks as "coment" "text"
	 * @param bool $stop bool default 
	 */
	public function stop_nexthooks($stop=true)
	{
		$this->stop_hooking=$stop;
	}
	/**
	 * call parse hooks by type;
	 * this function will call mosttime , attention to  benchmark
	 *
	 * @param string $hooktype hooktype
	 * @param mixed $arg
	 * @param bool $queque_mode the hooks chains called order , turn to First In First Out.
	 * @return mixed the hooks chains finally return;
	 */
	public function call_parsehooksbytype($hooktype,$arg,$queque_mode=false,$caller=null)
	{
		$hooks=$this->parsehooks[$hooktype];//if(!is_array($this->parsehooks))var_dump($this->parsehooks);
		$keys=array_keys($hooks);
		$this->stop_hooking=false;
		if(!$queque_mode)$keys=array_reverse($keys);
		foreach($keys as $key){
			$arg=call_user_func($hooks[$key],$arg,$this->manager_callback,$hooktype);
			$this->lasthookname=$key;
			if($this->stop_hooking){
				$this->stop_hooking=false;
				break;
			}
		}
		$this->stop_hooking=false;
		return $arg;
	}
	/**
	 * call a single hook
	 *
	 * @param string $hookname hookname
	 * @param string $hooktype hooktype
	 * @param mixed $arg
	 * @return mixed changed $arg;
	 */
	public function call_oneparsehook($hookname,$hooktype,$arg)
	{
		$callback=$this->parsehooks[$hooktype][$hookname];
		return call_user_func($callback,$arg,$this,$hooktype);
	}
	/**
	 * disable a single hook
	 *
	 * @param string $hookname hookname
	 * @param string $hooktype hooktype
	 * @param bool $disable  disable/enable
	 */
	public function disable_hook($hookname,$hooktype,$disable=true)
	{
		if($disable){
			$callback=$this->parsehooks[$hooktype][$hookname];
			if( !$callback )return false;
			$key="$hooktype\n$hookname";
			$this->disabledparsehooks[$key]=$callback;
			$this->parsehooks[$hooktype][$hookname]=array( __CLASS__,'BlankHook');
		}else{
			$this->parsehooks[$hooktype][$hookname]=$this->disabledparsehooks[$key]=$callback;
			$key="$hooktype\n$hookname";
			unset($this->disabledparsehooks[$key]);
		}
	}
	/** regist a hook object,for more see TF_HookObjectBase
	 * @param object $hookobj the hookobject
	 */
	public function reg_hookobject($hookobj)
	{
		$hookobj->reg($this,$callback,'reg');
	}
	/** Just as blank hook;
	 *
	 */
	public static function BlankHook($arg,$tf,$hooktype)
	{
		return $arg;
	}
}
