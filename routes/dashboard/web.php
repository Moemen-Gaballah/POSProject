<?php 

Route::group(
[
	'prefix' => LaravelLocalization::setLocale(),
	'middleware' => [ 'localeSessionRedirect', 'localizationRedirect', 'localeViewPath' ]
], 
function(){ 

	Route::prefix('dashboard')->name('dashboard.')->middleware(['auth'])->group(function() {
		Route::get('/', 'WelcomeController@index')->name('welcome');
		

		Route::get('/test', function(){
			echo LaravelLocalization::getCurrentLocale();
		});

		// categories routes
		Route::resource('categories', 'CategoryController')->except(['show']);

		// categories routes
		Route::resource('products', 'ProductController')->except(['show']);

		// client routes
		Route::resource('clients', 'ClientController')->except(['show']);
		Route::resource('clients.orders', 'Client\OrderController')->except(['show']);

		// order routes
		Route::resource('orders', 'OrderController');
		// Route::get('orders.products', 'OrderController@products')->name('orders.products');
		Route::get('/orders/{order}/products', 'OrderController@products')->name('orders.products');

		// user routes
		Route::resource('users', 'UserController')->except(['show']);



	}); // end of dashboard routes

});

Route::get('/', function (){
	return redirect()->route('dashboard.welcome');
});


