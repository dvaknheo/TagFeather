<?php 
namespace tests\TagFeather;
use TagFeather\HookObjectBase;

class HookObjectBaseTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(HookObjectBase::class);
        
        //code here
        
        \MyCodeCoverage::G()->end(HookObjectBase::class);
        $this->assertTrue(true);
        /*
        HookObjectBase::G()->__construct($hookmanager=null);
        HookObjectBase::G()->__destruct();
        HookObjectBase::G()->reg($hookmanager,$tf=null,$hooktype='reg');
        HookObjectBase::G()->unreg($arg=false,$tf=null,$hooktype='unreg');
        HookObjectBase::G()->modifier($arg,$tf,$hooktype);
        HookObjectBase::G()->error($arg,$tf,$hooktype);
        HookObjectBase::G()->prebuild($arg,$tf,$hooktype);
        HookObjectBase::G()->postbuild($arg,$tf,$hooktype);
        HookObjectBase::G()->tagbegin($arg,$tf,$hooktype);
        HookObjectBase::G()->tagend($arg,$tf,$hooktype);
        HookObjectBase::G()->text($arg,$tf,$hooktype);
        HookObjectBase::G()->asp($arg,$tf,$hooktype);
        HookObjectBase::G()->pi($arg,$tf,$hooktype);
        HookObjectBase::G()->comment($arg,$tf,$hooktype);
        HookObjectBase::G()->notation($arg,$tf,$hooktype);
        HookObjectBase::G()->cdata($arg,$tf,$hooktype);
        //*/
    }
}
