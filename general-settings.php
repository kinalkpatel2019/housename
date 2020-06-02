<!-- Add Custom Fonts To Website Section -->
<div class="postbox">
    <a name="step3"></a>
    <h2 class="hndle ui-sortable-handle" style="cursor:default;"><span><?php _e('1. Upload Fonts', 'bk-fonts'); ?></span></h2>
    <div class="inside">
        <span><?php _e('Upload custom fonts to be used in your website.', 'bk-fonts'); ?></span>
        <br />
        <span><?php _e('Name the font you want to upload with the actual name of the font or similar', 'bk-fonts'); ?></span>
        <div class="custom_font_message fo_warning" style="display: none;">
                <i class="fa fa-warning"></i>
                <?php _e("This font format is already selected. Reminder: you need to upload the font files for the same font weight.", "font-organizer"); ?>
                <span></span>
        </div>

        <form action="#0" id="add_font_form" name="add_font_form" method="post" enctype="multipart/form-data">
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="font_name" class="required"><?php _e('Font Name', 'bk-fonts'); ?></label></th>
                    <td><input type="text" id="font_name" required oninvalid="this.setCustomValidity('<?php _e('Font weight name cannot be empty.', 'bk-fonts'); ?>')" oninput="setCustomValidity('')" name="font_name" value="" class="required" maxlength="20" /></td>
                </tr>
                <!-- <tr>
                    <th scope="row"><label for="font_weight" class="required"><?php _e('Font Weight', 'bk-fonts'); ?></label></th>
                    <td><?php $this->print_fonts_weights_list('font_weight'); ?></td>
                </tr> -->
                <tr class="font_file_wrapper">
                    <th scope="row">
                        <label for="font_file" class="required"><?php _e('Font Weight File', 'bk-fonts'); ?></label>
                    </th>
                    <td id="font_file_parent" style="width:33%;">
                        <input type="file" name="font_file[]" value="" class="add_font_file required" onfocus="this.oldvalue = this.value;" accept="<?php echo join(',',$this->supported_font_files); ?>"  /><br/>
                        <em><?php echo __('Accepted Font Format : ', 'bk-fonts') . '<span style="direction: ltr">' . join(', ',$this->supported_font_files) . '</span>'; ?></em><br/>
                    </td>
                    <td>
                         <!-- <a href="javascript:void(0);" class="add_button" title="<?php _e('Add Another Font Format File', 'bk-fonts'); ?>"><i class="fa fa-plus fa-2x" aria-hidden="true"></i></a>
                         <span style="font-size: 11px;font-style: italic;position: absolute;padding: 6px;"><?php _e('Add Another Font Format File', 'bk-fonts'); ?></span> -->
                    </td>
                </tr>

                <tr>
                    <th scope="row"></th>
                    <td>
                     <?php wp_nonce_field( 'add_custom_font', 'add_custom_font_nonce' ); ?>
                    <input type="submit" name="submit_upload_font" id="submit_upload_font" class="button-primary" value="<?php _e('Upload', 'bk-fonts'); ?>" />
                    </td>
                </tr>
            </table>
        </form>
    </div>
</div>

<!-- Assign Fonts To Custom Elements Section -->
<div class="postbox">
    <a name="step5"></a>
    <h2 class="hndle ui-sortable-handle" style="cursor:default;"><span><?php _e('Assign Fonts', 'bk-fonts'); ?></span></h2>
    <div class="inside">

        <span><?php _e('Assign a font to custom elements in your website. Simply select the font, and then type the elements you want to assign with the font.', 'bk-fonts'); ?></span>
        <form action="#" id="add_custom_elements_form" name="add_custom_elements_form" method="post">
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="font_id" class="required"><?php _e('Font', 'bk-fonts'); ?></label></th>
                    <td><?php $this->print_custom_elements_usable_fonts_list('font_id', __('-- Select Font --', 'bk-fonts'), __("You must select a font for the elements.", "font-organizer")); ?></td>
                </tr>
                 <!-- <tr>
                    <th scope="row"><label for="font_weight" class="required"><?php _e('Font Weight', 'bk-fonts'); ?></label></th>
                    <td><?php $this->fonts_weight_list_field('font_weight'); ?></td>
                </tr> -->
                <tr>
                    <th scope="row">
                        <label for="custom_elements" class="required">
                            <?php _e('Custom Element', 'bk-fonts'); ?>
                        </label>
                    </th>
                    <td>
                        <textarea id="custom_elements" name="custom_elements" required oninvalid="this.setCustomValidity('<?php _e('Font custom elements cannot be empty.', 'bk-fonts'); ?>')" oninput="setCustomValidity('')" style="width: 100%" rows="2"></textarea>
                        <em><?php _e('Custom elements can be seperated by commas to allow multiple elements. Example: #myelementid, .myelementclass, .myelementclass .foo, etc.', 'bk-fonts'); ?></em>
                    </td>
                </tr>
                <tr>
                    <th></th>
                    <td><?php $this->print_is_important_checkbox('important'); ?></td>
                </tr>
                <tr>
                    <th scope="row"></th>
                    <td>
                     <?php wp_nonce_field( 'add_custom_elements', 'add_custom_elements_nonce' ); ?>
                    <input type="submit" name="submit_custom_elements" id="submit_custom_elements" class="button-primary" value="<?php _e('Apply Custom Elements', 'bk-fonts'); ?>" />
                    </td>
                </tr>
            </table>
        </form>
    </div>
