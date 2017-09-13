<?php
/**
 * TagFeather
 * A Template Engine,by PHP5 .Provider Html Editor What You See What You Get;
 * @author  Dvaknheo <dvaknheo@gmail.com>
 * @license Free For Personal , if you use it to make money ,wish you to get little me.
 * @version SVN: $Id: TF_HookObjectBase.class.php 78 2008-07-27 16:15:28Z dvaknheo $
 * @link	http://www.tagfeather.com 
 * @link    http://www.dvaknheo.com
 * @copyright	2006-2008 Chen Guobing E.
 * @package TagFeather
 * @since 2006.11
 */
class TF_HookObjectBase
{
	protected $hooktypes=array();
	protected $hookmanager;
	protected $hooks=array();
	public function __construct($hookmanager=null)
	{
		if($hookmanager){
			$this->hookmanager=$hookmanager;
			$this->reg($hookmanger);
		}
	}
	public function __destruct()
	{
		$this->unreg($this->hookmanager,null,'unreg');
	}
	public function __destruct()
	{
		return;
	}
	public function reg($hookmanager,$tf=null,$hooktype='reg')
	{
		if($hookmanager){$this->hookmanager=$hookmanager;}
		$hooktypes=$this->hooktypes;
		if(!$hooktypes){
			$hooktypes=array_keys($hookmanager->parsehooks);
		}
		foreach($hooktypes as $thehooktype){
			$hookname=$hookmanager->get_hooknamebycallback(array(&$this,$thehooktype),true);
			$hookmanager->add_parsehook(array(&$this,$thehooktype),$thehooktype,'',$hookname);
			$hooks[$thehooktype]=$hookname;
		}
		return hookmanager;
	}
	public function unreg($arg=false,$tf=null,$hooktype='unreg')
	{
		foreach($this->hooks as $thehooktype =>$hookname){
			$this->hookmanager->remove_parsehook($hookname,$thehooktype);
		}
		return $arg;
	}
	public function modifier($arg,$tf,$hooktype)
	{
		return $arg;
	}
	public function error($arg,$tf,$hooktype)
	{
		return $arg;
	}
	public function prebuild($arg,$tf,$hooktype)
	{
		//echo("OK");die(__LINE__);
		return $arg;
	}
	public function postbuild($arg,$tf,$hooktype)
	{
		return $arg;
	}
	public function tagbegin($arg,$tf,$hooktype)
	{
		return $arg;
	}
	public function tagend($arg,$tf,$hooktype)
	{
		return $arg;
	}
	public function text($arg,$tf,$hooktype)
	{
		return $arg;
	}
	public function asp($arg,$tf,$hooktype)
	{
		return $arg;
	}
	public function pi($arg,$tf,$hooktype)
	{
		return $arg;
	}
	public function comment($arg,$tf,$hooktype)
	{
		return $arg;
	}
	public function notation($arg,$tf,$hooktype)
	{
		return $arg;
	}
	public function cdata($arg,$tf,$hooktype)
	{
		return $arg;
	}
}
?>