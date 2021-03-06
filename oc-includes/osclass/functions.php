<?php
/*
 *      Osclass – software for creating and publishing online classified
 *                           advertising platforms
 *
 *                        Copyright (C) 2012 OSCLASS
 *
 *       This program is free software: you can redistribute it and/or
 *     modify it under the terms of the GNU Affero General Public License
 *     as published by the Free Software Foundation, either version 3 of
 *            the License, or (at your option) any later version.
 *
 *     This program is distributed in the hope that it will be useful, but
 *         WITHOUT ANY WARRANTY; without even the implied warranty of
 *        MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *             GNU Affero General Public License for more details.
 *
 *      You should have received a copy of the GNU Affero General Public
 * License along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

function osc_meta_publish($catId = null) {
    echo '<div class="row">';
        FieldForm::meta_fields_input($catId);
    echo '</div>';
}

function osc_meta_edit($catId = null, $item_id = null) {
    echo '<div class="row">';
        FieldForm::meta_fields_input($catId, $item_id);
    echo '</div>';
}

osc_add_hook('item_form', 'osc_meta_publish');
osc_add_hook('item_edit', 'osc_meta_edit');

function search_title() {
    $region   = osc_search_region();
    $city     = osc_search_city();
    $category = osc_search_category_id();
    $result   = '';

    $b_show_all = ($region == '' && $city == '' && $category == '');
    $b_category = ($category != '');
    $b_city     = ($city != '');
    $b_region   = ($region != '');

    if( $b_show_all ) {
        return __('Search results');
    }

    if( osc_get_preference('seo_title_keyword') != '' ) {
        $result .= osc_get_preference('seo_title_keyword') . ' ';
    }

    if($b_category && is_array($category) && count($category) > 0) {
        $cat = Category::newInstance()->findByPrimaryKey($category[0]);
        if( $cat ) {
            $result .= strtolower($cat['s_name']) . ' ';
        }
    }

    if($b_city && $b_region) {
        $result .= $city;
    } else if($b_city) {
        $result .= $city;
    } else if($b_region) {
        $result .= $region;
    }

    return ucfirst($result);
}

function meta_title() {
    $location = Rewrite::newInstance()->get_location();
    $section  = Rewrite::newInstance()->get_section();

    switch ($location) {
        case ('item'):
            switch ($section) {
                case 'item_add':    $text = __('Publish a listing'); break;
                case 'item_edit':   $text = __('Edit your listing'); break;
                case 'send_friend': $text = __('Send to a friend') . ' - ' . osc_item_title(); break;
                case 'contact':     $text = __('Contact seller') . ' - ' . osc_item_title(); break;
                default:            $text = osc_item_title() . ' ' . osc_item_city(); break;
            }
        break;
        case('page'):
            $text = osc_static_page_title();
        break;
        case('error'):
            $text = __('Error');
        break;
        case('search'):
            $region   = osc_search_region();
            $city     = osc_search_city();
            $pattern  = osc_search_pattern();
            $category = osc_search_category_id();
            $s_page   = '';
            $i_page   = Params::getParam('iPage');

            if($i_page != '' && $i_page > 1) {
                $s_page = ' - ' . __('page') . ' ' . $i_page;
            }

            $b_show_all = ($region == '' && $city == '' & $pattern == '' && $category == '');
            $b_category = ($category != '');
            $b_pattern  = ($pattern != '');
            $b_city     = ($city != '');
            $b_region   = ($region != '');

            if($b_show_all) {
                $text = __('Show all listings') . ' - ' . $s_page . osc_page_title();
            }

            $result = '';
            if($b_pattern) {
                $result .= $pattern . ' &raquo; ';
            }

            if($b_category && is_array($category) && count($category) > 0) {
                $cat = Category::newInstance()->findByPrimaryKey($category[0]);
                if( $cat ) {
                    $result .= strtolower($cat['s_name']) . ' ';
                }
            }

            if($b_city && $b_region) {
                $result .= $city . ' &raquo; ';
            } else if($b_city) {
                $result .= $city . ' &raquo; ';
            } else if($b_region) {
                $result .= $region . ' &raquo; ';
            }

            $result = preg_replace('|\s?&raquo;\s$|', '', $result);

            if($result == '') {
                $result = __('Search results');
            }

            $text = '';
            if( osc_get_preference('seo_title_keyword') != '' ) {
                $text .= osc_get_preference('seo_title_keyword') . ' ';
            }
            $text .= $result . $s_page;
        break;
        case('login'):
            switch ($section) {
                case('recover'): $text = __('Recover your password');
                default:         $text = __('Login');
            }
        break;
        case('register'):
            $text = __('Create a new account');
        break;
        case('user'):
            switch ($section) {
                case('dashboard'):       $text = __('Dashboard'); break;
                case('items'):           $text = __('Manage my listings'); break;
                case('alerts'):          $text = __('Manage my alerts'); break;
                case('profile'):         $text = __('Update my profile'); break;
                case('pub_profile'):     $text = __('Public profile') . ' - ' . osc_user_name(); break;
                case('change_email'):    $text = __('Change my email'); break;
                case('change_username'): $text = __('Change my username'); break;
                case('change_password'): $text = __('Change my password'); break;
                case('forgot'):          $text = __('Recover my password'); break;
            }
        break;
        case('contact'):
            $text = __('Contact','modern');
        break;
        default:
            $text = osc_page_title();
        break;
    }

    if( !osc_is_home_page() ) {
        $text .= ' - ' . osc_page_title();
    }

    return (osc_apply_filter('meta_title_filter', ucfirst($text)));
}

function meta_description( ) {
    $text = '';
    // home page
    if( osc_is_home_page() ) {
        $text = osc_page_description();
    }
    // static page
    if( osc_is_static_page() ) {
        $text = osc_highlight(osc_static_page_text(), 140, '', '');
    }
    // search
    if( osc_is_search_page() ) {
        if( osc_has_items() ) {
            $text = osc_item_category() . ' ' . osc_item_city() . ', ' . osc_highlight(osc_item_description(), 120) . ', ' . osc_item_category() . ' ' . osc_item_city();
        }
        osc_reset_items();
    }
    // listing
    if( osc_is_ad_page() ) {
        $text = osc_item_category() . ' ' . osc_item_city() . ', ' . osc_highlight(osc_item_description(), 120) . ', ' . osc_item_category() . ' ' . osc_item_city();
    }

    return (osc_apply_filter('meta_description_filter', $text));
}

function meta_keywords( ) {
    $text = '';
    // search
    if( osc_is_search_page() ) {
        if( osc_has_items() ) {
            $keywords = array();
            $keywords[] = osc_item_category();
            if( osc_item_city() != '' ) {
                $keywords[] = osc_item_city();
                $keywords[] = sprintf('%s %s', osc_item_category(), osc_item_city());
            }
            if( osc_item_region() != '' ) {
                $keywords[] = osc_item_region();
                $keywords[] = sprintf('%s %s', osc_item_category(), osc_item_region());
            }
            if( (osc_item_city() != '') && (osc_item_region() != '') ) {
                $keywords[] = sprintf('%s %s %s', osc_item_category(), osc_item_region(), osc_item_city());
                $keywords[] = sprintf('%s %s', osc_item_region(), osc_item_city());
            }
            $text = implode(', ', $keywords);
        }
        osc_reset_items();
    }
    // listing
    if( osc_is_ad_page() ) {
        $keywords = array();
        $keywords[] = osc_item_category();
        if( osc_item_city() != '' ) {
            $keywords[] = osc_item_city();
            $keywords[] = sprintf('%s %s', osc_item_category(), osc_item_city());
        }
        if( osc_item_region() != '' ) {
            $keywords[] = osc_item_region();
            $keywords[] = sprintf('%s %s', osc_item_category(), osc_item_region());
        }
        if( (osc_item_city() != '') && (osc_item_region() != '') ) {
            $keywords[] = sprintf('%s %s %s', osc_item_category(), osc_item_region(), osc_item_city());
            $keywords[] = sprintf('%s %s', osc_item_region(), osc_item_city());
        }
        $text = implode(', ', $keywords);
    }

    return (osc_apply_filter('meta_keywords_filter', $text));
}

function osc_search_footer_links() {
    $categoryID = '';
    if( osc_search_category_id() ) {
        $categoryID = osc_search_category_id();

        if( Category::newInstance()->isRoot( current($categoryID) ) ) {
            $cat = Category::newInstance()->findSubcategories(current($categoryID));
            if( count($cat) > 0 ) {
                $categoryID = array();
                foreach($cat as $c) {
                    $categoryID[] = $c['pk_i_id'];
                }
            }
        }
    }

    if( osc_search_city() != '' ) {
        return array();
    }

    $regionID = '';
    if( osc_search_region() != '' ) {
        $aRegion  = Region::newInstance()->findByName(osc_search_region());
        $regionID = $aRegion['pk_i_id'];
    }

    $conn = DBConnectionClass::newInstance();
    $data = $conn->getOsclassDb();
    $comm = new DBCommandClass($data);

    $comm->select('i.fk_i_category_id');
    $comm->select('l.*');
    $comm->select('COUNT(*) AS total');
    $comm->from(DB_TABLE_PREFIX . 't_item as i');
    $comm->from(DB_TABLE_PREFIX . 't_item_location as l');
    if( $categoryID != '' ) {
        $comm->whereIn('i.fk_i_category_id', $categoryID);
    }
    $comm->where('i.pk_i_id = l.fk_i_item_id');
    $comm->where('i.b_enabled = 1');
    $comm->where('l.fk_i_region_id IS NOT NULL');
    $comm->where('l.fk_i_city_id IS NOT NULL');
    if( $regionID != '' ) {
        $comm->where('l.fk_i_region_id', $regionID);
        $comm->groupBy('l.fk_i_city_id');
    } else {
        $comm->groupBy('l.fk_i_region_id');
    }
    $rs = $comm->get();

    if( !$rs ) {
        return array();
    }

    return $rs->result();
}

function osc_footer_link_url() {
    $f   = View::newInstance()->_get('footer_link');
    $url = osc_base_url();

    if( osc_get_preference('seo_url_search_prefix') != '' ) {
        $url .= osc_get_preference('seo_url_search_prefix') . '/';
    }

    $bCategory = false;
    if( osc_search_category_id() ) {
        $bCategory = true;
        $cat = osc_get_category('id', $f['fk_i_category_id']);
        $url .= $cat['s_slug'] . '_';
    }

    if( osc_search_region() == '' ) {
        $url .= osc_sanitizeString($f['s_region']) . '-r' . $f['fk_i_region_id'];
    } else {
        $url .= osc_sanitizeString($f['s_city']) . '-c' . $f['fk_i_city_id'];
    }

    return $url;
}

function osc_footer_link_title() {
    $f = View::newInstance()->_get('footer_link');
    $text = '';

    if( osc_get_preference('seo_title_keyword') != '' ) {
        $text .= osc_get_preference('seo_title_keyword') . ' ';
    }

    if( osc_search_category_id() ) {
        $cat = osc_get_category('id', $f['fk_i_category_id']);
        $text .= strtolower($cat['s_name']) . ' ';
    }

    if( osc_search_region() == '' ) {
        $text .= $f['s_region'];
    } else {
        $text .= $f['s_city'];
    }

    $text = trim($text);
    return ucfirst($text);
}

/**
 * Instantiate the admin toolbar object.
 *
 * @since 3.0
 * @access private
 * @return bool
 */
