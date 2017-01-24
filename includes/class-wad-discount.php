<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of class-wad-discount
 *
 * @author HL
 */
class WAD_Discount {

    public $id;
    public $settings;
    public $products_list;
    public $products;

    public function __construct($discount_id) {
        if ($discount_id) {
            $this->id = $discount_id;
            $this->settings = get_post_meta($discount_id, "o-discount", true);
            if (!$this->settings)
                return;

            $list_id = false;
            $products_actions = array("percentage-off-pprice", "fixed-amount-off-pprice");
            if (in_array($this->settings["action"], $products_actions))
                $list_id = $this->settings["products-list"];
            if ($list_id) {
                $this->products_list = new WAD_Products_List($list_id);
                $this->products = $this->products_list->get_products();
            }
        }
    }

    /**
     * Register the discount custom post type
     */
    public function register_cpt_discount() {

        $labels = array(
            'name' => __('Discount', 'wad'),
            'singular_name' => __('Discount', 'wad'),
            'add_new' => __('New discount', 'wad'),
            'add_new_item' => __('New discount', 'wad'),
            'edit_item' => __('Edit discount', 'wad'),
            'new_item' => __('New discount', 'wad'),
            'view_item' => __('View discount', 'wad'),
            //        'search_items' => __('Search a group', 'wad'),
            'not_found' => __('No discount found', 'wad'),
            'not_found_in_trash' => __('No discount in the trash', 'wad'),
            'menu_name' => __('Discounts', 'wad'),
        );

        $args = array(
            'labels' => $labels,
            'hierarchical' => false,
            'description' => 'Discounts',
            'supports' => array('title'),
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_nav_menus' => true,
            'publicly_queryable' => false,
            'exclude_from_search' => true,
            'has_archive' => false,
            'query_var' => false,
            'can_export' => true,
            'menu_icon' => WAD_URL . 'admin/images/wad-dashicon.png',
        );

        register_post_type('o-discount', $args);
    }

    /**
     * Adds the metabox for the discount CPT
     */
    public function get_discount_metabox() {

        $screens = array('o-discount');

        foreach ($screens as $screen) {

            add_meta_box(
                    'o-discount-settings-box', __('Discount settings', 'wad'), array($this, 'get_discount_settings_page'), $screen
            );
        }
    }

    /**
     * Discount CPT metabox callback
     */
    public function get_discount_settings_page() {
        $raw_wp_language = get_bloginfo("language");
        $formatted_wp_language = substr($raw_wp_language, 0, strpos($raw_wp_language, "-"));
        ?>
        <script type="text/javascript">
            var lang_wordpress = '<?PHP echo $formatted_wp_language; ?>';
        </script>
        <div class='block-form'>
            <?php
            $begin = array(
                'type' => 'sectionbegin',
                'id' => 'wad-datasource-container',
            );
            $start_date = array(
                'title' => __('Start date', 'wad'),
                'name' => 'o-discount[start-date]',
                'type' => 'text',
                'class' => 'o-date',
//                'custom_attributes' => array("required" => ""),
                'desc' => __('Date from which the discount is applied.<br>Format: <strong>YYYY-MM-DD</strong>.', 'wad'),
                'default' => '',
            );

            $end_date = array(
                'title' => __('End date', 'wad'),
                'name' => 'o-discount[end-date]',
                'type' => 'text',
                'class' => 'o-date',
//                'custom_attributes' => array("required" => ""),
                'desc' => __('Date when the discount ends.<br>Format: <strong>YYYY-MM-DD</strong>.', 'wad'),
                'default' => '',
            );

            
            $action = array(
                'title' => __('Action', 'wad'),
                'name' => 'o-discount[action]',
                'type' => 'select',
                'class' => 'discount-action',
                'desc' => __('Type of of discount to apply.', 'wad'),
                'default' => '',
                'options' => $this->get_discounts_actions(),
            );

            $product_lists = new WAD_Products_List(false);

            $products_action = array(
                'title' => __('Products list', 'wad'),
                'id' => 'products-list',
                'name' => 'o-discount[products-list]',
                'type' => 'select',
//                'row_css' => $product_css,
                'row_class' => 'product-action-row',
                'desc' => __('List of products the selected action applies on', 'wad'),
                'default' => '',
                'options' => $product_lists->get_all(),
            );

            $percentage_or_fixed_amount = array(
                'title' => __('Percentage / Fixed amount', 'wad'),
                'name' => 'o-discount[percentage-or-fixed-amount]',
                'type' => 'number',
                'custom_attributes' => array("step" => "any"),
                'row_class' => 'percentage-row',
//                'row_css' => $percentage_or_fixed_amount_css,
                'desc' => __('Percentage or fixed amount to apply.', 'wad'),
                'default' => '',
            );

            $is_cumulable = array(
                'title' => __('Cumulable ?', 'wad'),
                'name' => 'o-discount[is-cumulable]',
                'type' => 'radio',
                'default' => 'no',
                'desc' => __('Wether or not the discount can be applied if the condition is fullfilled more than once.', 'wad'),
                'options' => array(
                    "yes" => "Yes",
                    "no" => "No",
                )
            );

            $relationship = array(
                'title' => __('Rules groups relationship', 'wad'),
                'name' => 'o-discount[relationship]',
                'type' => 'radio',
                'desc' => __('AND: All groups rules must be verified to have the discount action applied.', 'wad') . "<br" . __('OR: AT least one group rules must be verified to have the discount action applied.', 'wad'),
                'default' => 'AND',
                'options' => array(
                    "AND" => "AND",
                    "OR" => "OR",
                )
            );

            $rules = array(
                'title' => __('Rules', 'wad'),
                'desc' => __('Allows you to define which rules should be checked in order to apply the discount. Not mandatory.', 'wad'),
                'name' => 'o-discount[rules]',
                'type' => 'custom',
                'callback' => array($this, "get_discount_rules_callback"),
            );

            $end = array('type' => 'sectionend');
            $settings = array(
                $begin,
                $start_date,
                $end_date,
                $relationship,
                $rules,
                $action,
                $percentage_or_fixed_amount,
                $products_action,
                $end
            );
            echo o_admin_fields($settings);
            ?>
        </div>

        <?php
        global $o_row_templates;
        ?>
        <script>
            var o_rows_tpl =<?php echo json_encode($o_row_templates); ?>;
        </script>
        <?php
        return;
    }

