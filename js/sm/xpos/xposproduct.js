(function (jQuery) {
    jQuery(document).ready(function () {
        productData = prepareProductData();
        var orders = $.jStorage.get("orders");
        if (orders!=null){
            jQuery('#count_pending_orders').html(orders.length);
        }

        //search base jquery autocomplete
        jQuery('#search-box').keyup( function (event) {
            delay(function(){
                var alert_box_stt = jQuery('#alert_box_stt').val();
                if(alert_box_stt == 1){
                    jQuery('#alert_box_stt').val('0');
                    return;
                }
                if(event.ctrlKey) return;
                var term = jQuery('#search-box').val();
                var lucky_search = parseInt(jQuery('#lucky_search').val());
                var last_key_search = jQuery('#last_key_search').val();
                if(term == last_key_search){
                    if(event.which ==13){
                        var data = $.jStorage.get('searchData');
                        if (data && data.length == 1) {
                            if(lucky_search ==1){
                                addToCart(data[0].id);
                                //window.product_cart_id = data[0].id;
                            }
                            document.getElementById("search-box").select();
                            return;
                        }
                    }

                }

                //alert(term);
                jQuery("#product-info").empty();
                //jQuery('#search-box').val('');
                if (offlineSearch && term!=""){
                    jQuery('#last_key_search').val(term);
                    //var data = search(term, productData);
                    term = term.toLowerCase();
                    var result = [];
                    var number_result = jQuery("#result_number_search").val();
                    jQuery.each(productData, function (i, item) {
                        if(item['name'] == null || item['sku'] == null){
                            return false;
                        }
                        if (result.length < number_result &&
                            (item['name'].toLowerCase().match(term) || item['sku'].toLowerCase().match(term) ||
                                (item['searchString'] != null && item['searchString'].toLowerCase().match(term)))) {
                            result.push(item);
                        }
                    });
                    if (result.length == 1) {
                        if(event.which ==13){
                            if(lucky_search ==1){
                                addToCart(result[0].id);
                            }
                            document.getElementById("search-box").select();
                        }
                    }
                    var data= result;
                    if(data.length ==1)
                        $.jStorage.set('searchData', data);
                    else $.jStorage.set('searchData', null);
                    if (data.length == 0) {
                        jQuery('#category-selected').text("No search result for : " + term);
                        jQuery('#category-selected').append('<i>icon</i>');
                        if ( event.which != 13 ) {
                            return;
                        }
                        else
                            document.getElementById("search-box").select();
                        return;
                    }

                    var count_rs=0;
                    jQuery.each(data, function (i, item) {
                        displayProductItem(item, true);
                        count_rs++;
                    });
                    var result_string  = "result";
                    if(count_rs > 1){
                        result_string  = "results";
                    }
                    jQuery('#category-selected').text(count_rs+ ' ' + result_string + ' for: ' + term);
                    jQuery('#category-selected').append('<i>icon</i>');
                    initScroll("#product-info");

                    if (data.length >1) {
                        if ( event.which != 13 ) {
                            return;
                        }
                        else
                            document.getElementById("search-box").select();
                        return;
                    }
                }else{
                    if(isOnline() && term!="" ){
                        jQuery('#last_key_search').val(term);
                        jQuery('#category-selected').text("Searching online result for : " + term);
                        jQuery('#category-selected').append('<i>icon</i>');

                        var url1 = searchProductUrl+"?query="+term;
                        var url = url1.replace("?___SID=U","");
                        jQuery.getJSON(url, function (json) {
                            var data_search = json;
                            var productInfo = data_search['productInfo'];
                            var lstProductId = [];
                            if(productInfo != null)
                                jQuery.each(productInfo, function (i, item) {
                                    lstProductId.push(item.id);
                                    if(productData[item.id] == null){
                                        productData[item.id] = item;
                                        $.jStorage.set('productData', productData);
                                    }
                                });
                            //$.jStorage.set('productData', productData);
                            var count = 0;
                            for(jsonObj in productInfo){
                                if(productInfo.hasOwnProperty(jsonObj)){
                                    count++;
                                }
                            }
                            var data = [];
                            jQuery("#product-info").empty();
                            if(count>0)
                                for (var i = 0;i< lstProductId.length;i++){
                                    data.push(productData[lstProductId[i]]);
                                }
                            if(data.length ==1)
                                $.jStorage.set('searchData', data);
                            else $.jStorage.set('searchData', null);
                            if (data.length == 0) {
                                jQuery('#category-selected').text("No search result for : " + term);
                                jQuery('#category-selected').append('<i>icon</i>');
                                if ( event.which != 13 ) {
                                    return;
                                }
                                else
                                    document.getElementById("search-box").select();
                                return;
                            }

                            var count_rs=0;
                            jQuery.each(data, function (i, item) {
                                displayProductItem(item, true);
                                count_rs++;
                            });

                            var result_string  = "result";
                            if(count_rs > 1){
                                result_string  = "results";
                            }
                            jQuery('#category-selected').text(count_rs+ ' ' + result_string + ' for: ' + term);
                            jQuery('#category-selected').append('<i>icon</i>');
                            initScroll("#product-info");

                            if (data.length == 1) {
                                if ( event.which != 13 ) {
                                    return;
                                }
                                else{
                                    if(lucky_search ==1){
                                        addToCart(data[0].id);
                                        //window.product_cart_id = data[0].id;
                                    }
                                    document.getElementById("search-box").select();
                                }

                            }
                            if(data.length > 1){
                                if ( event.which != 13 ) {
                                    return;
                                }
                                else
                                    document.getElementById("search-box").select();
                            }
                        })
                    }
                }
            },800);

//            if ( event.which != 13 ) {
//                return;
//            }

        });
        jQuery('body').on("keydown",'.item-qty',function(event){
            if(jQuery(this).attr('class').indexOf('qty_decimal') == -1  && event.keyCode == 190 )
                event.preventDefault();

            if(jQuery(this).attr('class').indexOf('qty_decimal') == -1  && event.keyCode == 188 )
                event.preventDefault();
        });

        jQuery('body').on("keydown",'.item-price',function(event){

            if (event.shiftKey == true) {
                // event.preventDefault(); allow to enter % char
            }

            if ((event.keyCode >= 48 && event.keyCode <= 57) ||
                (event.keyCode >= 96 && event.keyCode <= 105) ||
                event.keyCode ==16 ||
                event.keyCode == 8 || event.keyCode == 9 || event.keyCode == 37 ||
                event.keyCode == 39 || event.keyCode == 46 || event.keyCode == 190 || event.keyCode == 110 || event.keyCode == 188) {

            } else {
                event.preventDefault();
            }
//            if((jQuery(this).attr('class').indexOf('qty_decimal') != -1 && jQuery(this).attr('class').indexOf('item-qty') != -1) && event.keyCode == 190 )
//                event.preventDefault();
            if((jQuery(this).val().indexOf('.') !== -1 || jQuery(this).val().indexOf(',') !== -1) && event.keyCode == 190)
                event.preventDefault();

            if((jQuery(this).val().indexOf(',') !== -1 || jQuery(this).val().indexOf('.') !== -1 ) && event.keyCode == 188)
                event.preventDefault();

            if((jQuery(this).val().indexOf(',') !== -1 || jQuery(this).val().indexOf('.') !== -1 ) && event.keyCode == 110)
                event.preventDefault();

//            if(jQuery(this).val().indexOf('.') !== -1 && event.keyCode == 110)
//                event.preventDefault();
        });

    });
})(jQuery);

