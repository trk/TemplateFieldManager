<?php

namespace Altivebir\TemplateFieldManager;

use ProcessWire\Field as pwField;
use ProcessWire\WireException;

/**
 * Class Field
 *
 * @author			: İskender TOTOĞLU, @ukyo (community), @trk (Github)
 * @website			: https://www.altivebir.com
 *
 * @package Altivebir\TemplateFieldManager
 */
class Field extends Base
{
    /**
     * @var pwField $field;
     */
    public $field;

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

        // Rename field
        if($this->rename && $this->rename != $this->element->name) {
            $this->element->name = $this->rename;
            $this->info[] = "Field `{$this->name}` name changed with `{$this->rename}`.";
        }

        $type = 'FieldtypeText';
        if(array_key_exists('type', $this->props)) {
            $type = $this->props['type'];
            unset($this->props['type']);
        }

        if(strpos($type, 'Fieldtype') === false) {
            $type = 'Fieldtype' . ucfirst($type);
        }

        $language = false;
        if(array_key_exists('language', $this->props)) {
            $language = $this->props['language'];
            if($language) {
                $type .= 'Language';
            }
            unset($this->props['language']);
        }

        if($this->wire->modules->isInstalled($type)) {
            // add field type
            if(!$this->element->type) {
                $this->element->type = $this->wire->modules->get($type);
                $this->info[] = "`$type` added as field type.";
            }
            // update field type
            if($this->element->type != $type) {
                $this->info[] = "`{$this->element->type}` field type changed with `{$type}` field type.";
                $this->element->type = $type;
            }

            foreach ($this->props as $key => $value) {
                if(!$this->element->get($key)) {
                    $this->info[] = "`{$value}` added as {$key}.";
                    $this->element->set($key, $value);
                }
                if($this->element->get($key) && $this->element->get($key) != $value) {
                    $this->info[] = "`{$key}` old value `{$this->element->get($key)}` changed with `{$value}`.";
                    $this->element->set($key, $value);
                }
            }

            if($this->mode == Manage::MODE_UPDATE) {
                $this->element->save();
            }

            if($language && $this->wire->modules->isInstalled($type . 'Language')) {
                $this->element->type = $this->wire->modules->get($type . 'Language');
                $this->info[] = "`{$type}` field type changed with `{$type}Language` field type.";
                $this->element->save();
            }

        } else {
            $this->info[] = "Field type `{$type}` module not installed.";
        }
    }
}
