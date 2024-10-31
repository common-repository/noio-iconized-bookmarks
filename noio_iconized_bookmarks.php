<?php
/*
    Plugin Name: Noio Iconized Bookmarks
    Plugin URI: http://www.noio.nl/2009/05/version-one-of-nib/
    Description: Plugin that creates a widget that displays favicon next to links
    Author: Thomas van den Berg
    Version: 1.0.1
    Author URI: http://www.noio.nl/
*/

/* 
    I (Thomas van den Berg) do not take any responsibility for damage that might
    occur to your WordPress database or website from using this plugin.
    */

/*  Copyright 2008  Thomas van den Berg  (email : contact@noio.nl)

            This program is free software: you can redistribute it and/or modify
        it under the terms of the GNU General Public License as published by
        the Free Software Foundation, either version 3 of the License, or
        (at your option) any later version.

        This program is distributed in the hope that it will be useful,
        but WITHOUT ANY WARRANTY; without even the implied warranty of
        MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
        GNU General Public License for more details.

        You should have received a copy of the GNU General Public License
        along with this program.  If not, see <http://www.gnu.org/licenses/>.
    
    */

/********* 
  WIDGET 
*********/

class IconizedBookmarksWidget extends WP_Widget{
    function IconizedBookmarksWidget(){
        parent::WP_Widget(false, $name = 'Iconized Bookmarks Widget');
    }
    
    function widget($args, $instance){
        extract($args);
        $r = $instance['widget_args'];
        $r['title_before'] = $before_title; 
        $r['title_after'] = $after_title;
        $r['category_before'] = $before_widget;
        $r['category_after'] = $after_widget;
        list_iconized_bookmarks($r);
    }
    
    function update($new_instance,$old_instance){
        $instance = $old_instance;
        $instance['widget_args'] = $new_instance['widget_args']; 
        return $instance;
    }
    
    function form($instance){
        $widget_args = $instance['widget_args'];
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('widget_args');?>"><code>list_bookmarks()</code> options [<a href='http://codex.wordpress.org/Function_Reference/wp_list_bookmarks'>?</a>]: </label>
            <input type="text" class="widefat" id="<?php echo $this->get_field_id('widget_args');?>" 
                name="<?php echo $this->get_field_name('widget_args');?>" value="<?php echo $widget_args;?>" />
        </p>
        <?php
    }
}
add_action('widgets_init', create_function('', 'return register_widget("IconizedBookmarksWidget");'));

/**************************
  LIST BOOKMARKS FUNCTION 
**************************/
function list_iconized_bookmarks($args = '') {
    $defaults = array(
        'orderby' => 'name', 'order' => 'ASC',
        'limit' => -1, 'category' => '', 'exclude_category' => '',
        'category_name' => '', 'hide_invisible' => 1,
        'show_updated' => 0, 'echo' => 1,
        'categorize' => 1, 'title_li' => __('Bookmarks'),
        'title_before' => '<h2>', 'title_after' => '</h2>',
        'category_orderby' => 'name', 'category_order' => 'ASC',
        'class' => 'linkcat', 'category_before' => '<li id="%id" class="%class">',
        'category_after' => '</li>'
        );

    $r = wp_parse_args( $args, $defaults );
    extract( $r, EXTR_SKIP );
    $output = '';


    if ( $categorize ) {
        //Split the bookmarks into ul's for each category
        $cats = get_terms('link_category', array('name__like' => $category_name, 'include' => $category, 'exclude' => $exclude_category, 'orderby' => $category_orderby, 'order' => $category_order, 'hierarchical' => 0));

        foreach ( (array) $cats as $cat ) {
            $params = array_merge($r, array('category'=>$cat->term_id));
            $bookmarks = get_bookmarks($params);
            if ( empty($bookmarks) )
                continue;
            $output .= str_replace(array('%id', '%class'), array("linkcat-$cat->term_id", $class), $category_before);
            $catname = apply_filters( "link_category", $cat->name );
            $output .= "$title_before$catname$title_after\n\t<ul class='iconized_bookmarks'>\n";
            $output .= _walk_iconized_bookmarks($bookmarks, $r);
            $output .= "\n\t</ul>\n$category_after\n";
        }
    } else {

        //output one single list using title_li for the title
        $bookmarks = get_bookmarks($r);

        if ( !empty($bookmarks) ) {
            if ( !empty( $title_li ) ){
                $output .= str_replace(array('%id', '%class'), array("linkcat-$category", $class), $category_before);
                $output .= "$title_before$title_li$title_after\n\t<ul class='iconized_bookmarks'>\n";
                $output .= _walk_iconized_bookmarks($bookmarks, $r);
                $output .= "\n\t</ul>\n$category_after\n";
            } else {
                $output .= _walk_iconized_bookmarks($bookmarks, $r);
            }
        }
    }

    $output = apply_filters( 'wp_list_bookmarks', $output );

    if ( !$echo )
        return $output;
    echo $output;
}

