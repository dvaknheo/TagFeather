<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace TagFeather;

use TagFeather\Helper;

class Hooks
{
    /** if no default dest file , be the  basename $soure and end with.cache.php   */
    public static function modifier_filename($is_build, $tf, $hooktype)
    {
        if (!$tf->source) {
            $tf->source = basename($_SERVER['SCRIPT_FILENAME']);
        }
        
        $filename = $tf->dest;
        
        if (!$filename) {
            $filename = basename($tf->source);
        }
        if ($tf->is_outputphp) {
            $extname = array_pop(explode('.', $filename));
            if (in_array($extname, array('php','html','htm','shtml'))) {
                $filename = substr($filename, 0, -1 - strlen($extname));
            }
            $tf->dest = $filename.'.cache.php';
        } else {
            $tf->dest = $filename;
        }
        return false;
    }
    /** compare the dest file , check is need to build ,and set the source time */
    public static function modifier_time($is_build, $tf, $hooktype)
    {
        if ($tf->is_forcebuild) {
            return false;
        }
        $sourceTime = @filemtime($tf->template_dir.$tf->source);
        $maxtime = $sourceTime;
        
        foreach ($tf->struct_files as $filename) {
            $filename = $tf->get_abspath($filename);
            
            $theTime = filemtime($filename);
            if ($theTime > $maxtime) {
                $maxtime = $theTime;
            }
        }
        $sourceTime = $maxtime;
        
        $destTime = @filemtime($tf->cache_dir.$tf->dest);
        if (false === $destTime) {
            return false;
        }
        if ($destTime >= $sourceTime) {
            return true;
        }
        return false;
    }
    ///////////////////////////////////////////////////////////////////////////
    /** prepare for signature */
    public static function prebuild_signature($data, $tf, $hooktype)
    {
        $tf->runtime['initdata_md5'] = md5($data);
        $tf->runtime['source_time'] = @filemtime($tf->template_dir.$tf->source);
        $tf->runtime['begintime'] = microtime(true);
        return $data;
    }
    public static function prebuild_commitfirsttag($data, $tf, $hooktype)
    {
        if ("prebuild" == $hooktype) {
            $tf->hookmanager->add_parsehook(array("TF_Hooks","prebuild_commitfirsttag"), 'postbuild');
            $tf->tagStack[0] = array_merge($tf->tagStack[0], array("\ntf children" => 0,"\ntf index" => 0,'tf:notag' => 'yes'));
            return $data;
        }
        if ("postbuild" == $hooktype) {
            $firsttag = $tf->tagStack[0];
            $data = $firsttag['tf:pretag'].$firsttag['tf:pretext'].
                $data.
                $firsttag['tf:posttext'].$firsttag['tf:posttag'];
        }
        return $data;
    }
    /** insert  struct file at head */
    public static function prebuild_struct($data, $tf, $hooktype)
    {
        if (!$tf->struct_files) {
            return $data;
        }
        $sig = "TF:".substr(md5(Helper::UniqString()), 0, 4);
        $tf->runtime['structendsig'] = $sig;
        $str = '';
        foreach ($tf->struct_files as $filename) {
            $filename = htmlspecialchars($filename);
            $str .= "<tagfeather tf:struct=\"$filename\"></tagfeather>";
        }
        if ($tf->safetemplate_mode) {
            $tf->safetemplate_mode = false;
            $ext = "<tagfeather tf:safe='yes'/>";
            if ($tf->safeedit_mode) {
                $tf->safeedit_mode = false;
                $ext = "<safeblock tf:safe='yes' tf:safe_scriptsafe='yes' />";
            }
        }
        $str = "<$sig tf:notag='yes' tf:notext='yes' >".$str."<tagfeather  tf:struct='null' tf:struct_loaded='yes'> $ext</tagfeather></$sig>";
        $data = $str.$data;
        return $data;
    }
    ///////////////////////////////////////////////////////////////////////////
    /** for tf:bind */
    public static function postbuild_bind($data, $tf, $hooktype)
    {
        $data = str_replace(array_keys($tf->runtime['bind']), array_values($tf->runtime['bind']), $data);
        return $data;
    }
    public static function postbuild_ssidel($data, $tf, $hooktype)
    {
        if (!is_array($tf->runtime['ssidel'])) {
            return $data;
        }
        foreach ($tf->runtime['ssidel'] as $id) {
            $begin = Helper::UniqString($id)."_begin";
            $end = Helper::UniqString($id)."_end";
            $data = Helper::SliceReplace($data, '', $begin, $end, true, false);
        }
        return $data;
    }

    /** tags is not close or specail error */
    public static function postbuild_unmatch($data, $tf, $hooktype)
    {
        //if($tf->is_build_error)return $data;
        $l = sizeof($tf->tagStack);
        if ($l == 1) {
            return $data;
        }
        $tf->throw_error('TagUnclose', "tag_unmatch  level= $l");
        return $data;
    }
    /** add signature after postbuild*/
    public static function postbuild_signature($data, $tf, $hooktype)
    {
        if (!$tf->is_outputphp) {
            return $data;
        }
        $tf->runtime['endtime'] = microtime(true);
        $timecost = $tf->runtime['endtime'] - $tf->runtime['begintime'];
        
        $struct_timestring = '';
        foreach ($tf->struct_files as $filename) {
            $filename = $tf->get_abspath($filename);
            $theTime = filemtime($filename);
            $struct_timestring .= "//struct $filename Time ".date("Y-m-d H:i:s", $theTime).
                " MD5 ".md5(file_get_contents($filename))."\n";
        }
        
        $tf->runtime['pre_signature'] = "\n//Cache By TagFeather Version ".TAGFEATHER_VERSION."\n".
            "//source ".$tf->source." Time ".date("Y-m-d H:i:s", $tf->runtime['source_time']).
            " MD5 ".$tf->runtime['initdata_md5']."\n".
            "//dest   ".$tf->dest." Time ".date("Y-m-d H:i:s").
            " MD5 ".md5($tf->data)."\n".
            $struct_infostring.
            "//(MD5 no include this server block)\n".
            "//timecost:$timecost\n".$tf->runtime['pre_signature'];
        $data = "<\x3fphp ".$tf->runtime['pre_signature'].
            "if(!\$GLOBALS['TF_IN_CACHE']){exit('TagFeather:permission deny');}\n".$tf->runtime['post_signature'].
            " \x3f>".$data;
        return $data;
    }
    ///////////////////////////////////////////////////////////////////////////
    /** only a doctype can be output  */
    public static function notation_doctypeshowonce($str, $tf, $hooktype)
    {
        if ($tf->runtime['doctype_writed']) {
            return '';
        }
        $tf->runtime['doctype_writed'] = true;
        
        return $str;
    }
    ///////////////////////////////////////////////////////////////////////////
    /**  TagFeather::OutBegin TagFeather::OutEnd  is not run in cache */
    public static function pi_tf_outblock($data, $tf, $hooktype)
    {
        $search = array('TagFeather::OutBegin','TagFeather::OutEnd');
        $replace = array("if(!isset(\$GLOBALS['TF_IN_CACHE'])){\nTagFeather::OutBegin",
            "}//}if(!isset(\$GLOBALS['TF_IN_CACHE'])\nTagFeather::OutEnd");
        $data = str_replace($search, $replace, $data);
        return $data;
    }
    public static function pi_php_shorttag($data, $tf, $hooktype)
    {
        if (!$tf->is_outputphp) {
            return $data;
        }
        if (0 === strncmp($data, "<\x3fphp", strlen("<\x3fphp"))) {
            return $data;
        }
        if (0 === strncmp($data, "<\x3fxml", strlen("<\x3fxml"))) {
            return $data;
        }
        if (0 === strncmp($data, "<\x3f=", strlen("<\x3f="))) {
            $data = "<\x3fphp echo ".substr($data, strlen("<\x3f="));
            return $data;
        }
        if (0 === strncmp($data, "<\x3f", strlen("<\x3f"))) {
            $data = "<x3fphp ".substr($data, strlen("<\x3f"));
            return $data;
        }
        return $data;
    }
    public static function error_tagfeather($e, $tf, $hooktype)
    {
        //if(!$tf->is_build_error)return;
        $file = $tf->template_dir.$tf->source;
        $build_error_msg =
            "TagFeather Parser Error: <br />\n".
            "file: {$e['file']} <br />\n".
            "Line: {$e['line']} <br />\n".
            "Type:{$e['type']}<br />\n".
            "Message:'".htmlspecialchars($e['info'])."'<br />\n";
        
        $build_error_msg .= "Stack:".Helper::DumpTagStackString($tf->tagStack);
        $tf->is_build_error = true;
        $tf->build_error_msg = $build_error_msg;
        $tf->parser->data = ""; // to stop next paser;
        $tf->hookmanager->stop_nexthooks();
        return array();
    }
    
