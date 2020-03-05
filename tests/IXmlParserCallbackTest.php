<?php 
namespace tests\TagFeather;
use TagFeather\IXmlParserCallback;

class IXmlParserCallbackTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(IXmlParserCallback::class);
        
        //code here
        
        \MyCodeCoverage::G()->end(IXmlParserCallback::class);
        $this->assertTrue(true);
        /*
        IXmlParserCallback::G()->asp_handle($str);
        IXmlParserCallback::G()->cdata_handle($str);
        IXmlParserCallback::G()->comment_handle($str);
        IXmlParserCallback::G()->error_handle($error_info);
        IXmlParserCallback::G()->notation_handle($str);
        IXmlParserCallback::G()->pi_handle($str);
        IXmlParserCallback::G()->tagbegin_handle($attrs);
        IXmlParserCallback::G()->tagend_handle($tagname);
        IXmlParserCallback::G()->text_handle($str);
        //*/
    }
}