var delay = (function(){
    var timer = 0;
    return function(callback, ms){
        clearTimeout (timer);
        timer = setTimeout(callback, ms);
    };
})();


var data;
var page = 1;
var totalLoaded = 0;
function showCategoryDefault(){
    productData = prepareProductData();
    //show product default category in xpos, should be place after prepare productdata
    var default_cate = jQuery("#cate_default").val();
    var cate_default_name = jQuery("#cate_default_name").val();
    if(default_cate!=undefined){
        displayProduct(default_cate,0);
        initScroll("#product-info");
        // alert(cate_default_name);
        jQuery('#category-selected').text(cate_default_name);
        jQuery('#category-selected').append('<i>icon</i>');
        if (jQuery('#category-selected').hasClass("hide")) {
            jQuery('#category-list').slideDown('slow');
            jQuery('#category-selected').removeClass('hide').addClass('show');
        } else {
            jQuery('#category-list').slideUp('slow');
            jQuery('#category-selected').removeClass('show').addClass('hide');
        }
    }
}
function getData() {
    var warehouseId = $.jStorage.get('xpos_warehouse');
    var data_load_interval = jQuery('#data_load_interval').val() * 1000;
    var append = '';
    if (warehouseId!=null){
        append='?warehouse='+warehouseId.warehouse_id;
    }

    jQuery.getJSON(loadProductUrl+append, function (json) {

        data = json;
        var totalLoad = data['totalLoad'];
        var totalProduct = data['totalProduct'];
        totalLoaded += totalLoad;

        saveData(data, page);
        var status = 'Updated ' + totalLoaded + '/' + totalProduct +' products. Saved:'+Object.keys(productData).length ;
        jQuery('#status').text(status);
        var oldParam = 'page/' + page;
        page++;
        if (totalLoad == 0) {
            page = 1;
            totalLoaded = 0;
        }
        var newParam = 'page/' + page;
        loadProductUrl = loadProductUrl.replace(oldParam, newParam);
        if(window.show_cate_default==null)
            window.show_cate_default = 1;
        if(window.show_cate_default==1){
            showCategoryDefault();
            window.show_cate_default=0;
        }
        setTimeout(function () {
            getData();
        }, data_load_interval);
    });
}

function saveData(data, page) {
    var productInfo = data['productInfo'];
    jQuery.each(productInfo, function (i, item) {
        productData[item.id] = item;
    });
    $.jStorage.set('productData', productData);
}

function prepareProductData() {
    var data = {};
    var storageList = $.jStorage.get('productData');
    if (storageList !=null && storageList != undefined){
        data = storageList;
    }
    return data;
}

function search(term, productData) {
    term = term.toLowerCase();
    var result = [];
    jQuery.each(productData, function (i, item) {
        if (result.length < 51 &&
            (item['name'].toLowerCase().match(term) || item['sku'].toLowerCase().match(term) ||
                (item['searchString'] != null && item['searchString'].toLowerCase().match(term)))) {
            result.push(item);
        }
    })

    if (result.length == 1) {
        addToCart(result[0].id);
    }
    return result;
}