    public static function error_struct($e, $tf, $hooktype)
    {
        if (!$tf->in_struct) {
            return $e;
        }
        $flag = false;
        $l = sizeof($tf->tagStack);
        for ($i = $l - 1;$i >= 0;$i--) {
            $tag = &$tf->tagStack[$i];
            if ($tag['tf:struct']) {
                $flag = true;
                break;
            }
        }
        if (!$flag) {
            return $e;
        }
        
        $line = $tf->parser->current_line - $tag['tf:struct_currentline'] + 1;
        $e['file'] = $tag['tf:struct'];
        $e['line'] = $line;
        return $e;
    }
    
    ///////////////////////////////////////////////////////////////////////////
    /**
     * IMPRORTANT if a tag with same tf:showonce value ,when first show, do nothing .but if in second and more time ,it will be null tag.
     *
     * string tf:showonce indentify to show once.
     */
    public static function tagend_tf_final($attrs, $tf, $hooktype)
    {
        
        //echo htmlspecialchars($attrs['tf:pretag']);
        if (array_key_exists('tf:tagname', $attrs)) {
            $attrs["\ntagname"] = $attrs['tf:tagname'];
        }
        if (Helper::GetBool($attrs['tf:notag']??null)) {
            $attrs["\ntagname"] = '';
        }
        
        if (array_key_exists('tf:text', $attrs)) {
            $attrs["\ntext"] = $attrs['tf:text'];
        }
        if (Helper::GetBool($attrs['tf:notext']??null)) {
            $attrs["\ntext"] = '';
        }
        $attrs["\ntext"] = $attrs['tf:pretext'].$attrs["\ntext"].$attrs['tf:posttext'];
        
        if (array_key_exists('tf:pretag', $attrs)) {
            $attrs["\npretag"] = $attrs['tf:pretag'];
        }
        if (array_key_exists('tf:posttag', $attrs)) {
            $attrs["\nposttag"] = $attrs['tf:posttag'];
        }
        
        if (array_key_exists('tf:lastfrag', $attrs)) {
            $attrs["\nfrag zlast"] = $attrs["tf:lastfrag"];
            unset($attrs["tf:lastfrag"]);
        }

        if ($attrs["\ntagname"]) {
            $len = strlen("tf:");
            foreach ($attrs as $key => $value) {
                if (0 === strncmp($key, "tf:", $len)) {
                    unset($attrs[$key]);
                }
            }
        }
        //echo htmlspecialchars($attrs["\npretag"]);
        //echo "<br />";
        return $attrs;
    }

