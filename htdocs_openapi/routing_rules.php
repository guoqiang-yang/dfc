<?php

return array(
	// 首页
	array("/", "index.php", "GET"),
    
	// 订单
	array("/:version/order/cancelOrder",		"order/cancel_order.php",  array("GET","POST")),
	array("/:version/order/checkoutOrder",	"order/checkout_order.php",  array("GET","POST")),
	array("/:version/order/createOrder",		"order/create_order.php",  array("GET","POST")), 
    array("/:version/order/creatOrder",		"order/create_order.php",  array("GET","POST")),   //下单 文档接口错误兼容
	array("/:version/order/myOrder",			"order/my_order.php",  array("GET","POST")),
	array("/:version/order/orderDetail",		"order/order_detail.php",  array("GET","POST")),

	// 商品
	array("/:version/products/getAllTypes",			"shop/get_all_types.php", array("GET","POST")),
    array("/:version/products/getAllTypesDetail",   "shop/get_all_types_detail.php", array("GET","POST")),
	array("/:version/products/getProductList",		"shop/get_product_list.php",  array("GET","POST")),
	array("/:version/products/getSubTypes",			"shop/get_sub_types.php",  array("GET","POST")),
	array("/:version/products/searchProducts",		"shop/search_products.php",  array("GET","POST")),		//todo 等qihua接口，先介入现在接口
    
    // 用户（强验证）
	array("/:version/user/getUser",                 "user/get_user.php", array("GET", "POST")),
    array("/:version/user/getUsers",                "user/get_users.php", array("GET", "POST")),
    array("/:version/user/quickLogin",              "user/quick_login.php", array("GET", "POST")),
    
    // 通用访问接口（非线上使用）
    array("/:version/cc/cc",                        "xxxx.php", array("GET", "POST")),
);