</div>

<!-- Manage Used fonts Section -->
<div class="postbox">
    <a name="step6"></a>
    <h2 class="hndle ui-sortable-handle" style="cursor:default;"><span><?php _e('5. Manage Fonts', 'bk-fonts'); ?></span></h2>
    <div class="inside">
            <span>
                <?php _e('Step 5: Select a font to delete, edit or view its source and custom elements assigned to it.', 'bk-fonts'); ?>
            </span>
             <p>
                <strong><?php _e('Note: ', 'bk-fonts'); ?></strong>
                <?php _e('You can edit the values of every row to change the custom elements assigned or add and remove the important tag. Just change the text or check the box and the settings will automatically save.', 'bk-fonts'); ?>
            </p>
            <div class="custom_elements_message fo_success" style="display: none;">
                <i class="fa fa-info-circle"></i>
                <?php _e('Changes saved!', 'bk-fonts'); ?>
            </div>
            <div class="custom_elements_message fo_warning" style="display: none;">
                <i class="fa fa-warning"></i>
                <?php _e("Data is invalid", "font-organizer"); ?>
                <span></span>
            </div>
            <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Font', 'bk-fonts'); ?></th>
                        <td>
                            <form action="#step6" id="select_font_form" name="select_font_form" method="get">
                               <?php $this->print_custom_elements_usable_fonts_list('manage_font_id', __('-- Select Font --', 'bk-fonts')); ?>
                                <input type="hidden" name="page" value="<?php echo wp_unslash( $_REQUEST['page'] ); ?>">
                            </form>
                        </td>
                         <?php if($this->selected_manage_font): ?>

                        <td style="text-align:left;">
                            <form action="#step6" id="delete_usable_font_form" name="delete_usable_font_form" method="post">
                                <?php wp_nonce_field( 'delete_usable_font', 'delete_usable_font_nonce' ); ?>
                                <input type="hidden" name="page" value="<?php echo wp_unslash( $_REQUEST['page'] ); ?>">
                                <input type="hidden" name="font_id" value="<?php echo $_GET['manage_font_id']; ?>">
                                <input type="hidden" name="font_name" value="<?php echo $this->selected_manage_font->family; ?>">
                                <input type="submit" name="delete_usable_font" id="delete_usable_font" class="button-secondary" value="<?php _e('Delete Font', 'bk-fonts'); ?>" onclick="return confirm('<?php _e("Are you sure you want to delete this font from your website?", "font-organizer"); ?>')" />
                            </form>
                        </td>

                    <?php endif; ?>
                    </tr>
            </table>
        <?php if($this->selected_manage_font): ?>
       	<hr/>
        <table class="form-table">
            <tr>
                <th scope="row"><?php _e('Source', 'bk-fonts'); ?></th>
                <td><span><?php fo_print_source($this->selected_manage_font->kind); ?></span></td>
            </tr>
            <tr>
                <th scope="row"><?php _e('Urls', 'bk-fonts'); ?></th>
                <td>
                    <?php
                    foreach ($this->selected_manage_font->files as $weight => $urls) {
                        echo '<div style="margin-bottom: 10px;">';
                        echo '<span style="font-weight:bold">' . fo_get_font_weight($weight) . '</span><br />';
                        echo '<div style="direction:ltr;text-align:left;line-height:20px;">';
                        if(is_array($urls)){
                            foreach($urls as $url)
                                echo $url, '<br>';
                        }else{
                            echo $this->selected_manage_font->files->regular;
                        }

                        echo '<br />';
                        echo '</div>';
                        echo '</div>';
                    }
                    ?>
                </td>
            </tr>
        </table>
        <div class="wp-table-fo-container">
         	<form id="custom_elements-filter" method="get" action="#step6">
         		<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
         		<input type="hidden" name="manage_font_id" value="<?php echo $_GET['manage_font_id']; ?>">
       			<?php $this->custom_elements_table->display(); ?>
       		</form>
        </div>
        <?php
        endif;
        ?>
    </div>
</div>