    public static function tagend_showonce($attrs, $tf, $hooktype)
    {
        if (!$attrs['tf:showonce']) {
            return $attrs;
        }
        //$tf->runtime['showonce']=isset($tf->runtime['showonce'])?$tf->runtime['showonce']:array();
        if (!in_array($attrs['tf:showonce'], $tf->runtime['showonce'])) {
            $tf->runtime['showonce'][] = $attrs['tf:showonce'];
        } else {
            if (Helper::GetBool($attrs['tf:showonce_trim']??null)) {
                $parent = &$tf->tagStack[sizeof($tf->tagStack) - 1];
                TF_Builder::SetTagText($parent, rtrim(TF_Builder::GetTagText($parent)));
            }
            $attrs = array();
            $tf->hookmanager->stop_nexthooks();
        }
        
        return $attrs;
    }
    public static function tagend_phpheredoc($attrs, $tf, $hooktype)
    {
        if (!Helper::GetBool($attrs['tf:phpheredoc']??null)) {
            return $attrs;
        }
        $eot = $attrs['tf:phpheredoc_str']?$attrs['tf:phpheredoc_str']:"eot";
        $attrs['tf:pretag'] = "<\x3fphp echo <<"."<$eot\n".$attrs['tf:pretag'];
        $attrs['tf:posttag'] = $attrs['tf:posttag']."\n$eot;\n\x3f>";
        return $attrs;
    }
    public static function tagend_meta_showonce($attrs, $tf, $hooktype)
    {
        if ($attrs["\ntagname"] != 'meta') {
            return $attrs;
        }
        if ($tf->runtime['meta_writed']) {
            return Helper::ToBlankAttrs($attrs);
        }
        $tf->runtime['meta_writed'] = true;
        return $attrs;
    }
    /**
     * delete specail the attribute by tf:delattr
     * string tf:delattrs  keys split by space
     * stirng tf:delattrs_class  class split by space delete only the class inside
     */
    public static function tagend_delattrs($attrs, $tf, $hooktype)
    {
        if (!array_key_exists('tf:delattrs', $attrs)) {
            return $attrs;
        }
        $value = $attrs['tf:delattrs'];
        $keys = explode(' ', $value);
        if (array_key_exists('tf:delattrs_class', $attrs)) {
            $use_class = true;
        }
        foreach ($keys as $key) {
            if ($use_class && $key == 'class') {
                $classes = Helper::SplitClass($attrs['class']);
                if (!$classes) {
                    continue;
                }
                //Helper::SplitClass($attrs['tf:delattrs_class']);
                $classes_del = explode(' ', $attrs['tf:delattrs_class']);
                $newclasses = array_diff($classes, $classes_del);
                if ($newclasses) {
                    $attrs['class'] = implode(' ', $newclasses);
                } else {
                    unset($attrs['class']);
                }
            } else {
                unset($attrs[$key]);
            }
        }
        
        return $attrs;
    }
    /** tf:rewrite */
    public static function tagend_rewrite($attrs, $tf, $hooktype)
    {
        if (!array_key_exists('tf:rewrite', $attrs)) {
            return $attrs;
        }
        
        $rewrite_attr = $attrs['tf:rewrite'];
        $newvalue = preg_replace($attrs['tf:rewrite_match'], $attrs['tf:rewrite_replace'], $attrs[$rewrite_attr]);
        if ($newvalue) {
            $attrs[$rewrite_attr] = $newvalue;
        }
        
        return $attrs;
    }
    /** tf:ove="yes" turn on ,tf:over:attrkey="value" => attrkey="value" */
    public static function tagend_over($attrs, $tf, $hooktype)
    {
        if (!array_key_exists('tf:over', $attrs)) {
            return $attrs;
        }
        $flag = Helper::GetBool($attrs['tf:over']??null);
        $keys = array_keys($attrs);
        foreach ($keys as $key) {
            if (0 == strncmp($key, "tf:over:", strlen("tf:over:"))) {
                if ($flag) {
                    $newkey = substr($key, strlen("tf:over:"));
                    $attrs[$newkey] = $attrs[$key];
                }
                unset($attrs[$key]);
            }
        }
        return $attrs;
    }
    public static function tagend_showstruct($attrs, $tf, $hooktype)
    {
        if (!Helper::GetBool($attrs['tf:showstruct']??null)) {
            return $attrs;
        }
        $l = sizeof($tf->tagStack);
        for ($i = $l - 1;$i >= 0;$i--) {
            $tag = &$tf->tagStack[$i];
            if ($tag['tf:struct']) {
                $text = TF_Builder::GetTagText($attrs);
                $tag['tf:posttag'] .= $text;
                break;
            }
        }
        return $attrs;
    }
    public static function tagend_struct($attrs, $tf, $hooktype)
    {
        if (!array_key_exists('tf:struct', $attrs)) {
            return $attrs;
        }
        $tf->parser->current_line = $tf->parser->current_line - $attrs['tf:struct_lines'];
        if ($tf->in_struct == sizeof($tf->tagStack)) {
            $tf->in_struct = 0;
        }
        if ($tf->parser->current_line < 1) {
            $tf->parser->current_line = 1;
        }
        return $attrs;
    }
    /************************/
    public static function tagend_bindas($attrs, $tf, $hooktype)
    {
        //$tf->runtime['bind']=isset($tf->runtime['bind'])?$tf->runtime['bind']:array();
        
        if (!array_key_exists('tf:bindas', $attrs)) {
            return $attrs;
        }
        
        $id = $attrs['tf:bindas'];
        $cmd = Helper::UniqString($id);
        if (!array_key_exists($cmd, $tf->runtime['bind'])) {
            $tf->runtime['bind'][$cmd] = '';
        }
        $mode = $attrs['tf:bindas_mode'];
        switch ($mode) {
        case 'pretag':
            $attrs['tf:pretag'] = $cmd.$attrs['tf:pretag'];
        break;
        case 'pretext':
            $attrs['tf:pretext'] = $attrs['tf:pretext'].$cmd;
        break;
        case 'posttext':
            $attrs['tf:posttext'] = $cmd.$attrs['tf:posttext'];
        break;
        case 'posttag':
            $attrs['tf:posttag'] = $attrs['tf:posttag'].$cmd;
        break;
        case 'text':
        default:
            TF_Builder::SetTagText($attrs, $cmd);//$attrs['parser text']=$cmd;
        }
        
        return $attrs;
    }
    public static function tagend_bindwith($attrs, $tf, $hooktype)
    {
        //$tf->runtime['bind']=isset($tf->runtime['bind'])?$tf->runtime['bind']:array();
        
        if (!Helper::GetBool($attrs['tf:bindwith']??null)) {
            return $attrs;
        }
        $id = '';
        $cmd = '';
        if (array_key_exists('tf:bindwith_text', $attrs)) {
            $id = $attrs['tf:bindwith_text'];
            $cmd = Helper::UniqString($id);
            if (!array_key_exists($cmd, $tf->runtime['bind'])) {
                $tf->runtime['bind'][$cmd] = '';
            }
            TF_Builder::SetTagText($attrs, $cmd);
        }
        if (array_key_exists('tf:bindwith_pretag', $attrs)) {
            $id = $attrs['tf:bindwith_pretag'];
            $cmd = Helper::UniqString($id);
            if (!array_key_exists($cmd, $tf->runtime['bind'])) {
                $tf->runtime['bind'][$cmd] = '';
            }
            $attrs['tf:pretag'] = $cmd.$attrs['tf:pretag'];
        }
        if (array_key_exists('tf:bindwith_pretext', $attrs)) {
            $id = $attrs['tf:bindwith_pretext'];
            $cmd = Helper::UniqString($id);
            if (!array_key_exists($cmd, $tf->runtime['bind'])) {
                $tf->runtime['bind'][$cmd] = '';
            }
            $attrs['tf:pretext'] = $attrs['tf:pretext'].$cmd;
        }
        if (array_key_exists('tf:bindwith_posttext', $attrs)) {
            $id = $attrs['tf:bindwith_posttext'];
            $cmd = Helper::UniqString($id);
            if (!array_key_exists($cmd, $tf->runtime['bind'])) {
                $tf->runtime['bind'][$cmd] = '';
            }
            $attrs['tf:posttext'] = $cmd.$attrs['tf:posttext'];
        }
        if (array_key_exists('tf:bindwith_posttag', $attrs)) {
            $id = $attrs['tf:bindwith_posttag'];
            $cmd = Helper::UniqString($id);
            if (!array_key_exists($cmd, $tf->runtime['bind'])) {
                $tf->runtime['bind'][$cmd] = '';
            }
            $attrs['tf:posttag'] = $attrs['tf:posttag'].$cmd;
        }
        
        return $attrs;
    }
    public static function tagend_bindto($attrs, $tf, $hooktype)
    {
        //$tf->runtime['bind']=isset($tf->runtime['bind'])?$tf->runtime['bind']:array();
        if (!array_key_exists('tf:bindto', $attrs)) {
            return $attrs;
        }
        
        $newattrs = $attrs;
        $newattrs = self::tagend_tf_final($newattrs, $tf, $hooktype);
        $keeptext = (!in_array($newattrs["\ntagname"], $tf->parser->single_tag))?true:false;
        $text = Builder::TagToText($newattrs, "\nfrag", $keeptext);
        
        $id = $attrs['tf:bindto'];
        $cmd = Helper::UniqString($id);
        $text = str_replace($cmd, "", $text);
        
        if (array_key_exists('tf:bindto_mode', $attrs)) {
            $mode = $attrs['tf:bindto_mode'];
            if ("insert" == $mode) {
                $text = $text.$tf->runtime['bind'][$cmd];
            }
            if ("append" == $mode) {
                $text = $tf->runtime['bind'][$cmd].$text;
            }
        }
        $tf->runtime['bind'][$cmd] = $text;
        $attrs = Helper::ToHiddenAttrs($attrs);
        return $attrs;
    }
    public static function tagend_rewriteall($attrs, $tf, $hooktype)
    {
        //$tf->runtime['rewriteall']=isset($tf->runtime['rewriteall'])?$tf->runtime['rewriteall']:array();
        
        if (array_key_exists('tf:rewriteall', $attrs)) {
            $tagname = $attrs['tf:rewriteall_tagname'];
            
            if (!$tagname) {
                $tagname = "*";
            }
            if (!array_key_exists($tagname, $tf->runtime['rewriteall'])) {
                $tf->runtime['rewriteall'][$tagname] = array();
            }
            $tf->runtime['rewriteall'][$tagname][] = array($attrs['tf:rewriteall'],$attrs['tf:rewriteall_match'],$attrs['tf:rewriteall_replace']);
            $attrs = Helper::ToHiddenAttrs($attrs);
        } else {
            foreach ($tf->runtime['rewriteall'] as $tagname => $rewrite_array) {
                if ("*" != $tagname && $tagname != $attrs["\ntagname"]) {
                    continue;
                }
                foreach ($rewrite_array as $rewrite_rule) {
                    list($key, $match, $replace) = $rewrite_rule;
                    if (!array_key_exists($key, $attrs)) {
                        continue;
                    }
                    $newvalue = preg_replace($match, $replace, $attrs[$key]);
                    $attrs[$key] = $newvalue;
                    //break;
                }
            }
        }
        
        return $attrs;
    }
    public static function tagend_inner_waitforwrap($attrs, $tf, $hooktype)
    {
        if (!(array_key_exists('tf:wrap_data', $attrs))) {
            return $attrs;
        }
        $extattrs = $attrs['tf:wrap_data'];
        unset($attrs['tf:wrap_data']);
        
        $attrs = array_merge($extattrs, $attrs);
        $thetext = TF_Builder::GetTagText($attrs);
        
        if (!(array_key_exists('tf:wrap_usetext', $attrs)) &&
            !(array_key_exists('tf:byvisible', $attrs)) && !(array_key_exists('tf:byhref', $attrs))) {
            TF_Builder::ClearTagText($attrs);
        } else {
            //$attrs['tf:text']=$text;
            //TF_Builder::ClearTagText($attrs);
        }

        unset($attrs['tf:wrap']);
        unset($attrs['tf:wrap_data']);
        unset($attrs['tf:wrap_mode']);
        unset($attrs['tf:wrap_usetext']);
    
        $keys_to_call = array(
            'tf:quick' => "TF_Hooks::tagbegin_quick",
            'tf:selector' => "TF_Hooks::tagbegin_selector",
            'tf:byhref' => "TF_Hooks::tagbegin_byhref",
            'tf:byvisible' => "TF_Hooks::tagbegin_byvisible",
            'tf:toparent' => "TF_Hooks::tagbegin_toparent",
            );
        $flag = false;
        foreach ($keys_to_call as $key => $hookname) {
            if (array_key_exists($key, $attrs)) {
                $callback = $tf->hookmanager->parsehooks['tagbegin'][$hookname];
                $attrs = call_user_func($callback, $attrs, $tf, 'tagend');
            }
        }
        return $attrs;
    }
    public static function tagend_wrap($attrs, $tf, $hooktype)
    {
        if (!array_key_exists('tf:wrap', $attrs)) {
            return $attrs;
        }
        
        
        $headers = array('pi' => "<\x3f",'asp' => "<\x25",'comment' => "<!--");
        $footers = array('pi' => "\x3f>",'asp' => "\x25>",'comment' => "-->");
        
        if ("none" == $attrs['tf:wrap']) {
            return $attrs;
        }
        $header = $headers[$attrs['tf:wrap']];
        if (!$header) {
            $header = $headers['pi'];
        }
        
        $footer = $footers[$attrs['tf:wrap']];
        if (!$footer) {
            $footer = $footers['pi'];
        }
        $mode = $attrs['tf:wrap_mode'];
        if (!$mode) {
            $mode = "outside";
        }
        switch ($mode) {
            case 'both':
                $pretag = true;
                $pretext = true;
                $posttext = true;
                $posttag = true;
            break;
            case 'outside':
                $pretag = true;
                $posttag = true;
            break;
            case 'inside':
                $pretext = true;
                $posttext = true;
            break;
            case 'pretag':
                $pretag = true;
            break;
            case 'pretext':
                $pretext = true;
            break;
            case 'posttext':
                $posttext = true;
            break;
            case 'posttag':
                $posttag = true;
            break;
            default:
            return $attrs;
        }
        
        if ($pretag) {
            $parent = &$tf->tagStack[sizeof($tf->tagStack) - 1];
            $text = TF_Builder::GetTagText($parent);//$text=$parent['parser text'];
            $pos = strrpos($text, $header);
            if (false !== $pos) {
                $cuthead = trim(substr($text, $pos));
                if ($footer == substr($cuthead, -strlen($footer))) {
                    TF_Builder::SetTagText($parent, substr($text, 0, $pos));//$parent['parser text']=substr($text,0,$pos);
                    $attrs['tf:pretag'] = $cuthead;
                }
            }
        }
        
        if ($posttag) {
            $text = $tf->parser->data;
            $pos = strpos($text, $footer);
            if (false !== $pos) {
                $pos = $pos + strlen($footer);
                $cutfoot = trim(substr($text, 0, $pos));
                if ($header == substr($cutfoot, 0, strlen($header))) {
                    //TODO  call ASP/PI HOOK ON $cutfoot;
                    if ($attrs['tf:wrap'] == 'pi') {
                        $cutfoot = $tf->hookmanager->call_parsehooksbytype('pi', $cutfoot, false);
                    }
                    if ($attrs['tf:wrap'] == 'asp') {
                        $cutfoot = $tf->hookmanager->call_parsehooksbytype('asp', $cutfoot, false);
                    }
                    if ($attrs['tf:wrap'] == 'comment') {
                        $cutfoot = $tf->hookmanager->call_parsehooksbytype('comment', $cutfoot, false);
                    }
                    //$parser->current_line=substr_count($text,"\n");
                    $tf->parser->data = substr($text, $pos);
                    $attrs['tf:posttag'] = $cutfoot;
                }
            }
        }
        
        if ($pretext) {
            $text = TF_Builder::GetTagText($attrs);
            $pos = strpos($text, $footer);
            if (false !== $pos) {
                $pos = $pos + strlen($footer);
                $cuthead = trim(substr($text, 0, $pos));
                if ($footer == substr($cuthead, -strlen($footer))) {
                    TF_Builder::SetTagText($attrs, substr($text, $pos));
                    $attrs['tf:pretext'] = $cuthead;
                }
            }
        }
        
        if ($posttext) {
            $text = TF_Builder::GetTagText($attrs);//	$text=$attrs['parser text'];
            $pos = strrpos($text, $header);
            if (false !== $pos) {
                $cutfoot = trim(substr($text, $pos));
                if ($footer == substr($cutfoot, -strlen($footer))) {
                    TF_Builder::SetTagText($attrs, substr($text, 0, $pos));//	$attrs['parser text']=substr($text,0,$pos);
                    $attrs['tf:posttext'] = $cutfoot;
                }
            }
        }
        
        return $attrs;
    }
    public static function tagend_phpincmap($attrs, $tf, $hooktype)
    {
        if (!array_key_exists('tf:phpincmap', $attrs)) {
            return $attrs;
        }
        $tf->runtime['phpincmap'] = isset($tf->runtime['phpincmap'])?$tf->runtime['phpincmap']:array();
        list($htmlfile, $phpfile) = explode(" ", $attrs['tf:phpincmap']);
        $tf->runtime['phpincmap'][$htmlfile] = $phpfile;
        return $attrs;
    }
    public static function tagend_delheadfoot($attrs, $tf, $hooktype)
    {
        if (!(array_key_exists('tf:delhead', $attrs)) && !(array_key_exists('tf:delfoot', $attrs))) {
            return $attrs;
        }
        if ($tf->in_struct) {
            return $attrs;
        }

        if (Helper::GetBool($attrs['tf:delhead']??null)) {
            $id = uniqid();
            $tf->runtime['ssidel'][] = $id;
            $tf->tagStack[0]['tf:pretext'] = Helper::UniqString($id)."_begin".$tf->tagStack[0]['tf:pretext'];
            $attrs['tf:pretag'] = Helper::UniqString($id)."_end".$attrs['tf:pretag'];
        }
        if (Helper::GetBool($attrs['tf:delfoot']??null)) {
            $id = uniqid();
            $tf->runtime['ssidel'][] = $id;
            $tf->tagStack[0]['tf:posttext'] = Helper::UniqString($id)."_end".$tf->tagStack[0]['tf:posttext'];
            $attrs['tf:posttag'] = $attrs['tf:posttag'].Helper::UniqString($id)."_begin";
        }
        return $attrs;
    }
    public static function tagend_headfoot($attrs, $tf, $hooktype)
    {
        if (!(array_key_exists('tf:head', $attrs)) && !(array_key_exists('tf:foot', $attrs))) {
            return $attrs;
        }
        if ($attrs['tf:head']) {
            $filename = $attrs['tf:head'];
            $filename = $tf->get_abspath($filename, true);
            $data = ($filename)?file_get_contents($filename):'';
            $id = uniqid();
            $tf->runtime['ssidel'][] = $id;
            
            $tf->tagStack[0]['tf:pretext'] = Helper::UniqString($id)."_begin".$tf->tagStack[0]['tf:pretext'];
            if ($tf->in_struct) {
                $sig = $tf->runtime['structendsig'];
                $sig = "</$sig>";
                $pos = strpos($tf->parser->data, $sig);
                
                if (false !== $pos) {
                    $tf->parser->data = substr_replace($tf->parser->data, $data.Helper::UniqString($id)."_end", $pos + strlen($sig), 0);
                } else {
                    $tf->parser->data = Helper::UniqString($id)."_end".$data.$tf->parser->data;
                }
            } else {
                $tf->parser->data = Helper::UniqString($id)."_end".$data.$tf->parser->data;
            }
        }
        if ($attrs['tf:foot']) {
            $filename = $attrs['tf:foot'];
            $filename = $tf->get_abspath($filename, true);
            $data = ($filename)?file_get_contents($filename):'';
            $id = uniqid();
            $tf->runtime['ssidel'][] = $id;
            $tf->tagStack[0]['tf:posttext'] = Helper::UniqString($id)."_end".$tf->tagStack[0]['tf:posttext'];
            
            $tf->parser->data = $tf->parser->data.Helper::UniqString($id)."_begin".$data;
        }
        return $attrs;
    }
    /** tf:lang */
    public static function tagend_phplang($attrs, $tf, $hooktype)
    {
        if (!array_key_exists('tf:phplang', $attrs)) {
            return $attrs;
        }
        $_php_lang = Helper::GetPhp($attrs['tf:phplang']);
        $_php_lang = trim($_php_lang);
        if (substr($_php_lang, 0, 1) == '=') {
            $_php_lang = substr($_php_lang, 1);
        }
        $evalstr = "return $_php_lang ;";
        
        $filename = $attrs['tf:phplang_file'];
        
        if ($filename) {
            @include $filename;
            $lang = eval($evalstr);
        } else {
            $lang = eval($evalstr);
            if (!$lang) {
                $evalstr = "global " . $_php_lang . ' ;return '.$_php_lang.' ;';
                $lang = eval($evalstr);
            }
        }
        
        if (!is_array($lang)) {
            return $attrs;
        }
        
        $left = $attrs['tf:phplang_left'];
        $right = $attrs['tf:phplang_right'];
        if ($left || $right) {
            $new_lang = array();
            foreach ($lang as $key => $value) {
                $new_lang[$left.$key.$right] = $value;
            }
            $lang = $new_lang;
        }
        //$tf->runtime['lang']=isset($tf->runtime['lang'])?$tf->runtime['lang']:array();
        $tf->runtime['phplang'] = array_merge($tf->runtime['phplang'], $lang);
        
        return Helper::ToHiddenAttrs($attrs);
    }
    public static function tagend_textmap($attrs, $tf, $hooktype)
    {
        if (!Helper::GetBool($attrs['tf:textmap']??null)) {
            return $attrs;
        }
        $text = TF_Builder::GetTagText($attrs);
        if (array_key_exists('tf:textmap_key', $attrs)) {
            $key = $attrs['tf:textmap_key'];
            $value = trim($text);
            $array = array($key => $value);
        }
        $array = Helper::GetTextMap($text);
        $tf->runtime['textmap'] = array_merge($tf->runtime['textmap'], $array);
        
        return Helper::ToBlankAttrs($attrs);
    }
    public static function tagend_attrmap($attrs, $tf, $hooktype)
    {
        $flag = array_key_exists('tf:attrmap', $attrs);
        if (!flag && !$tf->runtime['attrmap']) {
            return $attrs;
        }
        if ($flag) {
            $text = TF_Builder::GetTagText($attrs);
            if (array_key_exists('tf:attrmap_key', $attrs)) {
                $key = $attrs['tf:attrmap_key'];
                $value = trim($text);
                $array = array($key => $value);
            }
            $array = Helper::GetTextMap($text);
            $keytomap = $attrs['tf:attrmap'];
            if (!is_array($tf->runtime['attrmap'][$attrtomap])) {
                $tf->runtime['attrmap'][$keytomap] = array();
            }
            $tf->runtime['attrmap'][$keytomap] = array_merge($tf->runtime['attrmap'][$keytomap], $array);
            return Helper::ToBlankAttrs($attrs);
        } else {
            foreach ($tf->runtime['attrmap'] as $keytomap => $array) {
                if (!array_key_exists($keytomap, $attrs)) {
                    continue;
                }
                foreach ($array as $key => $value) {
                    if ($attrs[$keytomap] == $key) {
                        $attrs[$keytomap] = $value;
                    }
                }
            }
            return $attrs;
        }
    }
    