function _osc_admin_toolbar_init()
{
    $adminToolbar = AdminToolbar::newInstance();

    $adminToolbar->init();
    $adminToolbar->add_menus();
    return true;
}
// and we hook our function via
osc_add_hook( 'init_admin', '_osc_admin_toolbar_init');

/**
 * Draws admin toolbar
 */
function osc_draw_admin_toolbar()
{
    $adminToolbar = AdminToolbar::newInstance();

    // run hook for adding
    osc_run_hook('add_admin_toolbar_menus');
    $adminToolbar->render();
}

/**
 * Add webtitle with link to frontend
 */
function osc_admin_toolbar_menu()
{
    AdminToolbar::newInstance()->add_menu( array(
                'id'        => 'home',
                'title'     => '<span class="">'.  osc_page_title() .'</span>',
                'href'      => osc_base_url(),
                'meta'      => array('class' => 'user-profile'),
                'target'    => '_blank'
            ) );
}

/**
 * Add logout link
 */
function osc_admin_toolbar_logout()
{
    AdminToolbar::newInstance()->add_menu( array(
                'id'        => 'logout',
                'title'     => __('Logout'),
                'href'      => osc_admin_base_url(true) . '?action=logout',
                'meta'      => array('class' => 'btn btn-dim ico ico-32 ico-power float-right')
            ) );
}

