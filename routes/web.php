<?php

Route::prefix(\App\Http\Middleware\LocaleMiddleware::getLocale())->group(function (){

//Home
    Route::get('/', 'HomeController@index')->name('home');

    Route::get('/test-email', 'HomeController@testMail');

    //Route::get('/load_users', 'HomeController@load_users');

    Route::get('supp', 'Admin\SupplierTest@index');

    Route::get('/test-gd', 'HomeController@gd');

    Route::get('/test-cifroteh', 'HomeController@test');

    Auth::routes();

    Route::get('auth/{provider}', 'Auth\LoginController@redirectToProvider');
    Route::get('auth/{provider}/callback', 'Auth\LoginController@handleProviderCallback');

//Account
    Route::get('/register', ['uses' => 'Auth\RegisterController@showRegistrationForm'])->name('account.register');
    Route::post('/register', ['uses' => 'Auth\RegisterController@register'])->name('account.register.action');

    Route::post('/login', 'Auth\LoginController@login')->name('account.login');

    Route::post('/forgotten', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('account.forgotten');

    Route::prefix('account')->middleware('auth')->group(function () {

        Route::get('/info', 'Auth\CustomerController@info');

        Route::get('/logout', 'Auth\LoginController@logout')->name('account.logout');

        Route::get('/get-orders', 'Auth\CustomerController@orders')->name('account.orders');

        Route::get('/get-orders/{order_id}/', 'Auth\CustomerController@order')->name('account.order');

        Route::get('/{path?}/{order_id?}', 'Auth\CustomerController@index')->name('account');

        Route::post('/{path?}', 'Auth\CustomerController@update')->name('account.update');
    });



    Route::prefix('wishlist')->group(function(){
        Route::get('/', 'Auth\WishlistController@index')->name('wishlist');
        Route::post('/{product_id}', 'Auth\WishlistController@store');
        Route::delete('/{product_id}', 'Auth\WishlistController@destroy');
    });

    //Compare
    Route::prefix('comparelist')->group(function(){
        Route::get('', 'ComparelistController@productsIds');
        Route::post('/{product_id}', 'ComparelistController@store');
        Route::delete('/{product_id}', 'ComparelistController@destroy');
    });

    Route::get('/compare-products', 'ComparelistController@index')->name('comparelist');
    Route::get('/compare-products/{category_id}', 'ComparelistController@show')->name('comparelist.category');
    Route::get('/compare-info/{category_id}', 'ComparelistController@categoryProducts');

    //Components
    Route::prefix('component')->group(function () {
        Route::prefix('shipping')->group(function () {
            Route::prefix('nova_poshta')->group(function () {
                Route::get('translation', 'Shipping\NovaPoshtaController@translation');
                Route::get('areas', 'Shipping\NovaPoshtaController@areas');
                Route::get('cities', 'Shipping\NovaPoshtaController@cities');
                Route::get('departments', 'Shipping\NovaPoshtaController@departments');
            });
        });

        Route::prefix('payment')->group(function () {
            Route::prefix('liqpay')->group(function () {
                Route::get('info', 'Payments\LiqPayController@info');
                Route::get('callback', 'Payments\LiqPayController@callback')->name('liqpay.callback');
            });
        });

    });

    Route::get('/change-currency', 'ProductController@changeCurrency')->name('change.currency');

    Route::post('/set-view-type/{type?}', 'ProductController@changeViewType');

//Category
    Route::get('{slug}/c{id}/{params?}/', 'CategoryController@index')->name('category');
    Route::get('/filters', 'CategoryController@getAttributes')->name('category.filters');

//Product
    Route::get('{slug}/p{id}', 'ProductController@show')->name('product');
    Route::get('products/{main_id}/attributes/{attribute_id}/get-variant-params', 'ProductController@getVariantParams')->name('product.variant_params');
    Route::get('products/{main_id}/get-variant-info', 'ProductController@getVariantInfo')->name('product.variant_info');
    Route::post('review/{id}', 'ProductController@review')->name('product.review');


    //Route::get('products', 'ProductController@getCategoryProducts')->name('api.products');

//Manufacturer
    //Route::get('{slug}/m{id}/', 'ManufacturerController@index')->name('manufacturer');

//Cart
    Route::get('/cart/total', 'CartController@total');
    Route::resource('cart', 'CartController');

//Search
    Route::prefix('/search')->group(function () {
        Route::get('/results', 'SearchController@results')->name('search.results');
        Route::post('/autocomplete', 'SearchController@autocomplete');

        Route::get('/{params?}', 'SearchController@show')->name('search');
    });

    //Checkout
    Route::group(['middleware' => 'can_create_order', 'prefix' => 'checkout'], function () {
        Route::get('/', 'CheckoutController@show')->name('checkout');
        Route::get('/translation', 'CheckoutController@translation');
        Route::get('/methods/{type}', 'CheckoutController@methods');
        Route::get('/method/{code}', 'CheckoutController@method');
        Route::post('/save/{type}', 'CheckoutController@save');
        Route::post('/confirm', 'CheckoutController@confirm');
        Route::get('/success', 'CheckoutController@success')->middleware(['except' => 'can_create_order'])->name('checkout.success');
    });
    Route::post('checkout/confirm-fast-order', 'CheckoutController@fast_order');

    Route::get('checkout/success', 'CheckoutController@success')->name('checkout.success');

//Article
    Route::get('/articles', 'ArticleController@index')->name('articles');
    Route::get('{slug}/a{id}', 'ArticleController@show')->name('article');

//Information
    Route::get('{slug}/i{id}', 'InformationController@show')->name('information');

//Message
    Route::post('/send-message', 'MessageController@save')->name('message.send');

//Testimonial
    Route::get('/testimonials', 'TestimonialsController@index')->name('testimonials');
    Route::post('/testimonial-send', 'TestimonialsController@save')->name('testimonial.send');

    Route::get('/contacts', 'ContactController@index')->name('contacts');

    Route::get('/sitemap.xml', 'SitemapController@index');

    Route::get('setlocale/{lang}', 'LocaleController@handle')->name('setlocale');

//Export
    Route::get('/export-products/{configuration_id}', 'ExportProducts@index')->name('export-products');
    Route::get('/export-products/{configuration_id}/force', 'ExportProducts@force_list');
});