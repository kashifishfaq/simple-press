<?php
/*
Simple:Press
Admin themes desktop
$LastChangedDate: 2017-12-28 11:37:41 -0600 (Thu, 28 Dec 2017) $
$Rev: 15601 $
*/

if (preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) die('Access denied - you cannot directly call this file');

function spa_themes_list_form() {
    # get current theme
    $curTheme = SP()->options->get('sp_current_theme');

    # get themes
	$themes = SP()->theme->get_list();

	spa_paint_options_init();
	spa_paint_open_tab(SP()->primitives->admin_text('Available Themes').'- '.SP()->primitives->admin_text('Select Simple:Press Theme'), true);
	spa_paint_open_panel();

	spa_paint_spacer();
	echo '<div class="sfoptionerror">';
	echo SP()->primitives->admin_text('Themes Folder').': <b>'.realpath(SP_STORE_DIR.'/'.SP()->plugin->storage['themes']).'</b>';
	echo '</div>';

	spa_paint_open_fieldset(SP()->primitives->admin_text('Theme Management'), true, 'themes');
?>
	<h3><?php echo SP()->primitives->admin_text('Current Theme'); ?></h3>
	<div class="theme-browser rendered">
	<div class="spThemeContainer">
	<div id="current-theme" class="spTheme">
<?php
        if (file_exists(SPTHEMEBASEDIR.$curTheme['theme'].'/styles/'.$curTheme['style'])) {
?>
			<h3 class="theme-name"><?php echo $themes[$curTheme['theme']]['Name']; ?></h3>

    		<img src="<?php echo SPTHEMEBASEURL.$curTheme['theme'].'/'.$themes[$curTheme['theme']]['Screenshot']; ?>" alt="" />
    		<h4>
    			<?php echo $themes[$curTheme['theme']]['Name'].' '.$themes[$curTheme['theme']]['Version'].'<br />'.SP()->primitives->admin_text('by').' <a href="'.$themes[$curTheme['theme']]['AuthorURI'].'" title="'.SP()->primitives->admin_text('Visit author homepage').'">'.$themes[$curTheme['theme']]['Author'].'</a>'; ?>
    		</h4>
<?php
            if (!empty($curTheme['parent'])) {
                if (file_exists(SPTHEMEBASEDIR.$curTheme['parent'])) {
                    echo '<p class="theme-parent">';
                    echo SP()->primitives->admin_text('This theme is a child theme of ').'<b>'.$curTheme['parent'].'</b>';
                    echo '</p>';
                } else {
                    echo '<p class="theme-parent">';
                    echo '<b>'.SP()->primitives->admin_text('The specified parent theme')." '".$curTheme['parent']."' ".SP()->primitives->admin_text('does not exist').'</b> ';
                    echo '</p>';
                }
            }
?>
    		<p class="description" style="padding: 0;">
    			<?php echo $themes[$curTheme['theme']]['Description']; ?>
    		</p>
<?php
            $overlays = SP()->theme->get_overlays(SPTHEMEBASEDIR.$curTheme['theme'].'/styles/overlays');

            # pull in parent overlays if child theme
            if (!empty($curTheme['parent'])) {
                $parent_overlays = SP()->theme->get_overlays(SPTHEMEBASEDIR.$curTheme['parent'].'/styles/overlays');
                $overlays = array_merge($overlays, $parent_overlays);
                $overlays = array_unique($overlays);
            }

            if (!empty($overlays)) {
?>
                <script>
					spj.loadAjaxForm('sftheme-<?php echo esc_js($curTheme['theme']); ?>', 'sfreloadtlist');
                </script>
                <br />
<?php
                $ajaxURL = wp_nonce_url(SPAJAXURL.'themes-loader&amp;saveform=theme', 'themes-loader');
            	echo '<form action="'.$ajaxURL.'" method="post" id="sftheme-'.esc_attr($curTheme['theme']).'" name="sftheme-'.esc_attr($curTheme['theme']).'">';
                echo sp_create_nonce('forum-adminform_themes');
                echo '<input type="hidden" name="theme" value="'.esc_attr($curTheme['theme']).'" />';
                echo '<input type="hidden" name="style" value="'.esc_attr($themes[$curTheme['theme']]['Stylesheet']).'" />';
                echo '<input type="hidden" name="parent" value="'.esc_attr($curTheme['parent']).'" />';

            	echo '<input type="hidden" name="default-color" value="'.esc_attr($overlays[0]).'" />';

				# if only one overlay hide select controls
				$style = (count($overlays) > 1) ? 'style="display:block"' : 'style="display:none"';
				echo '<div '.$style.'>';
    			echo SP()->primitives->admin_text('Select Overlay').': ';
    			echo '<select name="color-'.esc_attr($curTheme['theme']).'">';
            	foreach ($overlays as $overlay) {
            		$overlay = trim($overlay);
    	    		$selected = ($curTheme['color'] == $overlay) ? ' selected="selected" ' : '';
    				echo '<option'.$selected.' value="'.esc_attr($overlay).'">'.esc_html($overlay).'</option>';
        		}
    			echo '</select> ';

                echo ' <input type="submit" class="button-secondary action" id="update" name="update" value="'.SP()->primitives->admin_text('Update Overlay').'" />';
                echo '</form>';
				echo '</div>';

				if(current_theme_supports('sp-theme-customiser')) {
					echo '<b>'.SP()->primitives->admin_text('Use the Customiser option in the Simple:Press Themes menu to customise your colours').'</b>';
				}
    		}

         	# any upgrade for this theme?
		
			$check_for_addon_update = SP()->options->get( 'spl_theme_versioninfo_'.str_replace(' ', '-', esc_attr($curTheme['theme'])));
			
			$check_for_addon_update = json_decode($check_for_addon_update);
	
			$check_addons_status = SP()->options->get( 'spl_theme_info_'.str_replace(' ', '-', esc_attr($curTheme['theme'])));
			
			$check_addons_status = json_decode($check_addons_status);
		
			if (is_main_site() && isset($check_for_addon_update->new_version) && (version_compare($check_for_addon_update->new_version, $themes[$curTheme['theme']]['Version'], '>') == 1)) {
			
				echo '<br />';
				echo '<div class="plugin-update-tr"><div class="update-message notice inline notice-warning notice-alt" style="padding: 10px 0px;">';
				
				if($check_addons_status->license == 'valid'){
					
					printf(
						__( '<p>There is a new version of %1$s available. <a target="_blank" class="thickbox" href="%2$s">View version %3$s details</a> or <a href="%4$s">update now</a>.</p>', 'SP' ),
						esc_html( esc_attr($curTheme['theme']) ),
						esc_url( SP_Addon_STORE_URL ),
						esc_html( $check_for_addon_update->new_version ),
						esc_url( self_admin_url('update-core.php') )
					);
					
				} else{
					
					printf(
						__( '<p>There is a new version of %1$s available. <a target="_blank" class="thickbox" href="%2$s">View version %3$s details</a> Automatic update is unavailable for this theme.</p>', 'SP' ),
						esc_html( esc_attr($curTheme['theme']) ),
						esc_url( SP_Addon_STORE_URL ),
						esc_html( $check_for_addon_update->new_version )
					);
				}
				echo '</div></div>';
			}
    		
    		
    		
        } else {
     		echo '<h4>'.SP()->primitives->admin_text('The current theme stylesheet').':<br /><br />'.SPTHEMEBASEDIR.$curTheme['theme'].'/styles/'.$curTheme['style'].'<br /><br />'.SP()->primitives->admin_text('cannot be found. Please correct or select a new theme for proper operation.').'</h4>';
        }
?>
	</div></div></div>

	<br class="clear" />

	<h3><?php echo SP()->primitives->admin_text('Available Themes'); ?></h3>
<?php
	$numThemes = count($themes);
 	if ($numThemes > 1) {
?>
		<div class="theme-browser rendered">
			<div class="spThemeContainer">
<?php
	    	foreach ((array) $themes as $theme_file => $theme_data) {
   				# skip cur theme
	    		if ($theme_file == $curTheme['theme']) continue;

	    		$theme_desc = $theme_data['Description'];
	    		$theme_name = $theme_data['Name'];
	    		$theme_version = $theme_data['Version'];
	    		$theme_author = $theme_data['Author'];
	    		$theme_uri = $theme_data['AuthorURI'];
	    		$theme_style = $theme_data['Stylesheet'];
	            $theme_image = SPTHEMEBASEURL.$theme_file.'/'.$theme_data['Screenshot'];
                $theme_overlays = SP()->theme->get_overlays(SPTHEMEBASEDIR.$theme_file.'/styles/overlays');

                # pull in parent overlays if child theme
                if (!empty($theme_data['Parent'])) {
                    $parent_overlays = SP()->theme->get_overlays(SPTHEMEBASEDIR.$theme_data['Parent'].'/styles/overlays');
                    $theme_overlays = array_merge($theme_overlays, $parent_overlays);
                }
?>
				<div class="spTheme">
					<h3 class="theme-name"><?php echo $theme_name; ?></h3>
					<img alt="" src="<?php echo $theme_image; ?>" />
					<h4>
						<?php echo $theme_name.' '.$theme_version.'<br />'.SP()->primitives->admin_text('by').' <a href="'.$theme_uri.'" title="'.SP()->primitives->admin_text('Visit author homepage').'">'.$theme_author.'</a>'; ?>
					</h4>
<?php
                    if (!empty($theme_data['Parent'])) {
                        if (file_exists(SPTHEMEBASEDIR.$theme_data['Parent'])) {
                            echo '<p class="theme-parent">';
                            echo SP()->primitives->admin_text('This theme is a child theme of ').'<b>'.$theme_data['Parent'].'</b>';
                            echo '</p>';
                        } else {
                            echo '<p class="theme-parent">';
                            echo '<b>'.SP()->primitives->admin_text('The specified parent theme')." '".$theme_data['Parent']."' ".SP()->primitives->admin_text('does not exist').'</b> ';
                            echo '</p>';
                        }
                    }
?>
					<p class="description" style="padding: 0;">
						<?php echo $theme_desc; ?>
					</p>
					<br />
					<div class="action-links">
	                    <script>
	                    	spj.loadAjaxForm('sftheme-<?php echo esc_js($theme_file); ?>', 'sfreloadtlist');
	                    </script>
	                    <?php $ajaxURL = wp_nonce_url(SPAJAXURL.'themes-loader&amp;saveform=theme', 'themes-loader'); ?>
                        <?php $msg = SP()->primitives->admin_text('Are you sure you want to delete this Simple Press theme?'); ?>
	                	<form action="<?php echo $ajaxURL; ?>" method="post" id="sftheme-<?php echo esc_attr($theme_file); ?>" name="sftheme-<?php echo esc_attr($theme_file); ?>" >
	                    <?php echo sp_create_nonce('forum-adminform_themes'); ?>
	                    <input type="hidden" name="theme" value="<?php echo esc_attr($theme_file); ?>" />
	                    <input type="hidden" name="style" value="<?php echo esc_attr($theme_style); ?>" />
	                    <input type="hidden" name="parent" value="<?php echo esc_attr($theme_data['Parent']); ?>" />
<?php
                        $defOverlay = (!empty($theme_overlays)) ? esc_attr($theme_overlays[0]) : 0;
			        	echo "<input type='hidden' name='default-color' value='$defOverlay' />";
						if ($theme_overlays) {
							# only show if more than one overlay
							if(count($theme_overlays) > 1) {
								echo SP()->primitives->admin_text('Select Overlay').': ';
								echo ' <select name="color-'.esc_attr($theme_file).'" style="margin-bottom:5px;">';
								foreach ($theme_overlays as $theme_overlay) {
									$theme_overlay = trim($theme_overlay);
									$selected = ($theme_overlays[0] == $theme_overlay) ? ' selected="selected" ' : '';
									echo '<option'.$selected.' value="'.esc_attr($theme_overlay).'">'.esc_html($theme_overlay).'</option>';
								}
								echo '</select> ';
								echo '<div class="clearboth"></div>';
							}
						}
?>
	                    <input type="submit" class="button-secondary action" id="activate-<?php echo esc_attr($theme_file); ?>" name="activate" value="<?php echo SP()->primitives->admin_etext('Activate Theme'); ?>" />
	                    <?php if (!is_multisite() || is_super_admin()) { ?><input type="submit" class="button-secondary action spThemeDeleteConfirm" id="delete-<?php echo esc_attr($theme_file); ?>" name="delete" value="<?php echo SP()->primitives->admin_etext('Delete Theme'); ?>" data-msg="<?php echo $msg; ?>" /><?php }?>
	                    </form>
					</div>
<?php
		         	# any upgrade for this theme?
					
					$sp_theme_name = sanitize_title_with_dashes($theme_data['Name']);
					
					$check_for_addon_update = SP()->options->get( 'spl_theme_versioninfo_'.$sp_theme_name);
					$check_for_addon_update = json_decode($check_for_addon_update);
			
					$check_addons_status = SP()->options->get( 'spl_theme_info_'.$sp_theme_name);
					$check_addons_status = json_decode($check_addons_status);
				
					if (is_main_site() && isset($check_for_addon_update->new_version) && (version_compare($check_for_addon_update->new_version, $theme_data['Version'], '>') == 1)) {
					
						echo '<br />';
						echo '<div class="plugin-update-tr"><div class="update-message notice inline notice-warning notice-alt" style="padding: 10px 0px;">';
						
						if($check_addons_status->license == 'valid'){
							
        					printf(
								__( '<p>There is a new version of %1$s available. <a target="_blank" class="thickbox" href="%2$s">View version %3$s details</a> or <a href="%4$s">update now</a>.</p>', 'SP' ),
								esc_html( $theme_data['Name'] ),
								esc_url( SP_Addon_STORE_URL ),
								esc_html( $check_for_addon_update->new_version ),
								esc_url( self_admin_url('update-core.php') )
							);
							
        				} else{
        					
        					printf(
								__( '<p>There is a new version of %1$s available. <a target="_blank" class="thickbox" href="%2$s">View version %3$s details</a> Automatic update is unavailable for this theme.</p>', 'SP' ),
								esc_html( $theme_data['Name'] ),
								esc_url( SP_Addon_STORE_URL ),
								esc_html( $check_for_addon_update->new_version )
							);
        				}
						echo '</div></div>';
					}
								

				echo '</div>';
       		}
			echo "</div>";
    	echo '</div>';

 	} else {
 		echo SP()->primitives->admin_text('No other available themes found');
	}
	do_action('sph_themes_list_panel');

	spa_paint_close_fieldset();
	spa_paint_close_panel();
	spa_paint_close_container();
	spa_paint_close_tab();
}
