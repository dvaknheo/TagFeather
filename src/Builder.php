<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace TagFeather;

use TagFeather\SingletonEx;

class Builder implements IXmlParserCallback
{
    use SingletonEx;

    public $runtime = [];

    public $tagStack = [
        ["\ntext" => ''],
    ];
    
    /** @var string data to parse */
    public $data = ''; //TODO 这也有一个地方用到

    protected $has_build = false;
    /** @var bool is error ?  */
    public $is_build_error = false;
    /** @var string error infomation  */
    public $build_error_msg = 'Build Error';

    public $parser = null;  //TODO to protected
    protected $callback = null;
    
    public function __construct()
    {
        //
    }
    public function run($data)
    {
        $this->data = $data;
        $this->data = $this->callHooksByType('prebuild', $this->data, true);
        
        $this->parser = new XmlParser();
        $this->parser->run($this->data,$this);
        
        $data = $this->tagStack[0]["\ntext"] ?? '';
        $data = $this->callHooksByType('postbuild', $data, false);    
        return $data;
    }
    public function setCallback($handler)
    {
        $this->callback = $handler;
    }
    public function addTextToParse($ext_data, $shift_line = false)
    {
        return $this->parser->insert_data($ext_data, $shift_line);
    }
    ///////////////////////////////////////////////////////////////////////////
    public function addLastTagText($str)
    {
        $i = sizeof($this->tagStack) - 1;
        $this->tagStack[$i]["\ntext"] =$this->tagStack[$i]["\ntext"] ??'';
        $this->tagStack[$i]["\ntext"] .= $str;
    }
    public function callHooksByType($hooktype, $arg, $queque_mode = false)
    {
        if (null === $this->callback) {
            return $arg;
        }
        return call_user_func($this->callback, $hooktype, $arg, $queque_mode, $this);
    }
    protected function errorFinalHandle($e)
    {
        if (!$e) {
            return [];
        }
        $error_msg =
            "Builder Parser Error: <br />\n".
            "Line: {$e['line']} <br />\n".
            "Type:{$e['type']}<br />\n".
            "Message:'".htmlspecialchars($e['info'])."\n".
            "Stack Dump:";
        
        $str = '';
        foreach ($this->tagStack as $tag) {
            $tagname=$tag["\ntagname"]??null;
            if ($tagname) {
                $index = "<sup>".($tag["\ntf index"]??'')."</sup>";
            }
            $str .= "/".$tagname;
        }
        $error_msg .= $str;
        $this->is_build_error = true;
        $this->build_error_msg = $error_msg;
        return [];
    }
    public function isSingleTag($tag)
    {
        if(!$this->parser){ return false; }
        return $this->parser->isSingleTag($tag);
    }
    protected function endTagFinalHandle($attrs)
    {
        
        //TODO 研究这里。
        //if( !array_key_exists("\ntagname",$attrs) ){
        //	$attrs["\ntagname"]=end($this->parser->tagnames);
        //}
        $keeptext = $this->isSingleTag($attrs["\ntagname"]??null);
        $text = static::TagToText($attrs, "\nfrag", $keeptext);
        $this->addLastTagText($text);
        return;
    }
    ///////////////////////////////////////////////////////////////////////////
    public function asp_frag_handle($str)
    {
        return $this->callHooksByType('asp', $str);
    }
    /**
     *  asp handle for TF_XMLParser
     *
     * @param string $str include < %
     * @return string if $to_returntext;
     */
    public function asp_handle($str)
    {
        $this->addLastTagText($str);
    }
    /**
     * cdata handle for TF_XMLParser
     *
     * @param string $str include < ![cdata[ ]] >
     */
    public function cdata_handle($str)
    {
        $str = $this->callHooksByType('cdata', $str);
        $this->addLastTagText($str);
    }
    /**
     * comment handle for TF_XMLParser
     * include < !-- -- >
     * @param string $str
     */
    public function comment_handle($str)
    {
        $str = $this->callHooksByType('comment', $str);
        $this->addLastTagText($str);
    }
    /**
     * notation handle for TF_XMLParser
     *
     * @param string $str include < !  >
     */
    public function notation_handle($str)
    {
        $str = $this->callHooksByType('notation', $str);
        $this->addLastTagText($str);
    }
    /**
     * error handle for TF_XMLParser
     *
     * @param int $line the error line
     * @param string $name the error name
     * @param string $info more info about this error;
     */
    public function error_handle($error_info)
    {
        $error_array = array(
            'source' => 'parser',
            //'file'=>$file,
            'line' => $error_info['line'],
            'type' => $error_info['type'],
            'info' => $error_info['info'],
            //'level'=>$level,
        );
        $error_array = $this->callHooksByType('error', $error_array);
        $this->errorFinalHandle($error_array);
    }
    public function pi_frag_handle($str)
    {
        return $this->callHooksByType('pi', $str);
    }
    /**
     *  pi handle for TF_XMLParser
     *
     * @param string $str include < ?
     * @param false $to_returntext is in attribute
     * @return string if $to_returntext;
     */
    public function pi_handle($str)
    {
        return $this->addLastTagText($str);
    }
    /**
     *  tagbegin handle for TF_XMLParser
     *
     * @param string $tagname
     * @param array $attrs the attributes
     */
    public function tagbegin_handle($attrs)
    {
        $this->tagStack[] = $this->callHooksByType('tagbegin', $attrs, true);
    }
    /**
     *  tagend handle for TF_XMLParser
     *
     * @param string $tagname
     */
    public function tagend_handle($tagname)
    {
        $lasttag = array_pop($this->tagStack);
        $tag = $this->callHooksByType('tagend', $lasttag);
        $this->endTagFinalHandle($tag);
    }

