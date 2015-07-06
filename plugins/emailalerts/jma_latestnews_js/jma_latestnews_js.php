<?php
/*
 * @package Latest News - JomSocial  Plugin for J!MailAlerts Component
 * @copyright Copyright (C) 2009 -2010 Techjoomla, Tekdi Web Solutions . All rights reserved.
 * @license GNU GPLv2 <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html>
 * @link http://www.techjoomla.com
 */

// Do not allow direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.plugin.plugin' );

/*load language file for plugin frontend*/
$lang =  JFactory::getLanguage();
$lang->load('plg_emailalerts_jma_latestnews_js', JPATH_ADMINISTRATOR);

//include plugin helper file
$jma_helper=JPATH_SITE.DS.'components'.DS.'com_jmailalerts'.DS.'helpers'.DS.'plugins.php';
if(JFile::exists($jma_helper)){
	include_once($jma_helper);
}
else//this is needed when JMA integration plugin is used on sites where JMA is not installed
{
	if(JVERSION>'1.6.0'){
		$jma_integration_helper=JPATH_SITE.DS.'plugins'.DS.'system'.DS.'plg_sys_jma_integration'.DS.'plg_sys_jma_integration'.DS.'plugins.php';
	}else{
		$jma_integration_helper=JPATH_SITE.DS.'plugins'.DS.'system'.DS.'plg_sys_jma_integration'.DS.'plugins.php';
	}
	if(JFile::exists($jma_integration_helper)){
		include_once($jma_integration_helper);
	}
}

//class plgPluginTypePluginName extends JPlugin
class plgEmailalertsjma_latestnews_js extends JPlugin
{
	function plgEmailalertsLatestnews(&$subject,$config)
	{
		parent::__construct($subject, $config);
		if($this->params===false)
		{	
			$jPlugin=JPluginHelper::getPlugin('emailalerts','jma_latestnews_js');
			$this->params=new JParameter( $jPlugin->params);
		}
	}

	function onEmail_jma_latestnews_js($id,$date,$userparam,$fetch_only_latest)
	{
		$areturn	=  array();
	   if($id==NULL)//if no userid/or no guest user return blank array for html and css
		{
			$areturn[0] =$this->_name;
			$areturn[1]	= '';
			$areturn[2]	= '';
			return $areturn;
		}        
		$list=$this->getList($id,$date,$userparam,$fetch_only_latest);
	   
		$areturn[0] =$this->_name;
		if(empty($list))
		{
			//if no output is found, return array with 2 indexes with NO values
			$areturn[1]='';
			$areturn[2]='';
		}
		else
		{
			//get all plugin parameters in the variable, this will be passed to plugin helper function
			$plugin_params=$this->params;
			//create object for helper class
			$helper = new pluginHelper();    
			//call helper function to get plugin layout
			$ht=$helper->getLayout($this->_name,$list,$plugin_params);
			$areturn[1]=$ht;
			//call helper function to get plugin CSS layout path
			$cssfile=$helper->getCSSLayoutPath($this->_name,$plugin_params);
			$cssdata=JFile::read($cssfile);
			$areturn[2] = $cssdata;
		}
		return $areturn;
	}//onEmail_jma_latestnews_js() ends

