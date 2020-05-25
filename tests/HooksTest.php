<?php 
namespace tests\TagFeather;
use TagFeather\Hooks;

class HooksTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(Hooks::class);
        
        //code here
        $this->do_ssi();
        \MyCodeCoverage::G()->end(Hooks::class);
        $this->assertTrue(true);
        /*
        //Hooks::callback  by TF_Builder;
        Hooks::modifier_filename($is_build,$tf,$hooktype);
        Hooks::modifier_time($is_build,$tf,$hooktype);
        Hooks::prebuild_signature($data,$tf,$hooktype);
        Hooks::prebuild_commitfirsttag($data,$tf,$hooktype);
        Hooks::prebuild_struct($data,$tf,$hooktype);
        Hooks::postbuild_bind($data,$tf,$hooktype);
        Hooks::postbuild_ssidel($data,$tf,$hooktype);
        Hooks::postbuild_unmatch($data,$tf,$hooktype);
        Hooks::postbuild_signature($data,$tf,$hooktype);
        Hooks::notation_doctypeshowonce($str,$tf,$hooktype);
        Hooks::pi_tf_outblock($data,$tf,$hooktype);
        Hooks::pi_php_shorttag($data,$tf,$hooktype);
        Hooks::error_tagfeather($e,$tf,$hooktype);
        Hooks::error_struct($e,$tf,$hooktype);
        Hooks::tagend_tf_final($attrs,$tf,$hooktype);
        Hooks::tagend_showonce($attrs,$tf,$hooktype);
        Hooks::tagend_phpheredoc($attrs,$tf,$hooktype);
        Hooks::tagend_meta_showonce($attrs,$tf,$hooktype);
        Hooks::tagend_delattrs($attrs,$tf,$hooktype);
        Hooks::tagend_rewrite($attrs,$tf,$hooktype);
        Hooks::tagend_over($attrs,$tf,$hooktype);
        Hooks::tagend_showstruct($attrs,$tf,$hooktype);
        Hooks::tagend_struct($attrs,$tf,$hooktype);
        Hooks::tagend_bindas($attrs,$tf,$hooktype);
        Hooks::tagend_bindwith($attrs,$tf,$hooktype);
        Hooks::tagend_bindto($attrs,$tf,$hooktype);
        Hooks::tagend_rewriteall($attrs,$tf,$hooktype);
        Hooks::tagend_inner_waitforwrap($attrs,$tf,$hooktype);
        Hooks::tagend_wrap($attrs,$tf,$hooktype);
        Hooks::tagend_phpincmap($attrs,$tf,$hooktype);
        Hooks::tagend_delheadfoot($attrs,$tf,$hooktype);
        Hooks::tagend_headfoot($attrs,$tf,$hooktype);
        Hooks::tagend_phplang($attrs,$tf,$hooktype);
        Hooks::tagend_textmap($attrs,$tf,$hooktype);
        Hooks::tagend_attrmap($attrs,$tf,$hooktype);
        Hooks::tagend_bindmap($attrs,$tf,$hooktype);
        Hooks::tagbegin_tf_init($attrs,$tf,$hooktype);
        Hooks::tagbegin_safe($attrs,$tf,$hooktype);
        Hooks::tagbegin_struct($attrs,$tf,$hooktype);
        Hooks::tagbegin_showstruct($attrs,$tf,$hooktype);
        Hooks::tagbegin_tochildren($attrs,$tf,$hooktype);
        Hooks::tagbegin_inner_waitforwrap($attrs,$tf,$hooktype);
        Hooks::tagbegin_selector($attrs,$tf,$hooktype);
        Hooks::tagbegin_quick($attrs,$tf,$hooktype);
        Hooks::tagbegin_bycookie($attrs,$tf,$hooktype);
        Hooks::tagbegin_byvisible($attrs,$tf,$hooktype);
        Hooks::tagbegin_toparent($attrs,$tf,$hooktype);
        Hooks::tagbegin_byhref($attrs,$tf,$hooktype);
        Hooks::tagbegin_inserttoparse($attrs,$tf,$hooktype);
        Hooks::tagend_appendtoparse($attrs,$tf,$hooktype);
        Hooks::tagbegin_pure($attrs,$tf,$hooktype);
        Hooks::comment_ssi($comment,$tf,$hooktype);

        //    public static function all_quick_function($attrs, $tf, $hooktype)
    */
      
    }
    protected function do_text()
    {
        $hooktype='';
        $tf=new FakeTF();
        //$text=
        Hooks::text_phplang($text,$tf,$hooktype);
        Hooks::text_textmap($text,$tf,$hooktype);
        Hooks::text_bycookie($text,$tf,$hooktype);
    }
    protected function do_ssi()
    {
        $attrs=[];
        $hooktype='';
        $tf=new FakeTF();
        
        Hooks::ssi_noparse($attrs,$tf,$hooktype);
        Hooks::ssi_appendtoparse($attrs,$tf,$hooktype);
        Hooks::ssi_appendtotext($attrs,$tf,$hooktype);
        Hooks::ssi_delbegin($attrs,$tf,$hooktype);
        Hooks::ssi_delend($attrs,$tf,$hooktype);
        Hooks::ssi_tagbegin($attrs,$tf,$hooktype);
        Hooks::ssi_tagend($attrs,$tf,$hooktype);
        Hooks::ssi_tag($attrs,$tf,$hooktype);
    }
}
class FakeTF
{
    public $runtime=[];
}

