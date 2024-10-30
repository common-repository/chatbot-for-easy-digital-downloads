<?php

class qcld_eddbot_info_page
{

    function __construct()
    {
        add_action('admin_menu', array($this, 'qcopd_info_menu'));
    }

    function qcopd_info_menu()
    {
        add_submenu_page(
            'eddBot',
            esc_html('Help','eddchatot'),
            esc_html('Help','eddchatot'),
            'manage_options',
            'qcld_eddbot_info_page',
            array(
                $this,
                'qcopd_info_page_content'
            )
        );
    }

    function qcopd_info_page_content()
    {
        ?>
        
        <div class="wrap">

            <div id="poststuff">

                <div id="post-body" class="metabox-holder columns-2">

                    <div id="post-body-content" style="position: relative;">


                        <div class="qc-plugin-help-container">
                            <h3 class="qc-plugin-help-heading-lg">Help</h3>
                            <p>
                                <?php echo esc_html('Getting started with EDDBot is instantaneous. All you need to do is install and activate the plugin','eddchatbot'); ?>.
                            </p>
                            <p>
                            <?php echo esc_html('You can upload your own ChatBot icon from EDDBot panel->Icons section.','eddchatbot'); ?>
                            </p>
                            <p>
                            <?php echo esc_html(' You can also upload a custom Agent icon in the pro version.','eddchatbot'); ?>
                            </p>
                            <p>
                            <?php echo esc_html('In the lite version there are a few language settings that you can customize to your need. The default languages are fine for stores using the English language. But you can change the bot responses literally into any language!','eddchatbot'); ?>
                            </p>
                            <p><?php echo esc_html('Use the custom CSS panel if you need to tweak some colors or font settings inside EDDBot.','eddchatbot'); ?></p>
                            
                            <div class="clear"></div>
                            


							<div style="position:relative">
					
						</div>
                        </div>

                        <div style="padding: 15px 10px; border: 1px solid #ccc; text-align: center; margin-top: 20px;margin-left: 14px;width: 1170px;">
                        <?php echo esc_html(' Crafted By:','eddchatbot'); ?> <a href="<?php echo esc_url('http://www.quantumcloud.com','eddchatbot'); ?>" target="_blank"><?php echo esc_html('Web Design Company','eddchatbot'); ?></a> -
                        <?php echo esc_html('QuantumCloud','eddchatbot'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php
    }
}

new qcld_eddbot_info_page;

 