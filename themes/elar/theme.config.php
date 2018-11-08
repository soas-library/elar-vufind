<?php
return array(
    'extends' => 'bootprint3',
    'css' => array(
        //'vendor/bootstrap.min.css',
        //'vendor/bootstrap-accessibility.css',
        'scanbit.css',
        'bootstrap-custom.css',
        'compiled.css',
        'vendor/font-awesome.min.css',
        'vendor/bootstrap-slider.css',
        'print.css:print',
	'soas.css',
	'video-js.css',
	'tabs.css',
	'dataTables.bootstrap.min.css',
	//SCB TABLE SORTER
	'tablesorter.css',
	//SCB TABLE SORTER
	//SCB Mediaelement plugin
        'build/mediaelementplayer.min.css',
        //SCB Mediaelement plugin
    ),
    'js' => array(
        'vendor/base64.js:lt IE 10', // btoa polyfill
        'vendor/jquery.min.js',
        'vendor/bootstrap.min.js',
        'vendor/bootstrap-accessibility.min.js',
        //'vendor/bootlint.min.js',
        'vendor/typeahead.js',
        'vendor/validator.min.js',
        'vendor/rc4.js',
        //CUSTOM CODE FOR ELAR
        //@author Simon Barron sb174@soas.ac.uk
        // 'vendor/browse.js',
        //END
        'common.js',
        'lightbox.js',
        'tabs.js',
        'videojs-ie8.min.js',
        'video.js',
        'wavesurfer.min.js',
        'jquery.dataTables.min.js',
        'dataTables.bootstrap.min.js',
        'dataTables.js',
        //SCB TABLE FOR LIST
        'jquery.tablesorter.js',
        //SCB TABLE FOR LIST
	//SCB Mediaelement plugin
        //'build/jquery.js',
	'build/mediaelement-and-player.min.js',
        //SCB Mediaelement plugin
        //Statistics management
        'check_files_statistics.js',
        //Statistics management

    ),
    'less' => array(
        'active' => false,
        'compiled.less',
    ),
    'favicon' => 'elar-favicon.ico',
    'helpers' => array(
        'factories' => array(
            'flashmessages' => 'VuFind\View\Helper\Bootstrap3\Factory::getFlashmessages',
            'layoutclass' => 'VuFind\View\Helper\Bootstrap3\Factory::getLayoutClass',
        ),
        'invokables' => array(
            'highlight' => 'VuFind\View\Helper\Bootstrap3\Highlight',
            'search' => 'VuFind\View\Helper\Bootstrap3\Search',
            'vudl' => 'VuDL\View\Helper\Bootstrap3\VuDL',
        )
    )
);
