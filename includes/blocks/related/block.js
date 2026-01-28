/*!
 impleCode Admin scripts v1.0.0 - 2018-12
 Adds appropriate scripts to admin settings
 (c) 2019 impleCode - https://implecode.com
 */
/* globals ic_register_block */
ic_register_block('ic-epc/related-products', ['postId', 'postType'], 'related_products_widget');