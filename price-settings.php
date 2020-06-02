<!-- General Settings Section -->
<div class="postbox">
    <a name="step1"></a>
    <h2 class="hndle ui-sortable-handle" style="cursor:default;"><span><?php _e('Price per charcater', 'bk-fonts'); ?></span></h2>
    <div class="inside">
        <form method="post" action="options.php#1" class="bk-cpc-form">
        <?php
            // This prints out all hidden setting fields
            settings_fields( 'bk_cpc_options' );
            fo_do_settings_section( 'font-setting-admin', 'setting_general' );
            submit_button();
        ?>
        </form>
    </div>
</div>

<!-- Misc Settings Section -->
<div class="postbox">
    <a name="step1"></a>
    <h2 class="hndle ui-sortable-handle" style="cursor:default;"><span><?php _e('Misc Settings', 'bk-fonts'); ?></span></h2>
    <div class="inside">
        <form method="post" action="options.php#1">
        <?php
            // This prints out all hidden setting fields
            settings_fields( 'bk_misc_options' );
            fo_do_settings_section( 'font-setting-admin', 'bk_misc_settings' );
            submit_button();
        ?>
        </form>
    </div>
</div>

<!-- Product Images Settings Section -->
<div class="postbox">
    <a name="step1"></a>
    <h2 class="hndle ui-sortable-handle" style="cursor:default;"><span><?php _e('Product Images', 'bk-fonts'); ?></span></h2>
    <div class="inside">
        <form method="post" action="options.php#1">
        <?php
            // This prints out all hidden setting fields
            settings_fields( 'bk_pimg_options' );
            fo_do_settings_section( 'font-setting-admin', 'bk_pimg_settings' );
            submit_button();
        ?>
        </form>
    </div>
</div>

<!-- Product Images Settings Section -->
<div class="postbox">
    <a name="step1"></a>
    <h2 class="hndle ui-sortable-handle" style="cursor:default;"><span><?php _e('Mateials', 'bk-fonts'); ?></span></h2>
    <div class="inside">
        <form method="post" action="options.php#1">
        <?php
            // This prints out all hidden setting fields
            settings_fields( 'bk_material_options' );
            fo_do_settings_section( 'font-setting-admin', 'bk_material_settings' );
            submit_button();
        ?>
        </form>
    </div>
</div>
