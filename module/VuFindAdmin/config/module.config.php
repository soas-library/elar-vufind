<?php
namespace VuFindAdmin\Module\Configuration;

$config = [
    'controllers' => [
        'invokables' => [
            'admin' => 'VuFindAdmin\Controller\AdminController',
            'adminconfig' => 'VuFindAdmin\Controller\ConfigController',
            'adminsocial' => 'VuFindAdmin\Controller\SocialstatsController',
            'adminprivatestats' => 'VuFindAdmin\Controller\PrivateStatsController',
            'adminprivatestatsgeneral' => 'VuFindAdmin\Controller\PrivateStatsGeneralController',
            'adminprivatestatsdepositlist' => 'VuFindAdmin\Controller\PrivateStatsDepositlistController',
            'adminprivatestatsuserdownloads' => 'VuFindAdmin\Controller\PrivateStatsUserdownloadsController',
            'adminprivatestatsuploadedfiles' => 'VuFindAdmin\Controller\PrivateStatsUploadedfilesController',
            'adminprivatestatsuploadbyfiletype' => 'VuFindAdmin\Controller\PrivateStatsUploadbyfiletypeController',
            'adminprivatestatsdownloadbyfiletype' => 'VuFindAdmin\Controller\PrivateStatsDownloadbyfiletypeController',
            'adminprivatestatsdownloadbyuser' => 'VuFindAdmin\Controller\PrivateStatsDownloadbyuserController',
            'adminsettings' => 'VuFindAdmin\Controller\SettingsController',
            'adminmaintenance' => 'VuFindAdmin\Controller\MaintenanceController',
            'adminstatistics' => 'VuFindAdmin\Controller\StatisticsController',
            'admintags' => 'VuFindAdmin\Controller\TagsController',
        ],
    ],
    'router' => [
        'routes' => [
            'admin' => [
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => [
                    'route'    => '/Admin',
                    'defaults' => [
                        'controller' => 'Admin',
                        'action'     => 'Home',
                    ]
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'disabled' => [
                        'type' => 'Zend\Mvc\Router\Http\Literal',
                        'options' => [
                            'route'    => '/Disabled',
                            'defaults' => [
                                'controller' => 'Admin',
                                'action'     => 'Disabled',
                            ]
                        ]
                    ],
                    'config' => [
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => [
                            'route'    => '/Config[/:action]',
                            'defaults' => [
                                'controller' => 'AdminConfig',
                                'action'     => 'Home',
                            ]
                        ]
                    ],
                    'maintenance' => [
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => [
                            'route'    => '/Maintenance[/:action]',
                            'defaults' => [
                                'controller' => 'AdminMaintenance',
                                'action'     => 'Home',
                            ]
                        ]
                    ],
                    'social' => [
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => [
                            'route'    => '/Social[/:action]',
                            'defaults' => [
                                'controller' => 'AdminSocial',
                                'action'     => 'Home',
                            ]
                        ]
                    ],
                    'privatestats' => [
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => [
                            'route'    => '/PrivateStats[/:action]',
                            'defaults' => [
                                'controller' => 'AdminPrivateStats',
                                'action'     => 'Home',
                            ]
                        ]
                    ],
                    'privatestatsgeneral' => [
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => [
                            'route'    => '/PrivateStatsGeneral[/:action]',
                            'defaults' => [
                                'controller' => 'AdminPrivateStatsGeneral',
                                'action'     => 'Home',
                            ]
                        ]
                    ],
                    'privatestatsdepositlist' => [
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => [
                            'route'    => '/PrivateStatsDepositlist[/:action]',
                            'defaults' => [
                                'controller' => 'AdminPrivateStatsDepositlist',
                                'action'     => 'Home',
                            ]
                        ]
                    ],
                    'privatestatsuserdownloads' => [
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => [
                            'route'    => '/PrivateStatsUserdownloads[/:action]',
                            'defaults' => [
                                'controller' => 'AdminPrivateStatsUserdownloads',
                                'action'     => 'Home',
                            ]
                        ]
                    ],
                    'privatestatsuploadedfiles' => [
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => [
                            'route'    => '/PrivateStatsUploadedfiles[/:action]',
                            'defaults' => [
                                'controller' => 'AdminPrivateStatsUploadedfiles',
                                'action'     => 'Home',
                            ]
                        ]
                    ],
                    'privatestatsuploadbyfiletype' => [
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => [
                            'route'    => '/PrivateStatsUploadbyfiletype[/:action]',
                            'defaults' => [
                                'controller' => 'AdminPrivateStatsUploadbyfiletype',
                                'action'     => 'Home',
                            ]
                        ]
                    ],
                    'privatestatsdownloadbyfiletype' => [
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => [
                            'route'    => '/PrivateStatsDownloadbyfiletype[/:action]',
                            'defaults' => [
                                'controller' => 'AdminPrivateStatsDownloadbyfiletype',
                                'action'     => 'Home',
                            ]
                        ]
                    ],
                    'privatestatsdownloadbyuser' => [
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => [
                            'route'    => '/PrivateStatsDownloadbyuser[/:action]',
                            'defaults' => [
                                'controller' => 'AdminPrivateStatsDownloadbyuser',
                                'action'     => 'Home',
                            ]
                        ]
                    ],
                    'settings' => [
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => [
                            'route'    => '/Settings[/:action]',
                            'defaults' => [
                                'controller' => 'AdminSettings',
                                'action'     => 'Home',
                            ]
                        ]
                    ],
                    'statistics' => [
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => [
                            'route'    => '/Statistics[/:action]',
                            'defaults' => [
                                'controller' => 'AdminStatistics',
                                'action'     => 'Home',
                            ]
                        ]
                    ],
                    'tags' => [
                        'type' => 'Zend\Mvc\Router\Http\Segment',
                        'options' => [
                            'route'    => '/Tags[/:action]',
                            'defaults' => [
                                'controller' => 'AdminTags',
                                'action'     => 'Home',
                            ]
                        ]
                    ],
                ],
            ],
        ],
    ],
];

return $config;