<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\LoginController;

// CMS Controllers
use App\Http\Controllers\Cms4Controllers\{
    ArticleCategoryController, ArticleFrontController, ArticleController, AlbumController, PageController, MenuController, FileManagerController
};

// Settings
use App\Http\Controllers\Settings\{
    PermissionController,
    AccountController,
    AccessController,
    UserController,
    LogsController,
    RoleController,
    WebController,
    AnnouncementController
};

// Ecommerce Controller
use App\Http\Controllers\Ecommerce\{
    CustomerController,
    CustomerFrontController,
    ProductCategoryController,
    ProductController,
    PurchaseAdviceController,
    ProductFrontController,
    InventoryReceiverHeaderController,
    PromoController,
    DeliverablecitiesController,
    CouponController,
    CouponFrontController,
    CartController,
    MyAccountController,
    SalesController,
    ReportsController,
    InventoryRequestController
};

use App\Http\Controllers\CatalogueController;
use App\Http\Controllers\CodeController;
use App\Http\Controllers\IssuanceController;
use App\Http\Controllers\KpiController;
use App\Models\Ecommerce\Product;
use Illuminate\Http\Request;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


 Route::get('/', function () {
     return redirect(route('shop'));
 });

Route::prefix('kpi')->group(function () {
    Route::get('/user', [KPIController::class, 'user'])->name('kpi.user');
    Route::get('/mrs', [KPIController::class, 'mrs'])->name('kpi.mrs');
});

// CMS4 Front Pages
    Route::get('/', [FrontController::class, 'home'])->name('home');
    Route::get('/privacy-policy/', [FrontController::class, 'privacy_policy'])->name('privacy-policy');
    Route::post('/contact-us', [FrontController::class, 'contact_us'])->name('contact-us');

    Route::get('/search', [FrontController::class, 'search'])->name('search');

    //News Frontend
        Route::get('/news/', [ArticleFrontController::class, 'news_list'])->name('news.front.index');
        Route::get('/news/{slug}', [ArticleFrontController::class, 'news_view'])->name('news.front.show');
        Route::get('/news/{slug}/print', [ArticleFrontController::class, 'news_print'])->name('news.front.print');
        Route::post('/news/{slug}/share', [ArticleFrontController::class, 'news_share'])->name('news.front.share');

        Route::get('/albums/preview', [FrontController::class, 'test'])->name('albums.preview');
        Route::get('/search-result', [FrontController::class, 'seach_result'])->name('search.result');
    //
//



    Route::get('/login', [CustomerFrontController::class, 'login'])->name('customer-front.login');
    Route::post('/login', [CustomerFrontController::class, 'customer_login'])->name('customer-front.customer_login');

    Route::get('/customer-sign-up', [CustomerFrontController::class, 'sign_up'])->name('customer-front.sign-up');
    Route::post('/customer-sign-up', [CustomerFrontController::class, 'customer_sign_up'])->name('customer-front.customer-sign-up');

    Route::get('/forgot-password', [CustomerFrontController::class, 'forgot_password'])->name('customer-front.forgot_password');
    Route::post('/forgot-password', [CustomerFrontController::class, 'sendResetLinkEmail'])->name('customer-front.send_reset_link_email');

    Route::get('/reset-password/{token}', [CustomerFrontController::class, 'showResetForm'])->name('customer-front.reset_password');
    Route::post('/reset-password', [CustomerFrontController::class, 'reset'])->name('customer-front.reset_password_post');


