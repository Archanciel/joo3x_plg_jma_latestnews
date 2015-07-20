<?php
/*
 * @package 	J!MailALerts
 * @version		$versionID$
 * @author		TechJoomla
 * @author mail	extensions@techjoomla.com
 * @website		http://techjoomla.com
 * @copyright	Copyright © 2009-2013 TechJoomla. All rights reserved.
 * @license		GNU General Public License version 2, or later
*/

$k = array();
$i = 1;
$model = $this->getModel();
$qry_concat = $this->qry_concat;
$option = $this->defaultoption;
$default_setting = $this->default_setting;
$altid = $this->altid;
//$allow_user_select_plugin=$this->allow_user_select_plugin;
for ($s = 0; $s < count($qry_concat); $s++) //for loop for alert types
{
	$plugin_data = $model->getPluginData($qry_concat[$s]);
	//checking alert id with default selected alert for checking checkbox
	/*changed in 2.4.3*/
	//if(in_array($altid[$s],$option))
	if (isset($option[$altid[$s]]) && $option[$altid[$s]]['state'] == 1) 
	{
		$plugin_name = $model->getData($altid[$s]);
		$alertchk = "checked";
		$bstyle = 'class="subscribed_alert"';
		$sub_status_msg = JText::_('JMA_UNCHECK_SUB_MSG');
		$show_plugins = "block";
		$check_hidden_plugin = 0;
	} else 
	{
		$plugin_name = $model->getData($altid[$s]);
		$alertchk = "";
		$bstyle = 'class="unsubscribed_alert"';
		$sub_status_msg = JText::_('JMA_CHECK_SUB_MSG');
		$show_plugins = 'none';
		$check_hidden_plugin = 1;
	}

	//get frequency
	$altdata = $model->getFreq($altid[$s]);
	if ($altdata[3] == 1)
		$allowuser = "display";
	else
		$allowuser = "none";

	echo '
	<div>
								
		<div class="well">
			<input type="checkbox" name="alt[]" value="' . $altid[$s] . '" onclick="divhide(this);" ' . $alertchk . '/>
			<b ' . $bstyle . '>' . $altdata[1] . '</b> 
			<span class="sub_status_msg">' . $sub_status_msg . '</span>
			
			<div>
	        	<div class="jma_alert_desc">' . $altdata[2] . '</div>
		  		<div id="' . $altid[$s] . '" style="display:' . $show_plugins . '">
			  		<div style="display:' . $allowuser . '">
		  				<div class="alert_frequncy">
		  					<b>' . JText::_("CURRENT_SETTING") . '</b>
		  				</div>
		  				<div>' . $altdata[0] . '</div>';
						if ($plugin_data) 
						{
							foreach ($plugin_data as $single_plugin_name) 
							{
								$plugtitleparm = explode(':', $single_plugin_name->params);
								$plugtitltlex = explode(',', $plugtitleparm[1]);
								$plugtitle = str_replace('"', '', $plugtitltlex[0]);
								$flag = 0;
								if(!empty($plugin_name))
								foreach ($plugin_name as $key => $v) 
								{
									if ($single_plugin_name->element == $key) 
									{
										$params = implode("\n", $v);
										$params = str_replace(',', '|', $params);
										$disp = '';
										$chk = $v[count($v) - 1];
										if ($chk == "checked=''")
										    $checked = '';
										else
										    $checked = "checked";
										$flag = 1;
										break;
									}
									else 
									{
										$disp = 'style="display:none"';
										$flag = 0;
										$checked = "";
									}
								}
								// var_dump($plugin_name);
								if ($flag == 1) 
								{
									if (!in_array($single_plugin_name->element, $k)) 
									{
										$k[] = $single_plugin_name->element;?>
										<div class="jmail-blocks">

											<div class="well jma_plugin_background">
												<?php
												//if($default_setting ==1)		
												
												if ($check_hidden_plugin)
												    $checked = "checked";
												// JPS the div visibility is set to hidden to avoid having two
												// dupliclate alert subscription checkbox on the form. Simply removing
												// this php snippet breaks the saving of the subscriotion prefs !
												echo '
												<div class="jma_alert" style="visibility: hidden;">
													<input type="checkbox" name="ch' . $altid[$s] . '[]" value="' . $single_plugin_name->element . '_' . $altid[$s] . '" onclick="divhide(this);" ' . $checked . '/>
													<b>' . $plugtitle . '</b>
												</div>';
												?>

												<div class="jmail-expands" id="<?php echo $single_plugin_name->element . '_' . $altid[$s] . $disp ?>">
													<?php
													$form = null;
													$form_path = JPATH_SITE . DS . 'plugins' . DS . 'emailalerts' . DS . $single_plugin_name->element . DS . $single_plugin_name->element . DS . 'form' . DS . 'form_' . $single_plugin_name->element . '.xml';
													$test = $single_plugin_name->element . '_' . $altid[$s];

													//$form = JForm::getInstance($single_plugin_name->element,$form_path);
													$form = JForm::getInstance($test, $form_path);
													$params = explode("\n", $params);

													foreach ($params as $param) 
													{
														$par = explode('=', $param);
														$par_name = $par[0];
														$par_val = $par[1];
														if (strpos($par_val, '|')) 
														{
															$array_par_val = explode('|', $par_val);
															$array[$single_plugin_name->element][$par_name] = $array_par_val;
														} 
														else 
														{
															$array[$single_plugin_name->element][$par_name] = $par_val;
														}
													}
													$form->bind($array);
													
													foreach ($form->getFieldsets() as $fieldset): // Iterate through the form fieldsets and display each one.
														$fields = '';
														$fields = $form->getFieldset($fieldset->name);
														if (count($fields)): 
														?>
															<fieldset>
																<?php 
																if (isset($fieldset->label)): // If the fieldset has a label set, display it as the legend.
																?>
																<legend>
																<?php
																	echo JText::_($fieldset->label);
																?>
																</legend>
																<?php endif;?>

																	<?php
																	foreach ($fields as $field): // Iterate through the fields in the set and display them.
																	?>
																	<div class="control-group">
																		<?php
																		if ($field->hidden): // If the field is hidden, just display the input.
																		?>
																			<?php
																			//echo $field->input;
																			$in = str_replace($single_plugin_name->element, $test, $field->input);
																			echo $in;
																			?>
																			<?php
																			else:
																			?>
																			<div class="control-label">
																				<?php echo $field->label; ?>
																				<?php
																				if (!$field->required && (!$field->type == "spacer")):
																				?>
																				<span
																					class="optional"><?php echo JText::_('COM_USERS_OPTIONAL');?></span>
																				<?php
																				endif;
																				?>
																			</div>
																			<div class="controls">
																			<?php
																				//$in=  $field->input;
																				$in = str_replace($single_plugin_name->element, $test, $field->input);
																				echo $in;
																				//echo $field->input;
																				?>
																			 </div>
																		<?php endif; ?>
																		</div>
																	<?php endforeach;?>

															</fieldset>
														<?php endif; ?>
													<?php endforeach; ?>
												</div><!--jmail-expand ends-->
											</div><!--well-->
											

										</div><!--jmail-blocks-->
										<?php
									}
								}
								unset($plugtitle);
								unset($plugtitltlex);
								unset($plugtitleparm);
							}
							echo '
									</div>
								</div>
							</div>
						</div>


						';
						}
						else
						{
							echo JText::_('NO_PLUGINS_ENABLED_OR_INSTALLED');
						}
						unset($k);
						$k = array();
						$i = 1;
		echo '
		</div>
	</div>';
}
