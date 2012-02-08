<?php 
/**
 * View for the settings page for Density Map plugin
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author	   John Etherton <john@ethertontech.com> 
 * @package    Ushahidi - http://source.ushahididev.com
 * @license    http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL) 
 */
?>
			<div class="bg">
				<h2>
					<?php echo Kohana::lang("decayimage.decayimage_settings"); ?>
				</h2>
				<?php
				if ($form_error) {
				?>
					<!-- red-box -->
					<div class="red-box">
						<h3><?php echo Kohana::lang('ui_main.error');?></h3>
						<ul>
						<?php
						foreach ($errors as $error_item => $error_description)
						{
							// print "<li>" . $error_description . "</li>";
							print (!$error_description) ? '' : "<li>" . $error_description . "</li>";
						}
						?>
						</ul>
					</div>
				<?php
				}

				if ($form_saved) {
				?>
					<!-- green-box -->
					<div class="green-box">
						<h3><?php echo Kohana::lang('decayimage.decayimage_has_been');?> <?php echo $form_action; ?>!</h3>
					</div>
				<?php
				}
				?>
				<!-- report-table -->
				<div class="report-form">
								<!-- tabs -->
				<div class="tabs">
					<!-- tabset -->
					<a name="add"></a>
					<ul class="tabset">
						<li><a href="#" class="active"><?php echo Kohana::lang('ui_main.add_edit');?></a></li>
					</ul>
					<!-- tab -->
					<div class="tab">
						<?php print form::open(NULL,array('enctype' => 'multipart/form-data', 
							'id' => 'decayimageMain', 'name' => 'decayimageMain')); ?>
						<input type="hidden" id="decayimage_id" 
							name="decayimage_id" value="" />
						<input type="hidden" name="action" 
							id="action" value="a"/>
						<div class="tab_form_item">
							<strong><?php echo Kohana::lang('ui_main.category');?>:</strong><br />
							<?php print form::dropdown('category_id',$cat_array); ?>
						</div>
						<div class="tab_form_item">
							<strong><?php echo Kohana::lang('ui_main.image_icon');?>:</strong><br />
							<?php print form::dropdown(
                      'decayimage_thumb',
                      $decayimage_array, 
                      array($default_decayimage_thumb)); ?>
						</div>
						<div class="tab_form_item">
							<strong><?php echo Kohana::lang('ui_main.image_icon');?>:</strong><br />
							<?php print form::upload('decayimage_file', '', ''); ?>
            </div>
            <div class="tab_form_item decayimage_preview">
            </div>
						<div style="clear:both"></div>
						<div class="tab_form_item">
							&nbsp;<br />
							<input type="image" src="<?php echo url::file_loc('img'); ?>media/img/admin/btn-save.gif" class="save-rep-btn" />
						</div>
						<?php print form::close(); ?>			
					</div>
				</div>
				
					<?php print form::open(NULL,array('id' => 'decayimageListing',
					 	'name' => 'decayimageListing')); ?>
						<input type="hidden" name="action" id="action" value="">
						<input type="hidden" name="decayimage_id" id="decayimage_id_action" value="">
						<div class="table-holder">
							<table class="table">
								<thead>
									<tr>
										<th class="col-1">&nbsp;</th>
										<th class="col-2"><?php echo Kohana::lang('ui_main.category');?></th>
										<th class="col-3"><?php echo Kohana::lang('decayimage.decayimage');?></th>
										<th class="col-4"><?php echo Kohana::lang('ui_main.actions');?></th>
									</tr>
								</thead>
								<tbody>
									<?php
									foreach ($decayimages as $decayimage)
									{
										$decayimage_id = $decayimage->id;
										$category_id = $decayimage->category_id;
										$decayimage_image = $decayimage->decayimage_image;
										$decayimage_icon = $decayimage->decayimage_icon;
										?>
										<tr>
											<td class="col-1">&nbsp;</td>
											<td class="col-2">
												<div class="post">
													<h4><?php echo isset($cat_array[$category_id]) ? $cat_array[$category_id] : "--CATEGORY MISSING--" ; ?></h4>
												</div>
											</td>
											<td class="col-3">
                      <?php 
                        if ($decayimage_icon == $default_decayimage_thumb) {
                          echo "<img src='$url_site/plugins/decayimage/images/$default_decayimage_thumb'>";
                        } else {
                          echo "<img src='$url_site/media/uploads/$decayimage_icon'>";
                        }
                      ?>
											</td>
											<td class="col-4">
												<ul>
                          <!-- TODO: this is where I left off, fillFields is in settings_js.php -->
													<li class="none-separator">
                          <a href="#add" 
                            onClick="fillFields('<?php 
                              echo(rawurlencode($decayimage_id)); 
                            ?>','<?php 
                              echo(rawurlencode($category_id)); 
                            ?>','<?php 
                              echo(rawurlencode($kml_file)); 
                            ?>', '<?php 
                              echo(rawurlencode($lat));
                            ?>', '<?php 
                              echo(rawurlencode($lon)); ?>')"><?php 
                            echo Kohana::lang('ui_main.edit');?></a>
                          </li>													
													<li>
                          <a href="javascript:decayimageAction('d','DELETE','<?php 
                            echo(rawurlencode($decayimage_id)); ?>')" class="del"><?php 
                            echo Kohana::lang('ui_main.delete');?></a>
                          </li>
												</ul>
											</td>
										</tr>
										<?php									
									}
									?>
								</tbody>
							</table>
						</div>
					<?php print form::close(); ?>
				</div>
				
			</div>
