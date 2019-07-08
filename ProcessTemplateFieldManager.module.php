<?php

namespace ProcessWire;

use Altivebir\TemplateFieldManager\Build;

class ProcessAltivebirBuilder extends Process
{
    /**
     * @var AltivebirBuilder $builder
     */
    protected $builder;

    /**
     * @var array $resources
     */
    protected $resources;

    /**
     * @inheritdoc
     *
     * @return array
     */
    public static function getModuleInfo() {
        return [
            'title' => 'Process Altivebir Builder',
            'version' => 1,
            'summary' => __('Process module for AltivebirBuilder module.'),
            'href' => 'https://www.altivebir.com',
            'author' => 'İskender TOTOĞLU | @ukyo(community), @trk (Github), https://www.altivebir.com',
            'requires' => [
                'PHP>=7.0.0',
                'ProcessWire>=3.0.0',
                'AltivebirBuilder'
            ],
            'installs' => [],
            // 'permanent' => false,
            // 'permission' => 'permission-name',
            'permissions' => [],
            'icon' => 'cogs',
            'singular' => false,
            'autoload' => false,
            'page' => [
                'name' => 'altivebir-builder',
                'parent' => 'setup',
                'title' => 'Altivebir Builder'
            ]
        ];
    }

    public function __construct()
    {
        parent::__construct();

        /* @var AltivebirBuilder $builder */
        $this->builder = $this->wire->modules->get('TemplateFieldManager');

        $this->wire('classLoader')->addNamespace('Altivebir\TemplateFieldManager', __DIR__ . '/src');
    }

    public function ___execute()
    {
        parent::___execute();

        $table = $this->resourcesTable();

        return $table->render();
    }

    public function ___executeCheck()
    {
        $resource = $this->getResourceName();

        if(is_string($resource)) {
            return $resource;
        }

        $info = $this->getResourceInfo($resource);

        /* @var MarkupAdminDataTable $table */
        $table = $this->wire->modules->get('MarkupAdminDataTable');
        $table->setSortable(false);
        $table->setEncodeEntities(false);


        $table->row(['<b>' . $this->_('Title') . '</b><p>' . $info['title'] . '</p>']);
        if($info['description']) {
            $table->row(['<b>' . $this->_('Description') . '</b><p>' . $info['description'] . '</p>']);
        }
        if($info['notes']) {
            $table->row(['<b>' . $this->_('Notes') . '</b><p>' . $info['notes'] . '</p>']);
        }

        $build = new Build($resource);
        $build->mode = $build::MODE_CHECK;
        $build->create();

        $table->row([$this->getInfoTable($build->info)]);

        $buildButton = $this->createButton([
            'href' => '../build/?name=' . $resource['__name'],
            'value' => $this->_('Build'),
            'class' => 'ui-button ui-state-default',
            'icon' => 'plus'
        ]);

        $table->row([$buildButton->render()]);

        return $table->render();
    }

    public function ___executeBuild()
    {
        $resource = $this->getResourceName();

        if(is_string($resource)) {
            return $resource;
        }

        $info = $this->getResourceInfo($resource);

        /* @var MarkupAdminDataTable $table */
        $table = $this->wire->modules->get('MarkupAdminDataTable');
        $table->setSortable(false);
        $table->setEncodeEntities(false);


        $table->row(['<b>' . $this->_('Title') . '</b><p>' . $info['title'] . '</p>']);

        if($info['description']) {
            $table->row(['<b>' . $this->_('Description') . '</b><p>' . $info['description'] . '</p>']);
        }

        if($info['notes']) {
            $table->row(['<b>' . $this->_('Notes') . '</b><p>' . $info['notes'] . '</p>']);
        }

        $build = new Build($resource);
        $build->mode = $build::MODE_BUILD;
        $build->create();

        $table->row([$this->getInfoTable($build->info)]);

        $buildButton = $this->createButton([
            'href' => '?name=' . $resource['__name'],
            'value' => $this->_('Re-build'),
            'class' => 'ui-button ui-state-default',
            'icon' => 'plus'
        ]);

        $table->row([$buildButton->render()]);

        return $table->render();

    }

