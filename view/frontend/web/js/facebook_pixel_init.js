define([
   'jquery'
], function ($) {
   "use strict";
      return function (config) {
         var id = config.id;
         var actionName = config.action;
         var isPageView = config.pageView;
         var productData = config.productData;
         var categoryData = config.categoryData;
         var addToWishListData = config.addToWishList;
         var search = (config.searchdata['enable'] != false ? (document.querySelector("#search") == null ?
             'disable' : document.querySelector("#search").value == "" ? 'disable' :
            document.querySelector("#search").value) : 'disable');
         var orderData = config.orderData;

         !function (f, b, e, v, n, t, s) {
            if (f.fbq) return; n = f.fbq = function () {
               n.callMethod ?
               n.callMethod.apply(n, arguments) : n.queue.push(arguments)
            };
            if (!f._fbq) f._fbq = n; n.push = n; n.loaded = !0; n.version = '2.0';
            n.queue = []; t = b.createElement(e); t.async = !0;
            t.src = v; s = b.getElementsByTagName(e)[0];
            s.parentNode.insertBefore(t, s)
         }(window, document, 'script',
            'https://connect.facebook.net/en_US/fbevents.js');

         window.facebookpixel = function () {
            fbq('init', id);

            if (actionName == 'catalog_product_view' && productData['enable'] != false) {
               fbq('track', 'ViewProductContent', {
                   content_name: productData.content_name ,
                   content_ids: productData.content_ids,
                   content_type: 'product',
                   value: productData.value,
                   currency: productData.currency
               });
            }
            if (actionName == 'catalog_category_view' && categoryData['enable'] != false) {
               fbq('trackCustom', 'ViewCategory', {
                   content_name: categoryData.content_name,
                   content_ids: categoryData.content_ids,
                   content_type: 'product_group',
                   currency: categoryData.currency
               });
            }
            if (addToWishListData['enable'] != false) {
               fbq('track', 'AddToWishlist', {
                   content_type : 'product',
                   content_ids : addToWishListData.content_ids,
                   content_name : addToWishListData.content_name,
                   currency: addToWishListData.currency,
                   value: addToWishListData.price
               });
            }
            if (search != 'disable') {
               fbq('track', 'Search', {
                   search_string : search
               });
            }
            if (orderData['enable'] != false) {
               fbq('track', 'Purchase', {
                   content_ids: orderData.content_ids,
                   content_type: 'product',
                   contents: orderData.contents,
                   value: orderData.value,
                   num_items : orderData.num_items,
                   currency: orderData.currency
               });
            }
            if (actionName == 'checkout_index_index' && isPageView != 'enable') {
               fbq.disablePushState = true;
            }
            if (isPageView == 'enable') {
               fbq('track', 'PageView');
            }
         }
         return window.facebookpixel();
      }
});