// Ecommerce Pages

    Route::get('/product-list', [ProductFrontController::class, 'list'])->name('product.list');
    Route::get('/products/{category}',[ProductFrontController::class, 'product_list'])->name('product.front.list');
    Route::get('/product-details/{slug}', [ProductFrontController::class, 'show'])->name('product.front.show');
    Route::post('add-to-cart',[CartController::class, 'add_to_cart'])->name('product.add-to-cart');

    Route::post('/payment-notification', [CartController::class, 'receive_data_from_payment_gateway'])->name('cart.payment-notification');

    Route::get('/account/order/{id}/details', [MyAccountController::class, 'viewDetails'])->name('my-account.order.details');
    Route::get('/employee-lookup', [UserController::class, 'employee_lookup'])->name("users.employee_lookup");

    // ECOMMERCE CUSTOMER AUTH ROUTES
        Route::group(['middleware' => ['authenticated']], function () {
            Route::post('/add-manual-coupon', [CouponFrontController::class, 'add_manual_coupon'])->name('add-manual-coupon');
            Route::get('/show-coupons', [CouponFrontController::class, 'collectibles'])->name('show-coupons');

            Route::get('/manage-account', [MyAccountController::class, 'manage_account'])->name('customer.manage-account');

            Route::post('/account-update', [MyAccountController::class, 'update_personal_info'])->name('my-account.update-personal-info');
            Route::get('/account/change-password', [MyAccountController::class, 'change_password'])->name('my-account.change-password');
            Route::post('/account/change-password', [MyAccountController::class, 'update_password'])->name('my-account.update-password');
            Route::get('/account-logout', [CustomerFrontController::class, 'logout'])->name('account.logout');

            Route::get('/my-orders', [MyAccountController::class, 'orders'])->name('profile.sales');
            Route::post('/account/cancel/order', [MyAccountController::class, 'cancel_order'])->name('my-account.cancel-order');
            Route::post('/account/reorder', [MyAccountController::class, 'reorder'])->name('my-account.reorder');
            Route::put('/account/order/{id}/update', [MyAccountController::class, 'updateOrder'])->name('my-account.update.order');
            Route::get('/account/order/{id}/{status}', [MyAccountController::class, 'submitRequest'])->name('my-account.submit.request');
            Route::get('/account/order/{id}/order/{status}/submit', [MyAccountController::class, 'orderRequest'])->name('my-account.submit.order.request');
            Route::get('/mrs/updateRequestApproval', [MyAccountController::class, 'updateRequestApproval'])->name('mrs.updateRequestApproval');
            Route::post('/mrs/getDetails', [MyAccountController::class, 'getDetails'])->name('mrs.getDetails');
            Route::post('/mrs/deleteItem', [MyAccountController::class, 'deleteItem'])->name('mrs.deleteItem');
            Route::post('/mrs/saveItem', [MyAccountController::class, 'saveItem'])->name('mrs.saveItem');

            // CART CONTROLLER
            Route::get('/checkout', [CartController::class, 'checkout'])->name('cart.front.checkout');
            Route::post('/temp_save',[CartController::class, 'save_sales'])->name('cart.temp_sales');
            Route::get('/order-success',[CartController::class, 'success'])->name('order.success');
            Route::post('/submit-payment', [CartController::class, 'submit_payment'])->name('order.submit-payment');

            Route::get('/cart', [CartController::class, 'cart'])->name('cart.front.show');
            Route::post('cart-remove-product', [CartController::class, 'remove_product'])->name('cart.remove_product');
            Route::post('cart-update', [CartController::class, 'cart_update'])->name('cart.update');
            Route::post('proceed-checkout',[CartController::class, 'proceed_checkout'])->name('cart.front.proceed_checkout');
            // CART CONTROLLER

            //Product Catalogue
            Route::prefix('catalogue')->group(function () {
                Route::get('/home', [CatalogueController::class, 'home'])->name('catalogue.home');
                Route::get('/category-products/{category}', [CatalogueController::class, 'category_products'])->name('catalogue.category_products');
                //Route::get('/load-products', [CatalogueController::class, 'load_products'])->name('catalogue.home');
                Route::any('/search', [CatalogueController::class, 'search'])->name('catalogue.search');
            });

            Route::post('/fetch_codes', [CodeController::class, 'fetch_codes'])->name('code.fetch_codes');
            Route::get('/account/approval/order/{id}', [MyAccountController::class, 'approvalStatus'])->name('my-account.order.approval');

            Route::get('/pa/generate_report', [PurchaseAdviceController::class, 'generate_report'])->name('pa.generate_report_customer');
        });
        
    
    Route::get('/catalogue/home', [CatalogueController::class, 'home'])->name('catalogue.home');

    //INVENTORY
    Route::prefix('inventory')->group(function () {
        Route::get('/new-stock/{id}/update/status', [InventoryRequestController::class, 'updateStatus'])->name('new-stock.update.status');
        Route::get('/updateRequestApproval', [InventoryRequestController::class, 'updateRequestApproval'])->name('new-stock.updateRequestApproval');
        Route::get('/new-stock/{id}/submit/{type}', [InventoryRequestController::class, 'submitRequest'])->name('new-stock.submit.request');
        Route::post('/imf-update/{id}', [InventoryRequestController::class, 'update'])->name('imf.update');
        Route::resource('/new-stock', InventoryRequestController::class);
    
        Route::get('/{code}', function($code) {
            return json_encode(array(
                "code" => $code,
                "stocks" => rand(1, 100)
            ));
        })->name('inventory');
    });

    Route::post('/products-search', [ProductController::class, 'product_search'])->name('products.search');
    Route::post('/products-lookup', [ProductController::class, 'product_lookup'])->name('products.lookup');
    Route::get('/download-template',  [InventoryRequestController::class, 'download'])->name('download.template');
    Route::get('/download-attached-files', [InventoryRequestController::class, 'downloadAttachedFiles'])->name('download.files');
    Route::get('/generate_report', [InventoryRequestController::class, 'generateReport'])->name('imf-request.generate_report');


    Route::get('/code/search', function (Request $request) {
        $input = $request->input('q');
        $product = Product::where('code', $input)->first();
        return response()->json($product);
    })->name('product.search');

    // ADMIN ROUTES
    Route::group(['prefix' => 'admin-panel'], function (){
        Route::get('/', [LoginController::class, 'showLoginForm'])->name('panel.login');

        Auth::routes();

        Route::group(['middleware' => 'admin'], function (){

            Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
            Route::get('/admin/ecommerce-dashboard', [DashboardController::class, 'ecommerce'])->name('ecom-dashboard');
            Route::resource('/announcements', AnnouncementController::class);
            Route::post('/announcements/mail', [AnnouncementController::class, 'send'])->name('announcements.mail');
            Route::post('/announcements/status', [AnnouncementController::class, 'change_status'])->name('announcements.change.status');

            // Account
            Route::prefix('account')->group(function () {
                Route::get('/edit', [AccountController::class, 'edit'])->name('account.edit');
                Route::put('/update', [AccountController::class, 'update'])->name('account.update');
                Route::put('/update_email', [AccountController::class, 'update_email'])->name('account.update-email');
                Route::put('/update_password', [AccountController::class, 'update_password'])->name('account.update-password');
            });
            //

            // Website
            Route::post('/store-payment-option',[WebController::class, 'store_payment_option'])->name('setting.ecommerce-add-payment-option');
            Route::post('/update-payment-option',[WebController::class, 'update_payment_option'])->name('setting.ecommerce-update-payment-option');
            Route::post('/delete-payment-option',[WebController::class, 'delete_payment_option'])->name('setting.ecommerce-delete-payment-option');

            Route::prefix('website-settings')->group(function () {
                Route::get('/edit', [WebController::class, 'edit'])->name('website-settings.edit');
                Route::put('/update', [WebController::class, 'update'])->name('website-settings.update');
                Route::post('/update_contacts', [WebController::class, 'update_contacts'])->name('website-settings.update-contacts');
                Route::post('/update-ecommerce', [WebController::class, 'update_ecommerce'])->name('website-settings.update-ecommerce');
                Route::post('/update-paynamics', [WebController::class, 'update_paynamics'])->name('website-settings.update-paynamics');
                Route::post('/update_media_accounts', [WebController::class, 'update_media_accounts'])->name('website-settings.update-media-accounts');
                Route::post('/update_data_privacy', [WebController::class, 'update_data_privacy'])->name('website-settings.update-data-privacy');
                Route::post('/remove_logo', [WebController::class, 'remove_logo'])->name('website-settings.remove-logo');
                Route::post('/remove_icon', [WebController::class, 'remove_icon'])->name('website-settings.remove-icon');
                Route::post('/remove_media', [WebController::class, 'remove_media'])->name('website-settings.remove-media');
            });
            //

            // Audit
                Route::get('/audit-logs', [LogsController::class, 'index'])->name('audit-logs.index');
            //

            // Users
                Route::prefix('users')->group(function () {
                    Route::resource('users', UserController::class);
                    Route::post('/deactivate', [UserController::class, 'deactivate'])->name('users.deactivate');
                    Route::post('/activate', [UserController::class, 'activate'])->name('users.activate');
                });
                Route::get('/user-search/', [UserController::class, 'search'])->name('user.search');
                Route::get('/profile-log-search/', [UserController::class, 'filter'])->name('user.activity.search');
            //

            // Roles
                Route::prefix('role')->group(function () {
                    Route::resource('role', RoleController::class);
                    Route::post('/delete', [RoleController::class, 'destroy'])->name('role.delete');
                    Route::get('/restore/{id}', [RoleController::class, 'restore'])->name('role.restore');
                });
            //

            // Access
                Route::resource('/access', AccessController::class);
                Route::post('/roles_and_permissions/update', [AccessController::class, 'update_roles_and_permissions'])->name('role-permission.update');

                if (env('APP_DEBUG') == "true") {
                    // Permission Routes
                    Route::prefix('permission')->group(function () {
                        Route::resource('permission', PermissionController::class);
                        Route::get('/permission-search', [PermissionController::class, 'search'])->name('permission.search');
                        Route::post('/destroy', [PermissionController::class, 'destroy'])->name('permission.destroy');
                        Route::get('/restore/{id}', [PermissionController::class, 'restore'])->name('permission.restore');
                    });
                }
            //

            ###### CMS4 Standard Routes ######
                //Pages
                    Route::resource('/pages', PageController::class);
                    Route::get('/pages-advance-search', [PageController::class, 'advance_index'])->name('pages.index.advance-search');
                    Route::post('/pages/get-slug', [PageController::class, 'get_slug'])->name('pages.get_slug');
                    Route::put('/pages/{page}/default', [PageController::class, 'update_default'])->name('pages.update-default');
                    Route::put('/pages/{page}/customize', [PageController::class, 'update_customize'])->name('pages.update-customize');
                    Route::put('/pages/{page}/contact-us', [PageController::class, 'update_contact_us'])->name('pages.update-contact-us');
                    Route::post('/pages-change-status', [PageController::class, 'change_status'])->name('pages.change.status');
                    Route::post('/pages-delete', [PageController::class, 'delete'])->name('pages.delete');
                    Route::get('/page-restore/{page}', [PageController::class, 'restore'])->name('pages.restore');
                //

                // Albums
                    Route::resource('/albums', AlbumController::class);
                    Route::post('/albums/upload', [AlbumController::class, 'upload'])->name('albums.upload');
                    Route::put('/albums/quick/{album}', [AlbumController::class, 'quick_update'])->name('albums.quick_update');
                    Route::post('/albums/{album}/restore', [AlbumController::class, 'restore'])->name('albums.restore');
                    Route::post('/albums/banners/{album}', [AlbumController::class, 'get_album_details'])->name('albums.banners');
                    Route::delete('/many/album', [AlbumController::class, 'destroy_many'])->name('albums.destroy_many');
                //

                // News
                    Route::resource('/news', ArticleController::class)->except(['show', 'destroy']);
                    Route::get('/news-advance-search', [ArticleController::class, 'advance_index'])->name('news.index.advance-search');
                    Route::post('/news-get-slug', [ArticleController::class, 'get_slug'])->name('news.get-slug');
                    Route::post('/news-change-status', [ArticleController::class, 'change_status'])->name('news.change.status');
                    Route::post('/news-delete', [ArticleController::class, 'delete'])->name('news.delete');
                    Route::get('/news-restore/{news}', [ArticleController::class, 'restore'])->name('news.restore');

                    // News Category
                    Route::resource('/news-categories', ArticleCategoryController::class)->except(['show']);;
                    Route::post('/news-categories/get-slug', [ArticleCategoryController::class, 'get_slug'])->name('news-categories.get-slug');
                    Route::post('/news-categories/delete', [ArticleCategoryController::class, 'delete'])->name('news-categories.delete');
                    Route::get('/news-categories/restore/{id}', [ArticleCategoryController::class, 'restore'])->name('news-categories.restore');
                //

                // Files
                    Route::get('/laravel-filemanager', '\UniSharp\LaravelFilemanager\Controllers\LfmController@show')->name('file-manager.show');
                    Route::post('/laravel-filemanager/upload', '\UniSharp\LaravelFilemanager\Controllers\UploadController@upload')->name('file-manager.upload');
                    Route::get('/file-manager', [FileManagerController::class, 'index'])->name('file-manager.index');
                //

                // Menu
                    Route::resource('/menus', MenuController::class);
                    Route::delete('/many/menu', [MenuController::class, 'destroy_many'])->name('menus.destroy_many');
                    Route::put('/menus/quick1/{menu}', [MenuController::class, 'quick_update'])->name('menus.quick_update');
                    Route::get('/menu-restore/{menu}', [MenuController::class, 'restore'])->name('menus.restore');
                //
            ###### CMS4 Standard Routes ######


            ###### Ecommerce Routes ######
                // Customers
                    Route::resource('/admin/customers', CustomerController::class);
                    Route::post('/customer/deactivate', [CustomerController::class, 'deactivate'])->name('customer.deactivate');
                    Route::post('/customer/activate', [CustomerController::class, 'activate'])->name('customer.activate');
                //

                // Product Categories
                    Route::resource('/admin/product-categories',ProductCategoryController::class);
                    Route::post('/admin/product-category-get-slug', [ProductCategoryController::class, 'get_slug'])->name('product.category.get-slug');
                    Route::post('/admin/product-categories-single-delete', [ProductCategoryController::class, 'single_delete'])->name('product.category.single.delete');
                    Route::get('/admin/product-category/search', [ProductCategoryController::class, 'search'])->name('product.category.search');
                    Route::get('/admin/product-category/restore/{id}', [ProductCategoryController::class, 'restore'])->name('product.category.restore');
                    Route::get('/admin/product-category/{id}/{status}', [ProductCategoryController::class, 'update_status'])->name('product.category.change-status');
                    Route::post('/admin/product-categories-multiple-change-status',[ProductCategoryController::class, 'multiple_change_status'])->name('product.category.multiple.change.status');
                    Route::post('/admin/product-category-multiple-delete',[ProductCategoryController::class, 'multiple_delete'])->name('product.category.multiple.delete');
                    
                    /*Route::get('/product-favorites/', 'EcommerceControllers\FavoriteController@index')->name('product-favorite.list');
                    Route::get('/product-wishlist/', 'EcommerceControllers\WishlistController@index')->name('product-wishlist.list');*/
                //

                // Products
                    Route::resource('/admin/products', ProductController::class);
                    Route::get('/products-advance-search', [ProductController::class, 'advance_index'])->name('product.index.advance-search');
                    Route::post('/admin/product-get-slug', [ProductController::class, 'get_slug'])->name('product.get-slug');
                    Route::post('/admin/products/upload', [ProductController::class, 'upload'])->name('products.upload');

                    Route::get('/admin/product-change-status/{id}/{status}', [ProductController::class, 'change_status'])->name('product.single-change-status');
                    Route::post('/admin/product-single-delete', [ProductController::class, 'single_delete'])->name('product.single.delete');
                    Route::get('/admin/product/restore/{id}', [ProductController::class, 'restore'])->name('product.restore');
                    Route::post('/admin/product-multiple-change-status', [ProductController::class, 'multiple_change_status'])->name('product.multiple.change.status');
                    Route::post('/admin/product-multiple-delete', [ProductController::class, 'multiple_delete'])->name('products.multiple.delete');
                    Route::get('/admin/product/export', [ProductController::class, 'exportCsv'])->name('products.export');
                    Route::post('/admin/product/import', [ProductController::class, 'importCsv'])->name('products.import');
                    Route::post('/admin/product/upload', [ProductController::class, 'upload_stocks'])->name('products.upload');
                    Route::get('/admin/products/{slug}/history', [ProductController::class, 'history'])->name('products.history');
                //

                //Inventory
                    Route::resource('/inventory',InventoryReceiverHeaderController::class);
                    Route::get('/inventory-download-template',[InventoryReceiverHeaderController::class, 'download_template'])->name('inventory.download.template');
                    Route::post('/inventory-upload-template',[InventoryReceiverHeaderController::class, 'upload_template'])->name('inventory.upload.template');
                    Route::get('/inventory-post/{id}',[InventoryReceiverHeaderController::class, 'post'])->name('inventory.post');
                    Route::get('/inventory-cancel/{id}',[InventoryReceiverHeaderController::class, 'cancel'])->name('inventory.cancel');
                    Route::get('/inventory-view/{id}',[InventoryReceiverHeaderController::class, 'view'])->name('inventory.view');
                    Route::get('/inventory-logs/', [ProductController::class, 'inventoryLogs'])->name('product-inventory-history.list');
                //

                // Promos
                    Route::resource('/admin/promos', PromoController::class);
                    Route::get('/admin/promo/{id}/{status}', [PromoController::class, 'update_status'])->name('promo.change-status');
                    Route::post('/admin/promo-single-delete', [PromoController::class, 'single_delete'])->name('promo.single.delete');
                    Route::post('/admin/promo-multiple-change-status',[PromoController::class, 'multiple_change_status'])->name('promo.multiple.change.status');
                    Route::post('/admin/promo-multiple-delete',[PromoController::class, 'multiple_delete'])->name('promo.multiple.delete');
                    Route::get('/admin/promo-restore/{id}', [PromoController::class, 'restore'])->name('promo.restore');
                //

                // Delivery Rates
                    Route::resource('/locations', DeliverablecitiesController::class);
                    Route::get('/admin/location/{id}/{status}', [DeliverablecitiesController::class, 'update_status'])->name('location.change-status');
                    Route::post('/admin/location-single-delete', [DeliverablecitiesController::class, 'single_delete'])->name('location.single.delete');
                    Route::post('/admin/location-multiple-change-status',[DeliverablecitiesController::class, 'multiple_change_status'])->name('location.multiple.change.status');
                    Route::post('/admin/location-multiple-delete',[DeliverablecitiesController::class, 'multiple_delete'])->name('location.multiple.delete');
                //

                // Coupon
                    Route::resource('/coupons',CouponController::class);
                    Route::get('/coupon/{id}/{status}', [CouponController::class, 'update_status'])->name('coupon.change-status');
                    Route::post('/coupon-single-delete', [CouponController::class, 'single_delete'])->name('coupon.single.delete');
                    Route::get('/coupon-restore/{id}', [CouponController::class, 'restore'])->name('coupon.restore');
                    Route::post('/coupon-multiple-change-status',[CouponController::class, 'multiple_change_status'])->name('coupon.multiple.change.status');
                    Route::post('/coupon-multiple-delete',[CouponController::class, 'multiple_delete'])->name('coupon.multiple.delete');

                    Route::get('/get-product-brands', [CouponFrontController::class, 'get_brands'])->name('display.product-brands');
                    Route::get('/coupon-download-template', [CouponController::class, 'download_coupon_template'])->name('coupon.download.template');
                //

                // MRS Request
                    Route::put('/admin/sales-transaction/{id}/cancel', [SalesController::class, 'cancel'])->name('sales-transaction.cancel');
                    Route::get('/admin/sales-transaction/{id}/complete', [SalesController::class, 'markAsComplete'])->name('sales-transaction.complete');
                    Route::resource('/admin/sales-transaction', SalesController::class);
                    Route::post('/admin/sales-transaction/change-status', [SalesController::class, 'change_status'])->name('sales-transaction.change.status');
                    Route::post('/admin/sales-transaction/{sales}', [SalesController::class, 'quick_update'])->name('sales-transaction.quick_update');
                    Route::get('/admin/sales-transaction/view/{sales}', [SalesController::class, 'show'])->name('sales-transaction.view');
                    Route::get('/admin/generate_report', [SalesController::class, 'generateReport'])->name('sales-transaction.generate_report');
                    Route::post('/admin/change-delivery-status', [SalesController::class, 'delivery_status'])->name('sales-transaction.delivery_status');
                    Route::post('/admin/update-mrs', [SalesController::class, 'updateIssuance'])->name('mrs.update');
                    Route::get('/admin/mrs-action/{id}', [SalesController::class, 'mrs_action'])->name('mrs.action');
                    Route::post('/admin/issuance/submit', [IssuanceController::class, 'store'])->name('sales-transaction.issuance');
                    Route::post('/admin/issuance/{id}/update', [IssuanceController::class, 'update'])->name('sales-transaction.issuance.update');
                    Route::get('/admin/issuance/list', [IssuanceController::class, 'index'])->name('sales-transaction.issuance.index');
                    Route::get('/admin/issuance/{id}/edit', [IssuanceController::class, 'edit'])->name('sales-transaction.issuance.edit');
                    Route::get('/admin/sales-transaction/for_pa/{id}', [SalesController::class, 'for_pa'])->name('sales-transaction.for_pa');
                    Route::post('/admin/hold-item', [SalesController::class, 'hold_item'])->name('item.hold');

                    Route::get('/admin/sales-transaction/view-payment/{sales}', [SalesController::class, 'view_payment'])->name('sales-transaction.view_payment');
                    Route::post('/admin/sales-transaction/cancel-product', [SalesController::class, 'cancel_product'])->name('sales-transaction.cancel_product');
                    Route::get('/sales-advance-search/', [SalesController::class, 'advance_index'])->name('admin.sales.list.advance-search');


                    Route::get('/admin/report/sales', [ReportsController::class, 'sales'])->name('admin.report.sales');
                    Route::get('/admin/report/sales_summary', [ReportsController::class, 'sales_summary'])->name('report.sales.summary');
                    Route::get('/admin/report/delivery_status', [ReportsController::class, 'delivery_status'])->name('admin.report.delivery_status');
                    Route::get('/admin/report/delivery_report/{id}', [ReportsController::class, 'delivery_report'])->name('admin.report.delivery_report');
                    Route::get('/admin/report/issuances', [ReportsController::class, 'issuances'])->name('admin.report.issuances');
                    Route::get('/admin/report/mrs', [ReportsController::class, 'mrs'])->name('admin.report.mrs');
                    Route::get('/admin/report/fast-moving-items', [ReportsController::class, 'fastMovingItems'])->name('admin.report.fast-moving-items');

                    Route::get('/admin/sales-transaction/view-payment/{sales}', [SalesController::class, 'view_payment'])->name('sales-transaction.view_payment');
                    Route::post('/admin/sales-transaction/cancel-product', [SalesController::class, 'cancel_product'])->name('sales-transaction.cancel_product');

                    Route::get('/display-added-payments', [SalesController::class, 'display_payments'])->name('display.added-payments');
                    Route::get('/display-delivery-history', [SalesController::class, 'display_delivery'])->name('display.delivery-history');

                    /*Route::get('/sales/update-payment/{id}','EcommerceControllers\JoborderController@staff_edit_payment')->name('staff-edit-payment');
                    Route::post('/sales/update-payment','EcommerceControllers\JoborderController@staff_update_payment')->name('staff-update-payment');*/

                    Route::get('/bank-deposits', [SalesController::class, 'bank_deposits'])->name('bank-deposits');
                    Route::get('/validate-payment/{id}/{status}', [SalesController::class, 'validate_payment'])->name('validate-payment');
                //

                //IMF REQUESTS
                    Route::get('/imf/requests', [InventoryRequestController::class, 'imf_requests'])->name('imf.requests');
                    Route::get('/imf/request/view/{id}', [InventoryRequestController::class, 'imf_request_view'])->name('imf.requests.view');
                    Route::get('/imf/action/{id}', [InventoryRequestController::class, 'imf_action'])->name('imf.action');
                    Route::get('/imf/generate_report', [InventoryRequestController::class, 'generateReport'])->name('imf.generate_report');
                //

                // Reports
                    Route::get('/report/inventory_reorder_point', [ReportsController::class, 'inventory_reorder_point'])->name('report.inventory.reorder_point');
                    Route::get('/report/coupon_list', [ReportsController::class, 'coupon_list'])->name('report.coupon.list');
                    Route::get('/report/sales-transaction', [ReportsController::class, 'sales_list'])->name('report.sales-transaction');
                //
            ###### Ecommerce Routes ######

            ###### Purchasing Routes ######
                Route::get('/pa/mrs_for_pa', [PurchaseAdviceController::class, 'index'])->name('pa.index');
                Route::get('/pa/manage_pa', [PurchaseAdviceController::class, 'pa_list'])->name('pa.manage');
                Route::get('/admin/mrs/view/{id}', [PurchaseAdviceController::class, 'view_mrs'])->name('pa.view_mrs');
                Route::get('/pa/create_pa/{id}', [PurchaseAdviceController::class, 'create_pa'])->name('pa.create_pa');
                Route::get('/pa/generate_report', [PurchaseAdviceController::class, 'generate_report'])->name('pa.generate_report');
                Route::get('/purchaser/mrs_for_receive', [PurchaseAdviceController::class, 'purchaser_index'])->name('purchaser.index');
                Route::get('/purchaser/mrs_received', [PurchaseAdviceController::class, 'purchaser_received_index'])->name('purchaser.received_index');
                Route::get('/purchaser/mrs/view/{id}', [PurchaseAdviceController::class, 'purchaser_view'])->name('purchaser.view_mrs');
                Route::post('/purchaser/receive', [PurchaseAdviceController::class, 'receive_pa'])->name('purchaser.receive');
                Route::get('/admin/pa-action/{id}', [PurchaseAdviceController::class, 'pa_action'])->name('pa.action');
                Route::get('/pa/planner_pa', [PurchaseAdviceController::class, 'planner_pa'])->name('planner_pa.index');
                Route::get('/pa/planner_pa_create', [PurchaseAdviceController::class, 'planner_pa_create'])->name('planner_pa.create');
                Route::post('/pa/planner/mrs_items', [PurchaseAdviceController::class, 'mrs_items'])->name('mrs.items');
                Route::post('/pa/planner/insert', [PurchaseAdviceController::class, 'insert_pa'])->name('planner_pa.insert');
                Route::get('/pa/generate_report_pa', [PurchaseAdviceController::class, 'generate_report_pa'])->name('pa.generate_report_pa');
                Route::delete('/pa/delete/{id}', [PurchaseAdviceController::class, 'delete_pa'])->name('pa.delete_pa');
                Route::get('/pa/planner/view/{id}', [PurchaseAdviceController::class, 'planner_pa_view'])->name('pa.pa_view');
                Route::get('/pa/planner/pa-action/{id}', [PurchaseAdviceController::class, 'purchase_action'])->name('pa.purchase_action');
                Route::post('/pa/planner/update-pa', [PurchaseAdviceController::class, 'update_pa'])->name('pa.update');
            ###### Purchasing Routes ######
        });
    });

    // Pages Frontend
    Route::get('/{any}', [FrontController::class, 'page'])->where('any', '.*');
