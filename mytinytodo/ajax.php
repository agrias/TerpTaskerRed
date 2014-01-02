<?php

/*
	This file is part of myTinyTodo.
	(C) Copyright 2009-2010 Max Pozdeev <maxpozdeev@gmail.com>
	Licensed under the GNU GPL v3 license. See file COPYRIGHT for details.
*/ 

//set_error_handler('myErrorHandler');
//set_exception_handler('myExceptionHandler');

require_once('./init.php');

$db = DBConnection::instance();

if(isset($_GET['loadLists']))
{
	stop_gpc($_GET);
	$id = (int)_get('id');
	//error_log('huh?');
	if($needAuth && !is_logged()) $sqlWhere = 'WHERE published=1 AND userId=' . $id;
	else $sqlWhere = 'WHERE userID = ' . $id;
	$t = array();
	$t['total'] = 0;
	$q = $db->dq("SELECT * FROM Category $sqlWhere ORDER BY ow ASC, categoryID ASC");
	while($r = $q->fetch_assoc($q))
	{
		$t['total']++;
		$t['list'][] = prepareList($r);
	}
	jsonExit($t);
}
elseif(isset($_GET['loadTasks']))
{
	stop_gpc($_GET);
	$listId = (int)_get('list');
	$userId = (int)_get('id');
	$context = (int)_get('context');
	check_read_access($listId);

	$sqlWhere = '';
	$inner = '';
	if($listId == -1) {
		$userLists = getUserListsSimple($userId);
		$sqlWhere .= " AND mtt_todolist.categoryID IN (". implode(array_keys($userLists), ','). ") ";
	}
	else if ($context != -1)
	{
		$sqlWhere .= " AND mtt_todolist.contextID = $context";
	}
	else $sqlWhere .= " AND mtt_todolist.categoryID=". $listId;
	if(_get('compl') == 0) $sqlWhere .= ' AND compl=0';
	
	$tag = trim(_get('t'));
	if($tag != '')
	{
		$at = explode(',', $tag);
		$tagIds = array();
		$tagExIds = array();
		foreach($at as $i=>$atv) {
			$atv = trim($atv);
			if($atv == '' || $atv == '^') continue;
			if(substr($atv,0,1) == '^') {
				$tagExIds[] = getTagId(substr($atv,1), $userId);
			} else {
				$tagIds[] = getTagId($atv, $userId);
			}
		}

		if(sizeof($tagIds) > 1) {
			$inner .= "INNER JOIN (SELECT task_id, COUNT(tag_id) AS c FROM mtt_tag2task WHERE categoryID=$listId AND tag_id IN (".
						implode(',',$tagIds). ") GROUP BY task_id) AS t2t ON id=t2t.task_id";
			$sqlWhere = " AND c=". sizeof($tagIds); //overwrite sqlWhere!
		}
		elseif($tagIds) {
			$inner .= "INNER JOIN mtt_tag2task ON id=task_id";
			$sqlWhere .= " AND tag_id = ". $tagIds[0];
		}
		
		if($tagExIds) {
			$sqlWhere .= " AND id NOT IN (SELECT DISTINCT task_id FROM mtt_tag2task WHERE categoryID=$listId AND tag_id IN (".
						implode(',',$tagExIds). "))"; //DISTINCT ?
		}
	}

	$s = trim(_get('s'));
	if($s != '') $sqlWhere .= " AND (title LIKE ". $db->quoteForLike("%%%s%%",$s). " OR note LIKE ". $db->quoteForLike("%%%s%%",$s). ")";
	$sort = (int)_get('sort');
	$sqlSort = "ORDER BY compl ASC, ";
	if($sort == 1) $sqlSort .= "prio DESC, ddn ASC, duedate ASC, ow ASC";		// byPrio
	elseif($sort == 101) $sqlSort .= "prio ASC, ddn DESC, duedate DESC, ow DESC";	// byPrio (reverse)
	elseif($sort == 2) $sqlSort .= "ddn ASC, duedate ASC, prio DESC, ow ASC";	// byDueDate
	elseif($sort == 102) $sqlSort .= "ddn DESC, duedate DESC, prio ASC, ow DESC";// byDueDate (reverse)
	elseif($sort == 3) $sqlSort .= "d_created ASC, prio DESC, ow ASC";			// byDateCreated
	elseif($sort == 103) $sqlSort .= "d_created DESC, prio ASC, ow DESC";		// byDateCreated (reverse)
	elseif($sort == 4) $sqlSort .= "d_edited ASC, prio DESC, ow ASC";			// byDateModified
	elseif($sort == 104) $sqlSort .= "d_edited DESC, prio ASC, ow DESC";		// byDateModified (reverse)
	else $sqlSort .= "ow ASC";

	$t = array();
	$t['total'] = 0;
	$t['list'] = array();
	$q = $db->dq("SELECT *, duedate IS NULL AS ddn FROM mtt_todolist $inner WHERE 1=1 AND mtt_todolist.userID=$userId $sqlWhere $sqlSort");
	while($r = $q->fetch_assoc($q))
	{
		$t['total']++;
		$t['list'][] = prepareTaskRow($r);
	}
	if(_get('setCompl') && have_write_access($listId)) {
		$bitwise = (_get('compl') == 0) ? 'taskview & ~1' : 'taskview | 1';
		$db->dq("UPDATE Category SET taskview=$bitwise WHERE categoryID=$listId");
	}
	jsonExit($t);
}
elseif(isset($_GET['newTask']))
{
	stop_gpc($_POST);
	$listId = (int)_post('list');
	$userId = (int)_post('id');
	check_write_access($listId);
	$t = array();
	$t['total'] = 0;
	$title = trim(_post('title'));
	$prio = 0;
	$tags = '';
	if(Config::get('smartsyntax') != 0)
	{
		$a = parse_smartsyntax($title);
		if($a === false) {
			jsonExit($t);
		}
		$title = $a['title'];
		$prio = $a['prio'];
		$tags = $a['tags'];
	}
	if($title == '') {
		jsonExit($t);
	}
	if(Config::get('autotag')) $tags .= ','._post('tag');
	$q = $db->dq("SELECT * FROM Context WHERE userID=$userId AND name='None'");
	$context = 0;
	while($r = $q->fetch_assoc($q))
	{
		$context = $r['contextID'];
	}

	$ow = 1 + (int)$db->sq("SELECT MAX(ow) FROM mtt_todolist WHERE categoryID=$listId AND compl=0");
	$db->ex("BEGIN");
	$db->dq("INSERT INTO mtt_todolist (categoryID,title,d_created,d_edited,ow,prio, userID, contextID) VALUES (?,?,?,?,?,?,?,?)",
				array($listId, $title, time(), time(), $ow, $prio, $userId, $context) );
	$id = $db->last_insert_id();
	if($tags != '')
	{
		$aTags = prepareTags($tags, $userId);
		if($aTags) {
			addTaskTags($id, $aTags['ids'], $listId, $userId);
			$db->ex("UPDATE mtt_todolist SET tags=?,tags_id=? WHERE id=$id", array(implode(',',$aTags['tags']), implode(',',$aTags['ids'])));
		}
	}
	$db->ex("COMMIT");
	$r = $db->sqa("SELECT * FROM mtt_todolist WHERE id=$id AND userID=$userId");
	$t['list'][] = prepareTaskRow($r);
	$t['total'] = 1;
	jsonExit($t);
}
elseif(isset($_GET['fullNewTask']))
{
	stop_gpc($_POST);
	$listId = (int)_post('list');
	$userId = (int)_post('id');
	check_write_access($listId);
	$title = trim(_post('title'));
	$note = str_replace("\r\n", "\n", trim(_post('note')));
	$prio = (int)_post('prio');
	if($prio < -1) $prio = -1;
	elseif($prio > 2) $prio = 2;
	$duedate = parse_duedate(trim(_post('duedate')), trim(_post('duetime')));
	$estMins = (int)_post('estMins');
	$estHours = (int)_post('estHours');
	$contexts = (int)_post('contexts');
	$ereminder = (int)_post('ereminder'); 
	$preminder = (int)_post('preminder');
	if ($ereminder == -1)
		$ereminder = NULL;
	if ($preminder == -1)
		$preminder = NULL;
	$t = array();
	$t['total'] = 0;
	if($title == '') {
		jsonExit($t);
	}
	$tags = trim(_post('tags'));
	if(Config::get('autotag')) $tags .= ','._post('tag');
	$ow = 1 + (int)$db->sq("SELECT MAX(ow) FROM mtt_todolist WHERE categoryID=$listId AND userID = $userId AND compl=0");
	$db->ex("BEGIN");
	$db->dq("INSERT INTO mtt_todolist (categoryID,title,d_created,d_edited,ow,prio,note,duedate, estMins, estHours, contextID, emailReminder, popupReminder, userID) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
				array($listId, $title, time(), time(), $ow, $prio, $note, $duedate, $estMins, $estHours, $contexts, $ereminder, $preminder, $userId) );
	$id = $db->last_insert_id();
	if($tags != '')
	{
		$aTags = prepareTags($tags, $userId);
		if($aTags) {
			addTaskTags($id, $aTags['ids'], $listId, $userId);
			$db->ex("UPDATE mtt_todolist SET tags=?,tags_id=? WHERE id=$id", array(implode(',',$aTags['tags']), implode(',',$aTags['ids'])));
		}
	}
	$db->ex("COMMIT");
	$r = $db->sqa("SELECT * FROM mtt_todolist WHERE id=$id");
	$t['list'][] = prepareTaskRow($r);
	$t['total'] = 1;
	jsonExit($t);
}
elseif(isset($_GET['deleteTask']))
{
	$id = (int)_post('id');
	$deleted = deleteTask($id);
	$t = array();
	$t['total'] = $deleted;
	$t['list'][] = array('id'=>$id);
	jsonExit($t);
}
elseif(isset($_GET['completeTask']))
{
	check_write_access();
	$id = (int)_post('id');
	$userId = (int)_post('userId');
	$compl = _post('compl') ? 1 : 0;
	$listId = (int)$db->sq("SELECT CategoryID FROM mtt_todolist WHERE id=$id");
	if($compl) 	$ow = 1 + (int)$db->sq("SELECT MAX(ow) FROM mtt_todolist WHERE CategoryID=$listId AND compl=1 AND userID=$userId");
	else $ow = 1 + (int)$db->sq("SELECT MAX(ow) FROM mtt_todolist WHERE CategoryID=$listId AND compl=0 AND userID=$userId");
	$dateCompleted = $compl ? time() : 0;
	$db->dq("UPDATE mtt_todolist SET compl=$compl,ow=$ow,d_completed=?,d_edited=? WHERE id=$id",
				array($dateCompleted, time()) );
	$t = array();
	$t['total'] = 1;
	$r = $db->sqa("SELECT * FROM mtt_todolist WHERE id=$id");
	$t['list'][] = prepareTaskRow($r);
	jsonExit($t);
}
elseif(isset($_GET['editNote']))
{
	check_write_access();
	$id = (int)_post('id');
	stop_gpc($_POST);
	$note = str_replace("\r\n", "\n", trim(_post('note')));
	$db->dq("UPDATE mtt_todolist SET note=?,d_edited=? WHERE id=$id", array($note, time()) );
	$t = array();
	$t['total'] = 1;
	$t['list'][] = array('id'=>$id, 'note'=>nl2br(escapeTags($note)), 'noteText'=>(string)$note);
	jsonExit($t);
}
elseif(isset($_GET['editTask']))
{
	check_write_access();
	$id = (int)_post('id');
	stop_gpc($_POST);
	$title = trim(_post('title'));
	$note = str_replace("\r\n", "\n", trim(_post('note')));
	$prio = (int)_post('prio');
	if($prio < -1) $prio = -1;
	elseif($prio > 2) $prio = 2;
	$duedate = parse_duedate(trim(_post('duedate')), trim(_post('duetime')));
	
	$estMins = (int)_post('estMins');
	$estHours = (int)_post('estHours');
	$contexts = (int)_post('contexts');
	$ereminder = (int)_post('ereminder');
	$preminder = (int)_post('preminder');
	if ($ereminder == -1)
		$ereminder = NULL;
	if ($preminder == -1)
		$preminder = NULL;
	$userId = (int)_post('userId');
	$t = array();
	$t['total'] = 0;
	if($title == '') {
		jsonExit($t);
	}
	$listId = $db->sq("SELECT CategoryID FROM mtt_todolist WHERE id=$id");
	$tags = trim(_post('tags'));
	$db->ex("BEGIN");
	$db->ex("DELETE FROM mtt_tag2task WHERE task_id=$id");
	$aTags = prepareTags($tags, $userId);
	if($aTags) {
		$tags = implode(',', $aTags['tags']);
		$tags_id = implode(',',$aTags['ids']);
		addTaskTags($id, $aTags['ids'], $listId, $userId);
	}
	$db->dq("UPDATE mtt_todolist SET title=?,note=?,prio=?,tags=?,tags_id=?,duedate=?,d_edited=?,estMins=?, estHours=?, contextID=?, emailReminder=?, popupReminder=? WHERE id=$id", array($title, $note, $prio, $tags, $tags_id, $duedate, time(), $estMins, $estHours, $contexts, $ereminder, $preminder) );
	$db->ex("COMMIT");
	$r = $db->sqa("SELECT * FROM mtt_todolist WHERE id=$id");
	if($r) {
		$t['list'][] = prepareTaskRow($r);
		$t['total'] = 1;
	}
	jsonExit($t);
}
elseif(isset($_GET['changeOrder']))
{
	check_write_access();
	stop_gpc($_POST);
	$s = _post('order');
	parse_str($s, $order);
	$t = array();
	$t['total'] = 0;
	if($order)
	{
		$ad = array();
		foreach($order as $id=>$diff) {
			$ad[(int)$diff][] = (int)$id;
		}
		$db->ex("BEGIN");
		foreach($ad as $diff=>$ids) {
			if($diff >=0) $set = "ow=ow+".$diff;
			else $set = "ow=ow-".abs($diff);
			$db->dq("UPDATE mtt_todolist SET $set,d_edited=? WHERE id IN (".implode(',',$ids).")", array(time()) );
		}
		$db->ex("COMMIT");
		$t['total'] = 1;
	}
	jsonExit($t);
}
elseif(isset($_POST['login']))
{
	$t = array('logged' => 0);
	if(!$needAuth) {
		$t['disabled'] = 1;
		jsonExit($t);
	}
	stop_gpc($_POST);
	$password = _post('password');
	if($password == Config::get('password')) {
		$t['logged'] = 1;
		session_regenerate_id(1);
		$_SESSION['logged'] = 1;
	}
	jsonExit($t);
}
elseif(isset($_POST['logout']))
{
	unset($_SESSION['logged']);
	$t = array('logged' => 0);
	jsonExit($t);
}
elseif(isset($_GET['suggestTags']))
{
	$listId = (int)_get('list');
	check_read_access($listId);
	$begin = trim(_get('q'));
	$limit = (int)_get('limit');
	if($limit<1) $limit = 8;
	$q = $db->dq("SELECT name,id FROM mtt_tags INNER JOIN mtt_tag2task ON id=tag_id WHERE categoryID=$listId AND name LIKE ".
					$db->quoteForLike('%s%%',$begin) ." GROUP BY tag_id ORDER BY name LIMIT $limit");
	$s = '';
	while($r = $q->fetch_row()) {
		$s .= "$r[0]|$r[1]\n";
	}
	echo htmlarray($s);
	exit; 
}
elseif(isset($_GET['setPrio']))
{
	check_write_access();
	$id = (int)$_GET['setPrio'];
	$prio = (int)_get('prio');
	if($prio < -1) $prio = -1;
	elseif($prio > 2) $prio = 2;
	$db->ex("UPDATE mtt_todolist SET prio=$prio,d_edited=? WHERE id=$id", array(time()) );
	$t = array();
	$t['total'] = 1;
	$t['list'][] = array('id'=>$id, 'prio'=>$prio);
	jsonExit($t);
}
elseif(isset($_GET['setEst']))
{
	
	check_write_access();
	$id = (int)$_GET['setEst'];
	$estMins = (int)_post('estMins');
	$estHours = (int)_post('estHours');
	$db->ex("UPDATE mtt_todolist SET estMins=?, estHours=?, d_edited=? WHERE id=$id", array($estMins, $estHours, time()) );
	$t = array();
	$t['total'] = 1;
	$t['list'][] = array('id'=>$id, 'estMins'=>$estMins, 'estHours'=>$estHours);
	jsonExit($t);
}
elseif(isset($_GET['tagCloud']))
{
	$listId = (int)_get('list');
	check_read_access($listId);

	$q = $db->dq("SELECT name,tag_id,COUNT(tag_id) AS tags_count FROM mtt_tag2task INNER JOIN mtt_tags ON tag_id=id ".
						"WHERE categoryID=$listId GROUP BY (tag_id) ORDER BY tags_count ASC");
	$at = array();
	$ac = array();
	while($r = $q->fetch_assoc()) {
		$at[] = array('name'=>$r['name'], 'id'=>$r['tag_id']);
		$ac[] = $r['tags_count'];
	}

	$t = array();
	$t['total'] = 0;
	$count = sizeof($at);
	if(!$count) {
		jsonExit($t);
	}

	$qmax = max($ac);
	$qmin = min($ac);
	if($count >= 10) $grades = 10;
	else $grades = $count;
	$step = ($qmax - $qmin)/$grades;
	foreach($at as $i=>$tag)
	{
		$t['cloud'][] = array('tag'=>htmlarray($tag['name']), 'id'=>(int)$tag['id'], 'w'=> tag_size($qmin,$ac[$i],$step) );
	}
	$t['total'] = $count;
	jsonExit($t);
}
elseif(isset($_GET['addList']))
{
	check_write_access();
	stop_gpc($_POST);
	$userId = (int)_post('id');
	$t = array();
	$t['total'] = 0;
	$name = str_replace(array('"',"'",'<','>','&'),array('','','','',''),trim(_post('name')));
	$ow = 1 + (int)$db->sq("SELECT MAX(ow) FROM Category");
	//HARDCODED: COLOR = BLUE ON NEW LIST
	$db->dq("INSERT INTO Category (name,ow,d_created,d_edited, userID, color) VALUES (?,?,?,?,?,?)",
				array($name, $ow, time(), time(), $userId, 'blue') );
	$id = $db->last_insert_id();
	$t['total'] = 1;
	$r = $db->sqa("SELECT * FROM Category WHERE categoryID=$id");
	$t['list'][] = prepareList($r);
	jsonExit($t);
}
elseif(isset($_GET['getContexts']))
{
	check_read_access();
	
	$userId = (int)_post('id');
	$t = array();
	$q = $db->dq("SELECT * FROM Context WHERE userID=$userId");
	while ($r = $q->fetch_assoc($q))
	{
		$t['list'][] = prepareContext($r);
	}
	stop_gpc($_POST);
	jsonExit($t);
}
elseif(isset($_GET['getDefaults']))
{
	check_read_access();
	stop_gpc($_POST);
	$id = (int)_post('id');
	$t = array();
	$q = $db->dq("SELECT * FROM members WHERE id=$id");
	$r = $q->fetch_assoc($q);
	$t['emailReminder'] = $r['task_email_remind'];
	$t['popupReminder'] = $r['task_popup_remind'];
	if ($t['emailReminder'] == NULL)
		$t['emailReminder'] = -1;
	if ($t['popupReminder'] == NULL)
		$t['popupReminder'] = -1;
	$t['total'] = 1;
	//error_log("test NEW: " . $t['emailReminder']);
	jsonExit($t);
}
elseif(isset($_GET['renameList']))
{
	check_write_access();
	stop_gpc($_POST);
	$t = array();
	$t['total'] = 0;
	$id = (int)_post('list');
	$name = str_replace(array('"',"'",'<','>','&'),array('','','','',''),trim(_post('name')));
	$db->dq("UPDATE Category SET name=?,d_edited=? WHERE categoryID=$id", array($name, time()) );
	$t['total'] = $db->affected();
	$r = $db->sqa("SELECT * FROM Category WHERE categoryID=$id");
	$t['list'][] = prepareList($r);
	jsonExit($t);
}
elseif(isset($_GET['deleteList']))
{
	check_write_access();
	stop_gpc($_POST);
	$t = array();
	$t['total'] = 0;
	$id = (int)_post('list');
	$db->ex("BEGIN");
	$db->ex("DELETE FROM Category WHERE categoryID=$id");
	$t['total'] = $db->affected();
	if($t['total']) {
		$db->ex("DELETE FROM mtt_tag2task WHERE categoryID=$id");
		$db->ex("DELETE FROM mtt_todolist WHERE cateogryID=$id");
	}
	$db->ex("COMMIT");
	jsonExit($t);
}
elseif(isset($_GET['setSort']))
{
	check_write_access();
	$listId = (int)_post('list');
	$sort = (int)_post('sort');
	if($sort < 0 || $sort > 104) $sort = 0;
	elseif($sort < 101 && $sort > 4) $sort = 0;
	$db->ex("UPDATE Category SET sorting=$sort,d_edited=? WHERE categoryID=$listId", array(time()));
	jsonExit(array('total'=>1));
}
elseif(isset($_GET['publishList']))
{
	check_write_access();
	$listId = (int)_post('list');
	$publish = (int)_post('publish');
	$db->ex("UPDATE Category SET published=?,d_created=? WHERE categoryID=$listId", array($publish ? 1 : 0, time()));
	jsonExit(array('total'=>1));
}
elseif(isset($_GET['moveTask']))
{
	check_write_access();
	$id = (int)_post('id');
	$fromId = (int)_post('from');
	$toId = (int)_post('to');
	$result = moveTask($id, $toId);
	$t = array('total' => $result ? 1 : 0);
	if($fromId == -1 && $result && $r = $db->sqa("SELECT * FROM mtt_todolist WHERE id=$id")) {
		$t['list'][] = prepareTaskRow($r);
	}
	jsonExit($t);
}
elseif(isset($_GET['changeListOrder']))
{
	check_write_access();
	stop_gpc($_POST);
	$order = (array)_post('order');
	$t = array();
	$t['total'] = 0;
	if($order)
	{
		$a = array();
		$setCase = '';
		foreach($order as $ow=>$id) {
			$id = (int)$id;
			$a[] = $id;
			$setCase .= "WHEN CategoryID=$id THEN $ow\n";
		}
		$ids = implode($a, ',');
		$db->dq("UPDATE Category SET d_edited=?, ow = CASE\n $setCase END WHERE categoryID IN ($ids)",
					array(time()) );
		$t['total'] = 1;
	}
	jsonExit($t);
}
elseif(isset($_GET['parseTaskStr']))
{
	check_write_access();
	stop_gpc($_POST);
	$t = array(
		'title' => trim(_post('title')),
		'prio' => 0,
		'tags' => ''
	);
	if(Config::get('smartsyntax') != 0 && (false !== $a = parse_smartsyntax($t['title'])))
	{
		$t['title'] = $a['title'];
		$t['prio'] = $a['prio'];
		$t['tags'] = $a['tags'];
	}
	jsonExit($t);
}
elseif(isset($_GET['clearCompletedInList']))
{
	check_write_access();
	stop_gpc($_POST);
	$t = array();
	$t['total'] = 0;
	$listId = (int)_post('list');
	$db->ex("BEGIN");
	$db->ex("DELETE FROM mtt_tag2task WHERE task_id IN (SELECT id FROM mtt_todolist WHERE categoryID=? and compl=1)", array($listId));
	$db->ex("DELETE FROM mtt_todolist WHERE categoryID=$listId and compl=1");
	$t['total'] = $db->affected();
	$db->ex("COMMIT");
	jsonExit($t);
}
elseif(isset($_GET['setShowNotesInList']))
{
	check_write_access();
	$listId = (int)_post('list');
	$flag = (int)_post('shownotes');
	$bitwise = ($flag == 0) ? 'taskview & ~2' : 'taskview | 2';
	$db->dq("UPDATE Cateogry SET taskview=$bitwise WHERE categoryID=$listId");
	jsonExit(array('total'=>1));
}
elseif(isset($_GET['setHideList']))
{
	check_write_access();
	$listId = (int)_post('list');
	$flag = (int)_post('hide');
	$bitwise = ($flag == 0) ? 'taskview & ~4' : 'taskview | 4';
	$db->dq("UPDATE Category SET taskview=$bitwise WHERE categoryID=$listId");
	jsonExit(array('total'=>1));	
}


