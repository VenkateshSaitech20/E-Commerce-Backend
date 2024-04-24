<?php

use Illuminate\Routing\Router;

Admin::routes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
    'as'            => config('admin.route.prefix') . '.',
], function (Router $router) {

    $router->get('/', 'HomeController@index')->name('admin.home');
    $router->get('view_orders/{id}', 'ViewOrderController@index');
    $router->get('get_category', 'GeneralController@GetCategory');
    $router->get('live_chat', 'HomeController@live_chat');
    $router->resource('categories', CategoryController::class);
    $router->resource('sub-categories', SubCategoryController::class);
    $router->resource('items', ItemController::class);
    $router->resource('taxes', TaxController::class);
    $router->resource('sellers', RestaurantController::class);
    $router->resource('seller-timings', RestaurantTimingController::class);
    $router->resource('days', DayController::class);
    $router->resource('tickets', TicketController::class);
    $router->resource('ticket-histories', TicketHistoryController::class);
    $router->resource('buyer-complaints', CustomerComplaintController::class);
    $router->resource('faqs', FaqController::class);
    $router->resource('statuses', StatusController::class);
    $router->resource('banners', BannerController::class);
    $router->resource('food-cuisines', FoodCuisineController::class);
    $router->resource('buyers', CustomerController::class);
    $router->resource('favourite-seller', FavouriteRestaurantController::class);
    $router->resource('favourite-items', FavouriteItemController::class);
    $router->resource('buyer-addresses', CustomerAddressController::class);
    $router->resource('orders', OrderController::class);
    $router->resource('order-items', OrderItemController::class);
    $router->resource('notifications', NotificationController::class);
    $router->resource('buyer-notifications', CustomerNotificationController::class);
    $router->resource('delivery-partner-notifications', DeliveryBoyNotificationController::class);
    $router->resource('seller-notifications', RestaurantNotificationController::class);
    $router->resource('payment-modes', PaymentModeController::class);
    $router->resource('payment-types', PaymentTypeController::class);
    $router->resource('order-statuses', OrderStatusController::class);
    $router->resource('app-settings', AppSettingController::class);
    $router->resource('buyer-app-settings', CustomerAppSettingController::class);
    $router->resource('seller-settings', RestaurantSettingController::class);
    $router->resource('privacy-policies', PrivacyPolicyController::class);
    $router->resource('product-types', FoodTypeController::class);
    $router->resource('user-types', UserTypeController::class);
    $router->resource('promo-codes', PromoCodeController::class);
    $router->resource('promo-types', PromoTypeController::class);
    $router->resource('complaint-types', ComplaintTypeController::class);
    $router->resource('faq-categories', FaqCategoryController::class);
    $router->resource('delivery-partners', DeliveryBoyController::class);
    $router->resource('tags', TagController::class);
    $router->resource('address-types', AddressTypeController::class);
    $router->resource('seller-cuisines', RestaurantCuisineController::class);
    $router->resource('seller-earnings', RestaurantEarningController::class);
    $router->resource('seller-wallet-histories', RestaurantWalletHistoryController::class);
    $router->resource('seller-withdrawals', RestaurantWithdrawalController::class);
    $router->resource('delivery-partner-app-settings', DeliveryBoyAppSettingController::class);
    $router->resource('seller-app-settings', RestaurantAppSettingController::class);
    $router->resource('buyer-promo-histories', CustomerPromoHistoryController::class);
    $router->resource('buyer-wallet-histories', CustomerWalletHistoryController::class);
    $router->resource('order-ratings', OrderRatingController::class);
    $router->resource('cancellation-reasons', CancellationReasonController::class);
    $router->resource('seller-categories', RestaurantCategoryController::class);
    $router->resource('buyer-complaints', CustomerComplaintController::class);
    $router->resource('delivery-partner-earnings', DeliveryBoyEarningController::class);
    $router->resource('delivery-partner-wallet-histories', DeliveryBoyWalletHistoryController::class);
    $router->resource('delivery-partner-withdrawals', DeliveryBoyWithdrawalController::class);
    $router->resource('zones', ZoneController::class);
    $router->get('create_zones/{id}', 'HomeController@create_zone');
    $router->get('view_zones/{id}', 'HomeController@view_zone');
});
