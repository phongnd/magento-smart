/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
var AdminOrder = new Class.create();
AdminOrder.prototype = {
    initialize: function (data) {
        if (!data) data = {};
        this.loadBaseUrl = false;
        this.customerId = data.customer_id ? data.customer_id : false;
        this.storeId = data.store_id ? data.store_id : false;
        this.currencyId = false;
        this.currencySymbol = data.currency_symbol ? data.currency_symbol : '';
        this.doemailreceipt = false;
        this.doprintreceipt = false;
        this.emailreceipt = '';
        this.tillId = data.tillId ? data.tillId : false;
        this.warehouseId = data.warehouseId ? data.warehouseId : false;
        this.warehouseName = data.warehouseId ? data.warehouseId : false;
        this.addresses = data.addresses ? data.addresses : $H({});
        this.shippingAsBilling = data.shippingAsBilling ? data.shippingAsBilling : false;
        this.gridProducts = $H({});
        this.gridProductsGift = $H({});
        this.billingAddressContainer = '';
        this.shippingAddressContainer = '';
        this.isShippingMethodReseted = data.shipping_method_reseted ? data.shipping_method_reseted : false;
        this.overlayData = $H({});
        this.giftMessageDataChanged = false;
        this.productConfigureAddFields = {};
        this.productPriceBase = {};
    },

    setLoadBaseUrl: function (url) {
        this.loadBaseUrl = url;
    },

    setAddresses: function (addresses) {
        this.addresses = addresses;
    },

    setCustomerId: function (id) {
        this.customerId = id;
        this.loadArea('header', true);
        $(this.getAreaId('header')).callback = 'setCustomerAfter';
        $('back_order_top_button').hide();
        $('reset_order_top_button').show();
    },

    setCustomerAfter: function () {
        this.customerSelectorHide();
        if (this.storeId) {
            $(this.getAreaId('data')).callback = 'dataShow';
            this.loadArea(['data'], true);
        }
        else {
            this.storeSelectorShow();
        }
    },

    setStoreId: function (id) {
        this.storeId = id;
        this.storeSelectorHide();
        this.sidebarShow();
        //this.loadArea(['header', 'sidebar','data'], true);
        this.dataShow();
        this.loadArea(['header', 'data'], true);
    },

    setCurrencyId: function (id) {
        this.currencyId = id;
        //this.loadArea(['sidebar', 'data'], true);
        this.loadArea(['data'], true);
    },

    setCurrencySymbol: function (symbol) {
        this.currencySymbol = symbol;
    },

    selectAddress: function (el, container) {
        id = el.value;
        if (id.length == 0) {
            id = '0';
        }
        if (this.addresses[id]) {
            this.fillAddressFields(container, this.addresses[id]);
        }
        else {
            this.fillAddressFields(container, {});
        }

        var data = this.serializeData(container);
        data[el.name] = id;
        if (this.isShippingField(container) && !this.isShippingMethodReseted) {
            this.resetShippingMethod(data);
        }
        else {
            this.saveData(data);
        }
    },

    isShippingField: function (fieldId) {
        if (this.shippingAsBilling) {
            return fieldId.include('billing');
        }
        return fieldId.include('shipping');
    },

    isBillingField: function (fieldId) {
        return fieldId.include('billing');
    },

    bindAddressFields: function (container) {
        var fields = $(container).select('input', 'select');
        for (var i = 0; i < fields.length; i++) {
            Event.observe(fields[i], 'change', this.changeAddressField.bind(this));
        }
    },

    changeAddressField: function (event) {
        var field = Event.element(event);
        var re = /[^\[]*\[([^\]]*)_address\]\[([^\]]*)\](\[(\d)\])?/;
        var matchRes = field.name.match(re);
        var type = matchRes[1];
        var name = matchRes[2];
        var data;

        if (this.isBillingField(field.id)) {
            data = this.serializeData(this.billingAddressContainer)
        }
        else {
            data = this.serializeData(this.shippingAddressContainer)
        }
        data = data.toObject();

        if ((type == 'billing' && this.shippingAsBilling)
            || (type == 'shipping' && !this.shippingAsBilling)) {
            data['reset_shipping'] = true;
        }

        data['order[' + type + '_address][customer_address_id]'] = $('order-' + type + '_address_customer_address_id').value;

        if (data['reset_shipping']) {
            this.resetShippingMethod(data);
        }
        else {
            this.saveData(data);
            if (name == 'country_id' || name == 'customer_address_id') {
                this.loadArea(['shipping_method', 'billing_method', 'totals', 'items'], true, data);
            }
            // added for reloading of default sender and default recipient for giftmessages
            //this.loadArea(['giftmessage'], true, data);
        }
    },

    fillAddressFields: function (container, data) {
        var regionIdElem = false;
        var regionIdElemValue = false;

        var fields = $(container).select('input', 'select');
        var re = /[^\[]*\[[^\]]*\]\[([^\]]*)\](\[(\d)\])?/;
        for (var i = 0; i < fields.length; i++) {
            // skip input type file @Security error code: 1000
            if (fields[i].tagName.toLowerCase() == 'input' && fields[i].type.toLowerCase() == 'file') {
                continue;
            }
            var matchRes = fields[i].name.match(re);
            if (matchRes === null) {
                continue;
            }
            var name = matchRes[1];
            var index = matchRes[3];

            if (index) {
                // multiply line
                if (data[name]) {
                    var values = data[name].split("\n");
                    fields[i].value = values[index] ? values[index] : '';
                } else {
                    fields[i].value = '';
                }
            } else if (fields[i].tagName.toLowerCase() == 'select' && fields[i].multiple) {
                // multiselect
                if (data[name]) {
                    values = [''];
                    if (Object.isString(data[name])) {
                        values = data[name].split(',');
                    } else if (Object.isArray(data[name])) {
                        values = data[name];
                    }
                    fields[i].setValue(values);
                }
            } else {
                fields[i].setValue(data[name] ? data[name] : '');
            }

            if (fields[i].changeUpdater) fields[i].changeUpdater();
            if (name == 'region' && data['region_id'] && !data['region']) {
                fields[i].value = data['region_id'];
            }
        }
    },

    disableShippingAddress: function (flag) {
        this.shippingAsBilling = flag;
        if ($('order-shipping_address_customer_address_id')) {
            $('order-shipping_address_customer_address_id').disabled = flag;
        }
        if ($(this.shippingAddressContainer)) {
            var dataFields = $(this.shippingAddressContainer).select('input', 'select');
            for (var i = 0; i < dataFields.length; i++) dataFields[i].disabled = flag;
        }
    },

    setShippingAsBilling: function (flag) {
        this.disableShippingAddress(flag);
        if (flag) {
            var data = this.serializeData(this.billingAddressContainer);
        }
        else {
            var data = this.serializeData(this.shippingAddressContainer);
        }
        data = data.toObject();
        data['shipping_as_billing'] = flag ? 1 : 0;
        data['reset_shipping'] = 1;
        this.loadArea(['shipping_method', 'billing_method', 'shipping_address', 'totals', 'giftmessage'], true, data);
    },

    resetShippingMethod: function (data) {
        data['reset_shipping'] = 1;
        this.isShippingMethodReseted = true;
        this.loadArea(['shipping_method', 'billing_method', 'shipping_address', 'totals', 'giftmessage', 'items'], true, data);
    },

    loadShippingRates: function () {
        this.isShippingMethodReseted = false;
        this.loadArea(['shipping_method', 'totals'], true, {collect_shipping_rates: 1});
    },

    setShippingMethod: function (method, title) {
        var data = {};
        data['order[shipping_method]'] = method;
        //this.loadArea(['shipping_method', 'totals', 'billing_method'], true, data);
        this.loadArea(['shipping_method', 'totals'], true, data);
        jQuery("#shipping_detail").html(title);
    },

    switchPaymentMethod: function (method) {
        this.setPaymentMethod(method);
        var data = {};
        jQuery(".payment-method-item").removeClass('active');
        jQuery("#p_method_"+method).addClass('active');
        data['order[payment_method]'] = method;
        this.loadArea(['card_validation'], true, data);
    },

    setPaymentMethod: function (method) {
        if (this.paymentMethod && $('payment_form_' + this.paymentMethod)) {
            var form = 'payment_form_' + this.paymentMethod;
            [form + '_before', form, form + '_after'].each(function (el) {
                var block = $(el);
                if (block) {
                    block.hide();
                    block.select('input', 'select').each(function (field) {
                        field.disabled = true;
                    });
                }
            });
        }

        if (!this.paymentMethod || method) {
            $('order-billing_method_form').select('input', 'select').each(function (elem) {
                if (elem.type != 'radio') elem.disabled = true;
            })
        }

        if ($('payment_form_' + method)) {
            this.paymentMethod = method;
            var form = 'payment_form_' + method;
            [form + '_before', form, form + '_after'].each(function (el) {
                var block = $(el);
                if (block) {
                    block.show();
                    block.select('input', 'select').each(function (field) {
                        field.disabled = false;
                        if (!el.include('_before') && !el.include('_after') && !field.bindChange) {
                            field.bindChange = true;
                            field.paymentContainer = form; //@deprecated after 1.4.0.0-rc1
                            field.method = method;
                            field.observe('change', this.changePaymentData.bind(this))
                        }
                    }, this);
                }
            }, this);
        }

        var payment_title = jQuery("#" + method + "_title").html();
        jQuery("#payment_detail").html(payment_title);
    },

    changePaymentData: function (event) {
        var elem = Event.element(event);
        if (elem && elem.method) {
            var data = this.getPaymentData(elem.method);
            if (data) {
                this.loadArea(['card_validation'], true, data);
            } else {
                return;
            }
        }
    },

    getPaymentData: function (currentMethod) {
        if (typeof(currentMethod) == 'undefined') {
            if (this.paymentMethod) {
                currentMethod = this.paymentMethod;
            } else {
                return false;
            }
        }
        var data = {};
        var fields = $('payment_form_' + currentMethod).select('input', 'select');
        for (var i = 0; i < fields.length; i++) {
            data[fields[i].name] = fields[i].getValue();
        }
        if ((typeof data['payment[cc_type]']) != 'undefined' && (!data['payment[cc_type]'] || !data['payment[cc_number]'])) {
            return false;
        }
        return data;
    },

    applyCoupon: function (code) {
        this.loadArea(['items', 'shipping_method', 'totals', 'billing_method'], true, {'order[coupon][code]': code, reset_shipping: true});
    },

    addProduct: function (id) {
        this.loadArea(['items', 'shipping_method', 'totals', 'billing_method'], true, {add_product: id, reset_shipping: true});
    },

    removeQuoteItem: function (id) {
        this.loadArea(['items', 'shipping_method', 'totals', 'billing_method'], true,
            {remove_item: id, from: 'quote', reset_shipping: true});
    },

    moveQuoteItem: function (id, to) {
        this.loadArea(['sidebar_' + to, 'items', 'shipping_method', 'totals', 'billing_method'], this.getAreaId('items'),
            {move_item: id, to: to, reset_shipping: true});
    },

    productGridShow: function (buttonElement) {
        this.productGridShowButton = buttonElement;
        Element.hide(buttonElement);
        this.showArea('search');
    },

    productGridRowInit: function (grid, row) {
        var checkbox = $(row).select('.checkbox')[0];
        var inputs = $(row).select('.input-text');
        if (checkbox && inputs.length > 0) {
            checkbox.inputElements = inputs;
            for (var i = 0; i < inputs.length; i++) {
                var input = inputs[i];
                input.checkboxElement = checkbox;

                var product = this.gridProducts.get(checkbox.value);
                if (product) {
                    var defaultValue = product[input.name];
                    if (defaultValue) {
                        if (input.name == 'giftmessage') {
                            input.checked = true;
                        } else {
                            input.value = defaultValue;
                        }
                    }
                }

                input.disabled = !checkbox.checked || input.hasClassName('input-inactive');

                Event.observe(input, 'keyup', this.productGridRowInputChange.bind(this));
                Event.observe(input, 'change', this.productGridRowInputChange.bind(this));
            }
        }
    },

    productGridRowInputChange: function (event) {
        var element = Event.element(event);
        if (element && element.checkboxElement && element.checkboxElement.checked) {
            if (element.name != 'giftmessage' || element.checked) {
                this.gridProducts.get(element.checkboxElement.value)[element.name] = element.value;
            } else if (element.name == 'giftmessage' && this.gridProducts.get(element.checkboxElement.value)[element.name]) {
                delete(this.gridProducts.get(element.checkboxElement.value)[element.name]);
            }
        }
    },

    productGridRowClick: function (grid, event) {
        var trElement = Event.findElement(event, 'tr');
        var qtyElement = trElement.select('input[name="qty"]')[0];
        var eventElement = Event.element(event);
        var isInputCheckbox = eventElement.tagName == 'INPUT' && eventElement.type == 'checkbox';
        var isInputQty = eventElement.tagName == 'INPUT' && eventElement.name == 'qty';
        if (trElement && !isInputQty) {
            var checkbox = Element.select(trElement, 'input[type="checkbox"]')[0];
            var confLink = Element.select(trElement, 'a')[0];
            var priceColl = Element.select(trElement, '.price')[0];
            if (checkbox) {
                // processing non composite product
                if (confLink.readAttribute('disabled')) {
                    var checked = isInputCheckbox ? checkbox.checked : !checkbox.checked;
                    grid.setCheckboxChecked(checkbox, checked);
                    // processing composite product
                } else if (isInputCheckbox && !checkbox.checked) {
                    grid.setCheckboxChecked(checkbox, false);
                    // processing composite product
                } else if (!isInputCheckbox || (isInputCheckbox && checkbox.checked)) {
                    var listType = confLink.readAttribute('list_type');
                    var productId = confLink.readAttribute('product_id');
                    if (typeof this.productPriceBase[productId] == 'undefined') {
                        var priceBase = priceColl.innerHTML.match(/.*?([0-9\.,]+)/);
                        if (!priceBase) {
                            this.productPriceBase[productId] = 0;
                        } else {
                            this.productPriceBase[productId] = parseFloat(priceBase[1].replace(/,/g, ''));
                        }
                    }
                    productConfigure.setConfirmCallback(listType, function () {
                        // sync qty of popup and qty of grid
                        var confirmedCurrentQty = productConfigure.getCurrentConfirmedQtyElement();
                        if (qtyElement && confirmedCurrentQty && !isNaN(confirmedCurrentQty.value)) {
                            qtyElement.value = confirmedCurrentQty.value;
                        }
                        // calc and set product price
                        var productPrice = parseFloat(this._calcProductPrice() + this.productPriceBase[productId]);
                        priceColl.innerHTML = this.currencySymbol + productPrice.toFixed(2);
                        // and set checkbox checked
                        grid.setCheckboxChecked(checkbox, true);
                    }.bind(this));
                    productConfigure.setCancelCallback(listType, function () {
                        if (!$(productConfigure.confirmedCurrentId) || !$(productConfigure.confirmedCurrentId).innerHTML) {
                            grid.setCheckboxChecked(checkbox, false);
                        }
                    });
                    productConfigure.setShowWindowCallback(listType, function () {
                        // sync qty of grid and qty of popup
                        var formCurrentQty = productConfigure.getCurrentFormQtyElement();
                        if (formCurrentQty && qtyElement && !isNaN(qtyElement.value)) {
                            formCurrentQty.value = qtyElement.value;
                        }
                    }.bind(this));
                    productConfigure.showItemConfiguration(listType, productId);
                }
            }
        }
    },

    /**
     * Calc product price through its options
     */
    _calcProductPrice: function () {
        var productPrice = 0;
        var getPriceFields = function (elms) {
            var productPrice = 0;
            var getPrice = function (elm) {
                var optQty = 1;
                if (elm.hasAttribute('qtyId')) {
                    if (!$(elm.getAttribute('qtyId')).value) {
                        return 0;
                    } else {
                        optQty = parseFloat($(elm.getAttribute('qtyId')).value);
                    }
                }
                if (elm.hasAttribute('price') && !elm.disabled) {
                    return parseFloat(elm.readAttribute('price')) * optQty;
                }
                return 0;
            };
            for (var i = 0; i < elms.length; i++) {
                if (elms[i].type == 'select-one' || elms[i].type == 'select-multiple') {
                    for (var ii = 0; ii < elms[i].options.length; ii++) {
                        if (elms[i].options[ii].selected) {
                            productPrice += getPrice(elms[i].options[ii]);
                        }
                    }
                }
                else if (((elms[i].type == 'checkbox' || elms[i].type == 'radio') && elms[i].checked)
                    || ((elms[i].type == 'file' || elms[i].type == 'text' || elms[i].type == 'textarea' || elms[i].type == 'hidden')
                    && Form.Element.getValue(elms[i]))
                    ) {
                    productPrice += getPrice(elms[i]);
                }
            }
            return productPrice;
        }.bind(this);
        productPrice += getPriceFields($(productConfigure.confirmedCurrentId).getElementsByTagName('input'));
        productPrice += getPriceFields($(productConfigure.confirmedCurrentId).getElementsByTagName('select'));
        productPrice += getPriceFields($(productConfigure.confirmedCurrentId).getElementsByTagName('textarea'));
        return productPrice;
    },

    productGridCheckboxCheck: function (grid, element, checked) {
        if (checked) {
            if (element.inputElements) {
                this.gridProducts.set(element.value, {});
                var product = this.gridProducts.get(element.value);
                for (var i = 0; i < element.inputElements.length; i++) {
                    var input = element.inputElements[i];
                    if (!input.hasClassName('input-inactive')) {
                        input.disabled = false;
                        if (input.name == 'qty' && !input.value) {
                            input.value = 1;
                        }
                    }

                    if (input.checked || input.name != 'giftmessage') {
                        product[input.name] = input.value;
                    } else if (product[input.name]) {
                        delete(product[input.name]);
                    }
                }
            }
        } else {
            if (element.inputElements) {
                for (var i = 0; i < element.inputElements.length; i++) {
                    element.inputElements[i].disabled = true;
                }
            }
            this.gridProducts.unset(element.value);
        }
        grid.reloadParams = {'products[]': this.gridProducts.keys()};
    },

    /**
     * Submit configured products to quote
     */
    productGridAddSelected: function () {
        if (this.productGridShowButton) Element.show(this.productGridShowButton);
        var area = ['search', 'items', 'shipping_method', 'totals', 'giftmessage', 'billing_method'];
        // prepare additional fields and filtered items of products
        var fieldsPrepare = {};
        var itemsFilter = [];
        var products = this.gridProducts.toObject();
        for (var productId in products) {
            itemsFilter.push(productId);
            var paramKey = 'item[' + productId + ']';
            for (var productParamKey in products[productId]) {
                paramKey += '[' + productParamKey + ']';
                fieldsPrepare[paramKey] = products[productId][productParamKey];
            }
        }
        this.productConfigureSubmit('product_to_add', area, fieldsPrepare, itemsFilter);
        productConfigure.clean('quote_items');
        this.hideArea('search');
        this.gridProducts = $H({});
    },

    selectCustomer: function (grid, event) {
        var element = Event.findElement(event, 'tr');
        if (element.title) {
            this.setCustomerId(element.title);
        }
    },

    customerSelectorHide: function () {
        this.hideArea('customer-selector');
    },

    customerSelectorShow: function () {
        this.showArea('customer-selector');
    },

    storeSelectorHide: function () {
        this.hideArea('store-selector');
    },

    storeSelectorShow: function () {
        this.showArea('store-selector');
    },

    dataHide: function () {
        this.hideArea('data');
    },

    dataShow: function () {
        if ($('submit_order_top_button')) {
            $('submit_order_top_button').show();
        }
        this.showArea('data');
    },

    sidebarApplyChanges: function () {
        if ($(this.getAreaId('sidebar'))) {
            var data = {};
            var elems = $(this.getAreaId('sidebar')).select('input');
            for (var i = 0; i < elems.length; i++) {
                if (elems[i].getValue()) {
                    data[elems[i].name] = elems[i].getValue();
                }
            }
            data.reset_shipping = true;
            this.loadArea(['sidebar', 'items', 'shipping_method', 'billing_method', 'totals', 'giftmessage'], true, data);
        }
    },

    sidebarHide: function () {
        if (this.storeId === false && $('page:left') && $('page:container')) {
            $('page:left').hide();
            $('page:container').removeClassName('container');
            $('page:container').addClassName('container-collapsed');
        }
    },

    sidebarShow: function () {
        if ($('page:left') && $('page:container')) {
            $('page:left').show();
            $('page:container').removeClassName('container-collapsed');
            $('page:container').addClassName('container');
        }
    },

    /**
     * Show configuration of product and add handlers on submit form
     *
     * @param productId
     */
    sidebarConfigureProduct: function (listType, productId, itemId) {
        // create additional fields
        var params = {};
        params.reset_shipping = true;
        params.add_product = productId;
        this.prepareParams(params);
        for (var i in params) {
            if (params[i] === null) {
                unset(params[i]);
            } else if (typeof(params[i]) == 'boolean') {
                params[i] = params[i] ? 1 : 0;
            }
        }
        var fields = [];
        for (var name in params) {
            fields.push(new Element('input', {type: 'hidden', name: name, value: params[name]}));
        }
        // add additional fields before triggered submit
        productConfigure.setBeforeSubmitCallback(listType, function () {
            productConfigure.addFields(fields);
        }.bind(this));
        // response handler
        productConfigure.setOnLoadIFrameCallback(listType, function (response) {
            if (!response.ok) {
                return;
            }
            this.loadArea(['items', 'shipping_method', 'billing_method', 'totals', 'giftmessage'], true);
        }.bind(this));
        // show item configuration
        itemId = itemId ? itemId : productId;
        productConfigure.showItemConfiguration(listType, itemId);
        return false;
    },

    removeSidebarItem: function (id, from) {
        this.loadArea(['sidebar_' + from], 'sidebar_data_' + from, {remove_item: id, from: from});
    },

    itemsUpdate: function () {
        //var area = ['sidebar', 'items', 'shipping_method', 'billing_method','totals', 'giftmessage'];
        var type = jQuery('#magento_type').val();
        if(type == "Enterprise")
            var area = [ 'items', 'shipping_method', 'billing_method', 'totals', 'storecredit'];
        else
            var area = [ 'items', 'shipping_method', 'billing_method', 'totals'];
        // prepare additional fields
        var fieldsPrepare = {update_items: 1};
        var reload_order_val = jQuery("#reload_order").val();
        if(reload_order_val!="") fieldsPrepare['reload_order']= reload_order_val;
        var info = $('order-items_grid').select('input', 'select', 'textarea');
        for (var i = 0; i < info.length; i++) {
            if (!info[i].disabled && (info[i].type != 'checkbox' || info[i].checked)) {
                fieldsPrepare[info[i].name] = info[i].getValue();
            }
        }
        fieldsPrepare = Object.extend(fieldsPrepare, this.productConfigureAddFields);
        this.productConfigureSubmit('quote_items', area, fieldsPrepare);
        this.orderItemChanged = false;
    },

    itemsUpdateCoupon: function (code) {
        //var area = ['sidebar', 'items', 'shipping_method', 'billing_method','totals', 'giftmessage'];
        var type = jQuery('#magento_type').val();
        if(type == "Enterprise")
        var area = [ 'items', 'shipping_method', 'billing_method', 'totals', 'coupons', 'storecredit'];
        else
            var area = [ 'items', 'shipping_method', 'billing_method', 'totals', 'coupons'];
        // prepare additional fields
        var fieldsPrepare = {update_items: 1};
        var info = $('order-items_grid').select('input', 'select', 'textarea');
        for (var i = 0; i < info.length; i++) {
            if (!info[i].disabled && (info[i].type != 'checkbox' || info[i].checked)) {
                fieldsPrepare[info[i].name] = info[i].getValue();
            }
        }
        if(code!="")
            fieldsPrepare['order[coupon][code]'] = code;
        fieldsPrepare = Object.extend(fieldsPrepare, this.productConfigureAddFields);
        this.productConfigureSubmit('quote_items', area, fieldsPrepare);
        this.orderItemChanged = false;
    },

    itemsOnchangeBind: function () {
        var elems = $('order-items_grid').select('input', 'select', 'textarea');
        for (var i = 0; i < elems.length; i++) {
            if (!elems[i].bindOnchange) {
                elems[i].bindOnchange = true;
                elems[i].observe('change', this.itemChange.bind(this))
            }
        }
    },

    itemChange: function (event) {
        this.giftmessageOnItemChange(event);
        this.orderItemChanged = true;
    },

    /**
     * Show configuration of quote item
     *
     * @param itemId
     */
    showQuoteItemConfiguration: function (itemId) {
        jQuery('#item_custom_price_' + itemId).attr('disabled', 'disabled');
        var listType = 'quote_items';
        var qtyElement = $('order-items_grid').select('input[name="item\[' + itemId + '\]\[qty\]"]')[0];
        productConfigure.setConfirmCallback(listType, function () {
            // sync qty of popup and qty of grid
            var confirmedCurrentQty = productConfigure.getCurrentConfirmedQtyElement();
            if (qtyElement && confirmedCurrentQty && !isNaN(confirmedCurrentQty.value)) {
                qtyElement.value = confirmedCurrentQty.value;
            }
            this.productConfigureAddFields['item[' + itemId + '][configured]'] = 1;
            this.itemsUpdate();
        }.bind(this));
        productConfigure.setShowWindowCallback(listType, function () {
            // sync qty of grid and qty of popup
            var formCurrentQty = productConfigure.getCurrentFormQtyElement();
            if (formCurrentQty && qtyElement && !isNaN(qtyElement.value)) {
                formCurrentQty.value = qtyElement.value;
            }
        }.bind(this));
        productConfigure.showItemConfiguration(listType, itemId);
    },

    accountFieldsBind: function (container) {
        if ($(container)) {
            var fields = $(container).select('input', 'select');
            for (var i = 0; i < fields.length; i++) {
                if (fields[i].id == 'group_id') {
                    fields[i].observe('change', this.accountGroupChange.bind(this))
                }
                else {
                    fields[i].observe('change', this.accountFieldChange.bind(this))
                }
            }
        }
    },

    accountGroupChange: function () {
        this.loadArea(['data'], true, this.serializeData('order-form_account').toObject());
    },

    accountFieldChange: function () {
        this.saveData(this.serializeData('order-form_account'));
    },

    commentFieldsBind: function (container) {
        if ($(container)) {
            var fields = $(container).select('input', 'textarea');
            for (var i = 0; i < fields.length; i++)
                fields[i].observe('change', this.commentFieldChange.bind(this))
        }
    },

    commentFieldChange: function () {
        this.saveData(this.serializeData('order-comment'));
    },

    giftmessageFieldsBind: function (container) {
        if ($(container)) {
            var fields = $(container).select('input', 'textarea');
            for (var i = 0; i < fields.length; i++)
                fields[i].observe('change', this.giftmessageFieldChange.bind(this))
        }
    },

    giftmessageFieldChange: function () {
        this.giftMessageDataChanged = true;
    },

    giftmessageOnItemChange: function (event) {
        var element = Event.element(event);
        if (element.name.indexOf("giftmessage") != -1 && element.type == "checkbox" && !element.checked) {
            var messages = $("order-giftmessage").select('textarea');
            var name;
            for (var i = 0; i < messages.length; i++) {
                name = messages[i].id.split("_");
                if (name.length < 2) continue;
                if (element.name.indexOf("[" + name[1] + "]") != -1 && messages[i].value != "") {
                    alert("First, clean the Message field in Gift Message form");
                    element.checked = true;
                }
            }
        }
    },



    loadArea: function (area, indicator, params) {
        var url1 = this.loadBaseUrl;
        if (area) {
            area = this.prepareArea(area);
            url1 += 'block/' + area;
        }
        var url = url1.replace("?___SID=U","");
        if (indicator === true) indicator = 'html-body';
        params = this.prepareParams(params);
        params.json = true;
        if (!this.loadingAreas) this.loadingAreas = [];
        if (indicator) {
            this.loadingAreas = area;
            new Ajax.Request(url, {
                parameters: params,
                loaderArea: indicator,
                onSuccess: function (transport) {
                    var response = transport.responseText.evalJSON();
                    this.loadAreaResponseHandler(response);

                }.bind(this)
            });
        }
        else {
            new Ajax.Request(url, {parameters: params, loaderArea: indicator});
        }
        if (typeof productConfigure != 'undefined' && area instanceof Array && area.indexOf('items' != -1)) {
            productConfigure.clean('quote_items');
        }
    },

    loadAreaResponseHandler: function (response) {
        if (response.error) {
            alert(response.message);
        }
        if (response.ajaxExpired && response.ajaxRedirect) {
            setLocation(response.ajaxRedirect);
        }
        if (!this.loadingAreas) {
            this.loadingAreas = [];
        }
        if (typeof this.loadingAreas == 'string') {
            this.loadingAreas = [this.loadingAreas];
        }
        if (this.loadingAreas.indexOf('message' == -1)) this.loadingAreas.push('message');
        for (var i = 0; i < this.loadingAreas.length; i++) {
            var id = this.loadingAreas[i];
            if ($(this.getAreaId(id))) {
                if ('message' != id || response[id]) {
                    var wrapper = new Element('div');
                    wrapper.update(response[id] ? response[id] : '');
                    $(this.getAreaId(id)).update(wrapper);
                }
                if ($(this.getAreaId(id)).callback) {
                    this[$(this.getAreaId(id)).callback]();
                }
            }
        }
    },

    prepareArea: function (area) {
        if (this.giftMessageDataChanged) {
            return area.without('giftmessage');
        }
        return area;
    },

    saveData: function (data) {
        this.loadArea(false, false, data);
    },

    showArea: function (area) {
        var id = this.getAreaId(area);
        if ($(id)) {
            $(id).show();
            this.areaOverlay();
        }
    },

    hideArea: function (area) {
        var id = this.getAreaId(area);
        if ($(id)) {
            $(id).hide();
            this.areaOverlay();
        }
    },

    areaOverlay: function () {
        $H(order.overlayData).each(function (e) {
            e.value.fx();
        });
    },

    getAreaId: function (area) {
        return 'order-' + area;
    },

    prepareParams: function (params) {
        if (!params) {
            params = {};
        }
        if (!params.customer_id) {
            params.customer_id = this.customerId;
        }
        if (!params.store_id) {
            params.store_id = this.storeId;
        }
        if (!params.warehouse_id) {
            params.warehouse_id = this.warehouseId;
        }
        if (!params.till_id) {
            params.till_id = this.tillId;
        }
        if (!params.currency_id) {
            params.currency_id = this.currencyId;
        }
        if (!params.form_key) {
            params.form_key = FORM_KEY;
        }
        if(!params.user_limited){
            params.user_limited = jQuery('#is_user_limited').val();
        }
        var data = this.serializeData('order-billing-address-content');
        if (data) {
            data.each(function (value) {
                params[value[0]] = value[1];
            });
        }
        return params;
    },

    serializeData: function (container) {
        var fields = $(container).select('input', 'select', 'textarea');
        //alert(fields);
        var data = Form.serializeElements(fields, true);

        return $H(data);
    },

    toggleCustomPrice: function (checkbox, elemId, tierBlock) {
        if (checkbox.checked) {
            $(elemId).disabled = false;
            $(elemId).show();
            if ($(tierBlock)) $(tierBlock).hide();
        }
        else {
            $(elemId).disabled = true;
            $(elemId).hide();
            if ($(tierBlock)) $(tierBlock).show();
        }
    },

    submit: function () {
        if (jQuery('#edit_form').submit()) {
            this.printInvoice();
        }
    },

    overlay: function (elId, show, observe) {
        if (typeof(show) == 'undefined') {
            show = true;
        }

        var orderObj = this;
        var obj = this.overlayData.get(elId)
        if (!obj) {
            obj = {
                show: show,
                el: elId,
                order: orderObj,
                fx: function (event) {
                    this.order.processOverlay(this.el, this.show);
                }
            }
            obj.bfx = obj.fx.bindAsEventListener(obj);
            this.overlayData.set(elId, obj);
        }
        else {
            obj.show = show;
            Event.stopObserving(window, 'resize', obj.bfx);
        }

        Event.observe(window, 'resize', obj.bfx);

        this.processOverlay(elId, show);
    },

    processOverlay: function (elId, show) {
        var el = $(elId);

        if (!el) {
            return false;
        }

        var parentEl = el.up(1);
        var parentPos = Element.cumulativeOffset(parentEl);
        if (show) {
            parentEl.removeClassName('ignore-validate');
        }
        else {
            parentEl.addClassName('ignore-validate');
        }

        if (Prototype.Browser.IE) {
            parentEl.select('select').each(function (elem) {
                if (show) {
                    elem.needShowOnSuccess = false;
                    elem.style.visibility = '';
                } else {
                    elem.style.visibility = 'hidden';
                    elem.needShowOnSuccess = true;
                }
            });
        }

        el.setStyle({
            display: show ? 'none' : '',
            position: 'absolute',
            backgroundColor: '#999999',
            opacity: 0.8,
            width: parentEl.getWidth() + 'px',
            height: parentEl.getHeight() + 'px',
            top: parentPos[1] + 'px',
            left: parentPos[0] + 'px'
        });
    },

    /*Move from xpos.js*/

    productConfigureSubmit: function (listType, area, fieldsPrepare, itemsFilter) {
        // prepare loading areas and build url
        area = this.prepareArea(area);
        this.loadingAreas = area;
        var url1 = this.loadBaseUrl + 'block/' + area + '?isAjax=true';
        var url = url1.replace("?___SID=U","");
        // prepare additional fields
        fieldsPrepare = this.prepareParams(fieldsPrepare);
        //fieldsPrepare.reset_shipping = 1; -- Disable Reset Shipping
        fieldsPrepare.json = 1;

        // create fields
        var fields = [];
        for (var name in fieldsPrepare) {
            fields.push(new Element('input', {
                type: 'hidden',
                name: name,
                value: fieldsPrepare[name]
            }));
        }
        if (typeof productConfigure != 'undefined') {
            productConfigure.addFields(fields);

            // filter items
            if (itemsFilter) {
                productConfigure.addItemsFilter(listType, itemsFilter);
            }

            // prepare and do submit
            productConfigure.addListType(listType, {
                urlSubmit: url
            });
            productConfigure.setOnLoadIFrameCallback(listType, function (response) {
                this.loadAreaResponseHandler(response);
             //   var magento_version = jQuery('#magento_version').val();
             //   if (magento_version.match("^1.8")) {
                    updateShippingRates();
              //  }
            }.bind(this));
            productConfigure.submit(listType);
            // clean
            this.productConfigureAddFields = {};
        }
    },

    save: function (urlAction, messages) {
        if (isOnline()){
                order.itemsUpdate();
        }
         setTimeout(order.saveOrder.delay(1,urlAction));

    },
    saveOrder: function(urlAction){
        if (this.orderItemChanged) {
            alert('You have item changes\nPlease click "Update Items and Qty" before process saving.');
            return false;
        }

        if (urlAction == "") return false;
       // if (!confirm(messages)) return false;
        //else xpos_need_confirm_close = false;
        xpos_need_confirm_close = false;
        $('edit_form').action = urlAction;
        $('cash-in').removeClassName('required-entry validate-number-2 validate-zero-or-greater');
        //$('balance').removeClassName('validate-zero-or-greater');
        if (isOnline()) {
            jQuery('#edit_form').submit();
        } else {
            this.submitOffline();
        }
    },

    applyCoupon: function (code) {
        this.loadArea(['totals', 'billing_method', 'coupons'], true, {
            'order[coupon][code]': code
        });
    },
    applyGiftCard: function(code){
        this.loadArea(['totals', 'billing_method','giftcards','coupons','items'], true, {
            'giftcard_add':code
        });
    },

    doEmailRecept: function () {
        jQuery('input[name="doemailreceipt"]').val(1);
    },

    doPrintRecept: function () {
        jQuery('input[name="doprintreceipt"]').val(1);
    },

    undoEmailRecept: function () {
        jQuery('input[name="doemailreceipt"]').val(0);
    },

    undoPrintRecept: function () {
        jQuery('input[name="doprintreceipt"]').val(0);
    },

    setEmailReceipt: function (email) {
        jQuery('input[name="emailreceipt"]').val(email);
    },

    doShipment: function () {
        jQuery('input[name="doshipment"]').val(1);
    },

    undoShipment: function () {
        jQuery('input[name="doshipment"]').val(0);
    },

    doInvoice: function () {
        jQuery('input[name="doinvoice"]').val(1);
    },

    undoInvoice: function () {
        jQuery('input[name="doinvoice"]').val(0);
    },

    printInvoice: function () {
        var doprint = jQuery('#doprintreceipt').val();
        if(doprint == 1){
            var print_url = jQuery('#print_invoice_url').val();
            var cashier_name = jQuery('#cashier_name').html();
            var post_url = 'cashier_name/' + cashier_name;
            var win = window.open(print_url + post_url, '',
                'width=900,height=600,resizable=1,scrollbars=1');
            win.focus();
        }
        /*var win = window.open(urlPrint, '',
         'width=900,height=600,resizable=1,scrollbars=1');
         win.focus();*/
    },

    printCreditmemo: function(creditmemo_id){
            var print_url = jQuery('#print_creditmemo_url').val();
            var cashier_name = jQuery('#cashier_name').html();
            var post_url = 'cashier_name/' + cashier_name;
            if (creditmemo_id) {
                post_url += '/creditmemo_id/' + creditmemo_id
            }
            var win = window.open(print_url + post_url, '',
                'width=900,height=600,resizable=1,scrollbars=1');
            win.focus();
    },

    printZreport: function(report_type,transfer_amount,diff_total){
        var print_url = jQuery('#print_zreport_rul').val();
        var cashier_name = jQuery('#cashier_name').html();
        //var cashier_id = jQuery('#xpos_user_id').val();
        var cash_count = parseFloat(jQuery('#cashpayment').val());
        var check_count = parseFloat(jQuery('#checkmo').val());
        var cc_count = parseFloat(jQuery('#ccsave').val());
        var other_count = parseFloat(jQuery('#other_payment').val());

        var post_cashier = 'cashier_name/' + cashier_name;
        var post_reporttype = '/report_type/'+report_type;
        var post_transfer = '/transfer_amout/'+transfer_amount;
        var post_diff = '/diff_total/'+diff_total;
        if(isNumber(cash_count))
        var post_cash_count = '/cash_count/'+cash_count;
        else var post_cash_count = '';
        if(isNumber(check_count))
        var post_check_count = '/check_count/'+check_count;
        else var post_check_count = '';
        if(isNumber(cc_count))
        var post_cc_count = '/cc_count/'+cc_count;
        else var post_cc_count = '';
        if(isNumber(other_count))
        var post_other_count = '/other_count/'+other_count;
        else var post_other_count = '';
        var win = window.open(print_url + post_cashier + post_reporttype +
            post_transfer + post_diff + post_cash_count + post_check_count + post_cc_count + post_other_count, '',
            'width=900,height=600,resizable=1,scrollbars=1');
        win.focus();
    },

    cancel: function (urlAction, messages) {
        if (messages == '') {
            xpos_need_confirm_close = false;
            disableElements('save');
            disableElements('b-cancel');
            disableElements('complete');
            setLocation(urlAction);
        } else {
            if (confirm(messages)) {
                xpos_need_confirm_close = false;
                disableElements('save');
                disableElements('b-cancel');
                disableElements('complete');
                setLocation(urlAction);
            } else {
                return false;
            }
        }
    },
    complete: function (urlAction,confirm_checkout) {

        if (this.orderItemChanged) {
            alert('You have item changes\nPlease click "Update Items and Qty" before process checkout.');
            return false;
        }

        if (jQuery('#doemailreceipt').val() == 1 && isOnline() == 1 && confirm_checkout == 0) {
            if (!this.validEmail(jQuery('#emailreceipt').val())) {
                if (jQuery('#emailreceipt').html() == '') {
                    alert('Please enter right email format.');
                    return false;
                }
            }
        }

        $('edit_form').action = urlAction;

        if (isOnline()) {
            var print_url = jQuery('#print_invoice_url').val();
            if (print_url != '') {
                var check_url = jQuery('#check_order_url').val();
                var data = jQuery('form#edit_form').serialize();
                var params = this.prepareParams(data);
                params.json = true;
                var error_flag = 0;
                new Ajax.Request(check_url, {
                    parameters: params,
                    onSuccess: function (transport) {
                        if (transport.responseText == "0") {
                            error_flag = 1;
                        }
                        if(error_flag == 1){
                            return false;
                        }
                        var response = transport.responseText.evalJSON();
                        var entity_id = response.entity_id;
                        var order_id = response.order_id;
                        jQuery('#sm_order_id').val(entity_id);
                        if(confirm_checkout){
                            jQuery('#new_order_id').html(order_id);
                            jQuery('#checkout_popup').bPopup({
                                modalClose: false,
                                opacity: 0.6,
                                speed: 450,
                                transition: 'slideBack',
                                positionStyle: 'fixed'
                            });
                        }
                    },
                    onComplete: function () {
                        if(!confirm_checkout){
                            if(error_flag == 1){
                                this.loadArea(['data'], true);
                            }else{
                                this.submit();
                            }
                        }
                    }.bind(this)
                });


                return false;

            }
        } else {
            printReceipt(currentOrder);
            this.submitOffline();
        }

    },

    validEmail: function (e) {
        var filter = /^\s*[\w\-\+_]+(\.[\w\-\+_]+)*\@[\w\-\+_]+\.[\w\-\+_]+(\.[\w\-\+_]+)*\s*$/;
        return String(e).search (filter) != -1;
    },

    selectOrder: function (grid, event) {
        var element = Event.findElement(event, 'tr');
        if (element.title) {
            setLocation(element.title);
        }
    },

    submitOffline: function () {
        // check empty cart
        if (Object.keys(currentOrder).length == 0) {
            alert('The cart is empty. Please add an item to checkout.');
            return;
        }

        if (jQuery('input[name="order[account][type]"]').val() == 'new') {
            jQuery('input[name="order[account][email]"]').val(jQuery('input[name="order[account][email_temp]"]').val());
        }
        var data = jQuery('form#edit_form').serialize();
        var orders = $.jStorage.get("orders");
        if (!orders)
            orders = [data];
        else
            orders.push(data);
        $.jStorage.set("orders", orders);
        jQuery('#count_pending_orders').html(orders.length);
        // clean-up form
        clearOrder();
        jQuery('input#cash-in').val('');
        jQuery('#checkout_mode_button').click();

    }

}

function currencyToNumber(value) {
    if ((value.indexOf('.') != -1) || (value.indexOf(',') != -1)) {
        value = value.split('.').join('');
        value = value.split(',').join('');
        return Number(value.replace(/[^0-9\.,]+/g, "")) / 100;
    }
    return Number(value.replace(/[^0-9\.,]+/g, ""));
}

function myround(value) {
    if (Math.round(value) < value) return value;
    return Math.round(value);

}

function toFixed(value, precision) {
    var power = Math.pow(10, precision || 0);
    return Number(Math.round(value * power) / power);
}

function isNumber(n) {
    return !isNaN(parseFloat(n)) && isFinite(n);
}

Validation.add('validate-number-2', 'Please enter a valid number in this field.', function (v) {
    return Validation.get('IsEmpty').test(v) || (!isNaN(parseNumber(v)) && /^[0-9]+([\.|\,][0-9]+)?$/.test(v));
});