    public static function tagend_bindmap($attrs, $tf, $hooktype)
    {
        if (!Helper::GetBool($attrs['tf:bindmap']??null)) {
            return $attrs;
        }
        $text = TF_Builder::GetTagText($attrs);
        if (array_key_exists('tf:bindmap_key', $attrs)) {
            $key = $attrs['tf:bindmap_key'];
            $value = trim($text);
            $array = array($key => $value);
        }
        $array = Helper::GetTextMap($text);
        foreach ($array as $key => $value) {
            $array[$key] = Helper::UniqString($value);
        }
        $tf->runtime['textmap'] = array_merge($tf->runtime['textmap'], $array);
        
        return Helper::ToBlankAttrs($attrs);
    }
    ///////////////////////////////////////////////////////////////////////////

    public static function tagbegin_tf_init($attrs, $tf, $hooktype)
    {
        //$attrs["\ntagname"]=end($tf->parser->tagnames);
        $parent = &$tf->tagStack[sizeof($tf->tagStack) - 1];
        $parent["\ntf children"]++;
        $attrs["\ntf index"] = $parent["\ntf children"];
        return $attrs;
    }
    public static function tagbegin_safe($attrs, $tf, $hooktype)
    {
        if (Helper::GetBool($attrs['tf:safe']??null)) {
            $tf->safetemplate_mode = true;
            if (Helper::GetBool($attrs['tf:safe_safeedit'])) {
                $tf->safeedit_mode = true;
            }
            $data = $tf->parser->data;
            $pos = false;
            if ($tf->in_struct) {
                $sig = $tf->runtime['structendsig'];
                $sig = "</$sig>";
                $pos = strpos($data, $sig);
            }
            if (false !== $pos) {
                $headata = substr($data, 0, $pos);
                $data = substr($data, $pos);
            }
            if ($tf->safeedit_mode) {
                $data = str_replace("<!--#", "<!-- #");
            }
            $data = preg_replace('/<'.'\?(.*?)\?'.'>/a', '', $data);
            $data = preg_replace('/<'.'\%(.*?)\%'.'>/s', '', $data);
            $pi_pos = strpos($data, "<\x3f");
            if (false !== $pi_pos) {
                $data = substr($data, 0, $pi_pos);
            }
            $asp_pos = strpos($data, "<\x25");
            if (false !== $asp_pos) {
                $data = substr($data, 0, $asp_pos);
            }
            if ($headdata) {
                $data = $headdata.$data;
            }
            $tf->parser->data = $data;
            return $attrs;
        }
        

        if ($tf->safetemplate_mode && !$tf->in_struct) {
            if ($tf->safeedit_mode) {
                $deny_tagname = explode(" ", "iframe script frame");
                if (in_array($deny_tagname, strtolower($attrs["\ntagname"]))) {
                    return Helper::ToBlankAttrs($attrs);
                }
            }
            $keys = array_keys($attrs);
            foreach ($keys as $key) {
                if (0 == strncmp($key, "tf:", strlen("tf:"))) {
                    unset($attrs[$key]);
                }
                if ($tf->safeedit_mode && "on" == strtolower(substr($key, 0, 2))) {
                    unset($attrs[$key]);
                }
            }
        }
        return $attrs;
    }
    /** tf:struct tf:struct */
    public static function tagbegin_struct($attrs, $tf, $hooktype)
    {
        if (!$attrs['tf:struct']) {
            return $attrs;
        }

        if ($tf->in_struct == 0) {
            $tf->in_struct = sizeof($tf->tagStack);
        } else {
            if (sizeof($tf->tagStack) < $tf->in_struct) {
                $tf->in_struct = sizeof($tf->tagStack);
            }
        }
        if (Helper::GetBool($attrs['tf:struct_loaded']??null)) {
            return $attrs;
        }
        $attrs['tf:struct_loaded'] = "yes";
        if ($tf->parser->is_current_notext) {
            $attrs['tf:notag'] = "yes";
            $attrs['tf:notext'] = "yes";
            $data = '<tagfeather tf:struct="'.$attrs['tf:struct'].'" ></tagfeather>';
            return $attrs;
        }
        $filename = $attrs['tf:struct'];
        $filename = $tf->get_abspath($filename, true);
        $data = ($filename)?file_get_contents($filename):'';

        $tf->addTextToParse($data);
        $attrs['tf:struct_lines'] = substr_count($data, "\n");
        $attrs['tf:struct_currentline'] = $tf->parser->current_line;
        
        $attrs['tf:notag'] = "yes";
        $attrs['tf:notext'] = "yes";
        
        return $attrs;
    }
    public static function tagbegin_showstruct($attrs, $tf, $hooktype)
    {
        if (!Helper::GetBool($attrs['tf:showstruct']??null)) {
            return $attrs;
        }
        $attrs['tf:pure'] = "yes";
        if (Helper::GetBool($attrs['tf:showstruct_noserver'])) {
            $attrs['tf:pure_noserver'] = "yes";
        }
        return $attrs;
    }
    /** tf:tochildren */
    public static function tagbegin_tochildren($attrs, $tf, $hooktype)
    {
        $parent = end($tf->tagStack);
        if (array_key_exists('tf:tochildren', $parent) && is_array($parent['tf:tochildren']) && !Helper::GetBool($attrs['tf:tochildren_ignore'])) {
            $attrs = Helper::MergeAttrs($attrs, $parent['tf:tochildren']);
        }
        if (array_key_exists('tf:tochildren', $attrs)) {
            if (!Helper::GetBool($attrs['tf:tochildren'])) {
                unset($attrs['tf:tochildren']);
                return $attrs;
            }
            $obj_assign = Helper::AttrsPrepareAssign(
                $attrs,
                'tf:tochildren',
                'tf:tochildren_showtag',
                'tf:tochildren_showtext',
                'tf:tochildren_onlyserver'
            );
            $onlyserver = Helper::GetBool($attrs['tf:tochildren_onlyserver']);
            if ($onlyserver) {
                $obj_assign = Helper::ToServerOrNormalAttrs($obj_assign, true);
            }
            $show_tag = Helper::GetBool($attrs['tf:tochildren_showtag']);
            $show_text = Helper::GetBool($attrs['tf:tochildren_showtext']);
            
            $attrs = Helper::ToBlankAttrs($attrs);
            $attrs['tf:tochildren'] = $obj_assign;
            if ($show_tag) {
                unset($attrs['tf:notag']);
            }
            if ($show_text) {
                unset($attrs['tf:notext']);
            }
            return $attrs;
        }

        return $attrs;
    }

