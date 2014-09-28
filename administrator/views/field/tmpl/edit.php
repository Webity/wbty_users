<?php
/**
 * @version     1
 * @package     com_wbty_users
 * @copyright   Copyright (C) 2012-2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Webity <david@makethewebwork.com> - http://www.makethewebwork.com
 */

// no direct access
defined('_JEXEC') or die;

JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');

$document = &JFactory::getDocument();
JHTML::script("wbty_components/linked_tables.js", false, true);
JHTML::script("wbty_users/edit.js", false, true);

$script = "
    jQuery(document).ready(function($) { 
        // Declare this object outside of the change function to maintain values for the life of the script
        var jvalues = new Object();
    
        $( 'select.normal' ).change(
            function() {
                var option = ($( this ).val());
                var id = '".$this->form->getValue('id', 'field')."';
                
                // store all current values
                $(this).closest('fieldset').find('div.field-options').find('input').each(function(index, e) {
                    name = $(e).attr('name');
                    if ($(e).attr('type') != 'hidden') {
                        jvalues[name] = $(e).val();
                    }
                });
                
                if (option) {
                    $(this).closest('fieldset').find('div.field-options').html('loading');
                    $.ajax({  
                      type: \"POST\",  
                      url: \"".JRoute::_('index.php?option=com_wbty_users&task=field.extraFields&tmpl=component', false)."\",  
                      data: {'field' : option, 'id' : id},  
                      context: this,
                      success: function(applyData) {
                            console.log(applyData);
                            $(this).closest('fieldset').find('div.field-options').html(applyData);
                            // override any currently stored database values with values entered in this session
                            for (key in jvalues) {
                                $('input[name=\''+key+'\']').val(jvalues[key]);
                            }
                            if ($('#jfields_controls_subtables').length) {
                                $('#jfields_controls_subtables').trigger('change');
                            }
                        }
                    });
                } else {
                    $(this).closest('fieldset').find('div.field-options').html('');  
                }
            });
        if ($( 'select.normal' ).val()) {
            $( 'select.normal' ).trigger('change');
        }
        
        /* General Field listeners */
        
        $('#jfields_label').live('change', function() {
            $('#jfields_name').val($(this).val().toLowerCase().replace(/ /g,'_'));
        });
        
        
        $('#jfields_name').live('change', function() {
            var value = $(this).val();                         
            if (value === 'id' || value === 'ordering' || value === 'state' || value === 'checked_out' || value === 'checked_out_time' || value === 'created_by' || value === 'created_time' || value === 'modified_by' || value === 'modified_time') {
                alert('The table name \"'+ value +'\" is used by the core system. Please choose a different name.');
                $(this).val((value + '_1'));
            }
        });
        
        
        /* Start Field Specific listeners */
        
        $( '#jfields_query' ).live('change',
            function() {
                var option = $(this).val();
                
                if (option) {
                    var jqthis = $(this);
                    /*$('#jfields_key_field').closest('li').remove();*/
                    $('#jfields_value_field').closest('li').remove();
                    $.ajax({  
                      type: \"POST\",  
                      url: \"".JRoute::_('index.php?option=com_wbty_users&task=field.getKeyValue&tmpl=component', false)."\",  
                      data: {'ajax_table_id' : option},  
                      success: function(applyData) {
                            jqthis.closest('li').after(applyData);
                            // override any currently stored database values with values entered in this session
                            for (key in jvalues) {
                                $('input[name=\''+key+'\']').val(jvalues[key]);
                            }
                        }
                    });
                } else {
                    $(this).html('');  
                }
            });
        
        $('#jfields_controls_subtables').live('change', function() {
            if (!$(this).prop('checked')) {
                $('#controls_subtables').remove();
            } else {
                $(this).closest('li').after($('<li id=\"controls_subtables\" style=\"clear:both;\"></li>'));
                $('#controls_subtables').html('loading');
                $.ajax({  
                  type: \"POST\",  
                  url: \"".JRoute::_('index.php?option=com_wbty_users&task=field.subtableControls&tmpl=component&time='.time(), false)."\",  
                  data: {'config_id': $('#jfields_query').val(), 'id' : $('#jform_table_id').val(), 'field_id': $('#jform_id').val()},  
                  success: function(applyData) {
                        $('#controls_subtables').html(applyData);
                    }
                });
            }
        });
            
            
        
    });
    ";

$document->addScriptDeclaration($script);

ob_start();
// start javascript output -- script
?>
window.addEvent('domready', function(){
    // save validator, getting overwritten by AJAX call
    document.fieldvalidator = document.formvalidator;
    jQuery('#field-form .toolbar-list a').each(function() {
        $(this).attr('data-onclick', $(this).attr('onclick')).attr('onclick','');
    });
    jQuery('#field-form .toolbar-list a').click(function() { 
        Joomla.submitbutton = document.fieldsubmitbutton;
        
        // clean up hidden subtables
        jQuery('.subtables:hidden').remove();
        
        eval($(this).attr('data-onclick'));
    });
});

window.juri_root = '<?php echo JURI::root(); ?>';
window.juri_base = '<?php echo JURI::base(); ?>';

Joomla.submitbutton = function(task)
{
    if (jQuery('#sbox-window').attr('aria-hidden')==true) {
        Joomla.submitform = defaultsubmitform;
    }
    
    if (task == 'field.cancel' || document.fieldvalidator.isValid(document.id('field-form'))) {
        Joomla.submitform(task, document.getElementById('field-form'));
    }
    else {
        alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
    }
}
document.fieldsubmitbutton = Joomla.submitbutton;
<?php
// end javascript output -- /script
$script=ob_get_contents();
ob_end_clean();
$document->addScriptDeclaration($script);
?>

<?php echo JHTML::_('wbty_usersHelper.buildEditForm', $this->form); ?>

<form action="<?php echo JRoute::_('index.php?option=com_wbty_users&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="field-form" class="form-validate form-horizontal">
    <div class="row-fluid">
        <div class="span6">
            <fieldset class="adminform parentform" data-controller="field" data-task="field.ajax_save">
                <legend><?php echo JText::_('COM_WBTY_USERS_LEGEND_FIELD'); ?></legend>
                <div class="items">
                    <?php 
                        foreach($this->form->getFieldset('field') as $field):
                            JHtml::_('wbty.renderField', $field);
                        endforeach; 
                    ?>

                    <div class="field-options"></div>

                    <div class="control-group"> 
                        <div class="controls">
                            <span class="btn btn-success save-primary"><i class="icon-ok"></i> Save field Info</span>
                        </div>
                    </div>
                </div>

            </fieldset>
            
        </div>
            
        <?php // fieldset for each linked table  ?>
        <div class="span6 subtables">
		<?php
		// Add hidden form fields so as to run neccesary scripts for any modals, ect.
		require_once(JPATH_COMPONENT_ADMINISTRATOR . '/helpers/ajax.php');
		$helper = new wbty_usersHelperAjax;
		?></div>
    </div>
	
    
	<input type="hidden" name="task" value="" />
    <input type="hidden" name="option" id="option" value="com_wbty_users" />
    <input type="hidden" name="form_name" id="form_name" value="field" />
	<?php echo JHtml::_('form.token'); ?>
	<div class="clr"></div>
</form>