    private function get_rule_tpl($conditions, $default_condition = false, $default_operator = "", $default_value = "") {
        ob_start();
        $operators = $this->get_operator_fields_match($default_condition, $default_operator);
        $value_field = $this->get_value_fields_match($default_condition, $default_value);
        ?>
        <tr data-id="rule_{rule-group}">
            <td class="param">
                <select class="select wad-pricing-group-param" name="o-discount[rules][{rule-group}][{rule-index}][condition]" data-group="{rule-group}" data-rule="{rule-index}">
                    <?php
                    foreach ($conditions as $condition_key => $condition_val) {
                        if ($condition_key == $default_condition) {
                            ?><option value='<?php echo $condition_key; ?>' selected="selected"><?php echo $condition_val; ?></option><?php
                        } else {
                            ?><option value='<?php echo $condition_key; ?>'><?php echo $condition_val; ?></option><?php
                        }
                    }
                    ?>
                </select>
            </td>
            <td class="operator">
                <?php echo $operators; ?>
            </td>
            <td class="value">
                <?php echo $value_field; ?>
            </td>
            <td class="add">
                <a class="wad-add-rule button" data-group='{rule-group}'><?php echo __("and", "wad"); ?></a>
            </td>
            <td class="remove">
                <a class="wad-remove-rule acf-button-remove"></a>
            </td>
        </tr>
        <?php
        $rule_tpl = ob_get_contents();
        ob_end_clean();
        return $rule_tpl;
    }

    private function get_existing_user_roles() {
        global $wp_roles;
        $roles_arr = array();
        $all_roles = $wp_roles->roles;
        foreach ($all_roles as $role_key => $role_data) {
            $roles_arr[$role_key] = $role_data["name"];
        }
        return $roles_arr;
    }

    private function get_existing_users() {
        $users = get_users(array('fields' => array('ID', 'display_name', 'user_email')));
        $users_arr = array();
        foreach ($users as $user) {
            $users_arr[$user->ID] = "$user->display_name($user->user_email)";
        }

        return $users_arr;
    }

    private function get_value_fields_match($condition = false, $selected_value = "") {
        $selected_value_arr = array();
        $selected_value_str = "";
        if (is_array($selected_value))
            $selected_value_arr = $selected_value;
        else
            $selected_value_str = $selected_value;

        $field_name = "o-discount[rules][{rule-group}][{rule-index}][value]";
        $roles = $this->get_existing_user_roles();
        $roles_select = get_wad_html_select($field_name . "[]", false, "", $roles, $selected_value_arr, true, true);
        

        $users = $this->get_existing_users();
        $users_select = get_wad_html_select($field_name . "[]", false, "", $users, $selected_value_arr, true, true);

        $text = '<input type="number" name="' . $field_name . '" value="' . $selected_value_str . '" required>';
        
        $list_obj = new WAD_Products_List(false);
        $lists_arr = $list_obj->get_all();
        $products_list = get_wad_html_select($field_name, false, "", $lists_arr, $selected_value_str, false);



        $values_match = array(
            "customer-role" => $roles_select,
            "customer" => $users_select,
            "order-subtotal" => $text,
            "order-item-count" => $text,
            "order-products" => $products_list,
			"order-products-count" => $text,
        );

        if (isset($values_match[$condition]))
            return $values_match[$condition];
        else
            return $values_match;
    }

    private function get_operator_fields_match($condition = false, $selected_value = "") {
        $field_name = "o-discount[rules][{rule-group}][{rule-index}][operator]";
        $arrays_operators = array(
            "IN" => "IN",
            "NOT IN" => "NOT IN",
        );
        $arrays_operators_select = get_wad_html_select($field_name, false, "", $arrays_operators, $selected_value);

        $number_operators = array(
            "<" => __("is less than", "wad"),
            "<=" => __("is less or equal to", "wad"),
            "==" => __("equals", "wad"),
            ">" => __("is more than", "wad"),
            ">=" => __("is more or equal to", "wad"),
            "%" => __("is a multiple of", "wad"),
        );
        $number_operators_select = get_wad_html_select($field_name, false, "", $number_operators, $selected_value);
        $operators_match = array(
            "customer-role" => $arrays_operators_select,
            "customer" => $arrays_operators_select,
            "order-subtotal" => $number_operators_select,
            "order-item-count" => $number_operators_select,
            "order-products" => $arrays_operators_select,
			"order-products-count" => $number_operators_select,
        );

        if (isset($operators_match[$condition]))
            return $operators_match[$condition];
        else
            return $operators_match;
    }

