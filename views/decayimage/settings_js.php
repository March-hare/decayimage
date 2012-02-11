<?php
/**
 * Javascript view for the settings page for Density Map plugin
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
// geometrys JS
function fillFields(id, category_id, decayimage_image, decayimage_thumb)
{
	$("#decayimage_id").attr("value", unescape(id));
	$("#category_id").val(category_id);
  $("#decayimage_thumb").val(decayimage_thumb);
  // This is only called when editing an exising decayimage
	$("#action").val('e');
  changeDecayImagePreview(decayimage_thumb);
}

function decayimageAction ( action, confirmAction, id )
{
	var statusMessage;
	var answer = confirm('<?php echo Kohana::lang('ui_admin.are_you_sure_you_want_to'); ?> ' + confirmAction + '?')
	if (answer){
		// Set Category ID
		$("#decayimage_id_action").attr("value", id);
		// Set Submit Type
		$("#action").attr("value", action);		
		// Submit Form
		$("#decayimageListing").submit();
	}
}

function changeDecayImagePreview(src) {
  $(".decayimage_preview").html(src == "<?php echo $default_decayimage_thumb ?>" ? 
    "<img src='<?php echo url::site() ."plugins/decayimage/images/". $default_decayimage_thumb ?>'>" :
    "<img src='<?php echo url::site() ?>media/uploads/" + src + "'>"
    );
}

// TODO: this should take 
$(document).ready(function() {
    $("#decayimage_thumb").change(function() {
        changeDecayImagePreview($(this).val());
    });
    changeDecayImagePreview("<?php echo $default_decayimage_thumb ?>");
});
