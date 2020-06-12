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
        echo "=============================\n";
$data=<<<EOT

<!doctype html>
<html>
<body>
<div style='border:1px solid red'>xxx</div>
</body>
</html>
EOT;
        echo (new Builder())->run($data);
        ///////////
        
$data=<<<EOT
<!doctype html>
<html>
<body>
<div id='z' style='border:1px solid red'>xxx</div>
</body>
</html>
EOT;
        $b=new Builder();
        $b->setCallback(function($hooktype, $args, $queque_mode = false,$tf){
            if($hooktype==='tagend' && isset($args['id']) && $args['id']==='z'){
                var_dump(get_class($tf));
                $tf->addTextToParse("abbbbbbbbbbbbbbbbbbbbbbb"); // todo
            }
            return $args;
        });
        echo $b->run($data);
        echo "--------------------------------------------------\n\n\n\n";
        
        ///////////
        
$data=<<<EOT
<!--#include virtual="/includes/header.html"-->
EOT;
        $b=new Builder();
        $b->setCallback(function($hooktype, $args, $queque_mode,$tf){
            var_dump($hooktype, $args, $queque_mode);
            return $args;
        });
        echo $b->run($data);
        
        \MyCodeCoverage::G()->end();
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
        Builder::G()->asp_frag_handle($str);
        Builder::G()->pi_frag_handle($str);
        Builder::G()->asp_handle($str);
        Builder::G()->cdata_handle($str);
        Builder::G()->comment_handle($str);
        Builder::G()->notation_handle($str);
        Builder::G()->pi_handle($str);
        Builder::G()->text_handle($str);
        
        

        Builder::G()->tagStack=[["\ntagname"=>'X']];
        $error_array = array(
            'source' => 'parser',
            //'file'=>$file,
            'line' => 'line',
            'type' => 'type',
            'info' => 'info',
            //'level'=>$level,
        );
        Builder::G()->error_handle($error_array);
        
        
        
        $tagname="zz";
        $attrs=[
            "\ntagname"=>$tagname,
        ];
        Builder::G(new Builder())->tagbegin_handle($attrs);
        Builder::G()->tagend_handle($tagname);
        /*
                Builder::G()->error_handle($error_info);
        Builder::G()->tagbegin_handle($attrs);
        Builder::G()->tagend_handle($tagname);
        
        //*/
    }
}
