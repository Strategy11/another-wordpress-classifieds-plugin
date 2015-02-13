<?php

function awpcp_build_categories_hierarchy( &$categories ) {
    $hierarchy = array();

    foreach ( $categories as $category ) {
        if ( $category->parent == 0 ) {
            $hierarchy['root'][] = $category;
        } else {
            $hierarchy[ $category->parent ][] = $category;
        }
    }

    return $hierarchy;
}