###################################################################################################

function prepareTaskRow($r)
{
	$lang = Lang::instance();
	$dueA = prepare_duedate($r['duedate']);
	$duetime = prepare_duetime($r['duedate']);
	$formatCreatedInline = $formatCompletedInline = Config::get('dateformatshort');
	if(date('Y') != date('Y',$r['d_created'])) $formatCreatedInline = Config::get('dateformat2');
	if($r['d_completed'] && date('Y') != date('Y',$r['d_completed'])) $formatCompletedInline = Config::get('dateformat2');

	$dCreated = timestampToDatetime($r['d_created']);
	$dCompleted = $r['d_completed'] ? timestampToDatetime($r['d_completed']) : '';

	return array(
		'id' => $r['id'],
		'title' => escapeTags($r['title']),
		'listId' => $r['categoryID'],
		'date' => htmlarray($dCreated),
		'dateInt' => (int)$r['d_created'],
		'dateInline' => htmlarray(formatTime($formatCreatedInline, $r['d_created'])),
		'dateInlineTitle' => htmlarray(sprintf($lang->get('taskdate_inline_created'), $dCreated)),
		'dateEditedInt' => (int)$r['d_edited'],
		'dateCompleted' => htmlarray($dCompleted),
		'dateCompletedInline' => $r['d_completed'] ? htmlarray(formatTime($formatCompletedInline, $r['d_completed'])) : '',
		'dateCompletedInlineTitle' => htmlarray(sprintf($lang->get('taskdate_inline_completed'), $dCompleted)),
		'compl' => (int)$r['compl'],
		'prio' => $r['prio'],
		'note' => nl2br(escapeTags($r['note'])),
		'noteText' => (string)$r['note'],
		'ow' => (int)$r['ow'],
		'tags' => htmlarray($r['tags']),
		'tags_ids' => htmlarray($r['tags_id']),
		'duedate' => $dueA['formatted'],
		'dueClass' => $dueA['class'],
		'dueStr' => htmlarray($r['compl'] && $dueA['timestamp'] ? formatTime($formatCompletedInline, $dueA['timestamp']) : $dueA['str']),
		'dueInt' => date2int($r['duedate']),
		'dueTitle' => htmlarray(sprintf($lang->get('taskdate_inline_duedate'), $dueA['formatted'])),
		'estMins' => (int)$r['estMins'],
		'estHours' => (int)$r['estHours'],
		'contexts' => (int)$r['contextID'],
		'duetime' => $duetime,
		'timeInt' => time2int($duetime),
		'ereminder' => ($r['emailReminder'] == NULL ? -1 : $r['emailReminder']),
		'preminder' => ($r['popupReminder'] == NULL ? -1 : $r['popupReminder'])
	);
}
function prepareContext($r)
{
	return array(
		'title' =>$r['name'],
		'id' => $r['contextID']
	);
}

