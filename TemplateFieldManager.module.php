<?php

namespace ProcessWire;

/**
 * Class TemplateFieldManager
 *
 * @author			: İskender TOTOĞLU, @ukyo (community), @trk (Github)
 * @website			: https://www.altivebir.com
 *
 * @package Altivebir\TemplateFieldManager
 */
class TemplateFieldManager extends WireData implements Module
{
    /* @var TemplateFieldManager $instance */
    public static $instance;

    /* @var array $paths Resources paths */
    protected static $paths = [];

    /**
     * @inheritdoc
     *
     * @return array
     */
    public static function getModuleInfo() {
        return [
            'title' => 'Template & Field Manager',
            'version' => 1,
            'summary' => __('Template & Field Manager module allow you to create fields and templates via a config file.'),
            'href' => 'https://www.altivebir.com',
            'author' => 'İskender TOTOĞLU | @ukyo(community), @trk (Github), https://www.altivebir.com',
            'requires' => [
                'PHP>=7.0.0',
                'ProcessWire>=3.0.0'
            ],
            'installs' => [],
            // 'permanent' => false,
            // 'permission' => 'permission-name',
            'permissions' => [],
            'icon' => 'cogs',
            'singular' => true,
            'autoload' => true
        ];
    }

    /**
     * @inheritDoc
     *
     * @return AltivebirCreator
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * @inheritDoc
     */
    public function __construct()
    {
        parent::__construct();

        self::$paths = new FilenameArray();

        $this->wire('classLoader')->addNamespace('Altivebir\TemplateFieldManager', __DIR__ . '/src');
    }

    /**
     * @inheritDoc
     */
    public function init()
    {
        // Add default paths
        self::add(__DIR__ . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR);
        self::add($this->config->paths->templates . 'configs' . DIRECTORY_SEPARATOR);
    }

    /**
     * @inheritDoc
     */
    public function ready()
    {
    }

    public static function add($path)
    {
        $path = $path . self::getInstance()->className . '.*.php';
        self::$paths->add($path);
    }

    /**
     * Find all added resources
     *
     * @return array
     */
    public static function resource_paths()
    {
        $resources = [];
        $paths = [];

        foreach (self::$paths as $path) {
            $paths[] = $path;
        }

        $paths = glob('{' . implode(',', $paths) . '}', GLOB_BRACE);

        foreach ($paths as $k => $path) {
            $name = str_replace([realpath($path), self::getInstance()->className . '.', '.php'], '', basename($path));
            $resources[$name] = $path;
        }

        return $resources;
    }

    /**
     * Get resource data
     *
     * @param string $name
     * @param bool $json
     *
     * @return array|string
     */
    public static function resource($name = '', $json = false)
    {
        $resources = self::resource_paths();
        if(array_key_exists($name, $resources) && file_exists($resources[$name])) {
            $data = include $resources[$name];
            if(is_array($data)) {
                $resource = $data;
            } else {
                $resource = ['__data' => $data];
            }

            $resource['__name'] = $name;
            $resource['__path'] = $resources[$name];
        } else {
            $resource = [
                '__data' => 'Resource not found !',
                '__name' => $name,
                '__path' => ''
            ];
        }

        return $json ? json_encode($resource, true) : $resource;
    }

    /**
     * Get all resources data
     *
     * @param bool $json
     *
     * @return array|string
     */
    public static function resources($json = false)
    {
        $resources = [];

        foreach (self::resource_paths() as $name => $path) {
            $resources[$name] = self::resource($name);
        }

        return $json ? json_encode($resources, true) : $resources;
    }
}