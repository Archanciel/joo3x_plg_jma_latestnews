<?php
/**
* @copyright    Copyright (C) 2009 Open Source Matters. All rights reserved.
* @license      GNU/GPL
*/
// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();
if(!defined('DS'))
{
	define('DS',DIRECTORY_SEPARATOR);
}
/**
 * Renders a multiple item select element using SQL result and explicitly specified params
 
 * HOW TO USE IN XML ?? example is given below
 => <param name="catid" type="JMACategoriesUsers" default="1" label="LBL_CATEGORY_LN_JS" element="jma_latestnews_js" multiple="multiple" description="DESC_CATEGORY_LN_JS" key_field='id' value_field='title' />	
 * where element is the name of your plugin entry file 
 */
 jimport('joomla.html.html');
jimport( 'joomla.plugin.helper' );
require_once(JPATH_SITE.DS.'libraries/joomla/form/fields/list.php');
class JFormFieldJmacategoriesusers extends JFormFieldList
{
        /**
        * Element name
        *
        * @access       protected
        * @var          string
        */
        	var    $_name = 'Jmacategoriesusers';
 
         protected function getOptions() 
				{
          	$db  =JFactory::getDBO();   
			    	$plugin =JPluginHelper::getPlugin('emailalerts', 'jma_latestnews_js');
						if($plugin)
						{
               	$plug_params= $plugin->params;//example: plugintitle=K2-Latest Items category=1|3|4|5|2 no_of_items=5 catid=1|3|4|5|2 
						
				      	if(preg_match_all('/\[(.*?)\]/',$plug_params,$match)) {            
				       		 foreach($match[1] as $mat){  
				      					$match=str_replace(',','|',$mat);
				     						$plug_params= str_replace($mat,$match,$plug_params);
										}	
				        }
				        $newlin = explode(",",$plug_params);
				        
				        $new1 = explode(":",$newlin[1]);
				       
				      	$new1=str_replace('[','',$new1);$new1=str_replace(']','',$new1);
				      
				 				$cats=str_replace('|',',',$new1[1]);
				        
						 		if($cats){
						      		$sql = "SELECT id ,title FROM #__categories WHERE published = 1 AND extension !='com_docman' AND id IN (".$cats.")";
						      	}
						      	else{//if no category is yet selected
						      		$sql = "SELECT id ,title FROM #__categories WHERE published = 1 AND extension !='com_docman'";
						      	}
				            $db->setQuery($sql);
				            $options = $db->loadObjectList(); 
				           
										if($options)
										{
											foreach ($options as $i=>$option) { 
												$options[$i]->text = JText::_($option->title); 
												$options[$i]->value = JText::_($option->id);
											}
											//Merge any additional options in the XML definition. 
											$options = array_merge(parent::getOptions(), $options);
							       
											return $options; 
										} 
										else{
											return JText::_('NO_GROUP');
										}
            }
						else{
									return JText::_('Please Enable plugin first');
								}
        }
}