function check_read_access($listId = null)
{
	$db = DBConnection::instance();
	if(Config::get('password') == '') return true;
	if(is_logged()) return true;
	if($listId !== null)
	{
		$id = $db->sq("SELECT categoryID FROM Category WHERE categoryID=? AND published=1", array($listId));
		if($id) return;
	}
	jsonExit( array('total'=>0, 'list'=>array(), 'denied'=>1) );
}

function have_write_access($listId = null)
{
	if(is_readonly()) return false;
	// check list exist
	if($listId !== null)
	{
		$db = DBConnection::instance();
		$count = $db->sq("SELECT COUNT(*) FROM Category WHERE categoryID=?", array($listId));
		if(!$count) return false;
	}
	return true;
}

function check_write_access($listId = null)
{
	if(have_write_access($listId)) return;
	jsonExit( array('total'=>0, 'list'=>array(), 'denied'=>1) );
}

function inputTaskParams()
{
	$a = array(
		'id' => _post('id'),
		'title'=> trim(_post('title')),
		'note' => str_replace("\r\n", "\n", trim(_post('note'))),
		'prio' => (int)_post('prio'),
		'duedate' => '',
		'tags' => trim(_post('tags')),
		'listId' => (int)_post('list'),

	);
	if($a['prio'] < -1) $a['prio'] = -1;
	elseif($a['prio'] > 2) $a['prio'] = 2;
	return $a;
}

