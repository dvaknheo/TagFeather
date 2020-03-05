<?php 
namespace tests\TagFeather;
use TagFeather\Helper;

class HelperTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(Helper::class);
        
        //code here
        
        \MyCodeCoverage::G()->end(Helper::class);
        $this->assertTrue(true);
        /*
        Helper::G()->GetPhp($str);
        Helper::G()->GetBool($str);
        Helper::G()->WrapPhp($str,$use_decode=false,$is_show=false);
        Helper::G()->SplitClass($class);
        Helper::G()->MatchClass($class,$subclass);
        Helper::G()->SliceCut($data,$str1,$str2);
        Helper::G()->SliceReplace($data,$replacement,$str1,$str2,$is_outside=false,$wrap=false);
        Helper::G()->call_tagblock_hook($str_func,$attrs,$tf,$type);
        Helper::G()->ToBlankAttrs($attrs);
        Helper::G()->ToHiddenAttrs($attrs,$go=true);
        Helper::G()->MergeAttrs($attrs,$ext_attrs);
        Helper::G()->AttrsPrepareAssign($attrs,$extkey);
        Helper::G()->DumpTagStackString($tf);
        Helper::G()->UniqString($id='');
        Helper::G()->InsertDataAndFile($tf,$data,$filename);
        Helper::G()->GetTextMap($text);
        Helper::G()->ToServerOrNormalAttrs($attrs,$only_server);
        //*/
    }
}
