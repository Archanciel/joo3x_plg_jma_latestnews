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
		
		//get plugin parameters(not shown in frontend) 
		$ordering = $this->params->get('ordering');
		$show_front = $this->params->get('show_front',0);

		$introtext_count =(int) $this->params->get('introtext_count',400);
		$show_introtext =(int) $this->params->get('show_introtext',0);
		$is_exerptsource_introtext = 0;	// 0 means metadesc, 1 means introtext !
		
		if ($show_introtext) {
			$is_exerptsource_introtext = (int) $this->params->get('exerpt_source',0);
		}
		
		$show_date =(int) $this->params->get('show_date',0);
		$show_author =(int) $this->params->get('show_author',0);
		$show_author_alias =(int) $this->params->get('show_author_alias',0);
		$show_category =(int) $this->params->get('show_category',0);
		
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
			$catCondition = ' AND (cc.parent_id=' . implode( ' OR cc.parent_id=', $ids ) . ')';
// works identically as line above !
//			$catCondition = ' AND (cc2.id=' . implode( ' OR cc2.id=', $ids ) . ')';
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
		$groups	= implode(',', $user->getAuthorisedViewLevels());
		$checkacc=	'a.access IN ('.$groups.')';
		$query = 'SELECT '.$intro.' a.id,a.catid,a.title,a.created,a.created_by_alias,a.metadesc,u.name,u.username,cc.access,cc2.title as category,'.
		' CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(":", a.id, a.alias) ELSE a.id END as slug,'.
		' CASE WHEN CHAR_LENGTH(cc.alias) THEN CONCAT_WS(":", cc.id, cc.alias) ELSE cc.id END as catslug'.
		' FROM #__content AS a' .
		' LEFT JOIN #__users AS u ON u.id=a.created_by '.
		($show_front == '0' ? ' LEFT JOIN #__content_frontpage AS f ON f.content_id = a.id' : '') .
		' INNER JOIN #__categories AS cc ON cc.id = a.catid' .
		' INNER JOIN #__categories AS cc2 ON cc2.id = cc.parent_id' .
		' WHERE '. $where .
		($access ? ' AND '.$checkacc:'').
		($catid ? $catCondition : '').
		($show_front == '0' ? ' AND f.content_id IS NULL ' : '').
		' AND cc.published = 1';

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
						$strippedIntro = $this->stripFLikeTag($row->intro);
						$metadesc = $row->metadesc;
						if ($is_exerptsource_introtext || empty($metadesc)) {
							$lists[$i]->intro = $this->extractWordsUpToSize($strippedIntro, $introtext_count);
						} else {
							$lists[$i]->intro = $this->buildIntrotextFromMetadesc($strippedIntro,$metadesc);
						}
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
						$strippedIntro = $this->stripFLikeTag($row->intro);
						$metadesc = $row->metadesc;
						if ($is_exerptsource_introtext || empty($metadesc)) {
							$lists[$i]->intro = $this->extractWordsUpToSize($strippedIntro, $introtext_count);
						} else {
							$lists[$i]->intro = $this->buildIntrotextFromMetadesc($strippedIntro,$metadesc);
						}
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


	/**
	 * Return $size chars from the passed $text, but so that the returned
	 * text only contains whole words. The length of the returned text is
	 * thus <= $size.
	 *
	 * @param string $text
	 * @param int $size
	 *
	 * @return string of length <= $size, but containing only whole words
	 */
	private function extractWordsUpToSize($text, $size) {
		if ($size == 0) {
			return '';
		}
		 
		$partialWord = 1;
	
		if ($size >= strlen ( $text ) || strcmp ( substr ( $text, $size, 1 ), " " ) == 0) {
			$partialWord = 0;
		}
	
		$subsStr = substr ( $text, 0, $size );
		$words = explode ( " ", $subsStr );
		$fullWords = array_slice ( $words, 0, count ( $words ) - $partialWord );
	
		$fullWordString = implode ( " ", $fullWords );
	
		return trim ( $fullWordString, "\x20\"" ).' ...';
	}
	
	/**
	 * Extracts the Année and Durée component from the passed introtext and build an excerpt
	 * containing those two elements + the passed metadesc.
	 *
	 * @param string $introtext
	 * @param string $metadesc
	 *
	 * @return built exerpt string
	 */
	private function buildIntrotextFromMetadesc($introtext, $metadesc) {
		$exerpt = htmlspecialchars($metadesc);
		 
		// WARNINGS:
		//			1/ the class definition [a-zA-Z0-9\'’ ] used below include two
		//			   types of apostrophy, ascii 39 and ascii 146 !
		//			2/ for the dot to match newline chars (ascii 13 and 10), the
		//			   s flag must be used !
		$pattern = "#<p>(Année: [0-9]{4})</p>.*<p>(Durée: [a-zA-Z0-9\'’ ]+)<#s";
		 
		preg_match($pattern, $introtext, $matches);
		$annee = $matches[1];
		$duree = $matches[2];
		$exerpt = '<p>' . $annee . '</p>' . $duree . '</p><p>' . $exerpt;
	
		return $exerpt;
	}
	
	/**
	 * Removes the {flike} Facebook plugin tag from the article introtext.
	 * 
	 * @param unknown $introText
	 * @return stripped introtext
	 */
	private function stripFLikeTag($introText) {
		$strippedIntroText = str_replace('<p>{flike}</p>', '', $introText);
		
		return $strippedIntroText;
	}
}//class plgEmailalertsjma_latestnews_js  ends