function prepareTags($tagsStr, $userId)
{
	$tags = explode(',', $tagsStr);
	if(!$tags) return 0;

	$aTags = array('tags'=>array(), 'ids'=>array());
	foreach($tags as $tag)
	{
		$tag = str_replace(array('"',"'",'<','>','&','/','\\','^'),'',trim($tag));
		if($tag == '') continue;

		$aTag = getOrCreateTag($tag, $userId);
		if($aTag && !in_array($aTag['id'], $aTags['ids'])) {
			$aTags['tags'][] = $aTag['name'];
			$aTags['ids'][] = $aTag['id'];
		}
	}
	return $aTags;
}

function getOrCreateTag($name, $userId)
{
	$db = DBConnection::instance();
	$tagId = $db->sq("SELECT id FROM mtt_tags WHERE name=? AND userID=$userId", array($name));
	if($tagId) return array('id'=>$tagId, 'name'=>$name);

	$db->ex("INSERT INTO mtt_tags (name, userID) VALUES (?, $userId)", array($name));
	return array('id'=>$db->last_insert_id(), 'name'=>$name);
}

function getTagId($tag, $userId)
{
	$db = DBConnection::instance();
	$id = $db->sq("SELECT id FROM mtt_tags WHERE name=? AND userID=$userId", array($tag));
	return $id ? $id : 0;
}