    function get_discount_rules_callback() {

        $conditions = $this->get_discounts_conditions();
        $first_rule = $this->get_rule_tpl($conditions, "customer-role");
//        $rule_tpl = $this->get_rule_tpl($conditions);
        $values_match = $this->get_value_fields_match(-1);
        $operators_match = $this->get_operator_fields_match(-1);
        ?>
        <script>
            var wad_values_matches =<?php echo json_encode($values_match); ?>;
            var wad_operators_matches =<?php echo json_encode($operators_match); ?>;
        </script>
        <div class='wad-rules-table-container'>
            <textarea id='wad-rule-tpl' style='display: none;'>
                <?php echo $first_rule; ?>
            </textarea>
            <textarea id='wad-first-rule-tpl' style='display: none;'>
                <?php echo $first_rule; ?>
            </textarea>
            <?php
            $discount_id = get_the_ID();
            $metas = get_post_meta($discount_id, 'o-discount', true);
            $all_rules = array();
            if (isset($metas['rules']))
                $all_rules = $metas['rules'];

            if (is_array($all_rules) && !empty($all_rules)) {
                $rule_group = 0;
                foreach ($all_rules as $rules) {
                    $rule_index = 0;
                    ?>
                    <table class="wad-rules-table widefat wad-rules-table">
                        <tbody>
                            <?php
                            foreach ($rules as $rule_arr) {
                                $rule_arr["condition"] = get_proper_value($rule_arr, "condition");
                                $rule_arr["operator"] = get_proper_value($rule_arr, "operator");
                                $rule_arr["value"] = get_proper_value($rule_arr, "value");
                                $rule_html = $this->get_rule_tpl($conditions, $rule_arr["condition"], $rule_arr["operator"], $rule_arr["value"]);
                                $r1 = str_replace("{rule-group}", $rule_group, $rule_html);
                                $r2 = str_replace("{rule-index}", $rule_index, $r1);
                                echo $r2;
                                $rule_index++;
                            }
                            $rule_group++;
                            ?>
                        </tbody>
                    </table>
                    <?php
                }
            }
            ?>

        </div>
        <a class="button wad-add-group mg-top"><?php _e("Add rules group", "wad");?></a>
        <?php
    }

    /**
     * Saves the discount data
     * @param type $post_id
     */
    public function save_discount($post_id) {
        $meta_key = "o-discount";
        if (isset($_POST[$meta_key])) {
            update_post_meta($post_id, $meta_key, $_POST[$meta_key]);
            wad_remove_transients();
        }
    }

    public function get_user_role() {
        $uid = get_current_user_id();
        global $wpdb;
        $role = $wpdb->get_var("SELECT meta_value FROM {$wpdb->usermeta} WHERE meta_key = '{$wpdb->prefix}capabilities' AND user_id = {$uid}");
        if (!$role)
            return 'non-user';
        $rarr = unserialize($role);
        $roles = is_array($rarr) ? array_keys($rarr) : array('non-user');
        return $roles[0];
    }

    function get_customer_orders() {
        $current_user = wp_get_current_user();
        $args = array(
            "post_type" => "shop_order",
            "post_status" => array('wc-processing', 'wc-completed'),
            "meta_key" => "_billing_email",
            "meta_value" => $current_user->user_email,
            "nopaging" => true,
        );
        $orders = get_posts($args);
        return $orders;
    }
    
    /**
     * Return the cart sub total
     * @global type $woocommerce
     * @param Bool $inc_taxes Including taxes
     * @return type
     */
    function get_cart_total($inc_taxes = false) {
        global $woocommerce;
            $cart_total = 0;
            //Commented because works randomly on the cart page
            if (!$inc_taxes)
                $cart_total = $woocommerce->cart->subtotal_ex_tax;
            else
                $cart_total = $woocommerce->cart->subtotal_ex_tax + $woocommerce->cart->shipping_total + $woocommerce->cart->get_taxes_total(false, false);
            //Just in case what we're looking for has not been initialized yet
//        if(!$cart_total)
//        {
//        foreach ($woocommerce->cart->cart_contents as $cart_item_key => $cart_item) {
//            $cart_total+=$cart_item["line_total"];
//            if ($inc_taxes)
//                $cart_total+=$cart_item["line_tax"];
//        }
        
        return $cart_total;
    }

    function get_cart_items($item_id = false) {
        global $woocommerce;
        return $woocommerce->cart->get_cart();
    }

    private function apply_discount_on($cart, $discounted_products) {
        foreach ($cart->cart_contents as $cart_item_key => $cart_item) {
            $product_id = $cart_item["product_id"];
            if (in_array($product_id, $discounted_products)) {
                $cart_item['data']->price -= $this->get_discount_amount($cart_item['data']->price);
            }
        }
        return $cart;
    }

    function get_cart_item_html($price_html, $cart_item, $cart_item_key) {
        //Some plugins like woocommerce request a quote send an empty $cart_item which trigger a lot of issues.
        if(!empty($cart_item))
        {
            $product_id = $cart_item["product_id"];
            if ($cart_item["variation_id"])
                $product_id = $cart_item["variation_id"];
            $product_obj = wc_get_product($product_id);
            //We check the price used for add to cart
            $used_price = $this->get_regular_price($product_obj->price, $product_obj);
            //A discount is applied
            if ($used_price != $cart_item['data']->price) {
                $old_price_html = wc_price($product_obj->price);
                $price_html = "<span class='wad-discount-price' style='text-decoration: line-through;'>$old_price_html</span>" . " $price_html";
            }
        }
        return $price_html;
    }

