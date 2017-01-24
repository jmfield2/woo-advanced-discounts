<?php

/**
 * Builds a select dropdpown
 * @param type $name Name
 * @param type $id ID
 * @param type $class Class
 * @param type $options Options
 * @param type $selected Selected value
 * @param type $multiple Can select multiple values
 * @return string HTML code
 */
function get_wad_html_select($name, $id, $class, $options, $selected = '', $multiple = false, $required = false) {
    ob_start();
    if ($multiple && !is_array($selected))
        $selected = array();
    ?>
    <select name="<?php echo $name; ?>" <?php echo ($id) ? "id=\"$id\"" : ""; ?> <?php echo ($class) ? "class=\"$class\"" : ""; ?> <?php echo ($multiple) ? "multiple" : ""; ?> <?php echo ($required) ? "required" : ""; ?> >
        <?php
        if (is_array($options) && !empty($options)) {
            foreach ($options as $value => $label) {
                if (!$multiple && $value == $selected) {
                    ?> <option value="<?php echo $value ?>"  selected="selected" > <?php echo $label; ?></option> <?php
                } else if ($multiple && in_array($value, $selected)) {
                    ?> <option value="<?php echo $value ?>"  selected="selected" > <?php echo $label; ?></option> <?php
                } else {
                    ?> <option value="<?php echo $value ?>"> <?php echo $label; ?></option> <?php
                }
            }
        }
        ?>
    </select>
    <?php
    $output = ob_get_contents();
    ob_end_clean();
    return $output;
}

function wad_filter_order_products($var)
{
    return $var["condition"]=="order-products";
}

function wad_filter_order_item_count($var)
{
    return $var["condition"]=="order-item-count";
}

//add_shortcode("test_wad", "get_variations_id_by_categories");
function get_variations_id_by_categories() {
    global $wpdb;
    $category_name = 'habit';
    $term_id = $wpdb->get_var("SELECT terms.term_id FROM $wpdb->terms as terms,$wpdb->term_taxonomy as term_taxonomy WHERE 
                terms.term_id=term_taxonomy.term_id
                AND term_taxonomy.taxonomy='product_cat' 
                AND terms.name='$category_name'");

    if (is_null($term_id)) {
        echo'This category doesn\'t exist';
    } else {

        $get_post_id = $wpdb->get_results("SELECT object_id FROM $wpdb->term_relationships as term_relationships,$wpdb->posts as posts,$wpdb->postmeta as postmeta 
                WHERE term_relationships.object_id=posts.ID AND posts.ID=postmeta.post_id 
                AND term_taxonomy_id='$term_id' AND post_status='publish' AND meta_key='_product_attributes' 
                AND meta_value<>'a:0:{}'");
        foreach ($get_post_id as $key => $value) {
            $post_name = $value->object_id . '-variation';
            $get_id_variation = $wpdb->get_results("SELECT ID FROM $wpdb->posts WHERE post_name LIKE '%$post_name%' 
                    AND post_status<>'trash'");
            if (!empty($get_id_variation)) {
                foreach ($get_id_variation as $key => $value) {
                    $variation_id = $value->ID;
                    $resultats[] = $variation_id;
                }
            }
        }
    }
}

/**
 * Remove everything the plugin stores in the cache
 * @global type $wpdb
 */
function wad_remove_transients()
{
    global $wpdb;
    $sql="delete from $wpdb->options where option_name like '%_orion_wad_%transient_%'";
    $wpdb->query($sql);
}

function wad_is_checkout()
{
    $is_checkout=false;
    if (!is_admin() && function_exists( 'is_checkout' ) && is_checkout())
        $is_checkout=true;
    
    return $is_checkout;
}