function get_task_tags($id)
{
	$db = DBConnection::instance();
	$q = $db->dq("SELECT tag_id FROM mtt_tag2task WHERE task_id=?", $id);
	$a = array();
	while($r = $q->fetch_row()) {
		$a[] = $r[0];
	}
	return $a;
}


function addTaskTags($taskId, $tagIds, $listId, $userId)
{
	$db = DBConnection::instance();
	if(!$tagIds) return;
	foreach($tagIds as $tagId)
	{
		$db->ex("INSERT INTO mtt_tag2task (task_id,tag_id,categoryID, userID) VALUES (?,?,?,?)", array($taskId,$tagId,$listId, $userId));
	}
}

function parse_smartsyntax($title)
{
	$a = array();
	if(!preg_match("|^(/([+-]{0,1}\d+)?/)?(.*?)(\s+/([^/]*)/$)?$|", $title, $m)) return false;
	$a['prio'] = isset($m[2]) ? (int)$m[2] : 0;
	$a['title'] = isset($m[3]) ? trim($m[3]) : '';
	$a['tags'] = isset($m[5]) ? trim($m[5]) : '';
	if($a['prio'] < -1) $a['prio'] = -1;
	elseif($a['prio'] > 2) $a['prio'] = 2;
	return $a;
}

function tag_size($qmin, $q, $step)
{
	if($step == 0) return 1;
	$v = ceil(($q - $qmin)/$step);
	if($v == 0) return 0;
	else return $v-1;

}