    /**
     * Returns the total to widthdraw on cart or taxes
     * @global Array $wad_discounts
     * @global Object $woocommerce
     * @param Bool $on_taxes Whether to return the total on taxes or on the cart
     * @return Float
     */
    private function get_total_cart_discount($on_taxes=false) {
        global $wad_discounts;
        global $woocommerce;
        $discounts = $wad_discounts;
        $to_widthdraw = 0;
        $to_widthdraw_on_taxes=0;

        foreach ($discounts["order"] as $discount_id => $discount) {
            if ($discount->is_applicable()) {
                    if ($discount->settings["action"] == "percentage-off-osubtotal-inc-taxes" || $discount->settings["action"] == "fixed-amount-off-osubtotal-inc-taxes") {
                        $cart_total = $this->get_cart_total(true);
                        if($discount->settings["action"] == "percentage-off-osubtotal-inc-taxes")
                        {
                            $to_widthdraw_on_taxes+=$discount->settings["percentage-or-fixed-amount"];
                        }
                        else
                        {
                            //We determine which percentage of the total represents the fixed amount
                            $percentage=$discount->settings["percentage-or-fixed-amount"]*100/$cart_total;
                            $to_widthdraw_on_taxes+=$percentage;
                        }
                    } else
                        $cart_total = $this->get_cart_total();

                    $to_widthdraw+=$discount->get_discount_amount($cart_total);
                }

                //We save the discount in the session to use it later when completing the payment
                if (!in_array($discount_id, $_SESSION["active_discounts"]) && wad_is_checkout())
                    array_push($_SESSION["active_discounts"], $discount_id);
        }
        if($on_taxes)
            return $to_widthdraw_on_taxes/100;
        else
            return $to_widthdraw;
    }

    /**
     * Returns the product price in the cart
     * @global type $woocommerce
     * @param type $product_id
     * @return type
     */
    function get_cart_item_price($product_id) {
        global $woocommerce;
//        $price = 0;
//        foreach ($woocommerce->cart->cart_contents as $cart_item_key => $cart_item_data) {
//            if ($cart_item_data["product_id"] == $product_id) {
        $product = wc_get_product($product_id);
        $price = $product->get_price();
//                $price = $cart_item_data["line_total"] / $cart_item_data["quantity"];
//            }
//        }
        return $price;
    }
    
    function woocommerce_custom_surcharge() {
  global $woocommerce;
 
	if ( is_admin() && !is_ajax() )
		return;
        $discount_ttc=$this->get_total_cart_discount()*-1;
        $discount_ht=  $discount_ttc / (1+$this->get_total_cart_discount(true));
        
        
		$woocommerce->cart->add_fee( __('Total cart discount', 'wad'), $discount_ht, true, '' );
//	endif;
 
}

    function apply_cart_discounts() {
        global $woocommerce;
        $woocommerce->cart->total-= $this->get_total_cart_discount();
    }
    
    function apply_cart_discounts_on_taxes($tax_totals, $cart) {
        
        $discount_on_taxes=$this->get_total_cart_discount(true);
        
        foreach ($tax_totals as $tax_code=>$tax)
        {
            $tax_totals[$tax_code]->amount-=$discount_on_taxes*$tax_totals[$tax_code]->amount;
            $tax_totals[$tax_code]->formatted_amount=  wc_price($tax_totals[$tax_code]->amount);
        }
        
        return $tax_totals;
    }

    function get_discount_amount($amount) {
        $to_widthdraw = 0;
        if (in_array($this->settings["action"], array("percentage-off-pprice", "percentage-off-osubtotal", "percentage-off-osubtotal-inc-taxes")))
            $to_widthdraw = $amount * $this->settings["percentage-or-fixed-amount"] / 100;
        //Fixed discount
        else if (in_array($this->settings["action"], array("fixed-amount-off-pprice", "fixed-amount-off-osubtotal", "fixed-amount-off-osubtotal-inc-taxes"))) {
            $to_widthdraw = $this->settings["percentage-or-fixed-amount"];
        }
        //We save the discount in the session to use it later when completing the payment
//        if (!in_array($this->id, $_SESSION["active_discounts"]))
//            array_push($_SESSION["active_discounts"], $this->id);
        return $to_widthdraw;
    }

    function is_rule_valid($rule, $product_id = false) {
        $is_valid = false;
        $condition = $this->get_evaluable_condition($rule);
        $value = get_proper_value($rule, "value");
        
        //We check if the condition is IN or NOT IN the value
        if ($rule["condition"] == "customer-role" || $rule["condition"] == "customer") {
            $is_valid = in_array($condition, $value);
            if ($rule["operator"] == "NOT IN") {
                $is_valid = (!$is_valid);
            }
            //Checks if the a products is in a list
        } else if ($rule["condition"] == "order-products") {
            $products_list_id = $rule["value"];
            $list_object = new WAD_Products_List($products_list_id);
            $list_products_ids = $list_object->get_products();
            //We check if there is at list one occurence of the items in the condition in the list
            $diff = array_intersect($condition, $list_products_ids);
            $is_valid = count($diff);
            if ($rule["operator"] == "NOT IN") {
                $is_valid = (!$is_valid);
            }
        } else {
            //Avant d'utiliser EVAL, vérifier soigneusement l'input de l'user à savoir chaque élément composant l'expression à évaluer
            $operator = $rule["operator"];
            if ($operator == "%")//Modulo evaluation
                $expression_to_eval = "if($condition $operator $value==0) return true; else return false;";
            else
                $expression_to_eval = "if($condition $operator $value) return true; else return false;";
            
            $is_valid = eval($expression_to_eval);
        }
        return $is_valid;
    }