function _walk_iconized_bookmarks($bookmarks, $r){
    foreach ($bookmarks as $bookmark){
        $output .= '<li>';
        $output .= '<img class="favicon" alt="'. $bookmark->link_name .' favicon" src="' . $bookmark->link_image .'"></img>';
        $output .= '<a href="' . $bookmark->link_url.'">';
        $output .= $bookmark->link_name;
        $output .= '</a></li>';
    }
    return $output;
}

/***************************
  UPDATING ICONS ALGORITHM
***************************/
function noio_iconize_bookmarks_add_options_page() {
    add_options_page("Iconized Bookmarks Settings", "N.I.B.", "manage_options", __FILE__, "noio_iconize_bookmarks_options_page");
}

function noio_iconize_bookmarks_options_page() {

    global $wpdb;
    $dir = get_settings('siteurl');
    $emptyimage = $dir . "/wp-content/plugins/noio-iconized-bookmarks/empty.png";
    $notfoundimage = $dir . "/wp-content/plugins/noio-iconized-bookmarks/notfound.png";
    $defaultimage = $dir . "/wp-content/plugins/noio-iconized-bookmarks/default.gif";
    $selectbutton = $dir . "/wp-content/plugins/noio-iconized-bookmarks/select.png";
    $icontable = array();
        
    //Set & Update options
    $options = get_option('nib');
    if( !is_array($options)) { $options = array() ; }
    if( isset($_POST['nib_settings_defaulticon'])){
        $options['default_icon'] = $_POST['nib_settings_defaulticon'];
    }
    if( !isset($options['default_icon']) || $options['default_icon']  == ""){
        $options['default_icon'] = $defaultimage;
    }
    update_option('nib',$options);
    
    //Update icons if form was submitted
    if (isset($_POST['nib_update_icons'])){
        $sql = "SELECT link_name, link_url , link_image 
                        FROM $wpdb->links";
        $links = $wpdb->get_results( $wpdb->prepare( $sql ));
        
        $i = 0;
        while( isset($_POST['nib_use_icon_'.$i] )){
            $newicon = $_POST['nib_use_icon_'.$i];
            if($newicon == $emptyimage){
                $newicon = "";
            }
            $linkid = $_POST['nib_link_id_'.$i];
            
            $singlereplace = "UPDATE $wpdb->links
                                                SET link_image = %s
                                                WHERE link_id = %s";
            $replaced = $wpdb->query( $wpdb->prepare($singlereplace, $newicon, $linkid));
            $i++;
        }
        
        $message = "Icons successfully updated.";
        
    }

    //Get a list of links and their current images.
    $sql = "SELECT link_name, link_url , link_image, link_id
                    FROM $wpdb->links";
    $links = $wpdb->get_results( $wpdb->prepare( $sql ));
    
    foreach($links as $link){
        $iconinfo["name"] = $link->link_name;
        $iconinfo["curr"] = ($link->link_image != "") ? $link->link_image : $emptyimage;
        $iconinfo["url"] = $link->link_url;
        $iconinfo["id"] = $link->link_id;
        array_push($icontable, $iconinfo);
    }
    
    //FINDING ICONS
    if(isset($_POST['nib_find_icons'])){

        $message = "Icons found, click update when you have selected the desired favicons. <em>Back up your database before updating your icons!</em>";
        
        echo '<h4>Progress:</h4><p>';
        foreach($icontable as $i => $iconinfo){
            $found = noio_locate_icon($iconinfo["url"]);
            echo ($found ? "<span style='color:#43951A;'>".$iconinfo["url"]." found.</span><br/>" :
                    "<span style='color:#FA2323;'>".$iconinfo["url"]." not found.</span><br/>");
            $iconinfo["found"] = $found ? $found : $notfoundimage;
            $iconinfo["use"] = $found ? $found : $options["default_icon"];
            $icontable[$i] = $iconinfo;
        }
    
    //FIRST RUN, NOTHING SUBMITTED
    } else {
        if(!isset($_POST["nib_update_icons"])){
            $message = "Click <em>Find Icons</em> to find favicons for all your links. Then you can select the ones you want to use.";
        }
        
        foreach($icontable as $i => $iconinfo){
            $iconinfo["found"] = $notfoundimage;
            $iconinfo["use"] = $iconinfo["curr"];
            $icontable[$i] = $iconinfo;
        }
    }

    //The panel itself.
    ?>
    <div class="wrap">
    <h2>Noio Iconized Bookmarks</h2>
    
    <?php if(isset($message)):?>
    <div class="updated">
        <strong>
            <p><?php echo $message; ?></p>
        </strong>
    </div>
    <?php endif;?>
    
    <form method="post" action="options-general.php?page=noio-iconized-bookmarks/noio_iconized_bookmarks.php">
        
        <table class="favicon-table"><tbody>
            <tr><th>Link Name</th><th>Current Image</th><th>Found Icon</th>
                <th>Default</th><th>Custom</th><th>Your Choice</th></tr>
            <?php for($i = 0; $i < count($icontable); $i++){
                $link = $icontable[$i];?>
                <tr valign="middle">
                    <td> <strong><?php echo $link["name"];?></strong><br/><small><?php echo $link["url"];?></small></td>
                    <td>
                        <img class="icon" src="<?php echo $link["curr"];?>" alt=""></img>
                        <a href="javascript:void();" onclick="nibUseIcon(<?php echo $i;?>, '<?php echo $link["curr"];?>');">select</a>
                    </td>
                    <td>
                        <img class="icon" src="<?php echo $link["found"];?>" alt=""></img>
                        <?php if($link["found"] != $notfoundimage):?>
                            <a href="javascript:void();" onclick="nibUseIcon(<?php echo $i;?>, '<?php echo $link["found"];?>');">select</a>                        <?php endif;?>
                    </td>
                    <td>
                        <img class="icon" src="<?php echo $options['default_icon'];?>" alt=""></img>
                        <a href="javascript:void();" onclick="nibUseIcon(<?php echo $i;?>, '<?php echo $options["default_icon"];?>');">select</a>
                    </td>
                    <td>
                        <a href="javascript:void();" class="set-custom" onclick="getElementById('nib-custom-icon-<?php echo $i;?>').src = prompt('Enter Image Address', '')"/>set</a>
                        <img class="icon" id="nib-custom-icon-<?php echo $i;?>" src="<?php echo $emptyimage;?>"></img>
                        <a href="javascript:void();" onclick="nibUseIcon(<?php echo $i;?>, getElementById('nib-custom-icon-<?php echo $i;?>').src);">select</a>
                    </td>
                    <td>
                        <img class="final icon" id="nib-use-icon-<?php echo $i;?>" src="<?php echo $link['use'];?>" alt=""></img>
                        <input type="hidden" id="nib-use-icon-input-<?php echo $i;?>" name="nib_use_icon_<?php echo $i;?>" value="<?php echo $link['use'];?>"/>
                        <input type="hidden" name="nib_link_id_<?php echo $i;?>" value="<?php echo $link['id'];?>"/>
                    </td>
                </tr>
            <?php } ?>
        </table></tbody>
        
        <table class="form-table">
            <tbody>
                <tr>
                    <td width="30px"><img class="icon" src="<?php echo $emptyimage;?>" alt=""/></td>
                    <td class="setting-description">Image source is empty.</td>
                </tr>
                <tr>
                    <td width="30px"><img class="icon" src="<?php echo $notfoundimage;?>" alt=""/></td>
                    <td class="setting-description">An image was not found.</td>
                </tr>
                <tr>
                    <td width="30px"><img class="icon" src="<?php echo $selectbutton;?>" alt=""/></td>
                    <td class="setting-description">Click to use this icon.</td>
                <tr>
                    <td width="30px"><img id="nib-default-icon-preview" class="icon" src="<?php echo $options['default_icon'];?>"alt=""/></td>
                    <td width="200px">
                        <input name="nib_settings_defaulticon" type="text" id="nib_settings_defaulticon" value="<?php echo $options['default_icon']?>" />
                    </td>
                    <td class="setting-description">Set the default icon here.</td>
                </tr>
            </tbody>
        </table>
        <p class="submit">
            <input name="nib_find_icons" class="button-secondary" value="Find Icons" type="submit" />

            <input name="nib_update_icons" class="button-primary" value="Update Icons" type="submit" />
        </p>
    </form>
    
    <?php
}