    /**
     * text handle for TF_XMLParser
     *
     * @param string $str string to parse
     */
    public function text_handle($str)
    {
        $str = $this->callHooksByType('text', $str);
        $this->addLastTagText($str);
    }
    ///////////////////////////////////////////////////////////////////////////
    /**
     *
     */
    public static function GetTagName($attrs)
    {
        return $attrs["\ntagname"] ?? null;
    }
    /**
     *
     */
    public static function SetTagText(&$attrs, $text)
    {
        $attrs["\ntext"] = $text;
    }
    /**
     *
     */
    public static function AddTagText(&$attrs, $text)
    {
        $attrs["\ntext"] .= $text;
    }
    /**
     *
     */
    public static function GetTagText($attrs)
    {
        return $attrs["\ntext"];
    }
    /**
     *
     */
    public static function ClearTagText(&$attrs)
    {
        unset($attrs["\ntext"]);
        return $attrs;
    }
    /**
     * Get Tag Text
     *
     * @param array $attrs server attributes
     * @param array $reserve_prefixs
     * @return string
     */
    public static function TagToText($attrs, $pre_frag = "\nfrag", $keeptext = true)
    {
        $tagname = $attrs["\ntagname"] ?? null;
        $ret = '';
        $text = $attrs["\ntext"] ?? '';
        if ($tagname) {
            $pre_frag_len = strlen($pre_frag);
            
            $ret = "<$tagname";
            $len = strlen($ret);
            $headdata = array();
            foreach ($attrs as $key => $value) {
                if (0 === strncmp($key, $pre_frag, $pre_frag_len)) {
                    $headdata[] = "$value";
                    continue;
                }
                if (substr($key, 0, 1) != "\n") {
                    $headdata[] = "$key=\"$value\"";
                }
            }
            if ($headdata) {
                //if(!is_array($headdata)){var_dump($headdata);die;}
                $ret .= " ".implode(" ", $headdata);
            }
            if (!$keeptext && $tagname && (null === $text || "" === $text)) {
                $ret .= " />";
            } else {
                $ret .= ">$text</$tagname>";
            }
        } else {
            $ret = $attrs["\ntext"] ?? '';
        }
        if (array_key_exists("\nposttag", $attrs)) {
            $ret = $ret.$attrs["\nposttag"];
        }
        if (array_key_exists("\npretag", $attrs)) {
            $ret = $attrs["\npretag"].$ret;
        }
        return $ret;
    }
}
