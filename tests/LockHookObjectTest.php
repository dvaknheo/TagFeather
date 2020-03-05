<?php 
namespace tests\TagFeather;
use TagFeather\LockHookObject;

class LockHookObjectTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(LockHookObject::class);
        
        //code here
        
        \MyCodeCoverage::G()->end(LockHookObject::class);
        $this->assertTrue(true);
        /*
        LockHookObject::G()->reg($hookmanager,$tf=null,$fakehooktype='reg');
        LockHookObject::G()->modifier($is_build,$tf,$hooktype);
        LockHookObject::G()->postbuild($data,$tf,$hooktype);
        LockHookObject::G()->error($error,$tf,$hooktype);
        //*/
    }
}