    public static function tagbegin_inner_waitforwrap($attrs, $tf, $hooktype)
    {
        if (!(array_key_exists('tf:wrap', $attrs))) {
            return $attrs;
        }
        $wrap = $attrs['tf:wrap'];
        $mode = $attrs['tf:wrap_mode'];
        $keys_to_call = array(
            'tf:quick' => "TF_Hooks::tagbegin_quick",
            'tf:selector' => "TF_Hooks::tagbegin_selector",
            'tf:byhref' => "TF_Hooks::tagbegin_byhref",
            'tf:byvisible' => "TF_Hooks::tagbegin_byvisible",
            'tf:toparent' => "TF_Hooks::tagbegin_toparent",
            );
        $flag = false;
        foreach ($keys_to_call as $key => $hookname) {
            if (array_key_exists($key, $attrs)) {
                $flag = true;
                break;
            }
        };
        if (!$flag) {
            return $attrs;
        }
        $extattrs = array();
        foreach ($attrs as $key => $value) {
            if (0 == strncmp($key, "tf:", strlen("tf:"))) {
                $extattrs[$key] = $value;
                unset($attrs[$key]);
            }
        }
        $attrs['tf:wrap_data'] = $extattrs;
        $attrs['tf:wrap'] = $wrap;
        $attrs['tf:wrap_mode'] = $mode;
        return $attrs;
    }
    /**
     * if have "tf:selector" attribute , set other attributes to specificed CSS selector by tf:selector;
     * the CSS selector is specificed by TF_Selector;
     * if not tf:selector , merge the attributes to compact tag;
     */
    public static function tagbegin_selector($attrs, $tf, $hooktype)
    {
        if (!array_key_exists('tf:selector', $attrs)) {
            $matches = $tf->selector->objectsFromAttrs($tf->tagStack, $attrs);
            foreach ($matches as $match) {
                $attrs = Helper::MergeAttrs($attrs, $match);
            }
            return $attrs;
        }
        $assign_name = $name = $attrs['tf:selector'];
        $obj_assign = Helper::AttrsPrepareAssign($attrs, 'tf:selector');
        
        $tf->selector->addSelectorHash($assign_name, $obj_assign);
        return Helper::ToBlankAttrs($attrs);
    }
    /** tf:quick */
    public static function tagbegin_quick($attrs, $tf, $hooktype)
    {
        $flag = Helper::GetBool($attrs['tf:quick']??false);
        if (!$flag) {
            return $attrs;
        }
        
        $assign_name = '';
        if ($attrs['id']) {
            $assign_name = "#".$attrs['id'];
        } elseif ($attrs['name']) {
            $assign_name = "@".$attrs['name'];
        } elseif ($attrs['class']) {
            $csses = Helper::SplitClass($attrs['class']);
            foreach ($csses as $css) {
                if ('<' == substr($css, 0, 1)) {
                    continue;
                }
                $assign_name = ".".$css;
            }
        }
        if ($attrs['tf:quick_child']) {
            $assign_name .= "^".$attrs['tf:quick_child'];
        }
        $obj_assign = Helper::AttrsPrepareAssign($attrs, 'tf:quick', 'tf:quick_child');
        
        $tf->selector->addSelectorHash($assign_name, $obj_assign);
        
        return Helper::ToBlankAttrs($attrs);
    }
    /** assign by visible */
    public static function tagbegin_bycookie($attrs, $tf, $hooktype)
    {
        if (!array_key_exists('tf:bycookie', $attrs)) {
            return $attrs;
        }
        
        if ("tagbegin" == $hooktype) {
            if (!$tf->runtime['tagbegin_bycookie']) {
                $tf->hookmanager->add_parsehook(array("TF_Hooks","tagbegin_bycookie"), 'tagend');
                $tf->runctime['tagbegin_bycookie'] = true;
            }
            return $attrs;
        }
        if ("tagend" != $hooktype) {
            return $attrs;
        }
        
        $obj_assign = Helper::AttrsPrepareAssign($attrs, 'tf:bycookie');
        $key = $attrs['tf:bycookie'];//trim(TF_Builder::GetTagText($attrs));
        $tf->runtime['bycookie'][$key] = $obj_assign;
        return $attrs;
    }
    public static function tagbegin_byvisible($attrs, $tf, $hooktype)
    {
        if ("tagbegin" == $hooktype) {
            if (!$tf->runtime['tagbegin_byvisible']) {
                $tf->hookmanager->add_parsehook(array("TF_Hooks","tagbegin_byvisible"), 'tagend');
                $tf->runctime['tagbegin_byvisible'] = true;
            }
            return $attrs;
        }
        if ("tagend" != $hooktype) {
            return $attrs;
        }
        
        if (array_key_exists('tf:byvisible', $attrs)) {
            $obj_assign = Helper::AttrsPrepareAssign($attrs, 'tf:byvisible', 'tf:byvisible_reverse', 'tf:byvisible_keeptext');
            $is_keeptext = Helper::GetBool($attrs['tf:byvisible_keeptext']);
            if (!Helper::GetBool($attrs['tf:byvisible_reverse'])) {
                if (array_key_exists('value', $attrs)) {
                    $key = $attrs['value'];
                    if (!$is_keeptext) {
                        $obj_assign['value'] = $attrs['tf:byvisible'];
                    } else {
                        unset($obj_assign['value']);
                    }
                } else {
                    $key = TF_Builder::GetTagText($attrs);//$key=$attrs['parser text'];
                    if (!$is_keeptext) {
                        TF_Builder::SetTagText($obj_assign, $attrs['tf:byvisible']);//$obj_assign['parser text']=$attrs['tf:byvisible'];
                    } else {
                        TF_Builder::ClearTagText($obj_assign);//unset($obj_assign['parser text']);
                    }
                }
            } else {
                $key = $attrs['tf:byvisible'];
                if (array_key_exists('value', $attrs)) {
                    if (!$is_keeptext) {
                        $obj_assign['value'] = $attrs['value'];
                    } else {
                        unset($obj_assign['value']);
                    }
                } else {
                    if (!$is_keeptext) {
                        $obj_assgin = TF_Builder::SetTagText($obj_assign, TF_Builder::GetTagText($attrs));//$obj_assign['parser text']=$attrs['parser text'];
                    } else {
                        $obj_assign = TF_Builder::ClearTagText($obj_assign);//unset($obj_assign['parser text']);
                    }
                }
            }
            
            $tf->runtime['byvisible'][$key] = $obj_assign;
            return Helper::ToBlankAttrs($attrs);
        } else {
            if ('input' == $attrs["\ntagname"]) {
                $text = $attrs['value'];
            } else {
                $text = trim(TF_Builder::GetTagText($attrs));//$text=trim($attrs['parser text']);
            }
            if (array_key_exists($text, $tf->runtime['byvisible'])) {
                $attrs = Helper::MergeAttrs($attrs, $tf->runtime['byvisible'][$text]);
            }
        }
        
        return $attrs;
    }
    /** assign to same href attribute  */
    public static function tagbegin_toparent($attrs, $tf, $hooktype)
    {
        if ("tagbegin" == $hooktype) {
            if (!$tf->runtime['tagbegin_toparent']) {
                $tf->hookmanager->add_parsehook(array("TF_Hooks","tagbegin_toparent"), 'tagend');
                $tf->runctime['tagbegin_toparent'] = true;
            }
            return $attrs;
        }
        if ("tagend" != $hooktype) {
            return $attrs;
        }

        if (array_key_exists('tf:toparent', $attrs) && $attrs['tf:toparent']) {
            $level = -abs(0 + $attrs['tf:toparent_level']);
            if ($level == 0) {
                $level = -1;
            }
            $obj_assign = Helper::AttrsPrepareAssign($attrs, 'tf:toparent', 'tf:toparent_level', 'tf:toparent_onlyserver');
            $onlyserver = Helper::GetBool($attrs['tf:toparent_onlyserver']);
            if ($onlyserver) {
                $obj_assign = Helper::ToServerOrNormalAttrs($obj_assign, true);
            }
            TF_Builder::ClearTagText($obj_assgin);//unset($obj_assign['parser text']);
            $obj = &$tf->tagStack[sizeof($tf->tagStack) + $level];
            
            if (array_key_exists('tf:toparent_id', $obj)) {
                $ids = explode(" ", $obj['tf:toparent_id']);
            } else {
                $ids = array();
            }
            
            $id = $attrs['tf:toparent'];
            if (!in_array($id, $ids)) {
                $obj = array_merge($obj, $obj_assign);
                $ids[] = $id;
                $obj['tf:toparent_id'] = implode(" ", $ids);
            }
            return Helper::ToBlankAttrs($attrs);
        }
        return $attrs;
    }	/** assign to same href attribute  */
    public static function tagbegin_byhref($attrs, $tf, $hooktype)
    {
        if (!$attrs['href']) {
            return $attrs;
        }
        if ("tagbegin" == $hooktype) {
            if (!$tf->runtime['tagbegin_byhref']) {
                $tf->hookmanager->add_parsehook(array("TF_Hooks","tagbegin_byhref"), 'tagend');
                $tf->runctime['tagbegin_byhref'] = true;
            }
            return $attrs;
        }
        if ("tagend" != $hooktype) {
            return $attrs;
        }
        //$tf->runtime['byhref']=isset($tf->runtime['byhref'])?$tf->runtime['byhref']:array();
        if (array_key_exists('tf:byhref', $attrs) && Helper::GetBool($attrs['tf:byhref'])) {
            $obj_assign = Helper::AttrsPrepareAssign($attrs, 'tf:byhref', 'tf:byhref_echo', 'tf:byhref_reverse');
            TF_Builder::ClearTagText($obj_assign);//unset($obj_assign['parser text']);
            $text = trim(TF_Builder::GetTagText($attrs));//$text=trim($attrs['parser text']);
            if (!Helper::GetBool($attrs['tf:byhref_reverse'])) {
                $key = $text;
            } else {
                $key = $obj_assign['href'];
                $obj_assign['href'] = $text;
            }
            $tf->runtime['byhref'][$key] = $obj_assign;
            return Helper::ToBlankAttrs($attrs);
        } else {
            //do :assign
            $mappedhref = $tf->runtime['byhref'][$attrs['href']];
            
            if ($mappedhref) {
                if (Helper::GetBool($attrs['tf:byhref_echo'])) {
                    $mappedhref = "<\x3fphp echo \"$mappedhref\"\x3f>";
                }
                $attrs = Helper::MergeAttrs($attrs, $mappedhref);
            }
        }
        return $attrs;
    }
    public static function tagbegin_inserttoparse($attrs, $tf, $hooktype)
    {
        if (!array_key_exists('tf:inserttoparse', $attrs)) {
            return $attrs;
        }
        static::InsertDataAndFile($tf, $attrs['tf:inserttoparse'], $attrs['tf:inserttoparse_file']);
        return $attrs;
    }
        /**
     *
     */
    protected static function InsertDataAndFile($tf, $data, $filename)
    {
        if ($filename) {
            $filename = $tf->get_abspath($filename, true);
            $data = file_get_contents($filename);
        } else {
            $tf->parser->current_line -= substr_count($data, "\n");
        }
        $tf->parser->insert_data($data);
    }
    public static function tagend_appendtoparse($attrs, $tf, $hooktype)
    {
        if (!array_key_exists('tf:appendtoparse', $attrs)) {
            return $attrs;
        }
        static::InsertDataAndFile($tf, $attrs['tf:appendtoparse'], $attrs['tf:appendtoparse_file']);
        return $attrs;
    }
    public static function tagbegin_pure($attrs, $tf, $hooktype)
    {
        if ($hooktype != "tagbegin" && $hooktype != "tagend") {
            return $attrs;
        }
        $purehooktypes = array("tagbegin","tagend","text","comment","pi","asp","cdata","notation");
        if ($hooktype == "tagend") {
            if ($tf->runtime['in_pure'] != sizeof($tf->tagStack)) {
                if (!array_key_exists("\ntagname", $attrs)) {
                    $attrs["\ntagname"] = end($tf->parser->tagnames);
                }
                $showserver = $tf->runtime['in_pure_showserver'];
                if (!$showserver) {
                    $attrs = Helper::ToServerOrNormalAttrs($attrs, false);
                }
                $keeptext = (!in_array($attrs["\ntagname"], $tf->parser->single_tag))?true:false;
                $text = Builder::TagToText($attrs, "\nfrag", $keeptext);
                $tf->addLastTagText($text);
                $attrs = array();
                return $attrs;
            } else {
                unset($tf->runtime['in_pure']);
                unset($tf->runtime['in_pure_showserver']);
                unset($attrs['tf:pure']);
                foreach ($purehooktypes as $thehooktype) {
                    $tf->hookmanager->parsehooks[$thehooktype] = $tf->runtime['pure_savedhooktypes'][$thehooktype];
                }
                //TODO
                $attrs = $tf->hookmanager->call_parsehooksbytype($hooktype, $attrs, false);
                
                $keeptext = (!in_array($attrs["\ntagname"], $tf->parser->single_tag))?true:false;
                $text = Builder::TagToText($attrs, "\nfrag", $keeptext);
                $tf->addLastTagText($text);
                
                $tf->hookmanager->stop_nexthooks();
                $attrs = array();
                return $attrs;
            }
        }
        if ($hooktype == "tagbegin") {
            if (array_key_exists('tf:pure', $attrs)) {
                $tf->runtime['in_pure_showserver'] = !Helper::GetBool($attrs['tf:pure_noserver']);
                $level = 0 + $tf->runtime['in_pure'];
                $current_level = sizeof($tf->tagStack);
                if ($level == 0 || $current_level < $level) {
                    $tf->runtime['in_pure'] = $current_level;
                    $tf->runtime['pure_savedhooktypes'] = array();
                    foreach ($purehooktypes as $thehooktype) {
                        $tf->runtime['pure_savedhooktypes'][$thehooktype] = $tf->hookmanager->parsehooks[$thehooktype];
                        $tf->hookmanager->parsehooks[$thehooktype] = array("TF_Hooks::tagbegin_pure" => array("TF_Hooks" ,"tagbegin_pure"));
                    }
                    //$tf->hookmanager->stop_nexthooks();
                }
            }
        }
        return $attrs;
    }
    ///////////////////////////////////////////////////////////////////////////
    /** < !--# to ssi*/
    public static function comment_ssi($comment, $tf, $hooktype)
    {
        if ($tf->safeedit_mode && $tf->in_struct < sizeof($tf->tagStack)) {
            return $comment;
        }
        $flag = preg_match('/^<!--#(\w+)(.*)/', $comment, $match);
        if (!$flag) {
            return $comment;
        } else {
            $attrs = TF_XmlParser::ToAttrs($match[2], $match_byte,null, 0);
            $attrs['#'] = $match[1];
        }
        
        $attrs = $tf->hookmanager->call_parsehooksbytype('ssi', $attrs);
        if (!$attrs) {
            // ssi effected;
            $tf->hookmanager->stop_nexthooks();
            return '';
        }
        return $comment;
    }

