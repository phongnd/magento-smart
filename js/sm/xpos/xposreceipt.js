/**
 * Created by NguyenCT on 5/5/14.
 */
var pageReceiptTemplate =
    '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">\
    <head>\
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>\
        <title>Invoice No.1 / Magento Admin</title>\
        <link rel="stylesheet" type="text/css" href="{skin_url}adminhtml/default/default/reset.css" media="all" />\
        <link rel="stylesheet" type="text/css" href="{skin_url}adminhtml/default/default/boxes.css" media="all" />\
        <link rel="stylesheet" type="text/css" href="{skin_url}adminhtml/default/default/custom.css" media="all" />\
        <style>\
            .adminhtml-xpos-printinvoice a { color: #2976c9 !important; }\
            body{ background: #fff !important}\
            .adminhtml-xpos-printinvoice th, .adminhtml-xpos-printinvoice td { color: #333 !important; border-color: #ccc !important; padding: 2px; font-size: 12pt !important; font-weight: normal; }\
            .adminhtml-xpos-printinvoice .middle { padding: 0; height: auto; }\
            .adminhtml-xpos-printinvoice h1 { text-align: center; text-transform: uppercase; }\
            .adminhtml-xpos-printinvoice .wrapper { min-width: 0; width: 380px; overflow: hidden; }\
            .adminhtml-xpos-printinvoice .middle { min-height: 0; }\
            .adminhtml-xpos-printinvoice .entry-edit { margin-bottom: 10px; }\
            .adminhtml-xpos-printinvoice .entry-edit td.label { text-align: left !important; padding-right: 10px; font-size: 12pt !important; }\
            .adminhtml-xpos-printinvoice .entry-edit td.label .summary-collapse { text-align: right !important; }\
            .adminhtml-xpos-printinvoice .form-list td.label { padding: 0 !important; vertical-align: middle; width: 135px !important; }\
            .adminhtml-xpos-printinvoice .form-list td.label label { width: auto; font-size: 12pt !important; }\
            .adminhtml-xpos-printinvoice .form-list td.value { font-size: 12pt !important; }\
            .adminhtml-xpos-printinvoice .box-left, .adminhtml-xpos-printinvoice .box-right { width: auto; float: left; }\
            .adminhtml-xpos-printinvoice #store_information { font-size: 12pt !important; margin-bottom: 10px; height: auto; }\
            .adminhtml-xpos-printinvoice h1.a-center { font-size: 14pt !important; }\
            .adminhtml-xpos-printinvoice .a-center strong { font-weight: normal !important; }\
            .adminhtml-xpos-printinvoice td.a-right { font-size: 12pt !important; }\
            .adminhtml-xpos-printinvoice .footer { background: none; }\
            .adminhtml-xpos-printinvoice .price-excl-tax .price { font-weight: normal; }\
            .adminhtml-xpos-printinvoice .order-tables td h5.title { font-weight: normal; font-size: 12pt !important; }\
            .adminhtml-xpos-printinvoice .grid table, .adminhtml-xpos-printinvoice .grid table td, .adminhtml-xpos-printinvoice .grid tr.headings th { border: none; }\
            .adminhtml-xpos-printinvoice .grid th, .adminhtml-xpos-printinvoice .grid td { padding: 2px 0; }\
            .adminhtml-xpos-printinvoice .grid .no_col { display: none; }\
            .adminhtml-xpos-printinvoice .grid table, .adminhtml-xpos-printinvoice .grid table td:not(:nth-child(2)), .adminhtml-xpos-printinvoice .grid tr.headings th:not(:nth-child(2)) { padding: 0px 8px; }\
            .adminhtml-xpos-printinvoice .grid table, .adminhtml-xpos-printinvoice .grid table td:first-child, .adminhtml-xpos-printinvoice .grid tr.headings th:first-child { padding: 0px 0px; }\
            .adminhtml-xpos-printinvoice .grid tr.headings { background: none !important; }\
            .adminhtml-xpos-printinvoice .grid tr.even, .adminhtml-xpos-printinvoice .grid tr.even tr { background: none !important; }\
            .adminhtml-xpos-printinvoice .table-total td.label { font-size: 12pt !important; padding-right: 10px; text-align: right !important; }\
            .adminhtml-xpos-printinvoice .table-total td.a-right { font-size: 12pt !important; }\
            .adminhtml-xpos-printinvoice .table-total .emph, .adminhtml-xpos-printinvoice .table-total .accent { color: #EB5E00 !important; }\
            .adminhtml-xpos-printinvoice .table-total .a-right { text-align: right !important; }\
            @media print { .adminhtml-xpos-printinvoice { /* css-discuss.incutio.com/wiki/Printing_Tables */ @page { margin: 0cm; }\
             /*body {font-family: "MS Gothic" !important;}*/ }\
              .adminhtml-xpos-printinvoice * { background: transparent !important; color: #444 !important; text-shadow: none !important; line-height: 1; }\
              .adminhtml-xpos-printinvoice a, .adminhtml-xpos-printinvoice a:visited { color: #444 !important; text-decoration: underline; }\
              .adminhtml-xpos-printinvoice a:after { content: " (" attr(href) ")"; }\
              .adminhtml-xpos-printinvoice abbr:after { content: " (" attr(title) ")"; }\
              .adminhtml-xpos-printinvoice pre, .adminhtml-xpos-printinvoice blockquote { border: 1px solid #999; page-break-inside: avoid; }\
              .adminhtml-xpos-printinvoice thead { display: table-header-group; }\
              .adminhtml-xpos-printinvoice tr, .adminhtml-xpos-printinvoice img { page-break-inside: avoid; }\
              .adminhtml-xpos-printinvoice p, .adminhtml-xpos-printinvoice h2, .adminhtml-xpos-printinvoice h3 { orphans: 3; widows: 3; }\
              .adminhtml-xpos-printinvoice h2, .adminhtml-xpos-printinvoice h3 { page-break-after: avoid; } }\
        </style>\
    </head>\
    <body id="html-body" class=" adminhtml-xpos-printinvoice">\
        <div class="wrapper">\
            <noscript>\
                <div class="noscript">\
                    <div class="noscript-inner">\
                        <p><strong>JavaScript seems to be disabled in your browser.</strong></p>\
                        <p>You must have JavaScript enabled in your browser to utilize the functionality of this website.</p>\
                    </div>\
                </div>\
            </noscript>\
            <div id="anchor-content" class="middle">\
                <div id="store_information">\
                {store_information}\
                </div>\
            </div>\
            <h1 class="a-center">Invoice</h1>\
            <div class="entry-edit">\
                <table cellspacing="0" class="box-left form-list">\
                    <tbody>\
                        <tr>\
                            <td class="label"><label>Order No.</label></td>\
                            <td class="value"><strong>{order_no}</strong></td>\
                        </tr>\
                        <tr>\
                            <td class="label"><label>Date</label></td>\
                            <td class="value"><strong>{order_date}</strong></td>\
                        </tr>\
                        <tr>\
                            <td class="label"><label>Customer</label></td>\
                            <td class="value"><strong>{customer_name}</strong></td>\
                        </tr>\
                    </tbody>\
                </table>\
                <table cellspacing="0" class="box-right form-list">\
                    <tbody>\
                        <tr>\
                            <td class="label"><label>Time</label></td>\
                            <td class="value"><strong>{order_time}</strong></td>\
                        </tr>\
                        <tr>\
                            <td class="label"><label>Payment</label></td>\
                            <td class="value"><strong>{payment}</strong></td>\
                        </tr>\
                    </tbody>\
                </table>\
                <div class="clear"></div>\
            </div>\
            <div class="clear"></div>\
            <div class="grid np">\
                <div class="hor-scroll">\
                    <table cellspacing="0" class="data order-tables">\
                        <colgroup>\
                            <col>\
                                <col width="1">\
                                <col width="1">\
                                <col width="1">\
                        </colgroup>\
                        <thead>\
                            <tr class="headings">\
                                <th class="no_col"><b>No</b>\
                                </th>\
                                <th><b>Description</b>\
                                </th>\
                                <th class="a-center"><b>Qty</b>\
                                </th>\
                                <th><b>Price</b>\
                                </th>\
                                <th class="last"><b>Subtotal</b>\
                                </th>\
                            </tr>\
                        </thead>\
                        <tbody>\
                        {order_items}\
                        </tbody>\
                    </table>\
                </div>\
            </div>\
            <br>\
            <div class="clear"></div>\
            <div class="entry-edit table-total">\
                <table width="100%" cellspacing="0">\
                    <colgroup>\
                        <col>\
                        <col width="1">\
                    </colgroup>\
                    <tbody>\
                        <tr>\
                            <td class="label">\
                                <strong>Subtotal</strong>\
                            </td>\
                            <td class="emph a-right">\
                                <strong><span class="price"><span class="price">{subtotal}</span></span></strong>\
                            </td>\
                        </tr>\
                        <tr>\
                            <td class="label">\
                                <strong>Tax</strong>\
                            </td>\
                            <td class="emph a-right">\
                                <strong><span class="price"><span class="price">{tax}</span></span></strong>\
                            </td>\
                        </tr>\
                        <tr>\
                            <td class="label">\
                                <strong>Grand Total</strong>\
                            </td>\
                            <td class="emph a-right">\
                                <strong><span class="price"><span class="price">{grand_total}</span></span></strong>\
                            </td>\
                        </tr>\
                        <tr>\
                            <td class="label">\
                                <strong>Total Paid</strong>\
                            </td>\
                            <td class="emph a-right">\
                                <strong><span class="price"><span class="price">{total_paid}</span></span></strong>\
                            </td>\
                        </tr>\
                    </tbody>\
                </table>\
            </div>\
        </div>\
    </body>\
    </html>\
    ';
