<?php

Route::view("/", 'admin');

Route::get('languages/translate', 'LanguageController@translate');

Route::resource('languages', 'LanguageController')->except([
    'edit', 'create'
]);

Route::put('categories/update-hierarchy', 'CategoryController@updateHierarchy');
Route::resource('categories', 'CategoryController');

Route::resource('groups-attributes', 'AttributeGroupController')->except([
    'edit', 'create'
]);

Route::resource('attributes', 'AttributeController');

Route::resource('attribute-values', 'AttributeValueController')->except([
    'edit', 'create', 'show'
]);

Route::resource('excluded-categories', 'CategoryExcludedAttributeController')->except([
    'edit', 'create', 'show', 'update'
]);

Route::group(['prefix' => 'filemanager', 'middleware' => ['web', 'auth']], function () {
    \UniSharp\LaravelFilemanager\Lfm::routes();
});

Route::resource('currencies', 'CurrencyController')->except(
    ['edit', 'show', 'create']
);

Route::resource('stock-statuses', 'StockStatusController')->except(
    ['edit', 'show', 'create']
);

Route::resource('price-units', 'PriceUnitController')->except(
    ['edit', 'show', 'create']
);

Route::resource('user-groups', 'UserGroupController')->except(
    ['edit', 'show', 'create']
);

Route::delete('products/{id}/variants', 'ProductController@destroyVariants');
Route::put('products/refresh-prices/', 'ProductController@refreshPrices');
Route::delete('products', 'ProductController@deleteProducts');
Route::post('products/copy', 'ProductController@copyProducts');
Route::put('products/mass-edit', 'ProductController@massEdit');

Route::resource('products', 'ProductController');

Route::resource('users', 'UserController')->except(
    ['edit', 'show', 'create']
);

Route::post('set-tmp-file', 'SettingController@setTmpFile');

Route::resource('settings', 'SettingController')->except(
    ['edit', 'show', 'create']
);

Route::resource('locations', 'LocationController')->except(
    ['show', 'create']
);

Route::resource('order-statuses', 'OrderStatusController')->except(
    ['show', 'create']
);

Route::get('orders/not-viewed', 'OrderController@not_viewed');
Route::get('orders/{order_id}/histories', 'OrderController@histories');
Route::post('orders/{order_id}/histories', 'OrderController@storeHistory');

Route::resource('orders', 'OrderController');

Route::resource('banners', 'BannerController')->except(
    ['show', 'create']
);

Route::put('/filter/refresh', 'FilterController@refresh');

Route::get('/filter/category/{category_id}', 'FilterController@categoryAttributes');

Route::resource('informations', 'InformationController')->except(
    ['show', 'create']
);

Route::resource('articles', 'ArticleController')->except(
    ['show', 'create',]
);

Route::resource('testimonials', 'TestimonialController')->except(
    ['show', 'create', 'edit', 'store']
);

Route::resource('modules', 'ModuleController');

Route::resource('layouts', 'LayoutController');

Route::resource('totals', 'TotalController');

Route::resource('marketing-seo', 'SEOController');

Route::group(['prefix' => 'nova-poshta'], function(){
    Route::get('/generate-data', 'NovaPoshtaController@generate')->name('nova_poshta.generate');
});

Route::get('excel/export', 'ExcelController@export');

Route::resource('excel', 'ExcelController')->except(
    ['edit', 'show', 'create']
);

Route::resource('redirects', 'RedirectController')->except(
    ['edit', 'show', 'create']
);

Route::view('xml-import', 'admin');
Route::post('/xml/add-to-queue', 'XmlController@addToQueue');
Route::get('/xml/get-queue-status', 'XmlController@getQueueStatus');
Route::delete('/xml/break-manual/{id}', 'XmlController@breakManual');
Route::get('/xml/download-report/{id}', 'XmlController@downloadReport');
Route::post('/xml/get-source-categories', 'XmlController@getSourceCategories');

Route::view('xml-export', 'admin');


Route::view('excel', 'admin');
Route::post('/excel/add-to-queue', 'ExcelController@addToQueue');
Route::get('/excel/get-queue-status', 'ExcelController@getQueueStatus');
Route::delete('/excel/break-manual/{id}', 'ExcelController@breakManual');


Route::get('/search', 'SearchController@index');

Route::resource('auto-sync', 'AutoSyncController')->except(
    ['edit', 'show', 'create']
);

