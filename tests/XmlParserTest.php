<?php 
namespace tests\TagFeather;
use TagFeather\XmlParser;

class XmlParserTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(XmlParser::class);
        
        $this->do_testattrs();
        
        $str='text<!--notation--><tag><a b="c"><d /><br></a></tag><%asp%><?pi ?>annother<';;
        XmlParser::G()->insert_data($str,true);
        XmlParser::G()->parse();
        
        
        try{
            $str='<?xml';;
            XmlParser::G(new XmlParser())->insert_data($str);
            
            XmlParser::G()->parse();
        }catch(\Throwable $ex){
            //var_dump($ex);
        }
        try{
            $str='<%asp';;
            XmlParser::G(new XmlParser())->insert_data($str);
            
            XmlParser::G()->parse();
        }catch(\Throwable $ex){
            //var_dump($ex);
        }
        
        $str='<![CDATA[ xxx ]]]><!doctype html><!fadfd';;
        XmlParser::G(new XmlParser())->insert_data($str);
        XmlParser::G()->parse();
        
        
        try{
            $str='<a x=<??> b="a<% afsaf %>b" <?=ok?>></a>';;
            XmlParser::G(new XmlParser())->insert_data($str);
            
            XmlParser::G()->parse();
        }catch(\Throwable $ex){
            var_dump($ex);
        }
                $str='<br a="zza<%asp %>bbbb" <?php echo "OK"?>>';;
        XmlParser::G(new XmlParser())->insert_data($str);
        XmlParser::G()->parse();
        $this->do_script();
        
        
        try{
            
            $str='<a>zzz</b>';;
            XmlParser::G(new XmlParser())->insert_data($str);
            XmlParser::G()->parse();
        }catch(\Throwable $ex){
            //var_dump($ex);
        }
        
        
        $str='<br href="ab<?c?>" >';
        XmlParser::G(new XmlParser())->insert_data($str);
        XmlParser::G()->stop_parse_serverfrag=true;
        XmlParser::G()->parse();
        
        try{
        $str='<tag fadf fadfds sdfaf';
        XmlParser::G(new XmlParser())->insert_data($str);
        
        XmlParser::G()->parse();
        }catch(\Throwable $ex){
            //var_dump($ex);
        }
        try{
        $str='<>';
        XmlParser::G(new XmlParser())->insert_data($str);
        
        XmlParser::G()->parse();
        }catch(\Throwable $ex){
            //var_dump($ex);
        }
        
         try{
        $str='</!-->';
        XmlParser::G(new XmlParser())->insert_data($str);
        
        XmlParser::G()->parse();
        }catch(\Throwable $ex){
            //var_dump($ex);
        }
        //XmlParser::G()->insert_data("abc",3);
        
        ////
        echo "--------------------------------\n";
        $str='<script>fafsdf';
        XmlParser::G(new XmlParser())->insert_data($str);
        XmlParser::G()->handle=new Handler();
        XmlParser::G()->parse();
       
        \MyCodeCoverage::G()->end(XmlParser::class);
        $this->assertTrue(true);
    }
    protected function do_testattrs()
    {
        $match_type=0;
        $str='b="c" class="<?=$x ?>">';
        $a=XmlParser::ToAttrs($str,$match_byte);
        
        $str=' d=e>';
        $a=XmlParser::ToAttrs($str,$match_byte);
        
        $str='<?=$x ?>>';
        $a=XmlParser::ToAttrs($str,$match_byte);
    }
    protected function do_script()
    {
        try{
            $str='<script></script>';;
            XmlParser::G(new XmlParser())->insert_data($str);
            XmlParser::G()->parse();
            
            $str='<script>aa<?echo OK ?>bb<% aa%>cc</script>';;
            XmlParser::G(new XmlParser())->insert_data($str);
            XmlParser::G()->parse();
        }catch(\Throwable $ex){
            //var_dump($ex);
        }
        try{
            $str='<script>fafsdf';
            XmlParser::G(new XmlParser())->insert_data($str);
            XmlParser::G()->parse();
        }catch(\Throwable $ex){
            //var_dump($ex);
        }
        try{
            $str='<script>fafsd<? f</script>';
            XmlParser::G(new XmlParser())->insert_data($str);
            XmlParser::G()->parse();
        }catch(\Throwable $ex){
            //var_dump($ex);
        }
    }
}
class Handler
{
    public function tagbegin_handle($data)
    {
        return $data;
    }
    public function error_handle($data)
    {
        var_dump($data);
    }
}