/*****************
  LOCATING ICONS
*****************/
function noio_locate_icon($url) {
    // 
    // $context = stream_context_create(array(
    //     'http' => array(
    //         'timeout' => 3     // Timeout in seconds
    //     )
    // ));
    // 
    // create curl resource 
    $ch = curl_init(); 
    curl_setopt($ch, CURLOPT_URL, $url); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    $html = curl_exec($ch); 
    curl_close($ch);

    // echo substr(strip_tags($html),0,20);
    
    if( $html  ) { //= file_get_contents($url, 0, $context)

        if (preg_match('/<link[^>]+rel="(?:shortcut )?icon"[^>]+?href="([^"]+?)"/si', $html, $matches)) {

            $linkUrl = trim(html_entity_decode($matches[1]));
            if (substr($linkUrl, 0, 1) == '/') {
                $urlParts = parse_url($url);
                $faviconURL = $urlParts['scheme'].'://'.$urlParts['host'].$linkUrl;
            } else if (substr($linkUrl, 0, 7) == 'http://') {
                $faviconURL = $linkUrl;
            } else if (substr($url, -1, 1) == '/') {
                $faviconURL = $url.$linkUrl;
            } else {
                $faviconURL = $url.'/'.$linkUrl;
            }

        } else {

            $urlParts = parse_url($url);
            $faviconURL = $urlParts['scheme'].'://'.$urlParts['host'].'/favicon.ico';

        }
        if( $faviconURL_exists = noio_url_validate($faviconURL) )
            return $faviconURL;
    } 
    return false;
}