Route::resource('sync-configurations', 'SyncConfigurationController')->except(
    ['edit', 'show', 'create']
);

Route::put('export-products-list/products', 'ExportProductsListController@addProductsToList');

Route::resource('export-products-list', 'ExportProductsListController')->except(
    ['edit', 'show', 'create']
);

Route::resource('export-configurations', 'ExportConfigurationController')->except(
    ['edit', 'show', 'create']
);


Route::get('suppliers', 'SupplierController@index');

Route::post('suppliers', 'SupplierController@createNewProduct');

Route::put('suppliers/{supplier_id}', 'SupplierController@makeNewRelation');

Route::put('suppliers/{supplier_id}/sku', 'SupplierController@update');

Route::put('suppliers-categories/{supplier_category_id}', 'SupplierController@updateSupplierCategory');

Route::get('suppliers-categories', 'SupplierController@getSuppliersCategories');

//Syncs
Route::get('externals-api', 'Syncs\SyncsController@index')->name('externals-api');
Route::post('externals-api/moy-sklad/auth', 'Syncs\Moysklad\MoyskladController@authorization');
Route::put('externals-api/moy-sklad', 'Syncs\Moysklad\MoyskladController@update');

//Moysklad
Route::group(['prefix' => 'externals-api/', 'middleware' => ['external_api_auth']], function () {

    Route::group(['prefix' => 'moy-sklad/'], function () {

        Route::get('/', 'Syncs\Moysklad\MoyskladController@show');

        Route::view('edit', 'admin');

        Route::put('{data_type}/automatic-mode ', 'Syncs\Moysklad\MoyskladController@automaticMode');
        Route::put('{data_type}/hourly-mode', 'Syncs\Moysklad\MoyskladController@hourlyMode');
        Route::post('connection', 'Syncs\Moysklad\MoyskladController@connection');

        Route::group(['prefix' => 'users/', 'middleware' => ['check_external_api_status']], function () {
            Route::post('upload', 'Syncs\Moysklad\CounterpartyController@uploadCounterparties');
            Route::post('download', 'Syncs\Moysklad\CounterpartyController@downloadCounterparties');
        });

        Route::group(['prefix' => 'products/', 'middleware' => ['check_external_api_status']], function () {
            Route::post('upload', 'Syncs\Moysklad\ProductController@uploadProducts');
            Route::post('download', 'Syncs\Moysklad\ProductController@downloadProducts');

        });

        Route::group(['prefix' => 'categories/', 'middleware' => ['check_external_api_status']], function () {
            Route::post('upload', 'Syncs\Moysklad\CategoryController@uploadCategories');
            Route::post('download', 'Syncs\Moysklad\CategoryController@downloadCategories');
        });

        Route::group(['prefix' => 'orders/', 'middleware' => ['check_external_api_status']], function () {
            Route::post('upload', 'Syncs\Moysklad\OrderController@uploadOrders');
            Route::post('download', 'Syncs\Moysklad\OrderController@downloadOrders');
        });

        Route::group(['prefix' => 'prices_quantities/', 'middleware' => ['check_external_api_status']], function () {
            Route::post('download', 'Syncs\Moysklad\ProductController@downloadPricesQuantities');
        });

        Route::group(['prefix' => 'rates/', 'middleware' => ['check_external_api_status']], function () {
            Route::post('download', 'Syncs\Moysklad\CurrencyController@downloadCurrenciesRate');
        });

        Route::group(['prefix' => 'currencies/', 'middleware' => ['check_external_api_status']], function () {
            Route::post('upload', 'Syncs\Moysklad\CurrencyController@uploadCurrencies');
            Route::post('download', 'Syncs\Moysklad\CurrencyController@downloadCurrencies');
        });

        Route::get('info-sync', 'Syncs\Moysklad\MoyskladController@syncInfo');
        Route::put('stop-sync', 'Syncs\Moysklad\MoyskladController@stopSync');
        Route::put('pause-sync', 'Syncs\Moysklad\MoyskladController@pauseSync');
        Route::put('resume-sync', 'Syncs\Moysklad\MoyskladController@resumeSync');

        Route::put('{data_type}/parameters/{action}', 'Syncs\Moysklad\ParametersController@setParameters');
        Route::get('{data_type}/download-log', 'Syncs\Moysklad\MoyskladController@downloadLog');
    });
});

Route::prefix('design')->name('design')->group(function () {
    Route::get('change', 'DesignController@switchOnly');
});


