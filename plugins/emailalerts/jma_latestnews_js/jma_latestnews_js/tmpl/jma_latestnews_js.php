<?php
/*
 * @package Latest News - JomSocial  Plugin for J!MailALerts Component
 * @copyright Copyright (C) 2009 -2010 Techjoomla, Tekdi Web Solutions . All rights reserved.
 * @license GNU GPLv2 <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html>
 * @link     http://www.techjoomla.com
 */
defined('_JEXEC') or die('Restricted access');
?>
<h2 class="subTitle">
<?php 
echo $plugin_params->get('plugintitle');
?>
</h2>
<table class= "jma_latestnewsjs product-table">
<tr>
	<td class="jma_latestnewsjs_th">
		<?php //echo JText::_('TITLE_LN_JS'); ?>
	</td >
	<?php 
	if($plugin_params->get('show_author'))
	{
	?>
		<td class="jma_latestnewsjs_th">
			<?php echo JText::_('AUTHOR_LN_JS'); ?>
		</td>	
	<?php
	}
	?>

	<?php 
	if($plugin_params->get('show_date'))
	{
	?>
		<td class="jma_latestnewsjs_th">
			<?php echo JText::_('DATE_LN_JS'); ?>
		</td>
	<?php
	}
	?>
</tr>
		
		<?php
		$cat_array=array();
		foreach ($vars as $row)
		{
			if (in_array($row->catid, $cat_array))
			{
		?>	
				<tr>
					<td class="jma_latestnewsjs_td_60">
						<a href="<?php echo $row->link;?>"><?php echo $row->title;?></a>
					</td>
					
				   <?php 
				   if($plugin_params->get('show_author'))
				   {
				   ?>
					   <td class="jma_latestnewsjs_td_20">
							<?php echo $row->author; ?> 
						</td>
				   <?php
				   }
				   ?>
					
				   <?php 
				   if($plugin_params->get('show_date'))
				   {
				   ?>
					   <td class="jma_latestnewsjs_td_20">
							<?php 
							if(JVERSION<'1.6.0')
								echo JHTML::date($row->date,"%Y-%m-%d"); 
							else
								echo JHTML::date($row->date,"Y-m-d");
							?> 
						</td>
				   <?php
				   }
				   ?>	
			   </tr>
			   
			   <?php 
			   if($plugin_params->get('show_introtext'))
			   {
			   ?>
				   <tr>
						<td colspan="3" class="jma_justify">
							<?php echo $row->intro; ?>
						</td>
				   </tr>
			   <?php
			   }
			   ?>
			   
			<?php
			}
			else
			{
				array_push($cat_array, $row->catid);
			?>
				
				
				<?php 
				if($plugin_params->get('show_category'))
				{
				?>
					<tr>
						<td class="jma_latestnewsjs_th"><?php echo $row->category; ?></td>
					</tr>
				<?php
				}
				?>
				   
				<tr>
					<td class="jma_latestnewsjs_td_60">
						<a href="<?php echo $row->link;?>"><?php echo $row->title;?></a>
					</td>
				   <?php 
				   if($plugin_params->get('show_author'))
				   {
				   ?>
					   <td class="jma_latestnewsjs_td_20">
							<?php echo $row->author; ?> 
						</td>
				   <?php
				   }
				   ?>
					
				   <?php 
				   if($plugin_params->get('show_date'))
				   {
				   ?>
					   <td class="jma_latestnewsjs_td_20">
							<?php 
							if(JVERSION<'1.6.0')
								echo JHTML::date($row->date,"%Y-%m-%d"); 
							else
								echo JHTML::date($row->date,"Y-m-d");
							?> 
						</td>
				   <?php
				   }
				   ?>
			   </tr>
			   <?php 
			   if($plugin_params->get('show_introtext'))
			   {
			   ?>
				   <tr>
						<td colspan="3" class="jma_justify">
							<?php echo $row->intro; ?>
						</td>
				   </tr>
			   <?php
			   }
			   ?>
			<?php
			}	
			?>				    
		<?php
		}
		?>
	</table>
