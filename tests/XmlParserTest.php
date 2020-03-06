<?php 
namespace tests\TagFeather;
use TagFeather\XmlParser;

class XmlParserTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(XmlParser::class);
        
        $this->do_testattrs();
        
        $str='text<!--notation--><tag></tag><%asp%><?pi ?>annother<';;
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
        
        $str='<![CDATA[';;
        XmlParser::G(new XmlParser())->insert_data($str);
        XmlParser::G()->parse();
        
        //XmlParser::G()->insert_data("abc",3);
        
        ////
        
do{
        //XmlParser::G()->__construct($handle);
break;
        
        XmlParser::G()->call($handle,$arg);
        XmlParser::G()->insert_data($ext_data,$shift_line=false);
        XmlParser::G()->error_handle($line,$type,$info);
        XmlParser::G()->parse();
        XmlParser::G()->parse_text();
        XmlParser::G()->parse_tagend();
        XmlParser::G()->parse_notation();
        XmlParser::G()->parse_pi();
        XmlParser::G()->parse_asp();
        XmlParser::G()->parse_tag();
        XmlParser::G()->parse_script();
        XmlParser::G()->parse_serverattr($attrs);
        XmlParser::G()->parse_serverfrag($str);
        XmlParser::G()->parse_serverfrag_callback($match);
        XmlParser::G()->is_matchtagname($lasttagname);
        XmlParser::G()->error_info($info);
        XmlParser::G()->preg_splitdata($pattern,$text);
}while(false);
        
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
}
class MyXmlParser extends XmlParser
{
    
}