var productTemplate = '<li><div class="product-wrapper"><span class="{type}"></span><a class="product" id="product-{id}" href="#" onclick="addToCart({id})"><img src="{small_image}"><div class="price"><span>{finalPrice}<span></div><span style="margin-top: 4px; margin-bottom: -14px;" class="name" >{name1}</span><span style="-o-text-overflow: ellipsis;text-overflow:ellipsis; white-space:nowrap;overflow:hidden;margin-bottom:-14px; " class="name">{name2}</span></a></div></li>';
var productSearchTemplate = '<li><div class="product-wrapper"><span class="{type}"></span><a class="product" id="product-{id}" href="#" onclick="addToCart({id})"><img src="{small_image}"><div class="price"><span>{finalPrice}<span></div><span style="margin-top: 4px; margin-bottom: -14px;" class="name" >{name1}</span><span style="-o-text-overflow: ellipsis;text-overflow:ellipsis; white-space:nowrap;overflow:hidden;margin-bottom:-14px; " class="name">{name2}</span></a></div></li>';// the last div : <span class="name">{sku} - {name}</span></a>
function displayProduct(category, page) {
    var productList = getProductByCategory(category,page);
    jQuery("#product-info").attr('data', category);
    jQuery("#product-info").attr('page', page);
    jQuery("#product-info").empty();
    jQuery.each(productList, function (i, item) {
        displayProductItem(item, false);
    });
    initScroll("#product-info");
}

function getProductByCategory(category,page){
    var result = [];
    var p = categoryData[category];
    for (var i = 0;i< p.length;i++){
        result.push(productData[p[i]]);
    }
    return result;
}
//using nano to append product, isSearch = true to show in search mode, false in normal mode
function displayProductItem(item, isSearch) {
    if (item == null || item == undefined || item.sku == null || item.finalPrice == null){
        return;
    }
    //convert name
    var pro_name = item.name;
    var length = pro_name.length;
    if(length>10){
        var name1 = pro_name.substring(0,10);
        var next_char = pro_name.substring(10,11);
        if(next_char!= ' '){
            var index_last_space = name1.search(/ [^ ]*$/);
            name1 = pro_name.substring(0,index_last_space);
            var name2 = pro_name.substring(index_last_space,length);

        }else{
            var name2 = pro_name.substring(10,length);
        }
    }
    else name1 = pro_name;

//    var new_name = '';
//    if(length>25){
//        new_name = pro_name.substring(0,24);
//        new_name = new_name+ '...';
//    }
//    else new_name = pro_name;

    var productItem = {};
    productItem.type = item.type;
    productItem.id = item.id;
    productItem.small_image = item.small_image;
    productItem.name1 = name1;
    productItem.name2 = name2;

    //productItem.name = item.name;
    productItem.sku = item.sku;

    productItem.finalPrice = Number(item.finalPrice).toFixed(2);
    if (displayTaxInCatalog){
        if(productItem.type != "giftcard")
            productItem.finalPrice = Number(item.priceInclTax).toFixed(2);
    }
    if (isSearch) {
        jQuery("#product-info").append(nano(productSearchTemplate, productItem).replace('<img src="">', placeholder));
    } else {
        jQuery("#product-info").append(nano(productTemplate, productItem).replace('<img src="">', placeholder));
    }
}


function addToCart(itemId) {
    var item = productData[itemId];
    if (item.options) {
        var tax_percent = 0;
        if(item.tax != ''){
            tax_percent = item.tax;
        }
        //show config panel
        optionsPrice = new Product.OptionsPrice({
            "productId": itemId,
            "priceFormat": priceFormat,
            "showBothPrices": true,
            "productPrice": item.productPrice,
            "productOldPrice": item.price,
            "priceInclTax": item.price,
            "priceExclTax": item.price,
            "skipCalculate": 0,
            "defaultTax": tax_percent,
            "currentTax": tax_percent,
            "idSuffix": "_clone",
            "oldPlusDisposition": 0,
            "plusDisposition": 0,
            "plusDispositionTax": 0,
            "oldMinusDisposition": 0,
            "minusDisposition": 0,
            "tierPrices": [],
            "tierPricesInclTax": [],
            "includeTax": item.includeTax
        });
        if (item.type == 'grouped'){
            optionsPrice.productPrice = 0;
            optionsPrice.productOldPrice = 0;
            optionsPrice.priceInclTax = 0;
            optionsPrice.priceExclTax = 0;
        }
        jQuery('#product-option').empty();
        jQuery('#product-option').append('<div class="price-box">Price: <span id="price-excluding-tax-' + itemId + '" class="regular-price">' + item.finalPrice + '</span><span class="including-tax"> Incl. Tax: </span><span id="price-including-tax-' + itemId + '" class="regular-price">' + item.finalPrice + '</span></div>');
        jQuery('#product-option').append(item.options);
        jQuery('#product-option').append('<div class="action"><button id="cancel" class="button cancel" type="button"><span>Cancel</span></button><button id="ok" class="ok" type="submit" ><span>OK</span></button></div>');
        jQuery('#product-option-form').bPopup({closeClass: 'button'});
        var productCompositeConfigureForm = new varienForm('product-option');
        jQuery("#product-option").unbind();

        if(item.type=='bundle'){
            ProductConfigure.bundleControl.reloadPrice(); // config price at the first time if user don't select option
        }

        jQuery("#product-option").submit(function( event ) {
            if (productCompositeConfigureForm.validate()){
                jQuery('#product-option-form').bPopup().close();
                addConfigurableProduct(itemId);
                var term = jQuery('#search-box').val();
                if(term.length>0) document.getElementById("search-box").select();
            }
            event.preventDefault();
        });
        optionsPrice.reload();
    } else {
        addOrder(itemId, []);
        displayOrder(currentOrder, true);
    }
    auto_select_field();
    jQuery('#product-info li').removeClass('active');
    jQuery('#product-' + itemId).parent().parent().addClass('active');
    console.log("add to cart " + itemId);
    initScroll("#items_area");
    var term = jQuery('#search-box').val();
    if(term.length>0) document.getElementById("search-box").select();
}