function osc_admin_toolbar_comments()
{
    $total = ItemComment::newInstance()->countAll( '( c.b_active = 0 OR c.b_enabled = 0 OR c.b_spam = 1 )' );
    if( $total > 0 ) {
        $title = '<i class="circle circle-green">'.$total.'</i>'.__('New comments');

        AdminToolbar::newInstance()->add_menu(
                array('id'    => 'comments',
                      'title' => $title,
                      'href'  => osc_admin_base_url(true) . "?page=comments",
                      'meta'  => array('class' => 'action-btn action-btn-black')
                ) );
    }
}

function osc_admin_toolbar_spam()
{
    $total = Item::newInstance()->countByMarkas( 'spam' );
    if( $total > 0 ) {
        $title = '<i class="circle circle-red">'.$total.'</i>'.__('Spam');

        AdminToolbar::newInstance()->add_menu(
                array('id'    => 'spam',
                      'title' => $title,
                      'href'  => osc_admin_base_url(true) . "?page=items&action=items_reported&sort=spam",
                      'meta'  => array('class' => 'action-btn action-btn-black')
                ) );
    }
}

function osc_check_plugins_update( $force = false )
{
    $total = 0;
    $array = array();
    $array_downloaded = array();
    // check if exist a new version each day
    if( (time() - osc_plugins_last_version_check()) > (24 * 3600) || $force ) {
        $plugins    = Plugins::listAll();
        foreach($plugins as $plugin) {
            $info = osc_plugin_get_info($plugin);
            if(osc_check_plugin_update(@$info['plugin_update_uri'], @$info['version'])) {
                $array[] = @$info['plugin_update_uri'];
                $total++;
            }else{
            }
            $array_downloaded[] = @$info['plugin_update_uri'];
        }

        osc_set_preference( 'plugins_to_update' , json_encode($array) );
        osc_set_preference( 'plugins_downloaded', json_encode($array_downloaded) );
        osc_set_preference( 'plugins_update_count', $total );
        osc_set_preference( 'plugins_last_version_check', time() );
        osc_reset_preferences();
    } else {
        $total = getPreference('plugins_update_count');
    }

    return $total;
}

