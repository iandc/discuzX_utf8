<?php
if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

define('PLUGIN_NAME', 'ror_sitemap');

require_once libfile('lib/admin', 'plugin/'.PLUGIN_NAME);

$admin = new lib_admin();

$admin->run();