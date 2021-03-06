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
        Helper::GetBool('');
        Helper::GetBool($str);
        Helper::GetBool("yes");
        
        $str="a b <?=\$true?>";
        Helper::SplitClass("a\tb");
        Helper::SplitClass("a b");
        Helper::SplitClass($str);
        Helper::MatchClass("a b","a");

        $x=Helper::SliceCut("1a zzz b2","a",'b');
        echo $x;
        $y=Helper::SliceReplace("1a zzz b2\n",'yyy',"a",'b', $is_outside = false, $wrap = false);
        echo $y;

        $y=Helper::SliceReplace("1a zzz b2\n",'yyy',"a",'b', true, $wrap = false);
        echo $y;
        $y=Helper::SliceReplace("1a zzz b2\n",'yyy',"a1",'bx', true, $wrap = false);
        echo $y;
        
        Helper::ToBlankAttrs([]);
        Helper::ToHiddenAttrs([]);
        $attrs=[];$ext_attrs=[];
        Helper::MergeAttrs($attrs, $ext_attrs);
        
        Helper::AttrsPrepareAssign($attrs,"a","b");
        
        Helper::UniqString();
        
        //Helper::InsertDataAndFile($tf, $data, $filename)
        
        $text="
a <?php b?>
        ";
        Helper::GetTextMap($text);
        ////////////
        $attrs=[
            'a'=>'b',
            'tf:z'=>'c',
        ];
        Helper::ToServerOrNormalAttrs($attrs,true);
        Helper::ToServerOrNormalAttrs($attrs,false);
        

    
        //
        $attrs=[
            'text'=>'abc',
        ];
        Helper::TagToText($attrs);
        
        $attrs=[
            "\ntagname"=>'tag',
            'text'=>'abc',
            "\npretag"=>'pretag',
            "\nposttag"=>'posttag',
            "aa"=>"bb",
            "\nfrag"=>"haha",
        ];
        $str=Helper::TagToText($attrs);
        echo $str;
        
        
        $attrs=[
            "\ntagname"=>'tag',
        ];
        $str=Helper::TagToText($attrs,"\nfrag",false);
        echo $str;
        
        $attrs=[
            'tf:pretag'=>'tf_pretag1',
            'tf:pretext'=>'tf_pretag1',
            'tf:posttext'=>'tf_pretag1',
            'tf:posttag'=>'tf_pretag1',
            'tf:lastfrag'=>'tf_pretag1',
        ];
        $ext_attrs=[
            'tf:pretag'=>'pretag',
            'tf:pretext'=>'pretext',
            'tf:posttext'=>'posttext',
            'tf:posttag'=>'posttag',
            'tf:lastfrag'=>'lastfrag',
        ];
        Helper::MergeAttrs($attrs, $ext_attrs);
        
        
        $stack=[[]];
        Helper::DumpTagStackString($stack);
        $stack=[['tag'],["\ntagname"=>'x','id'=>'ID','class'=>'a b',]];
        Helper::DumpTagStackString($stack);
        
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
        Helper::UniqString($id='');
        Helper::InsertDataAndFile($tf,$data,$filename);
        Helper::GetTextMap($text);
        Helper::ToServerOrNormalAttrs($attrs,$only_server);
        //*/
    }
}