    private function get_cart_products() {
        global $woocommerce;
        $products = array();
        if (empty($woocommerce->cart->cart_contents))
            return $products;

        foreach ($woocommerce->cart->cart_contents as $cart_item_key => $cart_item_data) {
            array_push($products, $cart_item_data["product_id"]);
            //We add the variations too in case the customer is checking against a variation
            if (isset($cart_item_data["variation_id"]) && !empty($cart_item_data["variation_id"]))
                array_push($products, $cart_item_data["variation_id"]);
        }
        return $products;
    }

    private function get_cart_products_count($by_products = false) {
        global $woocommerce;
        $count = array();
        if (!empty($woocommerce->cart->cart_contents))
        {
            foreach ($woocommerce->cart->cart_contents as $cart_item_key => $cart_item_data) {
                //We add the variations too in case the customer is checking against a variation
                if (isset($cart_item_data["variation_id"]) && !empty($cart_item_data["variation_id"])) {
                    $count[$cart_item_data["variation_id"]] = $cart_item_data["quantity"];
                } else {
                    $count[$cart_item_data["product_id"]] = $cart_item_data["quantity"];
                }
            }
        }

        if ($by_products)
            return $count;
        else
            return array_sum($count);
    }

    private function get_evaluable_condition($rule) {
        $condition = $rule["condition"];
        $evaluable_condition = false;
//        global $woocommerce;
        switch ($condition) {
            case "customer-role"://If Customer has specified role
                $evaluable_condition = $this->get_user_role();
                break;
            case "customer"://If Customer is
                if (is_user_logged_in())
                    $evaluable_condition = get_current_user_id();
                break;
            case "order-subtotal"://If Order subtotal
                $evaluable_condition = $this->get_cart_total(false);
                break;
            case "order-item-count"://If Order items count
                $evaluable_condition = $this->get_cart_products_count(); //$woocommerce->cart->get_cart_contents_count();
                break;
            case "order-products"://If Order products
                $evaluable_condition = $this->get_cart_products();
                break;
            default :
                $evaluable_condition = false;
                break;
        }

        return $evaluable_condition;
    }

    function get_active_discounts($group_by_types = false) {
        global $wpdb;
        $args = array(
            "post_type" => "o-discount",
            "post_status" => "publish",
            "nopaging" => true,
        );
        if ($group_by_types)
            $valid_discounts = array(
                "product" => array(),
                "order" => array(),
            );
        else
            $valid_discounts = array();
        $discounts = get_posts($args);
        $today = date('Y-m-d');
        $today = date('Y-m-d', strtotime($today));
        $product_based_actions = array("percentage-off-pprice", "fixed-amount-off-pprice");
        foreach ($discounts as $discount) {
            $metas = get_post_meta($discount->ID, "o-discount", true);

            //We make sure empty dates are marked as active
            if (empty($metas["start-date"]))
                $start_date = date('Y-m-d');
            else
                $start_date = date('Y-m-d', strtotime($metas["start-date"]));

            if (empty($metas["start-date"]))
                $end_date = date('Y-m-d');
            else
                $end_date = date('Y-m-d', strtotime($metas["end-date"]));

            if (
                    (($today >= $start_date) && ($today <= $end_date)) ||
                    $this->is_in_valid_period($metas["start-date"], $metas["end-date"], $metas["period"], $metas["period-type"])
            ) {
                if ($group_by_types) {
                    if (in_array($metas["action"], $product_based_actions))
                        array_push($valid_discounts["product"], $discount->ID);
                    else
                        array_push($valid_discounts["order"], $discount->ID);
                } else
                    array_push($valid_discounts, $discount->ID);
            }
        }
        return $valid_discounts;
    }

    private function is_in_valid_period($start, $end, $period, $period_type) {
        if (empty($period)) {
            return false;
        }

        $begin_date = new DateTime($start);
        $end_date = new DateTime($end);

        $today = new DateTime();
        //We make sure the today does not includes the time otherwise it may interfere with the comparison
        $today->setTime(0, 0, 0);

        $nb_elapsed = $today->diff($begin_date);

        $nb_days_elapsed = $nb_elapsed->format("%$period_type");

        $nb_periods_elapsed = intval($nb_days_elapsed / $period);

        if ($period_type == "d") {
            $period_type_str = "day";
        } elseif ($period_type == "m") {
            $period_type_str = "month";
        } else if ($period_type == "y") {
            $period_type_str = "year";
        }

        $last_period_begin_date = $begin_date->modify("+" . ($nb_periods_elapsed * $period) . " $period_type_str");
        $last_period_end_date = $end_date->modify("+" . ($nb_periods_elapsed * $period) . " $period_type_str");

        return (($today >= $last_period_begin_date) && ($today <= $last_period_end_date));
    }

    function is_applicable($product_id = false) {
        $is_valid = true;
        if (!isset($this->settings["rules"]) || !is_array($this->settings["rules"])) {
            $this->settings["rules"] = array();
        }
        foreach ($this->settings["rules"] as $group) {
            foreach ($group as $rule) {
                $is_valid = $this->is_rule_valid($rule, $product_id);
                //Group is not valid
                if (!$is_valid) {
                    break;
                }
            }
            //If one rule of the group is not valid in a AND case, then the group is not valid
            if ($this->settings["relationship"] == "AND" && !$is_valid) {
                break;
            }
            //If one group is valid in a OR case, then the discount is valid
            if ($this->settings["relationship"] == "OR" && $is_valid)
                break;
        }
        return $is_valid;
    }

