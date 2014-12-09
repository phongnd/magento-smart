function clearInput() {
    jQuery('input[name="payment[cc_owner]"]').val('');
    jQuery('input[name="payment[cc_type]"]').val('');
    jQuery('input[name="payment[cc_number]"]').val('');
    jQuery('input[name="payment[cc_number]"]').next().val('');
    jQuery('input[name="payment[cc_exp_month]"]').val('');
    jQuery('input[name="payment[cc_exp_year]"]').val('');
}

function checkShippingInput() {
    if (jQuery('#exist_customer').is(':checked')) {
        var error = false
        if (jQuery('#order-shipping_address_address_firstname').val() == ''
            || jQuery('#order-shipping_address_address_lastname').val() == ''
            || jQuery('#order-shipping_address_address_street0').val() == ''
            || jQuery('#order-shipping_address_address_city').val() == ''
            || jQuery('#order-shipping_address_address_country_id').val() == ''
            || jQuery('#order-shipping_address_address_region').val() == ''
            || jQuery('#order-shipping_address_address_postcode').val() == ''
            || jQuery('#order-shipping_address_address_telephone').val() == ''
            ) {
        }

        if (error) {
            alert('Your shipping address need to be filled all field!');
            if (jQuery('#order-shipping_same_as_billing').is(':checked'))
                jQuery('#order-shipping_same_as_billing').click()
            return false;
        }
    }

    return true;
}

function show_customer_area(){
    enableBilling(jQuery("#order-billing_same_as_billing"));
    jQuery("#customer_search").hide();
    jQuery("#customer_area").toggle();
}

function change_checkout_mode(){

    if(jQuery("#checkout_mode_button").hasClass("checkout_mode")){
        if (isOnline()){

            var discount_change = jQuery('#discount_change').val();
            if(discount_change == 1){
                var value = jQuery('#discount_value').val();
                change_discount(value);
                jQuery('#discount_change').val(0);
            }
            else{
                order.itemsUpdate();
            }
//            var discount_code = jQuery('#discount_hidden').val();
//            if(discount_code != null && discount_code != "" ){
//                order.itemsUpdateCoupon(discount_code);
//                jQuery('#discount_hidden').val("");
//            }
//            else{
//                order.itemsUpdate();
//            }
        }
        jQuery("#checkout_mode_button").removeClass("checkout_mode");
        jQuery("#checkout_mode_button").addClass("button-checkout");

        jQuery("#overlay_left").show();

        jQuery("#center").hide();
        jQuery("#right").show();
        resizeTabbar();
    }else{
        jQuery("#search-box").focus();
        jQuery("#checkout_mode_button").removeClass("button-checkout");
        jQuery("#checkout_mode_button").addClass("checkout_mode");

        jQuery("#overlay_left").hide();

        jQuery("#right").hide();
        jQuery("#center").show();
    }

}

function check_mode(){
    var mode = "checkout";
    if(jQuery("#checkout_mode_button").hasClass("checkout_mode")){
        mode = "browser";
    }
    return mode;
}

function show_options_checkout(){
    jQuery(".option-panel").toggle();
    if(!jQuery("#options_checkout_button").hasClass("active")){
        var limit = jQuery('#is_user_limited').val();
        jQuery("#options_checkout_button").addClass("active");
        if(limit==0){
            jQuery('.invoice_option').css('display','none');
            jQuery('.shipment_option').css('display','none');
        }
    }else{
        jQuery("#options_checkout_button").removeClass("active");
    }
}

function isAvailability() {
    return jQuery('#network-status').val() == 'online';
}

function isOnline(){
    return jQuery('#network-availability').hasClass('network-online')
}

function IsPopupBlocker() {
    var oWin = window.open("", "testpopupblocker", "width=100,height=50,top=5000,left=5000");
    if (oWin == null || typeof(oWin) == "undefined") {
        return true;
    } else {
        oWin.close();
        return false;
    }
}

if (IsPopupBlocker()){
    alert('Please disable the popup blocker in your browser to be able to print receipt.');
}

function setCheckBox(id) {
    var objId = '#' + id;
    var option_type = "";
    switch(id){
        case "invoice_toggle":
            option_type = "doinvoice";
            break;
        case "shipment_toggle":
            option_type = "doshipment";
            break;
        case "receipt_toggle":
            option_type = "doprintreceipt";
            break;
        case "mail_toggle":
            option_type = "doemailreceipt";
            break;
        default :
            break;
    }

    if (jQuery(objId).hasClass('active')) {
        jQuery(objId).removeClass('active');
        jQuery('#'+option_type).val(0);
    } else {
        jQuery(objId).addClass('active');
        jQuery('#'+option_type).val(1);
    }
}

function show_checkout_area(area){
    switch (area){
        case "payment":
            jQuery(".checkout_area").hide();
            jQuery(".checkout_tab_button").removeClass('active');
            jQuery("#payment_tab_button").addClass('active');
            jQuery("#billing_method_area").show();
            break;
        case "shipping":
            jQuery(".checkout_area").hide();
            jQuery(".checkout_tab_button").removeClass('active');
            jQuery("#shipping_tab_button").addClass('active');
            jQuery("#shipping_method_area").show();
            break;
        case "discount":
            jQuery(".checkout_area").hide();
            jQuery(".checkout_tab_button").removeClass('active');
            jQuery("#discount_tab_button").addClass('active');
            jQuery("#coupon_area").show();
            break;
        default :
            break;
    }
}

