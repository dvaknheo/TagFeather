<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace TagFeather;

class TagFeather extends Builder
{
    ////public $feathers=array();		//$key=>$value; //no used TO Regard init version;
    /** @var string source file to build . */
    public $source = '';
    /** @var string dest file unde $cache_dir to output */
    public $dest = '';
    /** @var string cache directory  */
    public $cache_dir = 'cache/';
    /** @var string work directory */
    public $template_dir = '';
    /** @var bool  use in prebuild_safe */
    public $safetemplate_mode = false;
    /** @var bool  use in prebuild_safe */
    public $safeedit_mode = false;
    /** @var bool  use in prebuild_safe */
    public $safebuild_mode = false;
    /** @var bool */
    public $debug_mode = false;
    /** @var bool is allways build; */
    public $is_forcebuild = false;

    /** @var bool outputphp? */
    public $is_outputphp = true;
    /** @var array struct files , $file=>$is_earnable */
    public $struct_files = array();
    /** @var int instruct*/
    public $in_struct = 0;
    //$tf->runtime['structendsig']
    /** @var int random seed s*/
    public $seed = 0;
    
    /** @var TF_Builder builder*/
    public $builder = null;
    /** @var TF_HookMangager hookmanager*/
    public $hookmanager = null;
    /** @var TF_Parser parser link to builder->parser */
    public $parser = null;
    /** @var TF_Builder builder*/
    public $seletor = null;
    
