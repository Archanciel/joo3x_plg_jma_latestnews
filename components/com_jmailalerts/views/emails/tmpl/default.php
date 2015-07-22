<?php
/**
 * @version    SVN: <svn_id>
 * @package    JMailAlerts
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access.
defined('_JEXEC') or die();

JHTML::_('behavior.formvalidation');
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
JHtml::_('behavior.framework', true);

$doc = JFactory::getDocument();
$doc->addStyleSheet("components/com_jmailalerts/assets/css/jmailalerts.css");
$doc->addStyleDeclaration('.ui-accordion-header {margin: 1px 0px !important}');
$params   = JComponentHelper::getParams('com_jmailalerts');
$document = JFactory::getDocument();

$js='
	function divhide(thischk)
	{
		if(thischk.checked){
			document.getElementById(thischk.value).style.display="block";
		}
		else{
			document.getElementById(thischk.value).style.display="none";
		}
	}

	function divhide1(thischk)
	{
		if(thischk.value==0){
			document.getElementById("ac").style.display="none";
		}
		else{
			document.getElementById("ac").style.display="block";
		}
	}

	/*function chk_frequency(preferences_form)
	{
		/*
		Check if the "Select Frequency" is selected and "Unsubscribe" chkbox is not chked. This means the user hasnt entered any frequency not does he want to unsubscribe. So we are making him select a frequency before submitting the form
		*/
	/*	if(document.adminform.c[0].selected)
		{
			alert("1");
			if(!document.adminform.unsubscribe_chk_box.checked)
			{
				alert("2");
				alert("' . JText::_('SELECT_TYPE') . '");
				return;
			}
		}
		alert("3");
		return false;
		//preferences_form.submit();
	}*/
';

$document->addScriptDeclaration($js);
?>

<?php
// Added in 2.4.3
// Newly added for JS toolbar inclusion
if (JFolder::exists(JPATH_SITE . '/components/com_community') && $params->get('jstoolbar') == '1')
{
	require_once(JPATH_ROOT . '/components/com_community/libraries/toolbar.php');
	$toolbar = CFactory::getToolbar();
	$tool = CToolbarLibrary::getInstance();
?>
	<div id="community-wrap">
		<?php
		echo $tool->getHTML();
		?>
	</div>
<?php
}
// Eoc for JS toolbar inclusion

//Get the logged in user
$user = JFactory::getUser();
?>

<!--div for registration of guest user.-->
<div class="<?php echo JMAILALERTS_WRAPPER_CLASS;?>" id="jmailalerts-emails">
	<div class="col100" id="e-mail_alert">
		<!-- JPS: fixed handling of page header / page title -->
		<div class="componentheading page-header">
			<?php 
			$app = JFactory::getApplication();
			$appParam = $app->getParams();
			if ($appParam->get('show_page_heading')) : ?>
			<h1 class="page-title">
				<?php if ($this->escape($appParam->get('page_heading'))) :?>
					<?php echo $this->escape($appParam->get('page_heading')); ?>
				<?php else : ?>
					<?php echo $this->escape($appParam->get('page_title')); ?>
				<?php endif; ?>
			</h1>
			<?php endif; ?>
		</div>
			<form action="" class="form-validate form-horizontal" method="POST" id="adminform" name="adminform" ENCTYPE="multipart/form-data">
			<?php
			// if enable guest user registration then show name and email field.
			if (!$user->id && $params->get('guest_subcription')==1)
			{
				?>
				<div class="row-fluid" ><!--1-->
					<div class="span8"><!--2-->
						<div class="well">
							<div class="page-header">
								<h2><?php echo JText::_('COM_JMAILALERT_USER_REG');	?> </h2>
								<?php echo JText::_('COM_JMAILALERT_UN_REGISTER');?>
							</div>
							<div class="control-group">
								<label class="control-label"  for="user_name">
									<?php echo JText::_( 'COM_JMAILALERT_USER_NAME' ); ?>
								</label>
								<div class="controls">
									<input class="inputbox required validate-name" type="text" name="user_name" id="user_name" size="30" maxlength="50" value="" />
								</div>
							</div>
							<div class="control-group">
								<label class="control-label"  for="user_email">
									<?php echo JText::_( 'COM_JMAILALERT_USER_EMAIL' ); ?>
								</label>
								<div class="controls">
									<input class="inputbox required validate-email" type="text" name="user_email" id="user_email" size="30" maxlength="100" value="" />
								</div>
							</div>
						</div>
					</div>
					<div class="span4">
						<div class="well">
							<div class="page-header">
								<h2><?php echo JText::_('COM_JMAILALERT_LOGIN');	?> </h2>
								<?php echo JText::_('COM_JMAILALERT_REGISTER');?>
							</div>
							<a href='<?php
								$msg=JText::_('LOGIN');
								$uri=JRoute::_(JURI::root()."index.php?option=com_jmailalerts&view=emails");//@todo jmail alert url should be given to redirect
								$url=base64_encode($uri);
								echo 'index.php?option=com_users&view=login&return='.$url; ?>'>
								<div style="margin-left:auto;margin-right:auto;" class="control-group">
									<input id="LOGIN" class="btn btn-large btn-success validate" type="button" value="<?php echo JText::_('COM_JMAILALERT_SIGN_IN'); ?>">
								</div>
							</a>
						</div>
					</div>
				</div>
				<?php
				}
				elseif(!$user->id && $params->get('guest_subcription')==0)
				{
					?>
					<div class="alert alert-block">
						<?php echo JText::_('YOU_NEED_TO_BE_LOGGED_IN'); ?>
					</div>
				</div><!--techjoomla bootstrap ends if not logged in-->
			</div><!--mail_alert ends if not logged in-->
					<?php
					return false;
				}

				?>
				<div class="jma_email_intro">
					<div class=" well">
						<span class="alert_preferences_intro_msg" style="font-size:14px;font-weight:bold;">
							<?php echo JText::_('INTRO_MSG'); // $params->get('intro_msg'); must not come from the component parms defined in the jmailalert.xml file !!! ?>
						</span>
					</div>
				</div>
				<br>
				<?php

			$disp_none = " ";

			if (trim($this->cntalert) == 0)
			{
				$disp_none = "display:none";
			}
			?>

			<table class="jma_table">
				<tr>
					<td>
						<?php
						$maplist[] = JHTML::_('select.option', '0', JText::_('N0_FREQUENCY'), 'value', 'text');?>
					</td>
				</tr>
				<tr>
					<td>
						<div id="ac" style="<?php echo $disp_none;?>">
							<?php
							if (trim($this->cntalert) != 0) {
									echo $this->loadTemplate('joomla16');
							}
							?>
						</div>
					</td>
				</tr>
			</table>

			<div id="manual_div" align="left" style="display:block; padding-top: 10px;">
				<?php
				if (trim($this->cntalert) != 0)
				{
					?>
					<div class="form-actions">
					<button class="btn btn-primary validate" type="submit" ><?php echo JText::_('BUTTON_SAVE'); ?></button>
					</div>
					<?php
				}
				?>

				<input type="hidden" name="option" value="com_jmailalerts">
				<input type="hidden" id="task" name="task" value="savePref">
			</div>
		</form>
	</div>
</div>