function show_customer_search(){
    jQuery("#customer_options").toggleClass('active');
    jQuery("#customer_area").hide();
    jQuery("#customer_search").toggle();
}

function hide_message(){
    jQuery('#order-message').hide();
}

function initScroll(area){
    jQuery(area).niceScroll();
    if(area == "#items_area"){
        jQuery(area).attr( "style", "" );
        jQuery(area).attr( "style", "overflow: hidden;" );
    }
}

function show_menu_item_sidebar(offline_mode){
    if(offline_mode){
        jQuery('#sidebar_menu_items li').hide();
        jQuery('#manual_reload_button').hide();
    }else{
        jQuery('#sidebar_menu_items li').show();
        jQuery('#manual_reload_button').show();
        if(jQuery("#customer_type").val() != 'guest'){
            jQuery('.list-customer').removeClass('disabled');
            var limit = jQuery('#is_user_limited').val();
            if(limit==0) jQuery('.list-customer').addClass('disabled');
        }
    }
}

function manual_reload(){
    $.jStorage.flush();
    location.reload(true);
}

function auto_select_field(){
    jQuery('.item-price, .item-qty, input[name="coupon_code"]').click(
        function () {
            jQuery(this).select();
            createSelection(0, 10, this);
        }
    );
}

function addCommas(nStr) {
    nStr += '';
    var x = nStr.split('.');
    var x1 = x[0];
    var x2 = x.length > 1 ? '.' + x[1] : '';
    if(x2 == ''){
        x2 = '.00';
    }
    var rgx = /(\d+)(\d{3})/;
    while (rgx.test(x1)) {
        x1 = x1.replace(rgx, '$1' + ',' + '$2');
    }
    return x1 + x2;
}

//move from payment.phtml
function select_payment_method(payment_code) {
    jQuery("#payment_method_hidden").val(payment_code);
    jQuery("#payment_method_hidden").removeAttr('disabled');
    updateTotals();
    show_payment_block(payment_code);
    swipeNow(payment_code);
    resizeTabbar();
    window.payment_method_code = payment_code
}

function show_payment_block(payment_code) {
    switch (payment_code) {
        case "ccsave":
            jQuery(".ccsave-block").show();
            break;
    }
    jQuery("#method_area").getNiceScroll().resize();
}

function resizeTabbar(){
    jQuery('#payment_tab_button').css('height','auto');
    var max = Math.max.apply(Math, jQuery(".checkout_tab_button").map(
        function(){
            return jQuery(this).height();
        }
    ));
    jQuery("#checkout_method_bar").each(function() {
        jQuery(this).find(".checkout_tab_button").height(max);
    });
}

function validateCreditCar() {
    if (window.payment_method_code == 'ccsave'){
        var valid, result;
        valid = new Validation('payment_form_ccsave', {onSubmit:false});
        result =  valid.validate();
        return result;
    }
    return true;
}

jQuery(document).ready(function(){
    jQuery("#messages").html('');

    jQuery('#search-box').on('keypress', function (event) {
        if(event.which == '13'){
            //createSelection(0, 99, this);
        }
    });

    jQuery("#search-box").focus(function(){
        var mode = check_mode();
        if(mode == 'checkout'){
            jQuery("#checkout_mode_button").removeClass("button-checkout");
            jQuery("#checkout_mode_button").addClass("checkout_mode");
            jQuery("#overlay_left").hide();
            jQuery("#right").hide();
            jQuery("#center").show();
        }
    });

    if(jQuery('#keyboard_shortcut').val() == 1){
        Mousetrap.bind(['o'], function(e) {
            if(!orderListLoaded){
                showOrderList();
            }else{
                orderListLoaded = false;
                jQuery('.popup-list-order').bPopup().close();
            }
        });
        Mousetrap.bind(['c'], function(e) {
            cancelOrder();
        });
        Mousetrap.bind(['m'], function(e) {
            change_checkout_mode();
        });
        Mousetrap.bind(['q'], function(e) {
            xpos_user_logout();
        });
        Mousetrap.bind(['t'], function(e) {
            if(!transactionMoneyLoaded){
                showTransactionList();
            }else{
                transactionMoneyLoaded = false;
                jQuery('.popup-list-transaction').bPopup().close();
            }
        });
        Mousetrap.bind(['p'], function(e) {
            if(check_mode() == 'browser'){
                change_checkout_mode();
            }
            show_checkout_area('payment')
        });
        Mousetrap.bind(['s'], function(e) {
            if(check_mode() == 'browser'){
                change_checkout_mode();
            }
            show_checkout_area('shipping')
        });
        Mousetrap.bind(['d'], function(e) {
            if(check_mode() == 'browser'){
                change_checkout_mode();
            }
            show_checkout_area('discount')
        });
        Mousetrap.bind(['enter'], function(e) {
            if(check_mode() == 'checkout'){
                var checkoutcomfirm = jQuery('#checkoutcomfirm').hide();
                xpos_checkout(checkoutcomfirm);
            }
        });
        Mousetrap.bind('up up down down left right left right b a enter', function() {
            alert('X-POS v3.3');
        });
    }
});