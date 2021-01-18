<?php
if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
    exit('Access denied');
}

$plugin_name = 'ror_sitemap';

$sql = <<<EOT

DROP TABLE IF EXISTS pre_plugin_{$plugin_name};
DROP TABLE IF EXISTS pre_plugin_{$plugin_name}_thread_push;
DROP TABLE IF EXISTS pre_plugin_{$plugin_name}_portal_push;

EOT;

runquery($sql);

C::t('common_syscache')->delete($plugin_name);

//删除多余缓存
$local_path_grab = DISCUZ_ROOT.'data/plugindata/'.$plugin_name.'/';
removeDir($local_path_grab);
function removeDir($dirName)
{
    if(! is_dir($dirName)){
        return FALSE;
    }
    
    $handle = @opendir($dirName);
    while(($file = @readdir($handle)) !== FALSE)
    {
        if($file != '.' && $file != '..'){
            $dir = $dirName.'/'.$file;
            if(is_dir($dir)){
                removeDir($dir);
            }else{
                @unlink($dir);
            }
        }
    }
    
    closedir($handle);

    rmdir($dirName);
}

$finish = true;