    public function get_all() {
        $args = array(
            "post_type" => "o-discount",
            "nopaging" => true,
        );
        $discounts = get_posts($args);
        $discounts_arr = array();
        foreach ($discounts as $discount) {
            $discounts_arr[$discount->ID] = $discount->post_title;
        }
        return $discounts_arr;
    }

    function get_discounts_conditions() {
        return array(
            "customer-role" => __("If Customer role", "wad"),
            "customer" => __("If Customer", "wad"),
            "order-subtotal" => __("If Order subtotal", "wad"),
            "order-item-count" => __("If Order items count", "wad"),
            "order-products" => __("If Order products", "wad"),
			"order-products-count" => __("If number of matching products", "wad"),
        );
    }

    function get_discounts_actions() {
        return array(
            "percentage-off-pprice" => __("Percentage off product price", "wad"),
            "fixed-amount-off-pprice" => __("Fixed amount off product price", "wad"),
            "percentage-off-osubtotal" => __("Percentage off order subtotal", "wad"),
            "fixed-amount-off-osubtotal" => __("Fixed amount off order subtotal", "wad"),
        );
    }

    public function get_sale_price($sale_price, $product) {
        global $wad_discounts;

        if (is_admin() && !is_ajax() /* || empty($sale_price) */)
            return $sale_price;

        $pid = $this->get_product_id_to_use($product);

        if (empty($sale_price))
            $sale_price = $product->regular_price;
        $all_discounts = $wad_discounts;
        foreach ($all_discounts["product"] as $discount_id => $discount_obj) {
            $list_products = $discount_obj->products;
            if ($discount_obj->is_applicable($pid) && in_array($pid, $list_products)) {
                $sale_price -= $discount_obj->get_discount_amount($sale_price);
                //We save the discount in the session to use it later when completing the payment
                if (!in_array($discount_id, $_SESSION["active_discounts"]))
                    array_push($_SESSION["active_discounts"], $discount_id);
            }
        }

        //We check if there is a quantity pricing in order to apply that discount in the cart or checkout pages
        if (is_cart() || wad_is_checkout()) {
            $sale_price = $this->apply_quantity_based_discount_if_needed($product, $sale_price);
        }

        return $sale_price;
    }

    public function get_regular_price($regular_price, $product) {
        global $wad_discounts;

            if (is_admin() && !is_ajax())
            return $regular_price;

        $pid = $this->get_product_id_to_use($product);

        $all_discounts = $wad_discounts;
        foreach ($all_discounts["product"] as $discount_id => $discount_obj) {
            $list_products = $discount_obj->products;
            if ($discount_obj->is_applicable($pid) && in_array($pid, $list_products)) {
                $regular_price-=$discount_obj->get_discount_amount($regular_price);

                //We save the discount in the session to use it later when completing the payment
                if (!in_array($discount_id, $_SESSION["active_discounts"]) && wad_is_checkout()) {
                    array_push($_SESSION["active_discounts"], $discount_id);
                }
            }
        }

        //We check if there is a quantity pricing in order to apply that discount in the cart or checkout pages
        if (is_cart() || wad_is_checkout()) {
            $regular_price = $this->apply_quantity_based_discount_if_needed($product, $regular_price);
        }
//        var_dump($regular_price);
        return $regular_price;
    }

    /**
     * Returns the product id to use in order to apply the discounts
     * @param type $product Product to check
     * @return int
     */
    private function get_product_id_to_use($product) {
        $product_class = get_class($product);

        if ($product_class == "WC_Product_Variation") {
            $pid = $product->variation_id;
        } else
            $pid = $product->id;

        return $pid;
    }

    private function apply_quantity_based_discount_if_needed($product, $normal_price) {
        global $woocommerce;
        //We check if there is a quantity based discount for this product
        $quantity_pricing = get_post_meta($product->id, "o-discount", true);
        $products_qties = $this->get_cart_item_quantities();
        $rules_type = get_proper_value($quantity_pricing, "rules-type", "intervals");

        $id_to_check = $product->id;
        //If it's a variable product
        if ($product->variation_id)
            $id_to_check = $product->variation_id;

        if (!isset($products_qties[$id_to_check]) || empty($quantity_pricing) || !isset($quantity_pricing["enable"]))
            return $normal_price;

        if (isset($quantity_pricing["rules"]) && $rules_type == "intervals") {
//            $id_to_check = $product->id;
//            //If it's a variable product
//            if ($product->variation_id)
//                $id_to_check = $product->variation_id;
            foreach ($quantity_pricing["rules"] as $rule) {
                //if ($rule["min"] <= $products_qties[$id_to_check] && $products_qties[$id_to_check] <= $rule["max"]) {
                if (
                        ($rule["min"] === "" && $products_qties[$id_to_check] <= $rule["max"]) || ($rule["min"] === "" && $rule["max"] === "") || ($rule["min"] <= $products_qties[$id_to_check] && $rule["max"] === "") || ($rule["min"] <= $products_qties[$id_to_check] && $products_qties[$id_to_check] <= $rule["max"])
                ) {
                    if ($quantity_pricing["type"] == "fixed")
                        $normal_price-=$rule["discount"];
                    else if ($quantity_pricing["type"] == "percentage")
                        $normal_price-=($normal_price * $rule["discount"]) / 100;
                    break;
                }
            }
        }
        else if (isset($quantity_pricing["rules-by-step"]) && $rules_type == "steps") {

            foreach ($quantity_pricing["rules-by-step"] as $rule) {
                if ($products_qties[$id_to_check] % $rule["every"] == 0) {
                    if ($quantity_pricing["type"] == "fixed")
                        $normal_price-=$rule["discount"];
                    else if ($quantity_pricing["type"] == "percentage")
                        $normal_price-=($normal_price * $rule["discount"]) / 100;
                    break;
                }
            }
        }
        return $normal_price;
    }