var orderItemTemplate =
    '<tr class="border even">\
        <td class="no_col">{no}</td>\
        <td>\
            <div class="item-container" id="order_item_{id}">\
                <div class="item-text">\
                    <h5 class="title">\
                        <span id="order_item_{id}_title">{name}</span>\
                    </h5>\
                    <div><strong>SKU:</strong> {sku}</div>\
                </div>\
            </div>\
        </td>\
        <td class="a-center">{qty}</td>\
        <td class="a-right">\
            <span class="price-excl-tax">\
                <span class="price"><span class="price">{price}</span></span>\
            </span>\
        <br>\
        </td>\
        <td class="a-right last">\
            <span class="price-excl-tax">\
                <span class="price"><span class="price">{subtotal}</span></span>\
            </span>\
            <br>\
        </td>\
    </tr>';
function printReceipt(order){
    var itemNo = 1;
    var subtotal = parseFloat(0);
    var subtotalInclTax = parseFloat(0);

    var sortedOrder = sortObject(order);
    var orderItemsOutput = '';
    jQuery.each(sortedOrder, function (i, orderItem) {
        //calc
        orderItem.subtotal = orderItem.price * orderItem.qty;
        orderItem.subtotalInclTax = orderItem.subtotal * (1 + orderItem.tax / 100);

        var orderOption = '';
        if (orderItem.options.length > 0) {
            jQuery.each(orderItem.options, function (i, option) {
//                var optionName = option.name.replace('[', '][');
                //optionInput+='<span class="option-title">'+option.optionTitle+': </span><span class="option-name">'+option.qty+'x '+option.title+'</span></li>';
                //orderOption += optionInput;
            });
        }

        //orderOption+='</ul>';
        var tempOrderItem = {};
        if (orderItem.qty == 0) {
            tempOrderItem.style = 'style="display:none"';
        }

        tempOrderItem.no = itemNo;
        tempOrderItem.id = orderItem.id;
        tempOrderItem.name = orderItem.name;
        tempOrderItem.sku = orderItem.sku;
        tempOrderItem.qty = orderItem.qty;
        tempOrderItem.tax = orderItem.tax;
        tempOrderItem.price = formatCurrency(orderItem.price, receiptPriceFormat);
        tempOrderItem.subtotal = formatCurrency(orderItem.subtotal, receiptPriceFormat);
        orderItemsOutput += nano(orderItemTemplate, tempOrderItem);
        itemNo++;
        subtotal += parseFloat(orderItem.subtotal);
        subtotalInclTax += parseFloat(orderItem.subtotalInclTax);
    })
    var tax = subtotalInclTax - subtotal;

    var orderReceipt = {};
    orderReceipt.store_information = xposConfig.storeInfo;
    orderReceipt.order_no = 'N/A';
    orderReceipt.order_date = getCurrentDate();
    orderReceipt.customer_name = jQuery('#customer_name').text();
    orderReceipt.order_time = getCurrentTime();
    orderReceipt.payment = 'Check / Money order';
    orderReceipt.order_items = orderItemsOutput;
    orderReceipt.subtotal = formatCurrency(subtotal, receiptPriceFormat);
    orderReceipt.tax = formatCurrency(tax, receiptPriceFormat);
    orderReceipt.grand_total = formatCurrency(subtotalInclTax, receiptPriceFormat);
    orderReceipt.total_paid = formatCurrency(subtotalInclTax, receiptPriceFormat);
    orderReceipt.skin_url = xposConfig.skinUrl;
    var data = nano(pageReceiptTemplate,orderReceipt);
    var receipt = window.open('', '', 'width=900,height=600,resizable=1,scrollbars=1');
    receipt.document.write(data);
    receipt.document.close();
    receipt.print();

}

function getCurrentDate(){
    var today = new Date();
    var dd = today.getDate();
    var mm = today.getMonth()+1; //January is 0!
    var yyyy = today.getFullYear();
    if(dd<10) {
        dd='0'+dd
    }
    if(mm<10) {
        mm='0'+mm
    }
    return mm+'/'+dd+'/'+yyyy;
}

function getCurrentTime(){
    var today = new Date();
    var hh = today.getHours();
    var mm = today.getMinutes(); //January is 0!
    var ss = today.getSeconds();
    if(hh<10) {
        hh='0'+hh
    }
    if(mm<10) {
        mm='0'+mm
    }
    return hh+':'+mm+':'+ss;
}