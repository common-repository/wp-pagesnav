<?php
/*
Plugin Name: PageNav
Plugin URI: http://wp.sieker.info/projects/wp-pagesnav
Description: Header Navigation.
Author: Adi Sieker
Version: 0.0.1
Author URI: http://www.sieker.info/
*/
/*  Copyright 2004-2007  Adi J. Sieker  (email : adi@sieker.info)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

function wp_pages_nav($args = '') {
    global $wp_query;
	parse_str($args, $params);
	if (!isset($params['current']))          $params['current'] = -1;
	if (!isset($params['show_all_parents'])) $params['show_all_parents'] = 0;
	if (!isset($params['show_root']))        $params['show_root'] = 1;
	if (!isset($params['home']))        	 $params['home'] = 'home';
	if (!isset($params['list_tag']))         $params['list_tag'] = 1;
	if (!isset($params['depth']))            $params['depth'] = -1;
	if (!isset($params['show_parents']))     $params['show_parents'] = 1;
	if (!isset($params['show_children']))    $params['show_children'] = 1;

    if($params['current'] == "")
        return;

    if($params['current'] == -1 && $wp_query->is_page == true) {
        $params['current'] = $wp_query->post->ID;
    }

    if($params['current'] == -1 && $params['show_root'] != 0) {
        $params['current'] = 0;
    }
    
	// Query pages.
	$pages = get_pages($args);
	if ( $pages ) {
    	// Now loop over all pages that were selected
    	$page_tree = Array();
    	$parent_page_id = null;
    	$parents= Array();
		$is_home_modified = 0;
    	foreach($pages as $page) {
    		// set the title for the current page
    		$page_tree[$page->ID]['title'] = $page->post_title;
    		$page_tree[$page->ID]['parent'] = $page->post_parent;
    
			//next line added as a lookup for password
			$page_tree[$page->ID]['post_password'] = $page->post_password;

			//check for page called home to set it to current if at index
			if($params['current'] == 0 && $page->post_title == $params['home']) {
				$params['current'] = $page->ID;
				$params["is_home_modified"] = 1;
			}
    		// set the selected date for the current page
    		// depending on the query arguments this is either
    		// the createtion date or the modification date
    		// as a unix timestamp. It will also always be in the
    		// ts field.
    		if (! empty($params['show_date'])) {
    			if ('modified' == $params['show_date'])
    				$page_tree[$page->ID]['ts'] = $page->time_modified;
    			else
    				$page_tree[$page->ID]['ts'] = $page->time_created;
    		}
    
    		// The tricky bit!!
    		// Using the parent ID of the current page as the
    		// array index we set the current page as a child of that page.
    		// We can now start looping over the $page_tree array
    		// with any ID which will output the page links from that ID downwards.
    		$page_tree[$page->post_parent]['children'][] = $page->ID;
    		
            if( $params['current'] == $page->ID) {
                if($page->post_parent != 0 || $params['show_root'] == true)
                    $parents[] = $page->post_parent;
            }
    	}

    	$len = count($parents);
    	for($i = 0; $i < $len ; $i++) {
    	    $parent_page_id = $parents[$i];
    	    $parent_page = $page_tree[$parent_page_id];

    	    if(isset($parent_page['parent']) && !in_array($parent_page['parent'], $parents)) {
    	        if($parent_page['parent'] != 0 || $params['show_root'] == true) {
        	        $parents[] = $parent_page['parent'];
        	        $len += 1;
        	        if( $len >= 2 && $params['show_all_parents'] == 0) {
        	            break;
        	        }

        	    }
    	    }
        }

        $parents = array_reverse($parents);

        $level = 0;
        $parent_out = false;

		if( $params['show_parents'] == true ) {
	        $r = output_parents($params, $parents, $page_tree);
    	    $level = $r['level'];
        	$parent_out = $r['parent_out'];
		}

		if($params['show_children'] == false ) {
			return;
		}

    	if( is_array($page_tree[$params['current']]['children']) === true ) {
            $level += 1;

            if( $params['depth'] == -1 || $level <= $params['depth']) {
    	  		$css_class = 'level' . $level;
	      		if( $params['list_tag'] == true || $parent_out == true)
		    	   	echo "<ul class='". $css_class . " children'>\n";

        	    foreach( $page_tree[$params['current']]['children'] as $page_id) {
    	    		$cur_page = $page_tree[$page_id];
	        		$title = $cur_page['title'];
        
                	echo "<li class='" . $css_class . "'><a href='" . get_page_link($page_id) . "' title='" . wp_specialchars($title) . "'>" . $title . "</a></li>\n";
            	}
      			if( $params['list_tag'] == true || $parent_out == true)
		        	echo "</ul>\n";
		        echo "\n";
            }
        }
     }
}

function output_parents($params, $parents, $page_tree) {
	$parent_out = false;	
	$level = 0;

	foreach( $parents as $parent_page_id ) {
	    $level += 1;
    	if( $params['depth'] != -1 && $level > $params['depth']) {
    		break;
		}
		$css_class = 'level' . $level;

		if( $params['list_tag'] == true || $parent_out == true) {
		echo "<ul class='". $css_class . "'>\n";
		}
			
    	foreach( $page_tree[$parent_page_id]['children'] as $page_id) {
    		$cur_page = $page_tree[$page_id];
			$title = $cur_page['title'];

    	    $css_class = '';

			if ($page_tree[$page_id]['post_password'] == '') {
			
			    if( $page_id == $params['current']) {
    				$css_class .= ' current';
		  	    }

				if( in_array($page_id, $parents)) {
					$css_class .= 'currentparent';
				}

				//if-else statement to fix for home page linkage
				if ($params["is_home_modified"] == 1 && $params['current'] == $page_id){
					$url = get_settings('siteurl');
				}
				elseif ($params['home'] == $title){
					$url = get_settings('siteurl');
				}
				else {
					$url = get_page_link($page_id);
				}
				echo "<li class='" . $css_class . "' ><a href='" . $url . "' title='" . wp_specialchars($title) . "'>" . $title . "</a></li>\n";

				$parent_out = true;
			}
		}

	    if( $params['list_tag'] == true || $parent_out == true) {
			echo "</ul>\n";
		}

		echo "\n";
    }

	$ret['parent_out'] = $parent_out;
	$ret['level'] = $level;
	
	return $ret;
}
?>