    function save_used_discounts($order_id) {
        if (isset($_SESSION["active_discounts"]) && !empty($_SESSION["active_discounts"])) {
            $used_discounts = array_unique($_SESSION["active_discounts"]);
            foreach ($used_discounts as $discount_id) {
                add_post_meta($order_id, "wad_used_discount", $discount_id);
                $discout_obj = new WAD_Discount($discount_id);
                add_post_meta($order_id, "wad_used_discount_settings_$discount_id", $discout_obj->settings);
            }
            unset($_SESSION["active_discounts"]);
        }
    }

    /**
     * Adds the Custom column to the default products list to help identify which ones are custom
     * @param array $defaults Default columns
     * @return array
     */
    function get_columns($defaults) {
        $defaults['wad_start_date'] = __('Start Date', 'wad');
        $defaults['wad_end_date'] = __('End Date', 'wad');
        $defaults['wad_active'] = __('Active', 'wad');
        return $defaults;
    }

    /**
     * Sets the Custom column value on the products list to help identify which ones are custom
     * @param type $column_name Column name
     * @param type $id Product ID
     */
    function get_columns_values($column_name, $id) {
        global $wad_discounts;
        //var_dump($wad_discounts);
        if ($column_name === 'wad_active') {
            $class = "";
            $order_discounts_ids = array_map(create_function('$o', 'return $o->id;'), $wad_discounts["order"]);
            $products_discounts_ids = array_map(create_function('$o', 'return $o->id;'), $wad_discounts["product"]);
            if (in_array($id, $order_discounts_ids) || in_array($id, $products_discounts_ids))
                $class = "active";
            echo "<span class='wad-discount-status $class'></span>";
        }
        else if ($column_name === "wad_start_date") {
            $discount = new WAD_Discount($id);
            if (!$discount->settings) {
                echo "-";
                return;
            }
            $formatted_date = mysql2date(get_option('date_format'), $discount->settings["start-date"], false);
            echo $formatted_date;
        } else if ($column_name === "wad_end_date") {
            $discount = new WAD_Discount($id);
            if (!$discount->settings) {
                echo "-";
                return;
            }
            $formatted_date = mysql2date(get_option('date_format'), $discount->settings["end-date"], false);
            echo $formatted_date;
        }
    }

    /**
     * Adds new tabs in the product page
     */
    function get_product_tab_label($tabs) {

        $tabs['wad_quantity_pricing'] = array(
            'label' => __('Quantity Based Pricing', 'wad'),
            'target' => 'wad_quantity_pricing_data',
            'class' => array(),
        );
        return $tabs;
    }

    function get_product_tab_label_old() {
        ?>
        <li class="wad_quantity_pricing"><a href="#wad_quantity_pricing_data"><?php _e('Quantity Based Pricing', 'wad'); ?></a></li>
        <?php
    }

    function get_product_tab_data() {
//        var_dump("yes");
        $begin = array(
            'type' => 'sectionbegin',
            'id' => 'wad-quantity-pricing-rules',
        );

        $discount_enabled = array(
            'title' => __('Enabled', 'wad'),
            'name' => 'o-discount[enable]',
            'type' => 'checkbox',
            'desc' => __('Enable/Disable this feature', 'wad'),
        );

        $discount_type = array(
            'title' => __('Discount type', 'wad'),
            'name' => 'o-discount[type]',
            'type' => 'radio',
            'options' => array(
                "percentage" => __("Percentage", "wad"),
                "fixed" => __("Fixed amount", "wad"),
            ),
            'default' => 'percentage',
            'desc' => __('Apply a percentage or a fixed amount discount', 'wad'),
        );

        $rules_types = array(
            'title' => __('Rules type', 'wad'),
            'name' => 'o-discount[rules-type]',
            'type' => 'radio',
            'options' => array(
                "intervals" => __("Intervals", "wad"),
                "steps" => __("Steps", "wad"),
            ),
            'default' => 'intervals',
            'desc' => __('If Intervals, the intervals rules will be used.<br>If Steps, the steps rules will be used.', 'wad'),
        );

        $min = array(
            'title' => __('Min', 'wad'),
            'name' => 'min',
            'type' => 'number',
            'default' => '',
        );

        $max = array(
            'title' => __('Max', 'wad'),
            'name' => 'max',
            'type' => 'number',
            'default' => '',
        );

        $discount = array(
            'title' => __('Discount', 'wad'),
            'name' => 'discount',
            'type' => 'number',
            'custom_attributes' => array("step" => "any"),
            'default' => '',
        );

        $discount_rules = array(
            'title' => __('Intervals rules', 'wad'),
            'desc' => __('If quantity ordered between Min and Max, then the discount specified will be applied. <br>Leave Min or Max empty for any value (joker).', 'wad'),
            'name' => 'o-discount[rules]',
            'type' => 'repeatable-fields',
            'id' => 'intervals_rules',
            'fields' => array($min, $max, $discount),
        );

        $every = array(
            'title' => __('Every X items', 'wad'),
            'name' => 'every',
            'type' => 'number',
            'default' => '',
        );

        $discount_rules_steps = array(
            'title' => __('Steps Rules', 'wad'),
            'desc' => __('If quantity ordered is a multiple of the step, then the discount specified will be applied.', 'wad'),
            'name' => 'o-discount[rules-by-step]',
            'type' => 'repeatable-fields',
            'id' => 'steps_rules',
            'fields' => array($every, $discount),
        );

        $end = array('type' => 'sectionend');
        $settings = array(
            $begin,
            $discount_enabled,
            $discount_type,
            $rules_types,
            $discount_rules,
            $discount_rules_steps,
            $end
        );
        ?>
        <div id="wad_quantity_pricing_data" class="panel woocommerce_options_panel wpc-sh-triggerable">
            <?php
            echo o_admin_fields($settings);
            ?>
        </div>
        <?php
        global $o_row_templates;
        ?>
        <script>
            var o_rows_tpl =<?php echo json_encode($o_row_templates); ?>;
        </script>
        <?php
    }

