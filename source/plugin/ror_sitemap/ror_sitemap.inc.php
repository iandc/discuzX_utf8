<?php
if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

define('PLUGIN_NAME', 'ror_sitemap');

require_once libfile('lib/index', 'plugin/'.PLUGIN_NAME);

$index = new lib_index();

$index->run();