function parse_duedate($s, $t)
{
	$df2 = Config::get('dateformat2');
	if(max((int)strpos($df2,'n'), (int)strpos($df2,'m')) > max((int)strpos($df2,'d'), (int)strpos($df2,'j'))) $formatDayFirst = true;
	else $formatDayFirst = false;

	$y = $m = $d = 0;
	if(preg_match("|^(\d+)-(\d+)-(\d+)\b|", $s, $ma)) {
		$y = (int)$ma[1]; $m = (int)$ma[2]; $d = (int)$ma[3];
	}
	elseif(preg_match("|^(\d+)\/(\d+)\/(\d+)\b|", $s, $ma))
	{
		if($formatDayFirst) {
			$d = (int)$ma[1]; $m = (int)$ma[2]; $y = (int)$ma[3];
		} else {
			$m = (int)$ma[1]; $d = (int)$ma[2]; $y = (int)$ma[3];
		}
	}
	elseif(preg_match("|^(\d+)\.(\d+)\.(\d+)\b|", $s, $ma)) {
		$d = (int)$ma[1]; $m = (int)$ma[2]; $y = (int)$ma[3];
	}
	elseif(preg_match("|^(\d+)\.(\d+)\b|", $s, $ma)) {
		$d = (int)$ma[1]; $m = (int)$ma[2]; 
		$a = explode(',', date('Y,m,d'));
		if( $m<(int)$a[1] || ($m==(int)$a[1] && $d<(int)$a[2]) ) $y = (int)$a[0]+1; 
		else $y = (int)$a[0];
	}
	elseif(preg_match("|^(\d+)\/(\d+)\b|", $s, $ma))
	{
		if($formatDayFirst) {
			$d = (int)$ma[1]; $m = (int)$ma[2];
		} else {
			$m = (int)$ma[1]; $d = (int)$ma[2];
		}
		$a = explode(',', date('Y,m,d'));
		if( $m<(int)$a[1] || ($m==(int)$a[1] && $d<(int)$a[2]) ) $y = (int)$a[0]+1; 
		else $y = (int)$a[0];
	}
	else return null;
	if($y < 100) $y = 2000 + $y;
	elseif($y < 1000 || $y > 2099) $y = 2000 + (int)substr((string)$y, -2);
	if($m > 12) $m = 12;
	$maxdays = daysInMonth($m,$y);
	if($m < 10) $m = '0'.$m;
	if($d > $maxdays) $d = $maxdays;
	elseif($d < 10) $d = '0'.$d;

	$hr = '00';
	$min = '00';
	if (preg_match("|(\d+):(\d+)|", $t, $mat))
	{
		$hr = $mat[1];
		$min = $mat[2];
		
		$ihr = (int)$hr;
		$imin = (int)$min;
		if (($ihr < 0) || ($ihr > 23))
		{
			$hr = '00';
		}
		if (($imin < 0) || ($imin > 59))
		{
			$min = '00';
		} 

	}

	$all = $y.'-'.$m.'-'.$d.' '.$hr.':'.$min;
	$timestamp = strtotime($all);
	//return "$y-$m-$d $hr:$min";
	return $timestamp;
	//return "$y-$m-$d";
}

