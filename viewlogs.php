<?php
/***************************************************************************
	*
	*   This program is free software; you can redistribute it and/or modify
	*   it under the terms of the GNU General Public License as published by
	*   the Free Software Foundation; either version 2 of the License, or
	*   (at your option) any later version.
	*
	***************************************************************************/

/****************************************************************************

   Unicode Reminder ăĄă˘

	 view all logs of a cache

	 used template(s): viewlogs

	 GET Parameter: cacheid, start, count

 ****************************************************************************/

  //prepare the templates and include all neccessary
	if(!isset($rootpath)) $rootpath = '';   
	require_once('./lib/common.inc.php');
	
	//Preprocessing
	{
		//set here the template to process
		$tplname = 'viewlogs';

		require_once('./lib/caches.inc.php');
		require($stylepath . '/lib/icons.inc.php');
		require($stylepath . '/viewcache.inc.php');
		require($stylepath . '/viewlogs.inc.php');
		require($stylepath.'/smilies.inc.php');
		global $usr;
		
		$cache_id = 0;
		if (isset($_REQUEST['cacheid']))
		{
			$cache_id = $_REQUEST['cacheid'];
		}
		if (isset($_REQUEST['logid']))
		{
			$logid = $_REQUEST['logid'];
		$show_one_log = " AND `cache_logs`.`id` ='".$logid."'  ";
		}else {$show_one_log ='';}

		$start = 0;
		if (isset($_REQUEST['start']))
		{
			$start = $_REQUEST['start'];
			if (!is_numeric($start)) $start = 0;
		}
		$count = 99999;
		if (isset($_REQUEST['count']))
		{
			$count = $_REQUEST['count'];
			if (!is_numeric($count)) $count = 999999;
		}

		if ($cache_id != 0)
		{
			//get cache record
			$rs = sql("SELECT `user_id`, `name`, `founds`, `notfounds`, `notes`, `status`, `type` FROM `caches` WHERE `caches`.`cache_id`='&1'", $cache_id);

			if (mysql_num_rows($rs) == 0)
			{
				$cache_id = 0;
			}
			else
			{
				$cache_record = sql_fetch_array($rs);
				// check if the cache is published, if not only the owner is allowed to view the log
				if(($cache_record['status'] == 4 || $cache_record['status'] == 5 || $cache_record['status'] == 6 ) && ($cache_record['user_id'] != $usr['userid'] && !$usr['admin']))
				{
					$cache_id = 0;
				}
			}
			mysql_free_result($rs);
		} else {
		
					//get cache record
			$rs = sql("SELECT `cache_logs`.`cache_id`,`caches`.`user_id`, `caches`.`name`, `caches`.`founds`, `caches`.`notfounds`, `caches`.`notes`, `caches`.`status`, `caches`.`type` FROM `caches`,`cache_logs` WHERE `cache_logs`.`id`='&1' AND `caches`.`cache_id`=`cache_logs`.`cache_id` ", $logid);

			if (mysql_num_rows($rs) == 0)
			{
				$cache_id = 0;
			}
			else
			{
				$cache_record = sql_fetch_array($rs);
				// check if the cache is published, if not only the owner is allowed to view the log
				if(($cache_record['status'] == 4 || $cache_record['status'] == 5 || $cache_record['status'] == 6 ) && ($cache_record['user_id'] != $usr['userid'] && !$usr['admin']))
				{
					$cache_id = 0;
				} else { $cache_id =$cache_record['cache_id'] ;}
			}
			mysql_free_result($rs);
		}			


		if ($cache_id != 0)
		{
			//ok, cache is here, let's process
			$owner_id = $cache_record['user_id'];

			//cache data
			tpl_set_var('cachename', htmlspecialchars($cache_record['name'], ENT_COMPAT, 'UTF-8'));
			tpl_set_var('cacheid', $cache_id);

			if ($cache_record['type'] == 6)
			{
				tpl_set_var('found_icon', $exist_icon);
				tpl_set_var('notfound_icon', $wattend_icon);
			}
			else
			{
				tpl_set_var('found_icon', $found_icon);
				tpl_set_var('notfound_icon', $notfound_icon);
			}
		    tpl_set_var('note_icon', $note_icon);

			tpl_set_var('founds', htmlspecialchars($cache_record['founds'], ENT_COMPAT, 'UTF-8'));
			tpl_set_var('notfounds', htmlspecialchars($cache_record['notfounds'], ENT_COMPAT, 'UTF-8'));
			tpl_set_var('notes', htmlspecialchars($cache_record['notes'], ENT_COMPAT, 'UTF-8'));
			tpl_set_var('total_number_of_logs', htmlspecialchars($cache_record['notes'] + $cache_record['notfounds'] + $cache_record['founds'], ENT_COMPAT, 'UTF-8'));

			//check number of pictures in logs
			$rspiclogs =sqlValue("SELECT COUNT(*) FROM `pictures`,`cache_logs` WHERE `pictures`.`object_id`=`cache_logs`.`id` AND `pictures`.`object_type`=1 AND `cache_logs`.`cache_id`= $cache_id",0);

				if ($rspiclogs !=0){
				tpl_set_var('gallery', '<img src="tpl/stdstyle/images/free_icons/photo.png" class="icon16" alt="" />&nbsp;'.$rspiclogs.'x <a href=gallery_cache.php?cacheid='.$cache_id.'>'.tr(gallery).'</a>');
				} else {
				tpl_set_var('gallery', '');
				;}			
//START: edit by FelixP - 2013'10
			// prepare the logs - show logs marked as deleted if admin
			//$show_deleted_logs = "";
			//$show_deleted_logs2 = " AND `cache_logs`.`deleted` = 0 ";
			//$show_deleted_logs = "`cache_logs`.`deleted` `deleted`,";
			//$show_deleted_logs2 = "";
			//if( $usr['admin'] )
			//{
				$show_deleted_logs = "`cache_logs`.`deleted` `deleted`,";
				$show_deleted_logs2 = "";
			//}
 
			$rs = sql("SELECT `cache_logs`.`user_id` `userid`,
					".$show_deleted_logs."
					`cache_logs`.`id` AS `log_id`,
			        `cache_logs`.`encrypt` `encrypt`,
					`cache_logs`.`picturescount` AS `picturescount`,
					`cache_logs`.`user_id` AS `user_id`,
					`cache_logs`.`date` AS `date`,
					`cache_logs`.`type` AS `type`,
					`cache_logs`.`text` AS `text`,
					`cache_logs`.`text_html` AS `text_html`,
					`cache_logs`.`last_modified` AS `last_modified`,
					`cache_logs`.`last_deleted` AS `last_deleted`,
					`cache_logs`.`edit_count` AS `edit_count`,
					`cache_logs`.`date_created` AS `date_created`,
					`user`.`username` AS `username`,
					`user`.`hidden_count` AS    `ukryte`,
					`user`.`founds_count` AS    `znalezione`, 	
					`user`.`notfounds_count` AS `nieznalezione`,
                    `user`.`admin` AS `admin`,
					`u2`.`username` AS `del_by_username`,
					`u2`.`admin` AS `del_by_admin`,
					`u3`.`username` AS `edit_by_username`,
					`u3`.`admin` AS `edit_by_admin`,
					`log_types`.`icon_small` AS `icon_small`,
					`cache_moved`.`longitude` AS `mobile_longitude`, 
					`cache_moved`.`latitude` AS `mobile_latitude`, 
					`cache_moved`.`km` AS `km`,
					`log_types_text`.`text_listing` AS `text_listing`,
			    IF(ISNULL(`cache_rating`.`cache_id`), 0, 1) AS `recommended`
				FROM `cache_logs`
				INNER JOIN `log_types` ON `log_types`.`id`=`cache_logs`.`type`
				INNER JOIN `log_types_text` ON `log_types_text`.`log_types_id`=`log_types`.`id` AND `log_types_text`.`lang`='&1'
				INNER JOIN `user` ON `user`.`user_id` = `cache_logs`.`user_id`
				LEFT JOIN `cache_moved` ON `cache_moved`.`log_id` = `cache_logs`.`id`
				LEFT JOIN `cache_rating` ON `cache_logs`.`cache_id`=`cache_rating`.`cache_id` AND `cache_logs`.`user_id`=`cache_rating`.`user_id`
				LEFT JOIN `user` `u2` ON `cache_logs`.`del_by_user_id`=`u2`.`user_id`
				LEFT JOIN `user` `u3` ON `cache_logs`.`edit_by_user_id`=`u3`.`user_id`
				WHERE `cache_logs`.`cache_id`='&2'
				".$show_deleted_logs2."
				".$show_one_log."
				ORDER BY `cache_logs`.`date` DESC, `cache_logs`.`Id` DESC LIMIT &3, &4", $lang, $cache_id, $start+0, $count+0);

			$logs = '';

			$thisdateformat = $dateformat;
			$thisdatetimeformat = $datetimeformat;
//START: same code ->viewlogs.php / viewcache.php
			$edit_count_date_from = date_create('2005-01-01 00:00');
			for ($i = 0; $i < mysql_num_rows($rs); $i++)
			{
				$record = sql_fetch_array($rs);
				$show_deleted = "";
				$processed_text = "";
				if( isset( $record['deleted'] ) && $record['deleted'] )
				{
					if( $usr['admin'] )
						{	
							$show_deleted = "show_deleted";
							$processed_text= $record['text']; 
							
						} 
					else 
					{
						$record['icon_small']="log/16x16-trash.png"; //replace record icon with trash icon 
						$comm_replace =tr('vl_Record_of_type')." [". $record['text_listing']."] ".tr('vl_deleted'); 
						$record['text_listing']=tr('vl_Record_deleted'); ////replace type of record 
						if( isset( $record['del_by_username'] ) && $record['del_by_username'] )
						{
							if ($record['del_by_admin']==1) 
							{
								if ($record['del_by_username'] == $record['username'])
									{	
										$delByCOG=false;

									} else
									{
										$comm_replace.=" ".tr('vl_by_COG');
										$delByCOG=true;
									}
							}
							if ($delByCOG==false) 
							{
								$comm_replace.=" ".tr('vl_by_user')." ".$record['del_by_username'];
							}
						};
						if(isset($record['last_deleted'])) 
						{
							$comm_replace.=" ".tr('vl_on_date')." ".fixPlMonth(htmlspecialchars(strftime($thisdateformat, strtotime($record['last_deleted'])), ENT_COMPAT, 'UTF-8'));;
						};
						$comm_replace.="."; 	 	
						$processed_text = $comm_replace;
					}
				} else 
				{
					$processed_text= $record['text'];	
				} 
				 
		
				// add edit footer if record has been modified 
				$record_date_create = date_create($record['date_created']);
								
				if ($record['edit_count']>0) 
				//check if editted at all 
				{
					$edit_footer="<div><small>".tr('vl_Recently_modified_on')." ".fixPlMonth(htmlspecialchars(strftime($thisdatetimeformat, strtotime($record['last_modified'])), ENT_COMPAT, 'UTF-8'));
					if (!$usr['admin'] && isset($record['edit_by_admin']))
					{
						if ($record['edit_by_username'] == $record['username'])
						{
							$byCOG=false;
						} else 
						{
							$edit_footer.=" ".tr('vl_by_COG');
							$byCOG = true;
						}
					} 
					if ($byCOG==false)
					{
						$edit_footer.=" ".tr('vl_by_user')." ". $record['edit_by_username'];
					}	
					if ($record_date_create > $edit_count_date_from) //check if record created after implementation date (to avoid false readings for record changed before
					{
						$edit_footer.=" - ".tr('vl_totally_modified')." ".$record['edit_count']." ";
						if($record['edit_count']>1)	
							{$edit_footer.=tr('vl_count_plural');}
						else
							{$edit_footer.=tr('vl_count_singular');}
		
					}				

					$edit_footer.=".</small</div>";
						
				} else {
					$edit_footer ="";
				}
				
				$tmplog_text =  $processed_text.$edit_footer;
				$tmplog = read_file($stylepath . '/viewcache_log.tpl.php');
//END: same code ->viewlogs.php / viewcache.php					
//END: edit by FelixP - 2013'10
				$tmplog_username = htmlspecialchars($record['username'], ENT_COMPAT, 'UTF-8');
				$tmplog_date = fixPlMonth(htmlspecialchars(strftime($dateformat, strtotime($record['date'])), ENT_COMPAT, 'UTF-8'));
				// replace smilies in log-text with images
				$tmplog_text = str_replace($smileytext, $smileyimage, $tmplog_text);
				
				// display user activity (by Łza 2012)
				if ((date('m') == 4) and (date('d') == 1)){
					$tmplog_username_aktywnosc = ' (<img src="tpl/stdstyle/images/blue/thunder_ico.png" alt="user activity" width="13" height="13" border="0" title="'.tr('viewlog_aktywnosc').'"/>'. rand(1, 9) . ') ';
				} else {
					$tmplog_username_aktywnosc = ' (<img src="tpl/stdstyle/images/blue/thunder_ico.png" alt="user activity" width="13" height="13" border="0" title="'.tr('viewlog_aktywnosc').' ['.$record['znalezione'].'+'. $record['nieznalezione'].'+'. $record['ukryte'].']"/>'. ($record['ukryte'] + $record['znalezione'] + $record['nieznalezione']) . ') ';
				}
            	// hide nick of athor of COG(OC Team) for user
				if ($record['type'] == 12 && !$usr['admin']) 
				  {
				    $record['userid'] = '0';
					$tmplog_username_aktywnosc = '';
				    $tmplog_username = 'Centrum Obsługi Geocachera ';
				  }
            
				$tmplog = mb_ereg_replace('{username_aktywnosc}', $tmplog_username_aktywnosc, $tmplog);
				
				// mobile caches by Łza
				if (($record['type'] == 4) && ($record['mobile_latitude'] != 0))
				 {
				   $tmplog_kordy_mobilnej = mb_ereg_replace(" ", "&nbsp;",htmlspecialchars(help_latToDegreeStr($record['mobile_latitude']), ENT_COMPAT, 'UTF-8')) . '&nbsp;' . mb_ereg_replace(" ", "&nbsp;", htmlspecialchars(help_lonToDegreeStr($record['mobile_longitude']), ENT_COMPAT, 'UTF-8'));
				   $tmplog = mb_ereg_replace('{kordy_mobilniaka}', $record['km'] . ' km [<img src="tpl/stdstyle/images/blue/szczalka_mobile.png" title="'.tr('viewlog_kordy').'" />'.$tmplog_kordy_mobilnej .']', $tmplog);
				 }
				else $tmplog = mb_ereg_replace('{kordy_mobilniaka}', ' ', $tmplog);
				
				if ($record['text_html'] == 0)
					$tmplog_text = help_addHyperlinkToURL($tmplog_text);

				$tmplog_text = tidy_html_description($tmplog_text);

				$tmplog = mb_ereg_replace('{show_deleted}', $show_deleted, $tmplog);
				$tmplog = mb_ereg_replace('{username}', $tmplog_username, $tmplog);	
				$tmplog = mb_ereg_replace('{userid}', $record['userid'], $tmplog);
				$tmplog = mb_ereg_replace('{date}', $tmplog_date, $tmplog);
				$tmplog = mb_ereg_replace('{type}', $record['text_listing'], $tmplog);
				$tmplog = mb_ereg_replace('{logtext}', $tmplog_text, $tmplog);
				$tmplog = mb_ereg_replace('{logimage}', '<a href="viewlogs.php?logid='.$record['log_id'].'">'. icon_log_type($record['icon_small'], $record['log_id']).'</a>', $tmplog);
				
				//$rating_picture
				if ($record['recommended'] == 1 && $record['type']==1)
					$tmplog = mb_ereg_replace('{ratingimage}','<img src="images/rating-star.png" alt="'.tr('recommendation').'" />', $tmplog);
				else
					$tmplog = mb_ereg_replace('{ratingimage}', '', $tmplog);

				//user der owner
				$logfunctions = '';
				$tmpedit = mb_ereg_replace('{logid}', $record['log_id'], $edit_log);
				$tmpremove = mb_ereg_replace('{logid}', $record['log_id'], $remove_log);
				$tmpRevert = mb_ereg_replace('{logid}', $record['log_id'], $revertLog);
				$tmpnewpic = mb_ereg_replace('{logid}', $record['log_id'], $upload_picture);
				if(!isset($record['deleted'])) $record['deleted'] = false;
				if( $record['deleted']!=1 )
				{
					if ($record['user_id'] == $usr['userid'])
					{
						$logfunctions = $functions_start . $tmpedit . $functions_middle; 
						if ($record['type']!=12 && ($usr['userid']==$cache_record['cache_id'] || $usr['admin']==false)) {					
							$logfunctions .=$tmpremove . $functions_middle;
						}
						if ($usr['admin'])  {					
							$logfunctions .= $tmpremove . $functions_middle;
						}
					 	
						$logfunctions .= $tmpnewpic . $functions_end;
					} 
					else if( $usr['admin']) 
					{
						$logfunctions = $functions_start . $tmpedit . $functions_middle . $tmpremove . $functions_middle . $functions_end;
					} 
					elseif ($owner_id == $usr['userid'])
					{

						$logfunctions = $functions_start;
						if ($record['type']!=12){
 						$logfunctions .= $tmpremove;}
						$logfunctions .= $functions_end;
					}
				}
				else if( $usr['admin'])
				{				
					$logfunctions = $functions_start . $tmpedit . $functions_middle . $tmpRevert . $functions_middle . $functions_end;
				}
	
				$tmplog = mb_ereg_replace('{logfunctions}', $logfunctions, $tmplog);

				// pictures
				if ($record['picturescount'] > 0)
				{
					$logpicturelines = '';
					$append_atag='';
					$rspictures = sql("SELECT `url`, `title`, `uuid`, `user_id` FROM `pictures` WHERE `object_id`='&1' AND `object_type`=1", $record['log_id']);

					for ($j = 0; $j < mysql_num_rows($rspictures); $j++)
					{
						$pic_record = sql_fetch_array($rspictures);
						$thisline = $logpictureline;


                        $thisline = mb_ereg_replace('{link}', $pic_record['url'], $thisline);
                        $thisline = mb_ereg_replace('{longdesc}', str_replace("images/uploads","uploads",$pic_record['url']), $thisline);
	                    $thisline = mb_ereg_replace('{imgsrc}', 'thumbs2.php?'.$showspoiler.'uuid=' . urlencode($pic_record['uuid']), $thisline);
                        $thisline = mb_ereg_replace('{title}', htmlspecialchars($pic_record['title'], ENT_COMPAT, 'UTF-8'), $thisline);


						if ($pic_record['user_id'] == $usr['userid'] || $usr['admin'])
						{
							$thisfunctions = $remove_picture;
							$thisfunctions = mb_ereg_replace('{uuid}', urlencode($pic_record['uuid']), $thisfunctions);
							$thisline = mb_ereg_replace('{functions}', $thisfunctions, $thisline);
						}
						else
							$thisline = mb_ereg_replace('{functions}', '', $thisline);

						$logpicturelines .= $thisline;
					}
					mysql_free_result($rspictures);

					$logpicturelines = mb_ereg_replace('{lines}', $logpicturelines, $logpictures);
					$tmplog = mb_ereg_replace('{logpictures}', $logpicturelines, $tmplog);
				}
				else
					$tmplog = mb_ereg_replace('{logpictures}', '', $tmplog);

				$logs .= "$tmplog\n";
			}
			tpl_set_var('logs', $logs);
		}
		else
		{
			//display search page
			exit;
		}
	}

	//make the template and send it out
	tpl_BuildTemplate();
?>
