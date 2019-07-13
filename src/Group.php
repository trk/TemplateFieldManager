<?php

namespace Altivebir\TemplateFieldManager;

use ProcessWire\Template as pwTemplate;
use ProcessWire\WireException;

/**
 * Class Group
 *
 * @author			: İskender TOTOĞLU, @ukyo (community), @trk (Github)
 * @website			: https://www.altivebir.com
 *
 * @package Altivebir\TemplateFieldManager
 */
class Group extends Base
{
    /**
     * @var pwTemplate $template;
     */
    public $template;

    /**
     * @inheritDoc
     *
     * @throws WireException
     */
    public function run()
    {
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
            $this->info[] = "Field group `{$this->name}` name changed with `{$this->rename}`.";
        }

        $context = [];

        foreach ($this->props as $key => $value) {
            $name = $value;
            if(is_array($value)) {
                $name = $key;
                $context[$name] = $value;
            }
            $this->element->add($name);
            $this->info[] = "`{$name}` named field added to `{$this->element->name}` named field group.";
        }

        if($this->mode == Manage::MODE_UPDATE) {
            $this->element->save();
        }
        foreach ($context as $name => $value) {
            $field = $this->template->fieldgroup->getField($name, true);
            if($field) {
                foreach ($value as $property => $propertyValue) {
                    $propValue = is_array($propertyValue) ? json_encode($propertyValue) : $propertyValue;
                    if($field->get($property) !== $propertyValue) {
                        $this->info[] = "Context `{$property}` property `{$propValue}` value updated for `{$this->template->name}` template.";
                    } else {
                        $this->info[] = "Context `{$property}` property `{$propValue}` value added for `{$this->template->name}` template.";
                    }
                    $field->set($property, $propertyValue);
                }

                if($this->mode == Manage::MODE_UPDATE) {
                    $this->wire->fields->saveFieldgroupContext($field, $this->template->fieldgroup);
                }
            }
        }
    }
}
