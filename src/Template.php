<?php

namespace Altivebir\TemplateFieldManager;

use ProcessWire\WireException;

/**
 * Class Template
 *
 * @author			: İskender TOTOĞLU, @ukyo (community), @trk (Github)
 * @website			: https://www.altivebir.com
 *
 * @package Altivebir\TemplateFieldManager
 */
class Template extends Base
{
    /**
     * @var Group $group;
     */
    public $group;

    /**
     * @inheritDoc
     *
     * @param string $name
     * @param string $rename
     * @param array $props
     */
    public function __construct(string $name = '', string $rename = '', array $props = [])
    {
        parent::__construct($name, $rename, $props);

        $fields = [];

        if(array_key_exists('fields', $this->props)) {
            if(is_array($this->props['fields'])) {
                $fields = $this->props['fields'];
            }

            unset($this->props['fields']);
        }

        if(!in_array('title', $fields)) {
            $fields = array_merge(['title'], $fields);
        }

        $this->group = new Group($this->element->name, '', $fields);
        $this->group->mode = $this->mode;
        $this->group->template = $this->element;
    }

    /**
     * @inheritDoc
     *
     * @throws WireException
     */
    public function run($mode = false)
    {
        parent::run($mode);

        // unset name
        if(array_key_exists('name', $this->props)) {
            unset($this->props['name']);
        }
        // unset rename
        if(array_key_exists('rename', $this->props)) {
            unset($this->props['rename']);
        }

        // rename template
        if($this->rename && $this->rename != $this->element->name) {
            $this->element->name = $this->rename;
            $this->info[] = "Template `{$this->name}` name changed with `{$this->rename}`.";
        }

        $this->group->run();
        $this->element->fieldgroup = $this->group->element;

        $this->info['group'][$this->group->name] = $this->group->info;

        if(array_key_exists('fields', $this->props)) {
            unset($this->props['fields']);
        }

        // set template properties
        foreach ($this->props as $key => $value) {
            if(!$this->element->get($key)) {
                $this->info[] = "`{$value}` added as {$key}.";
                $this->element->set($key, $value);
            }
            if($this->element->get($key) && $this->element->get($key) != $value) {
                $this->info[] = "`{$key}` value updated with `{$value}`.";
                $this->element->set($key, $value);
            }
        }

        if($this->mode == Manage::MODE_UPDATE) {
            $this->element->save();
        }
    }
}