function prepare_duetime($timestamp)
{
	if ($timestamp == '')
	{
		return '00:00';
	}
	$duedate = gmdate("Y-m-d H:i:s", $timestamp);
	$time = explode(' ', $duedate);
	$sects = explode(':', $time[1]);
	$s = $sects[0] . ':' . $sects[1];
	return $s;
}

function prepare_duedate($timestamp)
{
	$lang = Lang::instance();

	$a = array( 'class'=>'', 'str'=>'', 'formatted'=>'', 'timestamp'=>0 );
	if($timestamp == '') {
		return $a;
	}
	$duedate = gmdate("Y-m-d H:i:s", $timestamp);
	//$duedate = date_timestamp_get($dt);
	$datetime = explode(' ', $duedate);
	$ad = explode('-', $datetime[0]);
	//error_log("datetime= " . $datetime[0] . " ad= " . $ad);
	//$ad = explode('-', $duedate);
	$at = explode('-', date('Y-m-d'));
	$a['timestamp'] = mktime(0,0,0,$ad[1],$ad[2],$ad[0]);
	$diff = mktime(0,0,0,$ad[1],$ad[2],$ad[0]) - mktime(0,0,0,$at[1],$at[2],$at[0]);

	if($diff < -604800 && $ad[0] == $at[0])	{ $a['class'] = 'past'; $a['str'] = formatDate3(Config::get('dateformatshort'), (int)$ad[0], (int)$ad[1], (int)$ad[2], $lang); }
	elseif($diff < -604800)	{ $a['class'] = 'past'; $a['str'] = formatDate3(Config::get('dateformat2'), (int)$ad[0], (int)$ad[1], (int)$ad[2], $lang); }
	elseif($diff < -86400)		{ $a['class'] = 'past'; $a['str'] = sprintf($lang->get('daysago'),ceil(abs($diff)/86400)); }
	elseif($diff < 0)			{ $a['class'] = 'past'; $a['str'] = $lang->get('yesterday'); }
	elseif($diff < 86400)		{ $a['class'] = 'today'; $a['str'] = $lang->get('today'); }
	elseif($diff < 172800)		{ $a['class'] = 'today'; $a['str'] = $lang->get('tomorrow'); }
	elseif($diff < 691200)		{ $a['class'] = 'soon'; $a['str'] = sprintf($lang->get('indays'),ceil($diff/86400)); }
	elseif($ad[0] == $at[0])	{ $a['class'] = 'future'; $a['str'] = formatDate3(Config::get('dateformatshort'), (int)$ad[0], (int)$ad[1], (int)$ad[2], $lang); }
	else						{ $a['class'] = 'future'; $a['str'] = formatDate3(Config::get('dateformat2'), (int)$ad[0], (int)$ad[1], (int)$ad[2], $lang); }

	$a['formatted'] = formatTime(Config::get('dateformat2'), $a['timestamp']);

	return $a;
}