    function get_quantity_pricing_tables() {
        $product_id = get_the_ID();
        $product_obj = wc_get_product($product_id);
        $quantity_pricing = get_post_meta($product_id, "o-discount", true);
        $rules_type = get_proper_value($quantity_pricing, "rules-type", "intervals");

        if (isset($quantity_pricing["enable"]) && isset($quantity_pricing["rules"])) {
            ?>
            <h3><?php _e("Quantity based pricing table", "wad"); ?></h3>

            <?php
            if ($rules_type == "intervals") {
                if ($product_obj->product_type == "variable") {
                    $available_variations = $product_obj->get_available_variations();
                    foreach ($available_variations as $variation) {
                        $product_price = $variation["display_price"];
                        $this->get_quantity_pricing_table($variation["variation_id"], $quantity_pricing, $product_price);
                    }
                } else {
                    $product_price = $product_obj->price;
                    $this->get_quantity_pricing_table($product_id, $quantity_pricing, $product_price, true);
                }
            } else if ($rules_type == "steps") {

                if ($product_obj->product_type == "variable") {
                    $available_variations = $product_obj->get_available_variations();
                    foreach ($available_variations as $variation) {
                        $product_price = $variation["display_price"];
                        $this->get_steps_quantity_pricing_table($variation["variation_id"], $quantity_pricing, $product_price);
                    }
                } else {
                    $product_price = $product_obj->price;
                    $this->get_steps_quantity_pricing_table($product_id, $quantity_pricing, $product_price, true);
                }
            }
        }
    }

    private function get_steps_quantity_pricing_table($product_id, $quantity_pricing, $product_price, $display = false) {
        $style = "";
        if (!$display)
            $style = "display: none;";
        ?>
        <table class="wad-qty-pricing-table" data-id="<?php echo $product_id; ?>" style="<?php echo $style; ?>">
            <thead>
                <tr>
                    <th><?php _e("Every multiple of", "wad"); ?></th>
                    <th><?php _e("Unit Price", "wad"); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($quantity_pricing["rules-by-step"] as $rule) {
                    if ($quantity_pricing["type"] == "fixed") {
                        $price = $product_price - $rule["discount"];
                    } else if ($quantity_pricing["type"] == "percentage") {
                        $price = $product_price - ($product_price * $rule["discount"]) / 100;
                    }
                    ?>
                    <tr>
                        <td><?php echo $rule["every"]; ?></td>
                        <td><?php echo wc_price($price); ?></td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
        <?php
    }

    private function get_quantity_pricing_table($product_id, $quantity_pricing, $product_price, $display = false) {
        $style = "";
        if (!$display)
            $style = "display: none;";
        ?>
        <table class="wad-qty-pricing-table" data-id="<?php echo $product_id; ?>" style="<?php echo $style; ?>">
            <thead>
                <tr>
                    <th><?php _e("Min", "wad"); ?></th>
                    <th><?php _e("Max", "wad"); ?></th>
                    <th><?php _e("Unit Price", "wad"); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($quantity_pricing["rules"] as $rule) {
                    if ($quantity_pricing["type"] == "fixed") {
                        $price = $product_price - $rule["discount"];
                    } else if ($quantity_pricing["type"] == "percentage") {
                        $price = $product_price - ($product_price * $rule["discount"]) / 100;
                    }
                    ?>
                    <tr>
                        <td><?php echo $rule["min"]; ?></td>
                        <td><?php echo $rule["max"]; ?></td>
                        <td><?php echo wc_price($price); ?></td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
        <?php
    }

    function get_cart_item_quantities() {
        global $woocommerce;
        $item_qties = array();
        foreach ($woocommerce->cart->cart_contents as $cart_item) {
            if (!empty($cart_item["variation_id"]))
                $item_qties[$cart_item["variation_id"]] = $cart_item["quantity"];
            else
                $item_qties[$cart_item["product_id"]] = $cart_item["quantity"];
        }
        return $item_qties;
    }

    function initialize_used_discounts_array() {
        if (!is_admin() && !wad_is_checkout())
            $_SESSION["active_discounts"] = array();
    }

    function get_variations_prices($prices, $product) {
        foreach ($prices["regular_price"] as $variation_id => $variation_price) {
            $variation = wc_get_product($variation_id);

            $variation_sale_price = $prices["sale_price"][$variation_id];
            $prices["sale_price"][$variation_id] = $this->get_sale_price($variation_sale_price, $variation);

            $variation_price = $prices["price"][$variation_id];
            $prices["price"][$variation_id] = $this->get_sale_price($variation_price, $variation);
        }

        return $prices;
    }

}
