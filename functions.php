<?php

add_action( 'admin_menu', 'extra_post_info_menu' );
function extra_post_info_menu(){
    $page_title = 'WordPress Extra Post Info';
    $menu_title = 'Update Catalog';
    $capability = 'manage_options';
    $menu_slug  = 'extra-post-info';
    $function   = 'extra_post_info_page';
    $icon_url   = 'dashicons-media-code';
    $position   = 4;
    add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
}

function extra_post_info_page() {
    global $wpdb;

    $csvFile = "http://thechesssets.com/catalogue/StockSunrise.csv";

    $h = fopen($csvFile, "r");

    $select =  "SELECT * FROM `wp_postmeta` WHERE ";
    $where = "";
    $csvData = [];
    while (($data = fgetcsv($h, 1000, ";")) !== FALSE) {
        if (!$data[0] OR $data[0] == ""){
            continue;
        }
        $meta_value = str_replace("'","",$data[0]);
        $meta_value = str_replace('"','',$meta_value);
        $meta_value = preg_replace("/[^a-z0-9 _-]+/i", "", $meta_value);
        $where.= "`meta_value` = '".$meta_value."' OR ";
        $key = strtolower(str_replace(' ', '', $meta_value));
        $csvData[$key] = $data[2];
    }
    $where = rtrim($where, " OR ");

    $results = $wpdb->get_results( $select.$where );

    //Updating _stock records
    foreach ($results as $result) {
        $key = strtolower(str_replace(' ', '', $result->meta_value));
        if (isset($csvData[$key]) AND $csvData[$key] != ''){

            $wpdb->update(
                'wp_postmeta',
                array(
                    'meta_value' => $csvData[$key],
                ),
                array(
                    'post_id' => $result->post_id,
                    'meta_key' => '_stock',
                )
            );
        } else {
            var_dump($result->meta_value);
        }
    }
    return true;
}

/**
 * Catching update catalog param from url
 */

if (isset($_GET['update_catalogue']) ) {
    extra_post_info_page();
}