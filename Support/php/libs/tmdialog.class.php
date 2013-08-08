<?php

require_once('plist.class.php');

/**
 * Class for interfacing with textmate tmdialog server.
 *
 * @octdoc      libs/tmdialog
 * @copyright   copyright © 2009-2013 by Harald Lapp
 * @author      Harald Lapp <harald@octris.org>
 */
class tmdialog 
/**/
{
    /**
     * TMDialog token.
     *
     * @octdoc  tmdialog/$token
     * @var     string
     */
    protected $token;
    /**/

    /**
     * Instance of plist parser.
     *
     * @octdoc  tmdialog/$plist
     * @var     plist
     */
    protected $plist;
    /**/
    
    /**
     * Action registry.
     *
     * @octdoc  tmdialog/$actions
     * @var     array
     */
    protected $actions = array();
    /**/
    
    /**
     * TMDialog action stati.
     *
     * @octdoc  tmdialog/T_CLOSE
     * @var     int
     */
    const T_CLOSE = -1;
    /**/

    /**
     * Constructor.
     *
     * @octdoc  tmdialog/__construct
     */
    public function __construct()
    /**/
    {
        $this->plist = new plist();
        
        $this->registerAction('closeWindow', function($model) {
            return self::T_CLOSE;
        });
    }
    
    /**
     * Register an action.
     *
     * @octdoc  tmdialog/registerAction
     * @param   string          $action             Name of action to register.
     * @param   callable        $callback           Callback for action.
     */
    public function registerAction($action, callable $callback)
    /**/
    {
        $this->actions[$action] = $callback;
    }

    /**
     * Convert a php array to a tmdialog model.
     *
     * @octdoc  tmdialog/toModel
     * @param   array           $params             Properties to convert.
     */
    public function toModel(array $params = array())
    /**/
    {
        $p = '';
        
        foreach ($params as $k => $v) {
            $p .= sprintf('%s = "%s"; ', $k, $v);
        }
        
        return '{ ' . $p . '}';
    }
    
    /**
     * Load a nib dialog file.
     *
     * @octdoc  tmdialog/load
     * @param   string          $dialog             Name of dialog (name of a nib file).
     * @param   array           $params             Optional properties to set for dialog.
     * @return  bool                                Returns true on success.
     */
    public function load($dialog, array $params = array())
    /**/
    {
        // validate NIB
        $nib = sprintf(
            '%s/php/nibs/%s.nib/', 
            $_ENV['TM_BUNDLE_SUPPORT'], 
            basename($dialog, '.nib')
        );
 
        if (!is_dir($nib)) {
            // NIB not found
            return false;
        }
        
        // run tmdialog
        $cmd = sprintf(
            '%s nib --load %s --model %s', 
            escapeshellarg($_ENV['DIALOG']),
            escapeshellarg($nib),
            escapeshellarg($this->toModel($params))
        );

        $this->token = `$cmd`;
        
        return ((int)$this->token > 0);
    }

    /**
     * Update dialog with new data.
     *
     * @octdoc  tmdialog/update
     * @param   array           $params             Optional properties to set for dialog.
     */
    public function update(array $params = array())
    /**/
    {
        // run tmdialog
        $cmd = sprintf(
            '%s nib --update %d --model %s', 
            escapeshellarg($_ENV['DIALOG']),
            $this->token,
            escapeshellarg($this->toModel($params))
        );

        `$cmd`;
    }

    /**
     * Close dialog.
     *
     * @octdoc  tmdialog/dispose
     */
    public function dispose()
    /**/
    {
        // run tmdialog
        $cmd = sprintf(
            '%s nib --dispose %d',
            escapeshellarg($_ENV['DIALOG']),
            $this->token
        );
        
        `$cmd`;
    }

    /**
     * Run dialog.
     *
     * @octdoc  tmdialog/run
     */
    public function run()
    /**/
    {
        $cmd = sprintf(
            '%s nib --wait %d', 
            escapeshellarg($_ENV['DIALOG']),
            $this->token
        );
     
        do {
            $plist = `$cmd`;
            file_put_contents('/tmp/test.log', $plist, FILE_APPEND);
            $data  = $this->plist->parse($plist);

            if (!is_array($data) || count($data) == 0) {
                break;
            }

            $action = (isset($data['eventInfo']) ? $data['eventInfo']['type'] : '');
            $model  = (isset($data['model']) ? $data['model'] : NULL);

            if (isset($this->actions[$action])) {
                $status = (int)$this->actions[$action]($model);
            } else {
                $status = 0;
            }
        } while($status >= 0);
    }
}