	function getList($id,$last_alert_date,$userparam,$fetch_only_latest)
	{
		if(!$id){
			 return false;
		}
		
		require_once (JPATH_SITE.DS.'components'.DS.'com_content'.DS.'helpers'.DS.'route.php');
		
		$mainframe  = JFactory::getApplication();
		$db			=JFactory::getDBO();
		
		$user		=JFactory::getUser($id);
		$userId		= (int) $user->get('id');
		
		//get user preferences for this plugin parameters(shown in frontend) 
		$count		= (int) $userparam['count'];
		$catid		= trim( $userparam['catid'] );
		
		$secid='';
		
		$aid		= $user->get('aid');
		if(JVERSION < '1.6.0')
		{
			$secid		= trim( $userparam['secid'] );
			$acl=JFactory::getACL() ;
			$grp=$acl->getAroGroup($id);
			if($acl->is_group_child_of($grp->name,'Registered')||$acl->is_group_child_of($grp->name,'Public Backend')){
				$aid = 2 ;
			}else{
				$aid = 1 ;
			}
		}
		
		//get plugin parameters(not shown in frontend) 
		$ordering = $this->params->get('ordering');
		$show_front = $this->params->get('show_front',0);

		$introtext_count =(int) $this->params->get('introtext_count',200);
		$show_introtext =(int) $this->params->get('show_introtext',0);
		$show_date =(int) $this->params->get('show_date',0);
		$show_author =(int) $this->params->get('show_author',0);
		$show_author_alias =(int) $this->params->get('show_author_alias',0);
		$show_category =(int) $this->params->get('show_author',0);
		
		$contentConfig = JComponentHelper::getParams( 'com_content' );
		$access		= !$contentConfig->get('show_noauth');
		
		$nullDate	= $db->getNullDate();
		$date =JFactory::getDate();
		$now = $date->toSql();
		
		$replace = JURI::root();
		
		//date filter
		$where='a.state = 1'
		. ' AND ( a.publish_up = '.$db->Quote($nullDate).' OR a.publish_up <= '.$db->Quote($now).' )'
		. ' AND ( a.publish_down = '.$db->Quote($nullDate).' OR a.publish_down >= '.$db->Quote($now).' )';

		//author Filter
		switch 	($userparam['user_id'])
		{
			case 'by_me':
				$where .= ' AND (created_by = ' . (int) $userId . ' OR modified_by = ' . (int) $userId . ')';
				break;
			case 'not_me':
				$where .= ' AND (created_by <> ' . (int) $userId . ' AND modified_by <> ' . (int) $userId . ')';
				break;
		}

		//ordering
		switch ($ordering)
		{
			case 'm_dsc':
				$ordering		= 'a.modified DESC, a.created DESC';
				break;
			case 'c_dsc':
			default:
				$ordering		= 'a.created DESC';
				break;
		}

		//category filter
		if($catid)
		{
			$ids = explode( ',', $catid );
			JArrayHelper::toInteger( $ids );
			$catCondition = ' AND (cc.id=' . implode( ' OR cc.id=', $ids ) . ')';
		}
		
		//section filter
		if($secid)
		{
			$ids = explode( ',', $secid );
			JArrayHelper::toInteger( $ids );
			$secCondition = ' AND (s.id=' . implode( ' OR s.id=', $ids ) . ')';
		}
		
		//introtext filter
		$intro='';
		if($show_introtext){
			$intro="a.introtext AS intro,";
		}
		
		//get content items/articles
		if(JVERSION < '1.6.0')
		{
			$query = 'SELECT '.$intro.' a.id,a.catid,a.title,a.created,a.created_by_alias,a.sectionid,u.name,u.username,cc.access,cc.title as category, ' .
			' CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(":", a.id, a.alias) ELSE a.id END as slug,'.
			' CASE WHEN CHAR_LENGTH(cc.alias) THEN CONCAT_WS(":", cc.id, cc.alias) ELSE cc.id END as catslug'.
			' FROM #__content AS a ' .
			' LEFT JOIN #__users AS u ON u.id=a.created_by '.
			($show_front == '0' ? ' LEFT JOIN #__content_frontpage AS f ON f.content_id = a.id' : '') .
			' INNER JOIN #__categories AS cc ON cc.id = a.catid' .
			' INNER JOIN #__sections AS s ON s.id = a.sectionid' .
			' WHERE '. $where .' AND s.id > 0' .
			($access ? ' AND a.access <= ' .(int) $aid. ' AND cc.access <= ' .(int) $aid. ' AND s.access <= ' .(int) $aid : '').
			($catid ? $catCondition : '').
			($secid ? $secCondition : '').
			($show_front == '0' ? ' AND f.content_id IS NULL ' : '').
			' AND s.published = 1' .
			' AND cc.published = 1';
		}
		else
		{
			$groups	= implode(',', $user->getAuthorisedViewLevels());
			$checkacc=	'a.access IN ('.$groups.')';
			$query = 'SELECT '.$intro.' a.id,a.catid,a.title,a.created,a.created_by_alias,u.name,u.username,cc.access,cc.title as category,'.
			' CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(":", a.id, a.alias) ELSE a.id END as slug,'.
			' CASE WHEN CHAR_LENGTH(cc.alias) THEN CONCAT_WS(":", cc.id, cc.alias) ELSE cc.id END as catslug'.
			' FROM #__content AS a' .
			' LEFT JOIN #__users AS u ON u.id=a.created_by '.
			($show_front == '0' ? ' LEFT JOIN #__content_frontpage AS f ON f.content_id = a.id' : '') .
			' INNER JOIN #__categories AS cc ON cc.id = a.catid' .
			' WHERE '. $where .
			($access ? ' AND '.$checkacc:'').
			($catid ? $catCondition : '').
			($show_front == '0' ? ' AND f.content_id IS NULL ' : '').
			' AND cc.published = 1';
		}
		//get only fresh content
		if($fetch_only_latest)
		{
			$query .=" AND a.created >= ";
			$query .= $db->Quote($last_alert_date);
		}
		
		$query .= ' ORDER BY '. $ordering;
		
		//use user's preferred value for count
		$db->setQuery($query,0,$count);
		$rows = $db->loadObjectList();
		if($rows)
		{
			//create object for helper class
			$helper = new pluginHelper(); 
			//call plugin function to sort output by category
			$rows=$helper->multi_d_sort($rows,'catid',0);
			$i		= 0;
			$lists	= array();
			if($mainframe->isAdmin())//if email is previewed from backend, do not generate sef urls as it won't work
			{
				foreach($rows as $row)
				{
					$lists[$i]->link = JRoute::_($replace.ContentHelperRoute::getArticleRoute($row->slug, $row->catslug));
					$lists[$i]->link = str_replace("&", "&amp;",$lists[$i]->link);
					$lists[$i]->title = htmlspecialchars($row->title);
					
					if($show_author_alias && $row->created_by_alias){
						$lists[$i]->author=htmlspecialchars($row->created_by_alias);
					}else{
						$lists[$i]->author=htmlspecialchars($row->name);
					}
					
					$lists[$i]->date=htmlspecialchars($row->created);
					$lists[$i]->catid=htmlspecialchars($row->catid);
					$lists[$i]->category=htmlspecialchars($row->category);
					if($show_introtext){
						$lists[$i]->intro = substr( strip_tags($row->intro) , 0, $introtext_count )." ...";
					}
					$i++;
				}
			}
			else//if email is previewed/generated from frontend, generate sef urls
			{
				foreach($rows as $row)
				{
					//links will generate sef urls
					$lists[$i]->link=JURI::root().substr(JRoute::_(ContentHelperRoute::getArticleRoute($row->slug, $row->catslug)),strlen(JURI::base(true))+1);
					$lists[$i]->title = htmlspecialchars($row->title);
	
					if($show_author_alias && $row->created_by_alias){
						$lists[$i]->author=htmlspecialchars($row->created_by_alias);
					}else{
						$lists[$i]->author=htmlspecialchars($row->name);
					}
	
					$lists[$i]->date=htmlspecialchars($row->created);
					$lists[$i]->catid=htmlspecialchars($row->catid);
					$lists[$i]->category=htmlspecialchars($row->category);
					if($show_introtext){
						$lists[$i]->intro = substr( strip_tags($row->intro) , 0, $introtext_count )." ...";
					}
					$i++;
				}
			}
			return $lists;
		}
		else//no output
		{
			return false;
		}
	}//getList() ends

}//class plgEmailalertsjma_latestnews_js  ends
