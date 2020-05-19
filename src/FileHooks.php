<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace TagFeather;

use TagFeather\Helper;

class FileHooks
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