function osc_admin_toolbar_update_plugins($force = false)
{
    if( !osc_is_moderator() ) {
        $total = osc_check_plugins_update( $force );

        if($force) {
            AdminToolbar::newInstance()->remove_menu('update_plugin');
        }
        if($total > 0) {
            $title = '<i class="circle circle-gray">'.$total.'</i>'.__('Plugin updates');
            AdminToolbar::newInstance()->add_menu(
                    array('id'    => 'update_plugin',
                          'title' => $title,
                          'href'  => osc_admin_base_url(true) . "?page=plugins#update-plugins",
                          'meta'  => array('class' => 'action-btn action-btn-black')
                    ) );
        }
    }
}

function osc_check_themes_update( $force = false )
{
    $total = 0;
    $array = array();
    $array_downloaded = array();
    // check if exist a new version each day
    if( (time() - osc_themes_last_version_check()) > (24 * 3600) || $force ) {
        $themes = WebThemes::newInstance()->getListThemes();
        foreach($themes as $theme) {
            $info = WebThemes::newInstance()->loadThemeInfo($theme);
            if(osc_check_theme_update(@$info['theme_update_uri'], @$info['version'])) {
                $array[] = $theme;
                $total++;
            }
            $array_downloaded[] = @$info['theme_update_uri'];
        }
        osc_set_preference( 'themes_to_update', json_encode($array) );
        osc_set_preference( 'themes_downloaded', json_encode($array_downloaded) );
        osc_set_preference( 'themes_update_count', $total );
        osc_set_preference( 'themes_last_version_check', time() );
        osc_reset_preferences();
    } else {
        $total = getPreference('themes_update_count');
    }

    return $total;
}

function osc_admin_toolbar_update_themes($force = false)
{
    if( !osc_is_moderator() ) {
        $total = osc_check_themes_update( $force );

        if($force) {
            AdminToolbar::newInstance()->remove_menu('update_theme');
        }
        if($total > 0) {
            $title = '<i class="circle circle-gray">'.$total.'</i>'.__('Theme updates');
            AdminToolbar::newInstance()->add_menu(
                    array('id'    => 'update_theme',
                          'title' => $title,
                          'href'  => osc_admin_base_url(true) . "?page=appearance",
                          'meta'  => array('class' => 'action-btn action-btn-black')
                    ) );
        }
    }
}

// languages todo
function osc_check_languages_update( $force = false )
{
    $total = 0;
    $array = array();
    $array_downloaded = array();
    // check if exist a new version each day
    if( (time() - osc_languages_last_version_check()) > (24 * 3600) || $force ) {
        $languages  = OSCLocale::newInstance()->listAll();
        foreach($languages as $lang) {
            if(osc_check_language_update($lang['pk_c_code'], $lang['s_version'] )) {
                $array[] = $lang['pk_c_code'];
                $total++;
            }
            $array_downloaded[] = $lang['pk_c_code'];
        }
        osc_set_preference( 'languages_to_update' , json_encode($array) );
        osc_set_preference( 'languages_downloaded', json_encode($array_downloaded) );
        osc_set_preference( 'languages_update_count', $total );
        osc_set_preference( 'languages_last_version_check', time() );
        osc_reset_preferences();
    } else {
        $total = getPreference('languages_update_count');
    }

    return $total;
}

function osc_admin_toolbar_update_languages($force = false)
{
    if( !osc_is_moderator() ) {
        $total = osc_check_languages_update( $force );

        if($force) {
            AdminToolbar::newInstance()->remove_menu('update_language');
        }
        if($total > 0) {
            $title = '<i class="circle circle-gray">'.$total.'</i>'.__('Language updates');
            AdminToolbar::newInstance()->add_menu(
                    array('id'    => 'update_language',
                          'title' => $title,
                          'href'  => osc_admin_base_url(true) . "?page=languages",
                          'meta'  => array('class' => 'action-btn action-btn-black')
                    ) );
        }
    }
}
?>