//change product bundle
function changeProductBundle(itemId,id_row) {
    var item = productData[itemId];
    if (item.options) {
        //show config panel
        optionsPrice = new Product.OptionsPrice({"productId": itemId, "priceFormat": priceFormat, "showBothPrices": true, "productPrice": item.finalPrice, "productOldPrice": item.price, "priceInclTax": item.price, "priceExclTax": item.price, "skipCalculate": 0, "defaultTax": 8.25, "currentTax": 8.25, "idSuffix": "_clone", "oldPlusDisposition": 0, "plusDisposition": 0, "plusDispositionTax": 0, "oldMinusDisposition": 0, "minusDisposition": 0, "tierPrices": [], "tierPricesInclTax": []});
        if (item.type == 'grouped'){
            optionsPrice.productPrice = 0;
            optionsPrice.productOldPrice = 0;
            optionsPrice.priceInclTax = 0;
            optionsPrice.priceExclTax = 0;
        }
        jQuery('#product-option').empty();
        jQuery('#product-option').append('<div class="price-box">Price: <span id="price-excluding-tax-' + itemId + '" class="regular-price">' + item.finalPrice + '</span><span class="including-tax"> Incl. Tax: </span><span id="price-including-tax-' + itemId + '" class="regular-price">' + item.finalPrice + '</span></div>');
        jQuery('#product-option').append(item.options);
        jQuery('#product-option').append('<div class="action"><button id="cancel" class="button cancel" type="button"><span>Cancel</span></button><button id="ok" class="ok" type="submit" ><span>OK</span></button></div>');
        jQuery('#product-option-form').bPopup({closeClass: 'button'});
        var productCompositeConfigureForm = new varienForm('product-option');
        jQuery("#product-option").unbind();
        jQuery("#product-option").submit(function( event ) {
            if (productCompositeConfigureForm.validate()){
                removeFromCat(id_row);
                jQuery('#product-option-form').bPopup().close();
                addConfigurableProduct(itemId);
            }
            event.preventDefault();
        });
        optionsPrice.reload();
    } else {
        addOrder(itemId, []);
        displayOrder(currentOrder, true);
    }
    auto_select_field();
    jQuery('#product-info li').removeClass('active');
    jQuery('#product-' + itemId).parent().parent().addClass('active');
    console.log("add to cart " + itemId);
    initScroll("#items_area");
}
//end change product bundle

function addConfigurableProduct(productId) {
    var options = jQuery('#product-option').serializeArray();
    addOrder(productId, options);
    displayOrder(currentOrder, true);
}

//create order item from itemId, option; auto create new if not find
function addOrder(productId, options) {
    // console.log(options);
    var product = productData[productId];
    if(product.type =='giftcard' )
    {
        if(options[0]['name'] =='custom_giftcard_amount' || options[0]['name'] == 'giftcard_amount' )
        {
            productData[productId].price = parseFloat(options[0]['value']);
            productData[productId].priceInclTax = parseFloat(options[0]['value']);
            productData[productId].finalPrice = parseFloat(options[0]['value']);
        }
    }
    var product = productData[productId];
    // console.log(product.price);
    var oldOrderItem = null;
    var tempOrder = Object.keys(currentOrder);
    for (var i = 0; i < tempOrder.length; i++) {
        var tempOrderItem = currentOrder[tempOrder[i]];
        if (tempOrderItem.productId == productId) {
            oldOrderItem = tempOrderItem;
            if (options.length > 0) {
                //check option
                var currentProductOption = oldOrderItem.options;
                if (currentProductOption.compare(options)) {
                    oldOrderItem.qty++;
                    if (oldOrderItem.qty > product.qty && product.type == "simple"){
                        jQuery('#alert_box_stt').val('1');
                        alert('Current qty greater than stock qty of this product');
                    }
                    return;
                }
            }else{
                oldOrderItem.qty++;
                if (oldOrderItem.qty > product.qty && product.type == "simple"){
                    jQuery('#alert_box_stt').val('1');
                    alert('Current qty greater than stock qty of this product');
                }
                return;
            }
        }
    }

    //create order item
    var newOrderItem = {};
    newOrderItem.pos = Object.keys(currentOrder).length;
    newOrderItem.id = product.id+'-'+newOrderItem.pos;
    newOrderItem.productId = product.id;
    newOrderItem.name = product.name;
    newOrderItem.sku = product.sku;
    newOrderItem.tax = product.tax;
    newOrderItem.is_qty_decimal = product.is_qty_decimal;
    if (isNaN(newOrderItem.tax)) {
        newOrderItem.tax = 0;
    }
    newOrderItem.finalPrice = product.finalPrice;
    newOrderItem.price = product.price;
    newOrderItem.priceInclTax = product.priceInclTax;
    if (options.length > 0 && product.type != 'giftcard') {
        newOrderItem.price = parseFloat(jQuery('#price-excluding-tax-' + product.id).text().replace(priceFormat.groupSymbol, ''));
        if(product.type != 'bundle')
            newOrderItem.priceInclTax = parseFloat(jQuery('#price-including-tax-' + product.id).text().replace(priceFormat.groupSymbol, ''));
        else
            newOrderItem.priceInclTax = newOrderItem.price;
    }
    if (product.type=='grouped' && newOrderItem.price == 0){
        alert('You must select at least one product to add to cart');
        addToCart(productId);
        return;
    }
    //newOrderItem.type="";
    newOrderItem.type=product.type;
    newOrderItem.options = options;
    if (product.type=='bundle'){
        ProductConfigure.bundleControl.getOption(options,null);
    }else if (product.type=='configurable'){
        getConfigurableOption(options,config.attributes);
    }
    newOrderItem.qty = 1;
    if (product.qty <= 0 && product.type == 'simple'){
        newOrderItem.out_stock = 1;
        jQuery('#alert_box_stt').val('1');
        alert('Currently this product is out of stock');
    }
    currentOrder[newOrderItem.id] = newOrderItem;
}

