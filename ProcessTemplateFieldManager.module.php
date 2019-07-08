<?php

namespace ProcessWire;

use Altivebir\TemplateFieldManager\Manage;

class ProcessTemplateFieldManager extends Process
{
    /**
     * @var TemplateFieldManager $manager
     */
    protected $manager;

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
            'title' => 'Process Template & Field Manager',
            'version' => 1,
            'summary' => __('Process module for Template & Field Manager module.'),
            'href' => 'https://www.altivebir.com',
            'author' => 'İskender TOTOĞLU | @ukyo(community), @trk (Github), https://www.altivebir.com',
            'requires' => [
                'PHP>=7.0.0',
                'ProcessWire>=3.0.0',
                'TemplateFieldManager'
            ],
            'installs' => [],
            // 'permanent' => false,
            // 'permission' => 'permission-name',
            'permissions' => [],
            'icon' => 'cogs',
            'singular' => false,
            'autoload' => false,
            'page' => [
                'name' => 'template-field-manager',
                'parent' => 'setup',
                'title' => 'Template & Field Manager'
            ]
        ];
    }

    public function __construct()
    {
        parent::__construct();

        /* @var TemplateFieldManager $manager */
        $this->manager = $this->wire->modules->get('TemplateFieldManager');

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

        $manage = new Manage($resource);
        $manage->mode = $manage::MODE_CHECK;
        $manage->run();

        $table->row([$this->getInfoTable($manage->info)]);

        $updateButton = $this->createButton([
            'href' => '../update/?name=' . $resource['__name'],
            'value' => $this->_('Update'),
            'class' => 'ui-button ui-state-default',
            'icon' => 'plus'
        ]);

        $table->row([$updateButton->render()]);

        return $table->render();
    }

    public function ___executeUpdate()
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

        $manage = new Manage($resource);
        $manage->mode = $manage::MODE_UPDATE;
        $manage->run();

        $table->row([$this->getInfoTable($manage->info)]);

        $updateButton = $this->createButton([
            'href' => '?name=' . $resource['__name'],
            'value' => $this->_('Re-update'),
            'class' => 'ui-button ui-state-default',
            'icon' => 'plus'
        ]);

        $table->row([$updateButton->render()]);

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
            $resource = $this->manager::resource($resource);
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
            $resource = $this->manager::resource($name);
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

        foreach ($this->manager::resources() as $resource) {
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

            $updateButton = $this->createButton([
                'href' => 'update/?name=' . $resource['__name'],
                'value' => $this->_('Update'),
                'class' => 'ui-button ui-state-default',
                'icon' => 'plus'
            ]);

            $buttons = $checkButton->render() . $updateButton->render();

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