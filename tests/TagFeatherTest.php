<?php 
namespace tests\TagFeather;
use TagFeather\TagFeather;

class TagFeatherTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(TagFeather::class);
        
        //code here
        TagFeather::OutBegin();
        TagFeather::OutEnd();
        
        TagFeather::G()->init(['path'=>__DIR__])->build('xfile');
        
        \MyCodeCoverage::G()->end(TagFeather::class);
        $this->assertTrue(true);
        /*
        TagFeather::G()->forcebuild($force=true);
        TagFeather::G()->is_build();
        TagFeather::G()->add_struct($filename);
        TagFeather::G()->build_file();
        TagFeather::G()->get_abspath($filename,$encode=false);
        TagFeather::G()->throw_error($type,$info);
        TagFeather::G()->__construct();
        TagFeather::G()->__destruct();
        TagFeather::G()->initHooks();
        TagFeather::G()->getTemplateFile($source,$ignore_in_cache=false,$is_forcebuild=false);
        TagFeather::G()->display($filename="",$structfile="");
        TagFeather::G()->DisplayAndExit($filename='',$structfile='',$is_forcebuild=false,$tf=null);
        TagFeather::G()->OutBegin();
        TagFeather::G()->OutEnd();
        TagFeather::G()->_reg($names,$the_type=false);
        //*/
    }
}
