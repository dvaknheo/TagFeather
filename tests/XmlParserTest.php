<?php 
namespace tests\TagFeather;
use TagFeather\XmlParser;

class XmlParserTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(XmlParser::class);
        
        //code here
        
        //XmlParser::G()->ToAttrs($str,$match_byte);
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
}
class MyXmlParser extends XmlParser
{
    
}