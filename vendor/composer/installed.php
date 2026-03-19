<?php return array(
    'root' => array(
        'name' => 'fuel/oil',
        'pretty_version' => 'dev-develop',
        'version' => 'dev-develop',
        'reference' => '90b2348fff5d73776e6105166189fba745b6c803',
        'type' => 'fuel-package',
        'install_path' => __DIR__ . '/../../',
        'aliases' => array(),
        'dev' => true,
    ),
    'versions' => array(
        'composer/installers' => array(
            'pretty_version' => 'v1.12.0',
            'version' => '1.12.0.0',
            'reference' => 'd20a64ed3c94748397ff5973488761b22f6d3f19',
            'type' => 'composer-plugin',
            'install_path' => __DIR__ . '/./installers',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'fuel/oil' => array(
            'pretty_version' => 'dev-develop',
            'version' => 'dev-develop',
            'reference' => '90b2348fff5d73776e6105166189fba745b6c803',
            'type' => 'fuel-package',
            'install_path' => __DIR__ . '/../../',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'roundcube/plugin-installer' => array(
            'dev_requirement' => false,
            'replaced' => array(
                0 => '*',
            ),
        ),
        'shama/baton' => array(
            'dev_requirement' => false,
            'replaced' => array(
                0 => '*',
            ),
        ),
    ),
);
