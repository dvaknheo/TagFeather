<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace TagFeather;

class Helper
{
    const PI_BEGIN = "<\x3f";
    const PI_END = "\x3f>";
    const ASP_BEGIN = "<\x25";
    const ASP_END = "\x25>";
    const PHP_BEGIN = "<\x3fphp";
    const PHP_BEGIN_SHOW = "<\x3fphp echo";
    const PHP_ENDBLOCK = "<\x3fphp }\x3f>";
    /**
     *  Get the attibute bool value  incasetive
     *  NULL,'','no','false','off' ,'disabled' will be false ,other is true;
     *
     * @param string $str
     * @return bool
     */
    public static function GetBool($str)
    {
        if (!$str) {
            return false;
        }
        $str = strtolower($str);
        if (!$str || in_array($str, array('no','false','off','disabled'))) {
            return false;
        } else {
            return true;
        }
    }
    /**
     * split class by space include pi/asp
     * e.g "a" => array('a');"a b " => array('a','b') "a b < ? c ? >" => array('a','b','< ?c? >')
     *
     * @param string $class  class text
     * @return array
     */
    public static function SplitClass($class)
    {
        if (false === strpos($class, ' ')) {
            $rc = array($class);
            return $rc;
        }
        if (false === strpos($class, '<')) {
            return explode(' ', $class);
        } else {
            $pattern = '/\S+|<.*?>/s';
            preg_match_all($pattern, $class, $matches);
            return $matches[0];
        }
    }
    /*
     * check is a cssclass in a html class texts can include pi/asp
     * @param stirng $class
     * @param stirng $subclass
     */
    public static function MatchClass($class, $subclass)
    {
        return in_array($subclass, static::SplitClass($class));
    }
    /**
     *
     */
    public static function SliceCut($data, $str1, $str2)
    {
        $pos_begin = strpos($data, $str1);
        $pos_begin = $pos_begin + strlen($str1);
        $pos_end = strpos($data, $str2, $pos_begin);
        
        return substr($data, $pos_begin, $pos_end - $pos_begin);
    }
    /**
     *
     */
    public static function SliceReplace($data, $replacement, $str1, $str2, $is_outside = false, $wrap = false)
    {
        $pos_begin = strpos($data, $str1);
        $extlen = ($pos_begin === false)?0:strlen($str1);
        $pos_end = strpos($data, $str2, $pos_begin + $extlen);
        
        if ($pos_begin === false || $pos_end === false) {
            if (!$wrap) {
                return  $data;
            }
        }
        if ($is_outside) {
            $pos_begin = ($pos_begin === false)?0:$pos_begin;
            $pos_end = ($pos_end === false)?strlen($data):$pos_end + strlen($str2);
        } else {
            $pos_begin = ($pos_begin === false)?0:$pos_begin + strlen($str1);
            $pos_end = ($pos_end === false)?strlen($data):$pos_end;
        }
        
        return substr_replace($data, $replacement, $pos_begin, $pos_end - $pos_begin);
    }

    /**
     */
    public static function ToBlankAttrs($attrs)
    {
        $attrs = array('tf:notag' => true,'tf:notext' => true ,'tf:pretag' => '','tf:posttag' => '',
                'tf:pretext' => '','tf:posttext' => '',	);
        return $attrs;
    }
    /**
     */
    public static function ToHiddenAttrs($attrs)
    {
        $ext_attrs = array(
                'tf:notag' => true,'tf:notext' => true ,
                'tf:pretag' => '','tf:posttag' => '','tf:pretext' => '','tf:posttext' => '',
            );
        $attrs = array_merge($attrs, $ext_attrs);
        unset($attrs["\ntext"]);
        return $attrs;
    }
    /**
     */
    public static function MergeAttrs($attrs, $ext_attrs)
    {
        $ret = array_merge($attrs, $ext_attrs);

        if (array_key_exists('tf:pretag', $ret)) {
            $ret['tf:pretag'] = ($ext_attrs['tf:pretag']??'').($attrs['tf:pretag']??'');
        }
        if (array_key_exists('tf:pretext', $ret)) {
            $ret['tf:pretext'] = ($attrs['tf:pretext']??'').($ext_attrs['tf:pretext']??'');
        }
        if (array_key_exists('tf:posttext', $ret)) {
            $ret['tf:posttext'] = ($ext_attrs['tf::posttext']??'').($attrs['tf::posttext']??'');
        }
        if (array_key_exists('tf:posttag', $ret)) {
            $ret['tf:posttag'] = ($attrs['tf:posttag']??'').($ext_attrs['tf:posttag']??'');
        }
        
        if (array_key_exists('tf:lastfrag', $ret)) {
            $ret['tf:lastfrag'] = ($attrs['tf:lastfrag']??'')." ".($ext_attrs['tf:lastfrag']??'');
        }
        return $ret;
    }
    /**
     */
    public static function AttrsPrepareAssign($attrs, $extkey)
    {
        $extkeys = func_get_args();
        array_shift($extkeys);
        
        unset($attrs["\ntagname"]);
        unset($attrs["\ntf index"]);
        unset($attrs["\ntf children"]);
        foreach ($extkeys as $extkey) {
            unset($attrs[$extkey]);
        }
        return $attrs;
    }
    /**
     * to move
     */
    public static function DumpTagStackString($tagStack)
    {
        $l = sizeof($tagStack);
        if ($l == 1) {
            return '';
        }
        $str = '';
        foreach ($tagStack as $tag) {
            $id = '';
            $class = '';
            $index='';
            if (isset($tag['id'])) {
                $id = "#".$tag['id'];
            }
            if (isset($tag['class'])) {
                $class = ".".str_replace(' ', '.', $tag['class']);
                //$class=implode(".",self::SplitClass($tag['class']));
            }
            if (isset($tag["\ntagname"])) {
                $index = "<sup>".($tag["\ntf index"]??'')."</sup>";
            }
            $str .= "$index".($tag["\ntagname"]??'')."$id$class";
        }
        return $str;
    }

    /**
     *
     */
    public static function UniqString($id = '')
    {
        if (!$id) {
            $id = uniqid();
        }
        $id = md5($id);
        return 'TF_{'.$id.'}';
    }
    /**
     *
     */
    public static function GetTextMap($text)
    {
        $ret = array();
        $PIpattern = '<'.'\?(.*?)\?'.'>';
        $ASPpattern = '<'.'\%(.*?)\%'.'>';
        $p_attr = "/($PIpattern)|($ASPpattern)/s";
        preg_match_all("/(\S+)\s*(($PIpattern)|($ASPpattern)|\S+)/s", $text, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $key = trim($match[1]);
            $value = $match[2];
            $ret[$key] = $value;
        }
        return $ret;
    }
    /**
     *
     */
    public static function ToServerOrNormalAttrs($attrs, $only_server)
    {
        $flag = false;
        $keys = array_keys($attrs);
        foreach ($keys as $key) {
            if (0 == strncmp($key, "tf:", strlen("tf:"))) {
                $flag = true;
            } else {
                $flag = false;
            }
            if (!$only_server) {
                $flag = !$flag;
            }
            if ($flag) {
                unset($attrs[$key]);
            }
        }
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