    ///////////////////////////////////////////////////////////////////////////
    /** Set the source .By default,it is the SCRIPT_FILENAME (by modifier_filename)
     * @param string $filename
     * @return void
     */
    public function set_source($filename)
    {
        $this->source = $filename;
    }
    /**
     * Set the destfile to write ,By default,it is the basename of source end with .cache.php
     * e.g.  source: index.php  =>  dest : index.cache.php
     * @param string $filename
     * @return void
     */
    public function set_dest($filename)
    {
        $this->dest = $filename;
    }
    /**
     * Set the cache directory
     * @param string $cache_dir
     * @return void
     */
    public function set_cache_dir($cache_dir)
    {
        if (!$cache_dir) {
            $this->cache_dir = '';
            return;
        }
        $cache_dir = rtrim($cache_dir, '/').'/';
        $this->cache_dir = $cache_dir;
    }
    /**
     * Set the template directory
     * @param string $template_dir
     * @return void
     */
    public function set_template_dir($template_dir)
    {
        if (!$template_dir) {
            $this->template_dir = '';
            return;
        }
        $template_dir = rtrim($template_dir, '/').'/';
        $this->template_dir = $template_dir;
    }
    /**
     * Set that if ignore judge filetime just build , usually in debug,or prototype develop
     * @param bool $force
     * @return void
     */
    public function forcebuild($force = true)
    {
        $this->is_forcebuild = $force;
    }
    /**
     * check is build
     */
    public function is_build()
    {
        return $this->has_build;
    }
    ///////////////////////////////////////////////////////////////////////////
    /** add struct file to build
     * @param string $filename structfilename
     */
    public function add_struct($filename)
    {
        $this->struct_files[] = $filename;
    }
    /**
     * Build the file from $source to $dest
     * call  the "modifier" hooks
     * it set_include_path in build time;
     * if no builded ,input the soure to build and output to dest.

     * @return string the final absolute dest filename to you use the include()
     */
    public function build_file()
    {
        if ($this->builder->has_build) {
            return $this->cache_dir.$this->dest;
        }
        
        $this->builder->has_build = $this->hookmanager->call_parsehooksbytype('modifier', $this->has_build);
        if ($this->builder->is_build_error) {
            return "";
        }
        //REMARK The cache dir and the dest mybe change in modifier hooks;
        if ($this->builder->has_build) {
            return $this->cache_dir.$this->dest;
        }
        
        $this->builder->data = file_get_contents($this->template_dir.$this->source);
        $this->builder->build(); // this method extends from TF_Compiler;
        if ($this->builder->is_build_error) {
            return "";
        }
        $handle = @fopen($this->cache_dir.$this->dest, 'wb');
        if (!$handle) {
            $e = array('source' => __CLASS__ ,'type' => 'WriteFileFailed','line' => '0','info' => $this->cache_dir.$this->dest);
            $this->hookmanager->call_parsehooksbytype('error', $e);
            return "";
        }
        flock($handle, LOCK_EX);
        fwrite($handle, $this->builder->data);
        flock($handle, LOCK_UN);
        fclose($handle);
        
        return $this->cache_dir.$this->dest;
    }
    /**
     *
     */
    public function get_abspath($filename, $encode = false)
    {
        if ($encode) {
            $filename = html_entity_decode($filename);
        }
        if ($this->safebuild_mode) {
            $thefile = $this->template_dir.basename($filename);
            if (!is_file($thefile)) {
                return '';
            } //  ../x.php
            if (!file_exists($thefile)) {
                return '';
            }
            return  $thefile;
        }
        $flag = preg_match('/^(\/|\\|([A-Za-z]:))/', $filename, $match);
        if (!$flag) {
            $filename = $this->template_dir.$filename;
        }
        return $filename;
    }
    /**
     *
     */
    public function throw_error($type, $info)
    {
        $e = array('source' => __CLASS__ ,'type' => $type,
            'line' => $this->parser->current_line,'info' => $info);
        $this->hookmanager->call_parsehooksbytype('error', $e);
        return;
    }
    /** Constructor */
    public function __construct()
    {
        parent::__construct();
        
        $this->builder = $this;
        $this->parser = $this->builder->parser;
        $this->hookmanager = new TF_HookManager();
        $this->selector = new TF_Selector();
        
        $ext_parsehooks = array(
            'unreg' => array(),
            'error' => array(),
            
            'prebuild' => array(),
            'postbuild' => array(),
            'tagbegin' => array(),
            'tagend' => array(),
            
            'text' => array()	,
            'asp' => array(),
            'pi' => array(),
            'comment' => array(),
            'notation' => array(),
            'cdata' => array(),
        );
        $this->hookmanager->parsehooks = $ext_parsehooks;
        
        $this->builder->builder_callback = array(&$this->hookmanager,'call_parsehooksbytype');
        $this->hookmanager->manager_callback = $this;
        $this->hookmanager->parsehooks['modifier'] = array();
        $this->hookmanager->parsehooks['ssi'] = array();
        
        $this->initHooks();
        $this->seed = mt_rand(0, 9999);
    }
    /** Destructor */
    public function __destruct()
    {
        parent::__destruct();
        
        $this->builder = null;
        $this->parser = null;
        $this->handle = null;
        $this->hookmanager = null;
    }
    /**
     * init hooks;
     */
    protected function initHooks()
    {
        $build_hooks = array(
            'modifier_time',
            'modifier_filename',
            
            'error_tagfeather',
            'error_struct',
            
            'prebuild_signature',
            'prebuild_commitfirsttag',
            'prebuild_struct',
            
            'postbuild_signature',
            'postbuild_unmatch',
            'postbuild_ssidel',
            'postbuild_bind',
            
            'notation_doctypeshowonce',
            'pi_tf_outblock',
            'pi_php_shortag',
            'comment_ssi',
            
            'tagbegin_tf_init',
            'tagbegin_safe',
            'tagbegin_struct',
            'tagbegin_showstruct',
            'tagbegin_tochildren',
            'tagbegin_inner_waitforwrap',
            'tagbegin_quick',
            'tagbegin_selector',
            'tagbegin_toparent',
            'tagbegin_byhref',
            'tagbegin_byvisible',
            'tagbegin_bycookie',
            'tagbegin_pure',
            'tagbegin_inserttoparse',
            
            'tagend_tf_final',
            'tagend_meta_once',
            'tagend_delheadfoot',
            'tagend_headfoot',
            'tagend_delattrs',
            'tagend_rewrite',
            'tagend_over',
            
            'tagend_showstruct',
            'tagend_struct',
            
            'tagend_bindas',
            'tagend_bindwith',
            'tagend_bindto',
            'tagend_rewriteall',
            'tagend_appendtoparse',
            
            'tagend_showonce',
            
            'tagend_phpheredoc',
            'tagend_phpincmap',
            'tagend_phplang',
            
            'tagend_inner_waitforwrap',
            'tagend_wrap',
            'tagend_bindmap',
            'tagend_textmap',
            'tagend_attrmap',
            
            
            'text_phplang',	//TODO be a hook object.
            'text_textmap',
            'text_bycookie',
            
            'ssi_noparse',
            'ssi_appendtoparse',
            'ssi_appendtotext',
            'ssi_delbegin',
            'ssi_delend',
            'ssi_tagbegin',
            'ssi_tagend',
            'ssi_tag',
            'ssi_include',
            );
        $this->_reg($build_hooks);
        
        foreach ($this->hookmanager->parsehooks as $hooktype => $blank) {
            $this->hookmanager->add_parsehook(array('TF_Hooks','all_quick_function'), $hooktype);
        }
        //////////////////////////
        $quick = array(
        'ssidel' => array(),
        'showonce' => array(),
        'rewriteall' => array(),
        'byhref' => array(),
        'byvisible' => array(),
        'bycookie' => array(),
        'bind' => array(),
        'phplang' => array(),
        'textmap' => array(),
        'attrmap' => array(),
        );
        $this->runtime = array_merge($this->runtime, $quick);
        
        //$headfoot=new TF_HeadFootHook();
        //$this->reg_hookobject($headfoot);
        
        //$autowrap= new TF_AutowrapHook();
        //$this->reg_hookobject($autowrap);
    }
    /**
     * get  builded template file.
     * use as :
     *   include $tagfeather->getTemplateFile($file);
     *
     * @param string $filename the filename to include;
     */
    public function getTemplateFile($source, $ignore_in_cache = false, $is_forcebuild = false)
    {
        if (!$ignore_in_cache && $GLOBALS['TF_IN_CACHE']) {
            return false;
        }
        //extract($GLOBALS);
        $GLOBALS['TF_IN_CACHE'] = true;
        $this->source = $source;
        if ($is_forcebuild) {
            $this->forcebuild();
        }
        return $this->build_file();
    }
    /**
    */
    public function display($filename = "", $structfile = "")
    {
        if ($GLOBALS['TF_IN_CACHE']) {
            return;
        }
        if ($structfile) {
            $this->add_struct($structfile);
        }
        $filename = $this->getTemplateFile($filename, false, $is_forcebuild);
        if (!$this->is_build_error) {
            extract($GLOBALS);
            include $filename;
        }
        exit;
    }
    /**
     * include template filename and exit;
     *
     * @param string $filename the filename to include;
     * @param string $structfile the structfile,or call config file
     * @param bool $is_forcebuild
     * @param TagFeather $tf TagFeather Object ,by default null , will auto create one;
     */
    public static function DisplayAndExit($filename = '', $structfile = '', $is_forcebuild = false, $tf = null)
    {
        if ($GLOBALS['TF_IN_CACHE']) {
            return;
        }
        if (!$tf) {
            $tf = new TagFeather();
        }
        if ($structfile) {
            if (is_array($structfile)) {
                foreach ($structfile as $file) {
                    $tf->add_struct($file);
                }
            } else {
                $tf->add_struct($structfile);
            }
        }
        
        $filename = $tf->getTemplateFile($filename, false, $is_forcebuild);
        if (!$tf->is_build_error) {
            extract($GLOBALS);
            include $filename;
        }
        exit;
    }
    ///////////////////////////////////////////////////////////////////////////
    /** the code wrap in TagFeather::OutBegin()  TagFeather::OutEnd run in cache file is ignore. for prevant re-decleare */
    public static function OutBegin()
    {
        // keep me; for parse
    }
    /** the code wrap in TagFeather::OutBegin()  TagFeather::OutEnd run in cache file is ignore. for prevant re-decleare */
    public static function OutEnd()
    {
        // keep me; for parse
    }
    ////////////////////////////////////////////////////////////////////////////
    /** For more quick  regist system parsehook */
    protected function _reg($names, $the_type = false)
    {
        foreach ($names as $hookname) {
            if (!$the_type) {
                $hooktype = array_shift(explode('_', $hookname));
            } else {
                $hooktype = $the_type;
            }
            $this->hookmanager->add_parsehook(array('TF_Hooks',$hookname), $hooktype);
        }
    }
}