function removeFromCat(orderId) {
    var orderItem = currentOrder[orderId];
    orderItem.qty = 0;
    if (orderId.indexOf('-')>0){
        delete currentOrder[orderId];
    }
    displayOrder(currentOrder, true);
}

function changeQty(orderId) {
    var orderItem = currentOrder[orderId];
    var string = jQuery('#item-qty-' + orderId).val();
    var value = string.replace(',','.');
    // var newQty = parseFloat(jQuery('#item-qty-' + orderId).val()).toFixed(2);
    // var is_decimal = jQuery(this).attr('class').indexOf('qty_decimal') == -1
    var class_name = jQuery('#item-qty-' + orderId).attr('class');
    if(class_name.indexOf('qty_decimal') == -1)
        var newQty = parseInt(value);
    else
        var newQty = parseFloat(value);
    if (newQty <= 0 || isNaN(newQty)) {
        newQty = orderItem.qty;
        jQuery('#item-qty-' + orderId).val(newQty);
    }
    orderItem.qty = newQty;

    var product = productData[orderItem.productId];
    var current = parseInt(orderItem.qty);
    var stock = parseInt(product.qty);
    if (current > stock && product.type == 'simple'){
        alert('Current quantity greater than stock quantity of this product');
    }
    displayOrder(currentOrder, true);
}

function changeQtyReload(orderId,qty_saved) {
    var orderItem = currentOrder[orderId];
    var string = qty_saved;
    var value = string.replace(',','');
    var class_name = jQuery('#item-qty-' + orderId).attr('class');
    if(class_name.indexOf('qty_decimal') == -1)
        var newQty = parseInt(value);
    else
        var newQty = parseFloat(value).toFixed(2);
    if (newQty < 1 || isNaN(newQty)) {
        newQty = orderItem.qty;
        jQuery('#item-qty-' + orderId).val(newQty);
    }
    orderItem.qty = newQty;

    var product = productData[orderItem.productId];
    var current = parseInt(orderItem.qty);
    var stock = parseInt(product.qty);
//    if (current > stock && product.type == 'simple'){
//        alert('Current quantity greater than stock quantity of this product');
//    }
    displayOrder(currentOrder, true);
}

function changePriceReload(orderId,price){
    var orderItem = currentOrder[orderId];
    var newPrice = price;
    if (newPrice < 0 || isNaN(newPrice) || !onFlyDiscount) {
        newPrice = orderItem.price;
        jQuery('#item-price-' + orderId).val(newPrice);
        return;
    }

    if(jQuery('#price_includes_tax').val() == 0){
        orderItem.price = newPrice;
        orderItem.priceInclTax = orderItem.price * (1 + orderItem.tax / 100);
    }else{
        orderItem.priceInclTax = newPrice;
        orderItem.price = Math.round(orderItem.priceInclTax / (1 + orderItem.tax / 100) *100)/100 ;
    }

    orderItem.price_name = 'item['+orderId+'][custom_price]';
    jQuery('#item-price-' + orderId).val(newPrice);
    displayOrder(currentOrder, true);

}

function changePrice(orderId) {
    var orderItem = currentOrder[orderId];
    var string = jQuery('#item-display-price-' + orderId).val();
    var string_convert = string.replace(',','.');
    var newPrice = parseFloat(string_convert).toFixed(2);
    // jQuery('#item-price-' + orderId).val(jQuery('#item-display-price-' + orderId).val());
    jQuery('#item-price-' + orderId).val(newPrice);
    if (newPrice < 0 || isNaN(newPrice)) {
        newPrice = orderItem.price;
        jQuery('#item-display-price-' + orderId).val(newPrice);
        jQuery('#item-price-' + orderId).val(newPrice);
    }
    var string = jQuery('#item-display-price-' + orderId).val();
    //var newPrice = (jQuery('#item-display-price-' + orderId).val());
    newPrice = string.replace(',','');

    var split = newPrice.split("%");
    if(split.length==2){
        var value = parseFloat(split[0]);
        if (value < 0 || isNaN(value) || !onFlyDiscount || value>100) {
            value = orderItem.price;
            jQuery('#item-price-' + orderId).val(value);
            return;
        }
        //var percent = value%orderItem.price;
        var newValue = (100-parseFloat(value))*0.01*orderItem.price;
        jQuery('#item-price-' + orderId).val(newValue);
        if(jQuery('#price_includes_tax').val() == 0){
            orderItem.price = newValue;
            orderItem.priceInclTax = orderItem.price * (1 + orderItem.tax / 100);
        }else{
            orderItem.priceInclTax = newValue;
            orderItem.price = Math.round(orderItem.priceInclTax / (1 + orderItem.tax / 100) *100)/100 ;
        }

        orderItem.price_name = 'item['+orderId+'][custom_price]';

        displayOrder(currentOrder, true);
        return;
    }else{
        var newPrice = parseFloat(jQuery('#item-price-' + orderId).val());
        if (newPrice < 0 || isNaN(newPrice) || !onFlyDiscount) {
            newPrice = orderItem.price;
            jQuery('#item-price-' + orderId).val(newPrice);
            return;
        }

        if(jQuery('#price_includes_tax').val() == 0){
            orderItem.price = newPrice;
            orderItem.priceInclTax = orderItem.price * (1 + orderItem.tax / 100);
        }else{
            orderItem.priceInclTax = newPrice;
            orderItem.price = Math.round(orderItem.priceInclTax / (1 + orderItem.tax / 100) *100)/100 ;
        }

        orderItem.price_name = 'item['+orderId+'][custom_price]';
        displayOrder(currentOrder, true);
    }
    if(priceFormat.decimalSymbol == ','){
        newPrice = number_format(newPrice, 2, '.', '');
    }

    jQuery('#item-price-' + orderId).attr('value', newPrice);

//    orderItem.price = newPrice;
//    orderItem.priceInclTax = orderItem.price * (1 + orderItem.tax / 100);
//    orderItem.price_name = 'item['+orderId+'][custom_price]';
//    displayOrder(currentOrder, true);
}

