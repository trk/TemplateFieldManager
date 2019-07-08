# Template & Field Manager Module for ProcessWire CMS/CMF

`TemplateFieldManager` module allow you to create fields and templates via a config file.

#### Example config file (location: site/templates/configs/TemplateFieldManager.components.php):

```php
<?php

namespace ProcessWire;

/**
 * Template & Field Manager : Components (sample config file)
 */
return [
    'fields' => [
        'text' => [
            'label' => 'Title',
            'type' => 'text',
            'language' => true
        ],
        'content' => [
            'label' => 'Content',
            'type' => 'textarea',
            'language' => true,
            'inputfieldClass' => 'InputfieldCKEditor'
        ]
    ],
    'templates' => [
        'alert' => [
            'label' => 'Alert',
            'fields' => [
                'title',
                'text' => [
                    'label' => 'Alert title',
                    'columnWidth' => 50
                ],
                'content'
            ]
        ]
    ],
    'translations' => [
        // name of language for selector (name=en)
        'default' => 'en',
        'fields' => [
            // name of language for selector (name=tr)
            'tr' => [
                'Title' => 'Başlık',
                'Content' => 'İçerik',
                'Alert title' => 'Uyarı başlığı'
            ],
            // name of language for selector (name=ru)
            'ru' => [
                'Title' => 'заглавие',
                'Content' => 'содержание',
                'Alert title' => 'Название оповещения'
            ]
        ],
        'templates' => [
            // name of language for selector (name=tr)
            'tr' => [
                'Alert' => 'Uyarı'
            ],
            // name of language for selector (name=ru)
            'ru' => [
                'Alert' => 'бдительный'
            ]
        ]
    ],
];
```

#### Running via api
```php
<?php

/* @var TemplateFieldManager $builder */
$manager = wire()->modules->get('TemplateFieldManager');
$resource = $manager::resource('components');

$manage = new \Altivebir\TemplateFieldManager\Manage($resource);
$manage->mode = $manage::MODE_UPDATE;
$manage->create();

echo '<pre>' . print_r($manage->info, true) . '</pre>';
```