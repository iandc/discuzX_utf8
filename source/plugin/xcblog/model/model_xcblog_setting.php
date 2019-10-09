<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
class model_xcblog_setting
{
    public function getDefault()
    {
		$setting = array (
            'home_paper_num' => 10,
            'list_paper_num' => 20,
		);
		return $setting;
    }
	public function get()
	{
		$setting = $this->getDefault();
		global $_G;
		if (isset($_G['setting']['xcblog_config'])){
			$config = unserialize($_G['setting']['xcblog_config']);
			foreach ($setting as $key => &$item) {
				if (isset($config[$key])) $item = $config[$key];
			}
		}
		return $setting;
	}
}
?>