function date2int($timestamp)
{
	if(!$timestamp) return 33330000;
	$d = gmdate("Y-m-d H:i:s", $timestamp);
	$at = explode(' ', $d);
	$ad = explode('-', $at[0]);
	$s = $ad[0];
	if(strlen($ad[1]) < 2) $s .= "0$ad[1]"; else $s .= $ad[1];
	if(strlen($ad[2]) < 2) $s .= "0$ad[2]"; else $s .= $ad[2];
	return (int)$s;
}

function time2int($t)
{
	if (!$t) return 0;
	$ad = explode(':', $t);
	$s = '' . $ad[0] . $ad[1];
	return (int)$s;
}

function daysInMonth($m, $y=0)
{
	if($y == 0) $y = (int)date('Y');
	$a = array(1=>31,(($y-2000)%4?28:29),31,30,31,30,31,31,30,31,30,31);
	if(isset($a[$m])) return $a[$m]; else return 0;
}

function myErrorHandler($errno, $errstr, $errfile, $errline)
{
	if($errno==E_ERROR || $errno==E_CORE_ERROR || $errno==E_COMPILE_ERROR || $errno==E_USER_ERROR || $errno==E_PARSE) $error = 'Error';
	elseif($errno==E_WARNING || $errno==E_CORE_WARNING || $errno==E_COMPILE_WARNING || $errno==E_USER_WARNING || $errno==E_STRICT) {
		if(error_reporting() & $errno) $error = 'Warning'; else return;
	}
	elseif($errno==E_NOTICE || $errno==E_USER_NOTICE) {
		if(error_reporting() & $errno) $error = 'Notice'; else return;
	}
	elseif(defined('E_DEPRECATED') && ($errno==E_DEPRECATED || $errno==E_USER_DEPRECATED)) { # since 5.3.0
		if(error_reporting() & $errno) $error = 'Notice'; else return;
	}
	else $error = "Error ($errno)";	# here may be E_RECOVERABLE_ERROR
	throw new Exception("$error: '$errstr' in $errfile:$errline", -1);
}

function myExceptionHandler($e)
{
	if(-1 == $e->getCode()) {
		echo $e->getMessage()."\n". $e->getTraceAsString();
		exit;
	}
	echo 'Exception: \''. $e->getMessage() .'\' in '. $e->getFile() .':'. $e->getLine(); //."\n". $e->getTraceAsString();
	exit;
}

function deleteTask($id)
{
	check_write_access();
	$db = DBConnection::instance();
	$db->ex("BEGIN");
	$db->ex("DELETE FROM mtt_tag2task WHERE task_id=$id");
	//TODO: delete unused tags?
	$db->dq("DELETE FROM mtt_todolist WHERE id=$id");
	$affected = $db->affected();
	$db->ex("COMMIT");
	return $affected;
}

function moveTask($id, $listId)
{
	check_write_access();
	$db = DBConnection::instance();

	// Check task exists and not in target list
	$r = $db->sqa("SELECT * FROM mtt_todolist WHERE id=?", array($id));
	if(!$r || $listId == $r['categoryID']) return false;

	// Check target list exists
	if(!$db->sq("SELECT COUNT(*) FROM Category WHERE categoryID=?", $listId))
		return false;

	$ow = 1 + (int)$db->sq("SELECT MAX(ow) FROM mtt_todolist WHERE categoryID=? AND compl=?", array($listId, $r['compl']?1:0));
	
	$db->ex("BEGIN");
	$db->ex("UPDATE mtt_tag2task SET categoryID=? WHERE task_id=?", array($listId, $id));
	$db->dq("UPDATE mtt_todolist SET categoryID=?, ow=?, d_edited=? WHERE id=?", array($listId, $ow, time(), $id));
	$db->ex("COMMIT");
	return true;
}

function prepareList($row)
{
	$taskview = (int)$row['taskview'];
	return array(
		'id' => $row['categoryID'],
		'name' => htmlarray($row['name']),
		'sort' => (int)$row['sorting'],
		'published' => $row['published'] ? 1 :0,
		'showCompl' => $taskview & 1 ? 1 : 0,
		'showNotes' => $taskview & 2 ? 1 : 0,
		'hidden' => $taskview & 4 ? 1 : 0,
	);
}

function getUserListsSimple($userId)
{
	$db = DBConnection::instance();
	$a = array();
	$q = $db->dq("SELECT categoryID,name FROM Category WHERE userID=$userId ORDER BY categoryID ASC");
	while($r = $q->fetch_row()) {
		$a[$r[0]] = $r[1];
	}
	return $a;
}

?>
