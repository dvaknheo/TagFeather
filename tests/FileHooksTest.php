<?php 
namespace tests\TagFeather;
use TagFeather\FileHooks;

class HooksTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(FileHooks::class);
        
        //code here
        
        \MyCodeCoverage::G()->end(FileHooks::class);
        $this->assertTrue(true);
        /*
        //FileHooks::callback  by TF_Builder;
        FileHooks::modifier_filename($is_build,$tf,$hooktype);
        FileHooks::modifier_time($is_build,$tf,$hooktype);
        FileHooks::prebuild_signature($data,$tf,$hooktype);
        FileHooks::prebuild_commitfirsttag($data,$tf,$hooktype);
        FileHooks::prebuild_struct($data,$tf,$hooktype);
        FileHooks::postbuild_bind($data,$tf,$hooktype);
        FileHooks::postbuild_ssidel($data,$tf,$hooktype);
        FileHooks::postbuild_unmatch($data,$tf,$hooktype);
        FileHooks::postbuild_signature($data,$tf,$hooktype);
        FileHooks::notation_doctypeshowonce($str,$tf,$hooktype);
        FileHooks::pi_tf_outblock($data,$tf,$hooktype);
        FileHooks::pi_php_shorttag($data,$tf,$hooktype);
        FileHooks::error_tagfeather($e,$tf,$hooktype);
        FileHooks::error_struct($e,$tf,$hooktype);
        FileHooks::tagend_tf_final($attrs,$tf,$hooktype);
        FileHooks::tagend_showonce($attrs,$tf,$hooktype);
        FileHooks::tagend_phpheredoc($attrs,$tf,$hooktype);
        FileHooks::tagend_meta_showonce($attrs,$tf,$hooktype);
        FileHooks::tagend_delattrs($attrs,$tf,$hooktype);
        FileHooks::tagend_rewrite($attrs,$tf,$hooktype);
        FileHooks::tagend_over($attrs,$tf,$hooktype);
        FileHooks::tagend_showstruct($attrs,$tf,$hooktype);
        FileHooks::tagend_struct($attrs,$tf,$hooktype);
        FileHooks::tagend_bindas($attrs,$tf,$hooktype);
        FileHooks::tagend_bindwith($attrs,$tf,$hooktype);
        FileHooks::tagend_bindto($attrs,$tf,$hooktype);
        FileHooks::tagend_rewriteall($attrs,$tf,$hooktype);
        FileHooks::tagend_inner_waitforwrap($attrs,$tf,$hooktype);
        FileHooks::tagend_wrap($attrs,$tf,$hooktype);
        FileHooks::tagend_phpincmap($attrs,$tf,$hooktype);
        FileHooks::tagend_delheadfoot($attrs,$tf,$hooktype);
        FileHooks::tagend_headfoot($attrs,$tf,$hooktype);
        FileHooks::tagend_phplang($attrs,$tf,$hooktype);
        FileHooks::tagend_textmap($attrs,$tf,$hooktype);
        FileHooks::tagend_attrmap($attrs,$tf,$hooktype);
        FileHooks::tagend_bindmap($attrs,$tf,$hooktype);
        FileHooks::tagbegin_tf_init($attrs,$tf,$hooktype);
        FileHooks::tagbegin_safe($attrs,$tf,$hooktype);
        FileHooks::tagbegin_struct($attrs,$tf,$hooktype);
        FileHooks::tagbegin_showstruct($attrs,$tf,$hooktype);
        FileHooks::tagbegin_tochildren($attrs,$tf,$hooktype);
        FileHooks::tagbegin_inner_waitforwrap($attrs,$tf,$hooktype);
        FileHooks::tagbegin_selector($attrs,$tf,$hooktype);
        FileHooks::tagbegin_quick($attrs,$tf,$hooktype);
        FileHooks::tagbegin_bycookie($attrs,$tf,$hooktype);
        FileHooks::tagbegin_byvisible($attrs,$tf,$hooktype);
        FileHooks::tagbegin_toparent($attrs,$tf,$hooktype);
        FileHooks::tagbegin_byhref($attrs,$tf,$hooktype);
        FileHooks::tagbegin_inserttoparse($attrs,$tf,$hooktype);
        FileHooks::tagend_appendtoparse($attrs,$tf,$hooktype);
        FileHooks::tagbegin_pure($attrs,$tf,$hooktype);
        FileHooks::comment_ssi($comment,$tf,$hooktype);

        //    public static function all_quick_function($attrs, $tf, $hooktype)
    */
      
    }
}

