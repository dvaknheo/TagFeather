<?php 
namespace tests\TagFeather;
use TagFeather\Builder;

class BuilderTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(Builder::class);
        
        //code here
        
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
        Builder::G()->asp_handle($str);
        Builder::G()->cdata_handle($str);
        Builder::G()->comment_handle($str);
        Builder::G()->notation_handle($str);
        Builder::G()->error_handle($error_info);
        Builder::G()->pi_handle($str);
        Builder::G()->tagbegin_handle($attrs);
        Builder::G()->tagend_handle($tagname);
        Builder::G()->text_handle($str);
        Builder::G()->GetTagName($attrs);
        Builder::G()->SetTagText($attrs,$text);
        Builder::G()->AddTagText($attrs,$text);
        Builder::G()->GetTagText($attrs);
        Builder::G()->ClearTagText($attrs);
        Builder::G()->TagToText($attrs,$pre_frag="\nfrag",$keeptext=true);
        //*/
    }
}
