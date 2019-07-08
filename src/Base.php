<?php

namespace Altivebir\TemplateFieldManager;

use ProcessWire\Wire;
use ProcessWire\Field as pwField;
use ProcessWire\Fieldgroup as pwGroup;
use ProcessWire\Template as pwTemplate;

/**
 * Class Base
 *
 * @author			: İskender TOTOĞLU, @ukyo (community), @trk (Github)
 * @website			: https://www.altivebir.com
 *
 * @package Altivebir\TemplateFieldManager
 */
class Base extends Wire
{
    /**
     * @var pwTemplate|pwField|pwGroup $element
     */
    protected $element;

    /**
     * @var string $name
     */
    public $name;

    /**
     * @var string $rename
     */
    public $rename;

    /**
     * @var array $props
     */
    public $props;

    /**
     * @var array $info
     */
    public $info = [];

    /**
     * @var int $mode
     */
    public $mode = 0;

    /**
     * @inheritDoc
     *
     * @param string $name
     * @param string $rename
     * @param array $props
     */
    public function __construct(string $name = '', string $rename = '', array $props = [])
    {
        parent::__construct();

        $this->name = $name;
        $this->rename = $rename;
        $this->props = $props;

        $this->init();
    }

    /**
     * @inheritDoc
     */
    public function init()
    {
        $this->get();
    }

    /**
     * @inheritDoc
     */
    public function get()
    {
        $caller = '';
        $class = '';
        $text = '';

        if ($this->className == 'Field') {
            $caller = 'fields';
            $class = new pwField();
            $text = 'field';
        } elseif ($this->className == 'Group') {
            $caller = 'fieldgroups';
            $class = new pwGroup();
            $text = 'field group';
        } elseif ($this->className == 'Template') {
            $caller = 'templates';
            $class = new pwTemplate();
            $text = 'template';
        }

        if($caller && $class) {
            $element = $this->wire->{$caller}->get($this->name);
            if($element) {
                $this->info[] = "`{$element->name}` named {$text} exist, this {$text} will be used for apply changes.";
            }

            if(!$element) {
                $element = $this->wire->{$caller}->get($this->rename);
                if($element) {
                    $this->info[] = "`{$element->name}` renamed {$text} exist, this {$text} will be used for apply changes.";
                }
            }

            if(!$element) {
                $element = $class;
                $element->name = $this->rename ?: $this->name;

                $this->info[] = "`{$element->name}` named {$text} will be created.";
            }

            $this->element = $element;
        }
    }

    /**
     * @inheritDoc
     */
    public function ready()
    {

    }

    /**
     * @inheritDoc
     */
    public function check()
    {

    }

    /**
     * @inheritDoc
     */
    public function update()
    {

    }

    /**
     * @inheritDoc
     */
    public function install()
    {

    }

    /**
     * @inheritDoc
     */
    public function uninstall()
    {

    }

    /**
     * @inheritDoc
     *
     * @return array
     */
    public function __debugInfo()
    {
        $info = parent::__debugInfo();
        $info = array_merge($info, $this->info);

        return $info;
    }
}
