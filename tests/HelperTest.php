<?php 
namespace tests\TagFeather;
use TagFeather\Helper;

class HelperTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(Helper::class);
        
        //code here
        $str="no";
        Helper::GetBool($str);
        
        
        $str="a b <?=\$true?>";
        Helper::SplitClass($str);
        
        $attrs=[
            'a'=>'b',
            'tf:z'=>'c',
        ];
        Helper::ToServerOrNormalAttrs($attrs,true);
        Helper::ToServerOrNormalAttrs($attrs,false);
        
        \MyCodeCoverage::G()->end(Helper::class);
        $this->assertTrue(true);
        /*
        
        Helper::WrapPhp($str,$use_decode=false,$is_show=false);
        Helper::SplitClass($class);
        Helper::MatchClass($class,$subclass);
        Helper::SliceCut($data,$str1,$str2);
        Helper::SliceReplace($data,$replacement,$str1,$str2,$is_outside=false,$wrap=false);
        Helper::call_tagblock_hook($str_func,$attrs,$tf,$type);
        Helper::ToBlankAttrs($attrs);
        Helper::ToHiddenAttrs($attrs,$go=true);
        Helper::MergeAttrs($attrs,$ext_attrs);
        Helper::AttrsPrepareAssign($attrs,$extkey);
        Helper::DumpTagStackString($tf);
        Helper::UniqString($id='');
        Helper::InsertDataAndFile($tf,$data,$filename);
        Helper::GetTextMap($text);
        Helper::ToServerOrNormalAttrs($attrs,$only_server);
        //*/
    }
}
