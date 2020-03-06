<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace TagFeather;

class LockHookObject extends HookObjectBase
{
    public $lockfile = '';
    protected $hooktypes = array('postbuild','error','unreg');
    
    protected $last_ignore = false;
    public function reg($hookmanager, $tf = null, $fakehooktype = 'reg')
    {
        parent::reg($hookmanager, $hooktype, $fakehooktype);
        
        $hooktype = 'modifier';
        $hookname = $hookmanager->get_hooknamebycallback(array(&$this,$hooktype), true);
        $hookmanager->insert_parsehook(array(&$this,$hooktype), $hooktype, '', $hookname);
        $this->hooktypes[] = $hookname;
        return $hookmanager;
    }
    
    public function modifier($is_build, $tf, $hooktype)
    {
        if ($is_build) {
            return true;
        }
        $this->last_ignore = ignore_user_abort(true);
        
        $this->lockfile = $tf->cache_dir.$tf->dest.".lock";
        if (file_exists($this->lockfile)) {
            $mtime = @filemtime($this->lockfile);
            if ($mtime < filemtime($tf->work_dir.$tf->source)) {
                $n = file_put_contents($this->lockfile, time());
                return false;
            }
            $lockfiletime = file_get_contents($this->lockfile);
            /*
            if (time() > $mtime + $tf->parser->timeout) {
                $n = file_put_contents($this->lockfile, time());
                return false;
            }
            */
            
            $tf->is_build_error = true;
            $tf->build_error_msg = "FileLocked";
            $error = array('source' => get_class($tf),'type' => 'FileLocked','info' => $this->lockfile);
            return true;
        } else {
            $n = file_put_contents($this->lockfile, time());
            $tf->runtime['lock'] = $this->lockfile;
            return false;
        }
    }
    public function postbuild($data, $tf, $hooktype)
    {
        if ($this->lockfile) {
            @unlink($this->lockfile);
        }
        ignore_user_abort($this->last_ignore);
        return $data;
    }
    public function error($error, $tf, $hooktype)
    {
        if ($this->lockfile) {
            @unlink($this->lockfile);
        }
        ignore_user_abort($this->last_ignore);
        return $error;
    }
}
