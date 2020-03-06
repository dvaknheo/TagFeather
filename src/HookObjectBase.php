<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace TagFeather;

class HookObjectBase
{
    protected $hooktypes = array();
    protected $hookmanager;
    protected $hooks = array();
    public function __construct($hookmanager = null)
    {
        if ($hookmanager) {
            $this->hookmanager = $hookmanager;
            $this->reg($hookmanger);
        }
        // unreg $this->unreg($this->hookmanager, null, 'unreg');
    }
    public function reg($hookmanager, $tf = null, $hooktype = 'reg')
    {
        if ($hookmanager) {
            $this->hookmanager = $hookmanager;
        }
        $hooktypes = $this->hooktypes;
        if (!$hooktypes) {
            $hooktypes = array_keys($hookmanager->parsehooks);
        }
        foreach ($hooktypes as $thehooktype) {
            $hookname = $hookmanager->get_hooknamebycallback(array(&$this,$thehooktype), true);
            $hookmanager->add_parsehook(array(&$this,$thehooktype), $thehooktype, '', $hookname);
            $hooks[$thehooktype] = $hookname;
        }
        return hookmanager;
    }
    public function unreg($arg = false, $tf = null, $hooktype = 'unreg')
    {
        foreach ($this->hooks as $thehooktype => $hookname) {
            $this->hookmanager->remove_parsehook($hookname, $thehooktype);
        }
        return $arg;
    }
    public function modifier($arg, $tf, $hooktype)
    {
        return $arg;
    }
    public function error($arg, $tf, $hooktype)
    {
        return $arg;
    }
    public function prebuild($arg, $tf, $hooktype)
    {
        //echo("OK");die(__LINE__);
        return $arg;
    }
    public function postbuild($arg, $tf, $hooktype)
    {
        return $arg;
    }
    public function tagbegin($arg, $tf, $hooktype)
    {
        return $arg;
    }
    public function tagend($arg, $tf, $hooktype)
    {
        return $arg;
    }
    public function text($arg, $tf, $hooktype)
    {
        return $arg;
    }
    public function asp($arg, $tf, $hooktype)
    {
        return $arg;
    }
    public function pi($arg, $tf, $hooktype)
    {
        return $arg;
    }
    public function comment($arg, $tf, $hooktype)
    {
        return $arg;
    }
    public function notation($arg, $tf, $hooktype)
    {
        return $arg;
    }
    public function cdata($arg, $tf, $hooktype)
    {
        return $arg;
    }
}
