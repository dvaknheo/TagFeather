<?php 
namespace tests\TagFeather;
use TagFeather\Builder;

class BuilderTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(Builder::class);
        
        //code here
        $this->do_static();
        $this->do_handler();
        
        \MyCodeCoverage::G()->end(Builder::class);
        $this->assertTrue(true);
        /*
        Builder::G()->__construct();
        Builder::G()->__destruct();
        Builder::G()->build();
        Builder::G()->addLastTagText($str);
        Builder::G()->callHooksByType($hooktype,$arg,$queque_mode=false);
        Builder::G()->needReturnAspPi();
        Builder::G()->errorFinalHandle($e);
        Builder::G()->endTagFinalHandle($attrs);


        //*/
    }
    protected function do_static()
    {
        $attrs=[
            
        ];
        Builder::GetTagName($attrs);
        $text="aa";
        Builder::SetTagText($attrs,$text);
        $text="bb";
        Builder::AddTagText($attrs,$text);
        Builder::GetTagText($attrs);
        Builder::ClearTagText($attrs);
    
        //
        $attrs=[
            'text'=>'abc',
        ];
        Builder::TagToText($attrs);
        
        $attrs=[
            "\ntagname"=>'tag',
            'text'=>'abc',
            "\npretag"=>'pretag',
            "\nposttag"=>'posttag',
            "aa"=>"bb",
            "\nfrag"=>"haha",
        ];
        $str=Builder::TagToText($attrs);
        echo $str;
        
        
        $attrs=[
            "\ntagname"=>'tag',
        ];
        $str=Builder::TagToText($attrs,"\nfrag",false);
        echo $str;
        
        //Builder::G()->TagToText($attrs,"\nfrag",false);
    }
    protected function do_handler()
    {
        $str='';
        Builder::G()->asp_handle($str);
        Builder::G()->cdata_handle($str);
        Builder::G()->comment_handle($str);
        Builder::G()->notation_handle($str);
        Builder::G()->pi_handle($str);
        Builder::G()->text_handle($str);
        
        $tagname="zz";
        $attrs=[
            "\ntagname"=>$tagname,
        ];
        Builder::G()->tagbegin_handle($attrs);
        Builder::G()->tagend_handle($tagname);
        /*
                Builder::G()->error_handle($error_info);
        Builder::G()->tagbegin_handle($attrs);
        Builder::G()->tagend_handle($tagname);
        
        //*/
    }
}
