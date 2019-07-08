<?php

namespace Altivebir\TemplateFieldManager;

use ProcessWire\Wire;

/**
 * Class Manage
 *
 * @author			: İskender TOTOĞLU, @ukyo (community), @trk (Github)
 * @website			: https://www.altivebir.com
 *
 * @package Altivebir\TemplateFieldManager
 */
class Manage extends Wire
{
    const MODE_NONE = 0;

    const MODE_CHECK = 1;

    const MODE_UPDATE = 2;

    /**
     * @var int $mode
     */
    public $mode = 0;

    /**
     * @var array $resource
     */
    public $resource = [];

    /**
     * @var array $fields
     */
    public $fields = [];

    /**
     * @var array $templates
     */
    public $templates = [];

    /**
     * @var array $languages
     */
    protected $languages = [];

    /**
     * Translatable properties
     *
     * @var array $translatable
     */
    protected $translatable = ['label', 'description', 'notes', 'checkboxLabel'];

    /**
     * @var array $translations
     */
    public $translations = [
        'default' => 'en',
        'fields' => [],
        'templates' => []
    ];

    /**
     * @var array $info
     */
    public $info = [];

    /**
     * @inheritDoc
     *
     * @param array|string $resource
     */
    public function __construct($resource = [])
    {
        parent::__construct();

        if(is_string($resource)) {
            $resource = json_decode($resource, true);
        }

        $this->resource = $resource;

        $this->init();
    }

    /**
     * @inheritDoc
     */
    protected function init()
    {
        // set fields
        if(array_key_exists('fields', $this->resource)) {
            $this->fields = $this->resource['fields'];
        }
        // set templates
        if(array_key_exists('templates', $this->resource)) {
            $this->templates = $this->resource['templates'];
        }
        // set translations
        if(array_key_exists('translations', $this->resource)) {
            $this->translations = array_merge($this->resource['translations']);
        }

        // set languages
        foreach ($this->wire->languages as $language) {
            $name = $language->isDefault() ? $this->translations['default'] : $language->name;
            $this->languages[$name] = $language->isDefault() ? '' : $language->id;
        }
    }

    /**
     * @inheritDoc
     */
    public function run($mode = false)
    {
        $this->mode = $mode;

        // first create fields
        $this->runFields();
        // create templates and add fields to templates
        $this->runTemplates();
    }

    /**
     * @inheritDoc
     */
    protected function runFields()
    {
        foreach ($this->fields as $name => $props) {
            $rename = '';
            if(array_key_exists('name', $props)) {
                $name = $props['name'];
                unset($props['name']);
            }
            if(array_key_exists('rename', $props)) {
                $rename = $props['rename'];
                unset($props['rename']);
            }

            // set translations for field props
            $props = $this->setTranslation($props);

            // create field
            $field = new Field($name, $rename, $props);
            $field->mode = $this->mode;
            $field->run();

            $this->info['field'][$name] = $field->info;
        }
    }

    /**
     * @inheritDoc
     */
    protected function runTemplates()
    {
        foreach ($this->templates as $name => $props) {
            $rename = '';
            if(array_key_exists('name', $props)) {
                $name = $props['name'];
                unset($props['name']);
            }
            if(array_key_exists('rename', $props)) {
                $rename = $props['rename'];
                unset($props['rename']);
            }

            // set translations for template props props
            $props = $this->setTranslation($props, 'templates');

            // set translations for template context
            if(array_key_exists('fields', $props) && is_array($props['fields'])) {
                foreach ($props['fields'] as $index => $field) {
                    if(is_array($field)) {
                        $props['fields'][$index] = $this->setTranslation($field);
                    }
                }
            }

            // create template
            $template = new Template($name, $rename, $props);
            $template->mode = $this->mode;
            $template->run();

            $this->info['template'][$name] = $template->info;
        }
    }

    /**
     * @inheritDoc
     *
     * @param array $props
     * @param string $element
     * @return array
     */
    public function setTranslation($props = [], $element = 'fields')
    {
        // set translatable content
        foreach ($this->translatable as $index => $property) {
            if(!array_key_exists($property, $props)) continue;
            foreach ($this->translations[$element] as $lang => $translations) {
                if(array_key_exists($lang, $this->languages) && array_key_exists($props[$property], $translations)) {
                    $props[$property . $this->languages[$lang]] = $translations[$props[$property]];
                }
            }
        }

        return $props;
    }
}
