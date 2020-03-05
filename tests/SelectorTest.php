<?php 
namespace tests\TagFeather;
use TagFeather\Selector;

class SelectorTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \MyCodeCoverage::G()->begin(Selector::class);
        
        //code here
        
        \MyCodeCoverage::G()->end(Selector::class);
        $this->assertTrue(true);
        /*
        Selector::G()->addSelectorHash($selector_string,$obj);
        Selector::G()->removeSelectorHash($selector_string);
        Selector::G()->objectsFromStack($stack);
        Selector::G()->objectsFromAttrs($stack,$attrs);
        Selector::G()->SelectorFromString($text);
        Selector::G()->MatchSelector($selector,$stack);
        Selector::G()->MatchSelectorNode($selector,$attrs);
        Selector::G()->SplitClass($class);
        //*/
    }
}
