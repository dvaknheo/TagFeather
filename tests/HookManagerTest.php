<?php 
namespace tests\TagFeather;
use TagFeather\HookManager;

class HookManagerTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(HookManager::class);
        
        //code here
        
        \MyCodeCoverage::G()->end(HookManager::class);
        $this->assertTrue(true);
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
