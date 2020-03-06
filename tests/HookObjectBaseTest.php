<?php 
namespace tests\TagFeather;
use TagFeather\HookObjectBase;

class HookObjectBaseTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(HookObjectBase::class);
        
        //code here
        $arg="fake";
        $tf=null;
        $hooktype='';
        (new HookObjectBase())->modifier($arg,$tf,$hooktype);
        (new HookObjectBase())->error($arg,$tf,$hooktype);
        (new HookObjectBase())->prebuild($arg,$tf,$hooktype);
        (new HookObjectBase())->postbuild($arg,$tf,$hooktype);
        (new HookObjectBase())->tagbegin($arg,$tf,$hooktype);
        (new HookObjectBase())->tagend($arg,$tf,$hooktype);
        (new HookObjectBase())->text($arg,$tf,$hooktype);
        (new HookObjectBase())->asp($arg,$tf,$hooktype);
        (new HookObjectBase())->pi($arg,$tf,$hooktype);
        (new HookObjectBase())->comment($arg,$tf,$hooktype);
        (new HookObjectBase())->notation($arg,$tf,$hooktype);
        (new HookObjectBase())->cdata($arg,$tf,$hooktype);
        
        
        \MyCodeCoverage::G()->end(HookObjectBase::class);
        $this->assertTrue(true);
        /*
        (new HookObjectBase())->__construct($hookmanager=null);
        (new HookObjectBase())->__destruct();
        (new HookObjectBase())->reg($hookmanager,$tf=null,$hooktype='reg');
        (new HookObjectBase())->unreg($arg=false,$tf=null,$hooktype='unreg');
        //*/
    }
}