function noio_url_validate( $link ) {
        
    $url_parts = @parse_url( $link );

    if ( empty( $url_parts["host"] ) )
        return false;

    if ( !empty( $url_parts["path"] ) ) {
        $documentpath = $url_parts["path"];
    } else {
        $documentpath = "/";
    }

    if ( !empty( $url_parts["query"] ) )
        $documentpath .= "?" . $url_parts["query"];

    $host = $url_parts["host"];
    $port = $url_parts["port"];
    
    if ( empty($port) )
        $port = "80";

    $socket = @fsockopen( $host, $port, $errno, $errstr, 30 );
    
    if ( !$socket )
        return false;
        
    fwrite ($socket, "HEAD ".$documentpath." HTTP/1.0\r\nHost: $host\r\n\r\n");

    $http_response = fgets( $socket, 22 );

    $responses = "/(200 OK)|(30[0-9] Moved)/";
    if ( preg_match($responses, $http_response) ) {
        fclose($socket);
        return true;
    } else {
        return false;
    }
}

/*****************
  INITIALIZATION 
*****************/

function noio_iconized_bookmarks_head(){
    $dir = get_settings('siteurl') . '/wp-content/plugins/noio-iconized-bookmarks/';
    echo '<link rel="stylesheet" type="text/css" href="' . $dir . 'style.css" /> ';
    echo '<script type="text/javascript" src="'.$dir .'nib-script.js" charset="utf-8"></script>';
}

add_action("admin_head","noio_iconized_bookmarks_head");
add_action('admin_menu', 'noio_iconize_bookmarks_add_options_page'); 
?>