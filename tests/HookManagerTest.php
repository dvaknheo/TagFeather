<?php 
namespace tests\TagFeather;
use TagFeather\HookManager;
use PHPUnit\Framework\Assert;

class HookManagerTest extends \PHPUnit\Framework\TestCase
{
    public static function Hook($arg, $callback, $hooktype)
    {
        $a=func_get_args();
        var_dump($a);
    }
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(HookManager::class);
        $callback=[HookManagerTest::class,'Hook'];
        $arg=["zz"];
        HookManager::G()->get_hooknamebycallback('JustFunction');
        HookManager::G()->get_hooknamebycallback([HookManagerObject::class,'Hook']);
        $x=HookManager::G()->get_hooknamebycallback([ new HookManagerObject(),'Hook'],false);
        var_dump($x);
        
        HookManager::G()->parsehooks["testshook"]=[];
        HookManager::G()->add_parsehook($callback,$hooktype='',$overfollow=false,$hookname='');
        HookManager::G()->add_parsehook(null,'testshook');
        HookManager::G()->add_parsehook([HookManagerObject::class,'StaticHook'],'testshook');
        HookManager::G()->add_parsehook([HookManagerObject::class,'StaticHook'],'testshook',true);
        
        
        HookManager::G()->insert_parsehook([HookManagerObject::class,'StaticHook2'],'testshook','');
        HookManager::G()->insert_parsehook([HookManagerObject::class,'StaticHook3'],'testshook',HookManagerObject::class.'::'.'StaticHook');
        //HookManager::G()->insert_parsehook($callback,$hooktype,$insertbefore='',$hookname='');
        
        
        HookManager::G()->append_parsehook([HookManagerObject::class,'StaticHook4'],'testshook','');
        HookManager::G()->append_parsehook([HookManagerObject::class,'StaticHook5'],'testshook',HookManagerObject::class.'::'.'StaticHook');        
        
        HookManager::G()->remove_parsehook('tests\\TagFeather\\HookManagerObject::StaticHook4',false);
        HookManager::G()->remove_parsehook('tests\\TagFeather\\HookManagerObject::StaticHook5','testshook');
        
        HookManager::G()->get_parsehook('tests\\TagFeather\\HookManagerObject::StaticHookNo','testshook');
        HookManager::G()->get_parsehook('tests\\TagFeather\\HookManagerObject::StaticHook3','testshook');
        HookManager::G()->get_parsehook('tests\\TagFeather\\HookManagerObject::StaticHook3');
        HookManager::G()->get_parsehook('tests\\TagFeather\\HookManagerObject::StaticHookNo');
        //HookManager::G()->get_parsehook($stop=true);
        HookManager::G()->stop_nexthooks($stop=true);
        
        HookManager::G()->call_parsehooksbytype('testshook','X');

        HookManager::G()->call_oneparsehook('tests\\TagFeather\\HookManagerObject::StaticHook2','testshook','X');
        
        
        
        HookManager::G()->disable_hook('tests\\TagFeather\\HookManagerObject::StaticHookNo','testshook',true);
        HookManager::G()->disable_hook('tests\\TagFeather\\HookManagerObject::StaticHook3','testshook',true);
        HookManager::G()->disable_hook('tests\\TagFeather\\HookManagerObject::StaticHook3','testshook',false);
        //HookManager::G()->disable_hook($hookname,$hooktype,$disable=true);
        //HookManager::G()->reg_hookobject($hookobj);
        HookManager::BlankHook(null,null,null);
        
        \MyCodeCoverage::G()->end(HookManager::class);
        Assert::assertTrue(true);
        //$this->assertTrue(true);
        /*
        HookManager::G()->__construct();
        HookManager::G()->__destruct();
        HookManager::G()->get_hooknamebycallback($callback,$overfollow=true);
        HookManager::G()->add_parsehook($callback,$hooktype='',$overfollow=false,$hookname='');
        HookManager::G()->insert_parsehook($callback,$hooktype,$insertbefore='',$hookname='');
        HookManager::G()->append_parsehook($callback,$hooktype,$appendafter='',$hookname='');
        HookManager::G()->remove_parsehook($hookname,$hooktype=false);
        HookManager::G()->get_parsehook($hookname,$hooktype=false);
        HookManager::G()->stop_nexthooks($stop=true);
        //call_parsehooksbytype($hooktype,$arg,$queque_mode=false,$caller=null);
        HookManager::G()->call_oneparsehook($hookname,$hooktype,$arg);
        HookManager::G()->disable_hook($hookname,$hooktype,$disable=true);
        HookManager::G()->reg_hookobject($hookobj);
        HookManager::G()->BlankHook($arg,$tf,$hooktype);
        //*/
    }
}
class HookManagerObject
{
    public static function StaticHook(){}
    public static function StaticHook2(){}
    public static function StaticHook3(){
        HookManager::G()->stop_nexthooks();
    }
    
    public function hook(){}
}