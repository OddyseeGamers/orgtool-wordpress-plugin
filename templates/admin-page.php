<div class="wrap">

<form method="post">
    <?php settings_fields( 'orgtool-plugin-settings-group' ); ?>
    <?php do_settings_sections( 'orgtool-plugin-settings-group' ); ?>

    <h2>Orgtool Settings</h2>
    <table class="form-table">
        <tr valign="top">
            <th scope="row">Orgtool URL slug</th>
            <td><input type="text" name="orgtool_slug" value="<?php echo esc_attr( get_option('orgtool_slug') ); ?>" />
            current: <?php $link = get_permalink( get_page_by_path(get_option('orgtool_slug'))); echo "<a href='" . $link . "'>" . $link . "</a>"; ?> </td>
        </tr>
<!--
        <tr valign="top">
            <th scope="row">RSI Organization</th>
            <td><input type="text" name="rsi_org" value="<?php echo esc_attr( get_option('rsi_org') ); ?>" /></td>
        </tr>
-->

        <tr valign="top">
            <th scope="row">Use Orgtool Avatar in Wordpress</th>
            <td>
                <input type="checkbox" value="1" name="overwrite_avatar" <?php checked( '1', get_option( 'overwrite_avatar' ) ); ?>  />
            </td>
        </tr>
        <tr valign="top">
            <th scope="row">Hide Wordpress adminbar in Orgtool</th>
            <td>
                <input type="checkbox" value="1" name="hide_adminbar" <?php checked( "1", get_option('hide_adminbar') ); ?> />
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">Show 'Orgtool Profile' link to Wordpress adminbar</th>
            <td>
                <input type="checkbox" value="1" name="add_profile_link" <?php checked( "1", get_option('add_profile_link') ); ?> />
            </td>
        </tr>

    </table>
    <input type="submit" name="save-submit" class="button button-primary" value="Save Changes" />
    
    <br><br>
    <hr>
    <br>
    <h2>Sync</h2>
    <table class="form-table" style="width:50%">
        <tr valign="top">
            <th scope="row">Sync Ships from RSI</th>
            <td></td>
            <td><input type="submit" name="fetch-ships-submit" class="button button-primary" value="Sync" /></td>
            <td></td>
        </tr>
    </table>

	
    <!--
    <br>
    <input type="submit" name="fetch-members-submit" class="button button-primary" value="Sync Members from RSI" />
 -->
	
</form>


</div>