    /** */
    public static function ssi_include($attrs, $tf, $hooktype)
    {
        if ('include' != $attrs['#']) {
            return $attrs;
        }
        $htmlfile = $attrs['file'];
        if (!isset($tf->runtime['phpincmap'])) {
            return $attrs;
        }
        
        if (!array_key_exists($htmlfile, $tf->runtime['phpincmap'])) {
            return $attrs;
        }
        $phpfile = $tf->runtime['phpincmap'][$htmlfile];
        $tf->addTextToParse("<\x3fphp include(\"".addslashes($phpfile)."\"); \x3f>");
        $tf->hookmanager->stop_nexthooks();
        return array();
    }
    /** ssi:noparsebegin ssi:noparsebegin ssi:noparseend */
    public static function ssi_noparse($attrs, $tf, $hooktype)
    {
        if ('noparseend' == $attrs['#']) {
            return array();
        }
        if ('noparsebegin' != $attrs['#']) {
            return $attrs;
        }
        $data = $tf->parser->data;
        $pos = strpos($data, "<!--#noparseend");
        if (false === $pos) {
            return $attrs;
        }
        
        $text = substr($data, 0, $pos);
        $pos = strpos($data, "-->", $pos);
        if (false === $pos) {
            return $attrs;
        }
        $text = substr($data, 0, $pos);
        $tf->parser->data = substr($data, $pos + strlen("-->"));
        $tf->parser->current_line += substr_count($text, "\n");
        return array();
    }
    /** ssi:appendtoparse */
    public static function ssi_appendtoparse($attrs, $tf, $hooktype)
    {
        if ('appendtoparse' != $attrs['#']) {
            return $attrs;
        }
        static::InsertDataAndFile($tf, $attrs['data'], $attrs['file']);
        return array();
    }
    /** ssi:appendtotextbegin ssi:appendtotextend */
    public static function ssi_appendtotext($attrs, $tf, $hooktype)
    {
        if ('appendtotextend' == $attrs['#']) {
            return array();
        }
        if ('appendtotextbegin' != $attrs['#']) {
            return $attrs;
        }
        $data = $tf->parser->data;
        $pos = strpos($data, "<!--#appendtotextend");
        if (false == $pos) {
            return $attrs;
        }
        
        $text = substr($data, 0, $pos);
        $pos = strpos($data, "-->", $pos);
        if (false == $pos) {
            return $attrs;
        }
        $tf->parser->data = substr($data, $pos + strlen("-->"));
        $tf->addLastTagText($text);
        $tf->parser->current_line += substr_count($text, "\n");
        return array();
    }
    /** ssi:delbegin  */
    public static function ssi_delbegin($attrs, $tf, $hooktype)
    {
        if ('delbegin' != $attrs['#']) {
            return $attrs;
        }
        $id = $attrs['id'];
        if (!$id) {
            $id = uniqid();
            $tf->runtime['ssidel_lastid'] = $id;
        };
        $tf->runtime['ssidel'][] = $id;
        $str = Helper::UniqString($id)."_begin";
        $tf->addTextToParse($str);
        return array();
    }
    /** ssi:delend */
    public static function ssi_delend($attrs, $tf, $hooktype)
    {
        if ('delend' != $attrs['#']) {
            return $attrs;
        }
        $id = $attrs['id'];
        if (!$id) {
            $id = $tf->runtime['ssidel_lastid'];
        };
        $str = Helper::UniqString($id)."_end";
        $tf->addTextToParse($str);
        return array();
    }
    /** ssi:tagbegin */
    public static function ssi_tagbegin($attrs, $tf, $hooktype)
    {
        if ('tagbegin' != $attrs['#']) {
            return $attrs;
        }
        unset($attrs['#']);
        $tf->tagStack[] = $tf->hookmanager->call_parsehooksbytype('tagbegin', $attrs, true);
        
        return array();
    }
    /** ssi:tagend */
    public static function ssi_tagend($attrs, $tf, $hooktype)
    {
        if ('tagend' != $attrs['#']) {
            return $attrs;
        }
        
        $newattrs = array_pop($tf->tagStack);
        $newattrs = $tf->hookmanager->call_parsehooksbytype('tagend', $newattrs, false);
        
        $keeptext = (!in_array($newattrs["\ntagname"], $tf->parser->single_tag))?true:false;
        $text = TF_Builder::TagToText($newattrs, "\nfrag", $keeptext);
        $tf->addLastTagText($text);
        return array();
    }
    /** ssi:tag */
    public static function ssi_tag($attrs, $tf, $hooktype)
    {
        if ('tag' != $attrs['#']) {
            return $attrs;
        }
        $attrs['#'] = 'tagbegin';
        self::ssi_tagbegin($attrs, $tf, $hooktype);
        $attrs['#'] = 'tagend';
        self::ssi_tagend($attrs, $tf, $hooktype);
        
        return array();
    }
    ///////////////////////////////////////////////////////////////////////////
    public static function text_phplang($text, $tf, $hooktype)
    {
        //$tf->runtime['lang']=isset($tf->runtime['lang'])?$tf->runtime['lang']:array();
        $lang = $tf->runtime['phplang'];
        $text = str_replace(array_keys($lang), array_values($lang), $text);
        return $text;
    }
    public static function text_textmap($text, $tf, $hooktype)
    {
        $textmap = $tf->runtime['textmap'];
        if ($textmap) {
            $text = str_replace(array_keys($textmap), array_values($textmap), $text);
        }
        return $text;
    }
    public static function text_bycookie($text, $tf, $hooktype)
    {
        if (!$text) {
            return $text;
        }
        $flag = false;
        $str = '';
        foreach ($tf->runtime['bycookie'] as $key => $obj_assign) {
            if (false !== strpos($text, $key)) {
                if (array_key_exists('tf:bycookie_replace', $obj_assign)) {
                    $flag = true;
                    $str = $obj_assign['tf:bycookie_replace'];
                    unset($obj_assign['tf:bycookie_replace']);
                }
                $parent = &$tf->tagStack[sizeof($tf->tagStack) - 1];
                $parent = Helper::MergeAttrs($parent, $obj_assign);
                if ($flag) {
                    $flag = false;
                    $text = substr_replace($text, $str, strlen($key));
                }
            }
        }
        return $text;
    }
    /**
     * if there is callable ,call the handle function such in
     * {tf|tf_tagbegin|tftagend}_{tagname|id|name|class|php}_{the_value}
     * OR tf_{modifier|prebuild|postbuild|pi|asp}_default;
     * tagname : the tagname of the attributes,
     * id : the attribute id
     * name : the attribute name
     * class : the class of the attribute, if included . such as class="a b" call tf_class_b
     * php  : if there has a attribute tf:the_value so call such as tf_php_thevalue
     * these handle is call by order ,but ,I hope you don't do too nest;
     */
    public static function all_quick_function($attrs, $tf, $hooktype)
    {
        $param = $attrs;
        $func = "tf_".$hooktype."_default";
        if (function_exists($func)) {
            $attrs = $func($param, $tf, $hooktype);
        }
        
        switch ($hooktype) {
        case 'ssi':
            $param = $attrs;
            $func = "tf_".$hooktype."_".$attrs['#'];
            if (function_exists($func)) {
                return $func($param, $tf, $hoktype);
            }
        break;
        case 'tagbegin':
        case 'tagend':
            if (!is_array($attrs)) {
                return $attrs;
            }
            $attrs = static::call_tagblock_hook("tagname_".$attrs["\ntagname"], $attrs, $tf, $hooktype);
            $attrs = static::call_tagblock_hook("id_".$attrs['id'], $attrs, $tf, $hooktype);
            $attrs = static::call_tagblock_hook("name_".$attrs['name'], $attrs, $tf, $hooktype);
            
            $csses = Helper::SplitClass($attrs['class']);
            foreach ($csses as $css) {
                $attrs = static::call_tagblock_hook("class_".$css, $attrs, $tf, $hooktype);
            }
            foreach ($attrs as $key => $value) {
                $pos = strpos($key, ':');
                if (false === $pos) {
                    continue;
                }
                $prekey = substr($key, 0, $pos);
                if ($prekey == 'php') {
                    $mainkey = substr($key, $pos + 1);
                    $attrs = static::call_tagblock_hook("php_".$mainkey, $attrs, $tf, $hooktype);
                }
            }
            return $attrs;
        break;
        }
        return $attrs;
    }
    /**
     * call tf_{$str_func} at tagbegin and tagend;
     * call tfbegin_{$str_func} at tagbegin;
     * call tfend_{$str_func} at tagend;
     *
     * @param $str_func
     * @param $attrs
     * @param $tf TagFeather Object
     * @param $type  'tagbegin'/'tagend'
     * @return array new $attrs;
     */
    protected static function call_tagblock_hook($str_func, $attrs, $tf, $type)
    {
        if (is_callable('tf_'.$str_func)) {
            $rc = call_user_func('tf_'.$str_func, $attrs, $tf, $type);
        } elseif ($type == 'tagbegin' && is_callable('tf_tagbegin_'.$str_func)) {
            $rc = call_user_func('tf_tagbegin_'.$str_func, $attrs, $tf, $type);
        } elseif ($type == 'tagend' && is_callable('tf_tagend_'.$str_func)) {
            $rc = call_user_func('tf_tagend_'.$str_func, $attrs, $tf, $type);
        } else {
            $rc = $attrs;
        }
        return $rc;
    }
}
