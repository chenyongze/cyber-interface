<?php

	$user = model('user');
	$user_id = $user->sessionCheck(function(){
		json(false, '未登录');
	});
	$admin = $user->adminCheck();
	if($admin) $user_id = 0;

	$site_id = filter('site_id', '/^[0-9]{1,9}$/', 'siteID格式错误');
	$page = filter('page', '/^[0-9]{1,9}$/', '页码格式错误');
	$limit = filter('limit', '/^[0-9]{1,9}$/', '偏移格式错误');

	/*$site_id = 0;
	$page = 1;
	$limit = 10;*/

	if($limit <= 0) $limit = 1;
	if($page < 1) $page = 1;
	$start = ($page - 1) * $limit;

	$siteModel = model('site');
	if($site_id == 0) $info = $siteModel->get($user_id, 'user_id');
	else $info = $siteModel->get($site_id);

	if(empty($info)) json(false, '站点不存在');
	if($info['remove'] > 0) json(false, '站点已经被移除');
	if(!$admin) if($info['user_id'] != $user_id) json(false, '不允许操作他人站点');

	$awsModel = model('aws');
	//$total = 1;
	$result = $awsModel->visitor($info['site_id'], $start, $limit);
	$result['limit'] = $limit;
	$result['page'] = $page;
	foreach ($result['list'] as $key => $value) {
		foreach ($value as $subkey => $subvalue) {
			if($subvalue == 'None'){
				$result['list'][$key][$subkey] = '未知';
			}
		}
	}

	json(true, $result);


?>