    protected function getInfoTable($info = [])
    {
        /* @var MarkupAdminDataTable $table */
        $table = $this->wire->modules->get('MarkupAdminDataTable');
        $table->setSortable(false);
        $table->setEncodeEntities(false);
        $table->row([$this->generateInfoList($info)]);

        return $table->render();
    }

    /**
     * @inheritDoc
     *
     * @param array $changes
     * @return string
     */
    protected function generateInfoList($changes = [])
    {
        $output = '<ul>';

        foreach ($changes as $index => $value) {
            $text = is_array($value) ? $index : $value;
            switch ($text) {
                case 'field':
                    $text = '<h5>' . $this->_('Changes will be apply for fields') . '</h5>';
                    break;
                case 'template':
                    $text = '<h5>' . $this->_('Changes will be apply for templates') . '</h5>';
                    break;
                case 'group':
                    $text = '<h5>' . $this->_('Changes will be apply for field groups') . '</h5>';
                    break;
            }

            if(is_array($value)) {
                $output .= '<li><b>' . $text . '</b>';
                $output .= $this->generateInfoList($value);
            } else {
                $output .= '<li>' . $text;
            }

            $output .= '</li>';
        }

        $output .= '</ul>';

        return $output;
    }

    /**
     * @inheritDoc
     *
     * @param string|array $resource
     * @return array
     */
    protected function getResourceInfo($resource = '')
    {
        if(is_string($resource)) {
            $resource = $this->builder::resource($resource);
        }

        $name = array_key_exists('name', $resource) && $resource['name'] ? $resource['name'] : $resource['__name'];
        $title = array_key_exists('title', $resource) && $resource['title'] ? $resource['title'] : $name;
        $description = array_key_exists('description', $resource) && $resource['description'] ? $resource['description'] : '';
        $notes = array_key_exists('notes', $resource) && $resource['notes'] ? $resource['notes'] : '';

        return [
            'name' => $name,
            'title' => $title,
            'description' => $description,
            'notes' => $notes
        ];
    }

    /**
     * @inheritDoc
     *
     * @return array|string
     */
    protected function getResourceName()
    {
        $name = $this->input->get->text("name");
        if($name) {
            $resource = $this->builder::resource($name);
            if(array_key_exists('__path', $resource) && $resource['__path']) {
                $data = $resource;
            } else {
                $data = $this->_('Given resource `%s` not found!');
            }
        } else {
            $data = $this->_('There is no resource name given.');
        }

        return $data;
    }

    /**
     * List of resources
     *
     * @return MarkupAdminDataTable
     * @throws WirePermissionException
     */
    protected function resourcesTable()
    {
        /* @var MarkupAdminDataTable $table */
        $table = $this->wire->modules->get('MarkupAdminDataTable');
        $table->setSortable(false);
        $table->setEncodeEntities(false);
        $table->headerRow([
            $this->_('Title'),
            $this->_('Description'),
            $this->_('Notes'),
            $this->_('Action')
        ]);

        foreach ($this->builder::resources() as $resource) {
            $name = array_key_exists('name', $resource) && $resource['name'] ? $resource['name'] : $resource['__name'];
            $title = array_key_exists('title', $resource) && $resource['title'] ? $resource['title'] : $name;
            $description = array_key_exists('description', $resource) && $resource['description'] ? $resource['description'] : '';
            $notes = array_key_exists('notes', $resource) && $resource['notes'] ? $resource['notes'] : '';

            $checkButton = $this->createButton([
                'href' => 'check/?name=' . $resource['__name'],
                'value' => $this->_('Check'),
                'class' => 'ui-button ui-state-secondary',
                'icon' => 'check'
            ]);

            $buildButton = $this->createButton([
                'href' => 'build/?name=' . $resource['__name'],
                'value' => $this->_('Build'),
                'class' => 'ui-button ui-state-default',
                'icon' => 'plus'
            ]);

            $buttons = $checkButton->render() . $buildButton->render();

            $table->row([
                $title,
                $description,
                $notes,
                $buttons
            ]);
        }

        return $table;
    }

    protected function createButton($props = [])
    {
        /* @var InputfieldButton $button */
        $button = $this->wire->modules->get('InputfieldButton');

        foreach ($props as $key => $value) {
            $button->set($key, $value);
        }

        return $button;
    }
}