function number_format(number, decimals, dec_point, thousands_sep) {

    // http://kevin.vanzonneveld.net
    // +   original by: Jonas Raoni Soares Silva (http://www.jsfromhell.com)
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +     bugfix by: Michael White (http://getsprink.com)
    // +     bugfix by: Benjamin Lupton
    // +     bugfix by: Allan Jensen (http://www.winternet.no)
    // +    revised by: Jonas Raoni Soares Silva (http://www.jsfromhell.com)
    // +     bugfix by: Howard Yeend
    // +    revised by: Luke Smith (http://lucassmith.name)
    // +     bugfix by: Diogo Resende
    // +     bugfix by: Rival
    // +      input by: Kheang Hok Chin (http://www.distantia.ca/)
    // +   improved by: davook
    // +   improved by: Brett Zamir (http://brett-zamir.me)
    // +      input by: Jay Klehr
    // +   improved by: Brett Zamir (http://brett-zamir.me)
    // +      input by: Amir Habibi (http://www.residence-mixte.com/)
    // +     bugfix by: Brett Zamir (http://brett-zamir.me)
    // +   improved by: Theriault
    // +   improved by: Drew Noakes
    // *     example 1: number_format(1234.56);
    // *     returns 1: '1,235'
    // *     example 2: number_format(1234.56, 2, ',', ' ');
    // *     returns 2: '1 234,56'
    // *     example 3: number_format(1234.5678, 2, '.', '');
    // *     returns 3: '1234.57'
    // *     example 4: number_format(67, 2, ',', '.');
    // *     returns 4: '67,00'
    // *     example 5: number_format(1000);
    // *     returns 5: '1,000'
    // *     example 6: number_format(67.311, 2);
    // *     returns 6: '67.31'
    // *     example 7: number_format(1000.55, 1);
    // *     returns 7: '1,000.6'
    // *     example 8: number_format(67000, 5, ',', '.');
    // *     returns 8: '67.000,00000'
    // *     example 9: number_format(0.9, 0);
    // *     returns 9: '1'
    // *    example 10: number_format('1.20', 2);
    // *    returns 10: '1.20'
    // *    example 11: number_format('1.20', 4);
    // *    returns 11: '1.2000'
    // *    example 12: number_format('1.2000', 3);
    // *    returns 12: '1.200'
    var n = !isFinite(+number) ? 0 : +number,
        prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
        sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
        dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
        toFixedFix = function (n, prec) {
            // Fix for IE parseFloat(0.55).toFixed(0) = 0;
            var k = Math.pow(10, prec);
            return Math.round(n * k) / k;
        },
        s = (prec ? toFixedFix(n, prec) : Math.round(n)).toString().split('.');
    if (s[0].length > 3) {
        s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
    }
    if ((s[1] || '').length < prec) {
        s[1] = s[1] || '';
        s[1] += new Array(prec - s[1].length + 1).join('0');
    }
    return s.join(dec);
}

var orderTemplate = '<tr class="hover {class_item}-{id}" {style}><td><div class="item-first"><a href="#" class="remove" onclick="removeFromCat(\'{id}\')"></a><h5 class="hover1 {config_change}-{id}">{name}</h5>{option}{out_stock}</div></td><td class="qty align-center"><input id="item-qty-{id}" maxlength="12" value="{qty}" class="item-price input-text item-qty {change_qty} {is_qty_decimal}" name="item[{id}][qty]" onclick="check_edit_product(\'{id}\')" onchange="changeQty(\'{id}\')"></td><td class="tax align-center">{tax}%</td><td class="price align-right"><input onclick="check_edit_product(\'{id}\')" id="item-display-price-{id}" maxlength="12" value="{price_display}" class="item-price input-text {edit_price} {change_qty} " onchange="changePrice(\'{id}\')">  <input id="item-price-{id}" class="item-original-price item-price {edit_price}" name="{price_name}" type="text" value="{price}" /> </td><td class="subtotal align-right"><div class="subtotal-list">{subtotal}<span></span></div></td></tr>';
var noDiscountTemplate = '<tr class="hover {class_item}" {style}><td><div class="item-first"><a href="#" class="remove" onclick="removeFromCat(\'{id}\')"></a><h5>{name}</h5>{option}</div></td><td class="qty align-center"><input id="item-qty-{id}" maxlength="12" value="{qty}" class="input-text item-qty" name="item[{id}][qty]" onchange="changeQty(\'{id}\')"></td><td class="tax align-center">{tax}%</td><td class="price align-right"><span class="no-change">{price}</span></td><td class="subtotal align-right"><div class="subtotal-list">{subtotal}<span></span></div></td></tr>';
function displayOrder(order, updatePrice) {
    jQuery('#order-items_grid table tbody').empty();
    var itemNumber = parseFloat(0);
    var subtotal = parseFloat(0);
    var subtotalInclTax = parseFloat(0);

    jQuery.each(order, function (i, orderItem) {
        //calc
        orderItem.subtotal = orderItem.price * orderItem.qty;
        //console.log(orderItem);
        if(orderItem.type == 'giftcard')
            orderItem.subtotalInclTax = orderItem.subtotal;
        else
            orderItem.subtotalInclTax = orderItem.priceInclTax * orderItem.qty;

        var orderOption = '';
        var edit_price='';
        var change_qty = '';
        var out_stock = '';
        var is_qty_decimal= '';

        var limit  = jQuery('#is_user_limited').val();
        if(limit==0) edit_price='edit_price';
        else edit_price='';
        var class_item = orderItem.class_item;
        var config_change = orderItem.config_change;
        if (orderItem.options.length > 0) {
            jQuery.each(orderItem.options, function (i, option) {
                var optionName = option.name.replace('[', '][');
                optionName = optionName.lastIndexOf(']') == (optionName.length - 1) ? optionName : optionName + ']';
                orderOption += '<input type="hidden" name="item[' + orderItem.id + '][' + optionName + '" value="' + option.value + '">';
                //optionInput+='<span class="option-title">'+option.optionTitle+': </span><span class="option-name">'+option.qty+'x '+option.title+'</span></li>';
            });
            config_change= "config_change item_config item_config-id";
            class_item = "config item_config item_config-id";
            if(orderItem.type == 'grouped')
                change_qty = 'grouped';
        }
        if(orderItem.out_stock == 1){
            var out_stock = '<div style="color: red" >Out of Stock</div>';
        }
        if(orderItem.is_qty_decimal == 1){
            is_qty_decimal= 'qty_decimal';
        }
        //orderOption+='</ul>';
        var tempOrderItem = {};
        if (orderItem.qty == 0) {
            tempOrderItem.style = 'style="display:none"';
        }

        tempOrderItem.id = orderItem.id;
        tempOrderItem.name = orderItem.name;
        tempOrderItem.option = orderOption;
        tempOrderItem.qty = orderItem.qty;
        tempOrderItem.tax = orderItem.tax;
        tempOrderItem.subtotal = formatCurrency(orderItem.subtotal, priceFormat);
        tempOrderItem.class_item = class_item;
        tempOrderItem.price_name = orderItem.price_name;
        tempOrderItem.config_change= config_change;
        tempOrderItem.edit_price = edit_price;
        tempOrderItem.change_qty = change_qty;
        tempOrderItem.out_stock= out_stock;
        tempOrderItem.is_qty_decimal = is_qty_decimal;

        if (displayTaxInSubtotal){
            tempOrderItem.subtotal = formatCurrency(orderItem.subtotalInclTax, priceFormat);
        }else{
            tempOrderItem.subtotal = formatCurrency(orderItem.subtotal, priceFormat);
        }

        var orderItemOutput = '';
        if (onFlyDiscount){

            if(jQuery("#price_includes_tax").val() == 1){
                tempOrderItem.price = formatCurrency(orderItem.priceInclTax, priceFormat);
            }else{
                tempOrderItem.price = formatCurrency(orderItem.price, priceFormat);
            }


            if (displayTaxInShoppingCart){
                tempOrderItem.price_display = formatCurrency(orderItem.priceInclTax, priceFormat);
            }else{
                tempOrderItem.price_display = formatCurrency(orderItem.price, priceFormat);
            }

            if(priceFormat.decimalSymbol == ','){
                tempOrderItem.price = number_format(parseFloat(tempOrderItem.price), 2, '.', '');
            }
            orderItemOutput = nano(orderTemplate, tempOrderItem);
        }else{

            if (displayTaxInShoppingCart){
                tempOrderItem.price = formatCurrency(orderItem.priceInclTax, priceFormat);
            }else{
                tempOrderItem.price = formatCurrency(orderItem.price, priceFormat);
            }

            orderItemOutput = nano(noDiscountTemplate, tempOrderItem);
        }

        jQuery('#order-items_grid table tbody').append(orderItemOutput);

        itemNumber += parseFloat(orderItem.qty);
        subtotal += parseFloat(orderItem.subtotal);
        subtotalInclTax += parseFloat(orderItem.subtotalInclTax);
    })
    var tax = subtotalInclTax - subtotal;
    jQuery('#item_count_value').text(itemNumber);
    if (updatePrice) {
        if (displayTaxInSubtotal){
            jQuery('#subtotal_value').text(addCommas(subtotalInclTax.toFixed(2)));
        }else{
            jQuery('#subtotal_value').text(addCommas(subtotal.toFixed(2)));
        }

        jQuery('#tax_value').text(tax.toFixed(2));
        var discount = Math.abs(jQuery('#order_discount').val());
       // if(displayTaxInGrandTotal)
            var grandTotal = subtotalInclTax - discount;
      //  else grandTotal = subtotal - discount;
        jQuery('#grandtotal').text(addCommas(grandTotal.toFixed(2)));
        jQuery('#grand_before').val(addCommas(grandTotal.toFixed(2)));
        jQuery('#cash-in').val(grandTotal.toFixed(2));
    }
    setTimeout(function () {
        jQuery('#items_area').getNiceScroll().doScrollPos(0,100000);
    }, 500);
    jQuery('#items_area').getNiceScroll().doScrollPos(0,100000);

}

function check_edit_product(orderId){
    var class_object = jQuery('#item-qty-' + orderId).attr('class');
    if(class_object.indexOf('grouped') > -1) {
        jQuery('#item-qty-' + orderId).val(1.00);
        jQuery('#item-qty-' + orderId).attr('readonly',true);
        jQuery('#item-display-price-' + orderId).attr('readonly',true);
        return;
    }
}

function sortObject(obj) {
    var arr = [];
    var new_object = {};
    for (var prop in obj) {
        if (obj.hasOwnProperty(prop)) {
            arr.push({
                'key': prop,
                'sort': obj[prop].pos,
                'value': obj[prop]
            });
        }
    }
    //arr.sort(function(a, b) { return a.sort - b.sort; });
    arr.sort(function (a, b) {
        return b.sort - a.sort;
    });
    for (var i = 0; i < arr.length; i++) {
        new_object[arr[i].sort] = arr[i].value;
    }
    return new_object; // returns array
}

Array.prototype.compare = function (array) {
    // if the other array is a falsy value, return
    if (!array)
        return false;

    // compare lengths - can save a lot of time
    if (this.length != array.length)
        return false;
    for(var i = 0;i<this.length;i++){
        var a = this[i];
        var b = array[i];
        if (a.name != b.name || a.value != b.value){
            return false;
        }
    }
    //return JSON.stringify(this) == JSON.stringify(array);
    return true;
}

Product.Options = Class.create();
Product.Options.prototype = {
    initialize : function(config) {
        this.config = config;
        this.reloadPrice();
        document.observe("dom:loaded", this.reloadPrice.bind(this));
    },
    reloadPrice : function() {
        var config = this.config;
        var skipIds = [];
        $$('body .product-custom-option').each(function(element){
            var optionId = 0;
            element.name.sub(/[0-9]+/, function(match){
                optionId = parseInt(match[0], 10);
            });
            if (config[optionId]) {
                var configOptions = config[optionId];
                var curConfig = {price: 0};
                if (element.type == 'checkbox' || element.type == 'radio') {
                    if (element.checked) {
                        if (typeof configOptions[element.getValue()] != 'undefined') {
                            curConfig = configOptions[element.getValue()];
                        }
                    }
                } else if(element.hasClassName('datetime-picker') && !skipIds.include(optionId)) {
                    dateSelected = true;
                    $$('.product-custom-option[id^="options_' + optionId + '"]').each(function(dt){
                        if (dt.getValue() == '') {
                            dateSelected = false;
                        }
                    });
                    if (dateSelected) {
                        curConfig = configOptions;
                        skipIds[optionId] = optionId;
                    }
                } else if(element.type == 'select-one' || element.type == 'select-multiple') {
                    if ('options' in element) {
                        $A(element.options).each(function(selectOption){
                            if ('selected' in selectOption && selectOption.selected) {
                                if (typeof(configOptions[selectOption.value]) != 'undefined') {
                                    curConfig = configOptions[selectOption.value];
                                }
                            }
                        });
                    }
                } else {
                    if (element.getValue().strip() != '') {
                        curConfig = configOptions;
                    }
                }
                if(element.type == 'select-multiple' && ('options' in element)) {
                    $A(element.options).each(function(selectOption) {
                        if (('selected' in selectOption) && typeof(configOptions[selectOption.value]) != 'undefined') {
                            if (selectOption.selected) {
                                curConfig = configOptions[selectOption.value];
                            } else {
                                curConfig = {price: 0};
                            }
                            optionsPrice.addCustomPrices(optionId + '-' + selectOption.value, curConfig);
                            optionsPrice.reload();
                        }
                    });
                } else {
                    optionsPrice.addCustomPrices(element.id || optionId, curConfig);
                    optionsPrice.reload();
                }
            }
        });
    }
}

var DateOption = Class.create({

    getDaysInMonth: function(month, year)
    {
        var curDate = new Date();
        if (!month) {
            month = curDate.getMonth();
        }
        if (2 == month && !year) { // leap year assumption for unknown year
            return 29;
        }
        if (!year) {
            year = curDate.getFullYear();
        }
        return 32 - new Date(year, month - 1, 32).getDate();
    },

    reloadMonth: function(event)
    {
        var selectEl = event.findElement();
        var idParts = selectEl.id.split("_");
        if (idParts.length != 3) {
            return false;
        }
        var optionIdPrefix = idParts[0] + "_" + idParts[1];
        var month = parseInt($(optionIdPrefix + "_month").value);
        var year = parseInt($(optionIdPrefix + "_year").value);
        var dayEl = $(optionIdPrefix + "_day");

        var days = this.getDaysInMonth(month, year);

        //remove days
        for (var i = dayEl.options.length - 1; i >= 0; i--) {
            if (dayEl.options[i].value > days) {
                dayEl.remove(dayEl.options[i].index);
            }
        }

        // add days
        var lastDay = parseInt(dayEl.options[dayEl.options.length-1].value);
        for (i = lastDay + 1; i <= days; i++) {
            this.addOption(dayEl, i, i);
        }
    },

    addOption: function(select, text, value)
    {
        var option = document.createElement('OPTION');
        option.value = value;
        option.text = text;

        if (select.options.add) {
            select.options.add(option);
        } else {
            select.appendChild(option);
        }
    }
});

var validateOptionsCallback = function (elmId, result){
    var container = $(elmId).up('ul.options-list');
    if (!container) {
        return;
    }
    if (result == 'failed') {
        container.removeClassName('validation-passed');
        container.addClassName('validation-failed');
    } else {
        container.removeClassName('validation-failed');
        container.addClassName('validation-passed');
    }
}