<?php

/**
 * Copyright 2001-2099 1314 ѧϰ.��.
 * This is NOT a freeware, use is subject to license terms
 * $Id: hook.class.php 3464 2019-11-20 20:15:32
 * Ӧ���ۺ����⣺http://www.1314study.com/services.php?mod=issue������ http://t.cn/RU4FEnD��
 * Ӧ����ǰ��ѯ��QQ 153.26.940
 * Ӧ�ö��ƿ�����QQ 64.330.67.97
 * �����Ϊ 1314ѧϰ����www.1314study.com�� ����������ԭ�����, ����ӵ�а�Ȩ��
 * δ�������ù������ۡ�������ʹ�á��޸ģ����蹺������ϵ���ǻ����Ȩ��
 */

if (!defined('IN_DISCUZ')) {
exit('Access Denied');
}
class plugin_addon_seo_linksubmit {

	function global_footer() {
		global $_G, $op, $article;
		$return = '';
		$splugin_setting = $_G['cache']['plugin']['addon_seo_linksubmit'];
		if (CURSCRIPT == 'portal' && CURMODULE == 'portalcp' && $_GET['ac'] == 'article' && submitcheck("articlesubmit") && $op == 'add_success') {
			loadcache(array('saddon_seo_linksubmit'));
			$linksubmit = $_G['cache']['saddon_seo_linksubmit'];
			$todaytime = strtotime(dgmdate(TIMESTAMP, 'Y-m-d', $_G['setting']['timeoffset']));
			if ($linksubmit['todaytime'] < $todaytime || $linksubmit['remain'] > 0) {
				include_once libfile('function/core', 'plugin/addon_seo_linksubmit/source');
				//$article['original'] = $splugin_setting['portal_original'] ? 1 : 0;
				addon_seo_linksubmit_baidu($article);
			}
		}elseif (CURSCRIPT == 'portal' && CURMODULE == 'view' && !empty($article) && $splugin_setting['portal_oldarticle_radio']) {
			$linkinfo = C::t('#addon_seo_linksubmit#addon_seo_linksubmit')->fetch_by_search(array('postid' => $article['aid'], 'posttype' => 2));
			if(empty($linkinfo)){
				loadcache(array('saddon_seo_linksubmit'));
				$linksubmit = $_G['cache']['saddon_seo_linksubmit'];
				$todaytime = strtotime(dgmdate(TIMESTAMP, 'Y-m-d', $_G['setting']['timeoffset']));
				if ($linksubmit['todaytime'] < $todaytime || $linksubmit['remain'] > 0) {
					include_once libfile('function/core', 'plugin/addon_seo_linksubmit/source');
					//$article['original'] = $splugin_setting['portal_original'] ? 1 : 0;
					addon_seo_linksubmit_baidu($article);
				}
			}
		}
		
		if ($splugin_setting['js_push'] && !isset($_GET['formhash']) && !$_G['inshowmessage']) {
			$return = $splugin_setting['js_code'];
		}

		return $return;
	}
}

class plugin_addon_seo_linksubmit_forum extends plugin_addon_seo_linksubmit {

	function viewthread_postheader() {
		global $_G;
		$return = array();
		if (isset($_G['thread']['linksubmit']) && empty($_G['thread']['linksubmit']) && $_G['thread']['displayorder'] >= 0) {
			if ($_G['page'] == 1 && !$_G['inajax']) {
				$splugin_setting = $_G['cache']['plugin']['addon_seo_linksubmit'];

				$study_fids = unserialize($splugin_setting['study_fids']);
				if (in_array($_G['fid'], $study_fids)) {
					if ($_G['thread']['dateline'] > strtotime($splugin_setting['forum_datetime'])) {
						loadcache(array('saddon_seo_linksubmit'));
						$linksubmit = $_G['cache']['saddon_seo_linksubmit'];
						$todaytime = strtotime(dgmdate(TIMESTAMP, 'Y-m-d', $_G['setting']['timeoffset']));
						if ($linksubmit['todaytime'] < $todaytime || $linksubmit['remain'] > 0) {
							include_once libfile('function/core', 'plugin/addon_seo_linksubmit/source');
							//$_G['thread']['original'] = $splugin_setting['forum_original'] ? 1 : 0;
							addon_seo_linksubmit_baidu($_G['thread']);
							$_G['thread']['linksubmit'] = 1;
						}
					}
				}
				if (empty($_G['thread']['linksubmit'])) {
					$study_gids = unserialize($splugin_setting['study_gids']);
					if (in_array($_G['groupid'], $study_gids)) {
						$return[0] = '<span class="pipe">|</span><a href="plugin.php?id=addon_seo_linksubmit&tid=' . $_G['tid'] . '" onclick="showWindow(\'addon_seo_linksubmit\', this.href, \'get\');" style="color:#FF0000;font-weight: bold;">&#x4E3B;&#x52A8;&#x63A8;&#x9001;</a>';
					}
				}
			}
		}
		return $return;
	}
}


//Copyright 2001-2099 .1314.ѧϰ��.
//This is NOT a freeware, use is subject to license terms
//$Id: hook.class.php 3926 2019-11-20 12:15:32
//Ӧ���ۺ����⣺http://www.1314study.com/services.php?mod=issue ������ http://t.cn/EUPqQW1��
//Ӧ����ǰ��ѯ��QQ 15.3269.40
//Ӧ�ö��ƿ�����QQ 643.306.797
//�����Ϊ 131.4ѧϰ����www.1314Study.com�� ����������ԭ�����, ����ӵ�а�Ȩ��
//δ�������ù������ۡ�������ʹ�á��޸ģ����蹺������ϵ���ǻ����Ȩ��