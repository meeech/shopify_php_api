## AUTHOR
William Lang (william [at] shopify [dot] com)

## DESCRIPTION

The PHP library will allow developers who are more comfortable with PHP to create Shopify apps.

It has all the bells and whistles of its Ruby counterpart to help you create great applications for release in the Shopify App Store.

## DOCUMENTATION

PHP_Shopify_API_Documentation.rtf has all the documentation on the API.

## INSTALL

Upload the shopify_php_api to your webserver. Modify lib/shopify_api_config.php with your application's API Key and Secret found in your partner account at http://www.shopify.com/partners/

## TESTING

You can then access it by going to http://www.yourdomain.com/shopify_php_api/test/index.php

## BASIC USAGE

Authentication is easy to do. The API will generate all the necessary URLs your application needs to authenticate.

    $api = new Session('mystore.myshopify.com', '', 'YOUR_API_KEY', 'YOUR_SECRET');
    header("Location: " . $api->create_permission_url());

This will send the owner of 'mystore.myshopify.com' (usually you would prompt for this information) to the permission URL needed for authentication and installation of your app. The Shopify platform will then automatically redirect the user to your application's return URL with their shop, token and signature.

    $shop = $_GET['url'];
    $token = $_GET['t'];
    $api = new Session($shop, $token, 'YOUR_API_KEY', 'YOUR_SECRET');

Now that your user has given your application permission to read and/or write, we can ask for the products in their store:

    $storeProducts = $api->product->get();

    foreach ($storeProducts as $product){
          echo $product['title'].'<br />';
    }

This prints all products in the authenticated user's shop. You can also get a specific product by passing its product id to the product get() method.

    $aProduct = $api->product->get(1234567);
    echo $aProduct['title'];

To create a new product (if your application has write permissions), you can use the product create() method by passing fields you would like your new product to have.

    $fields = array('title' => 'My New Product');
    $api->product->create($fields);

To update a product (if your application has write permissions), you can use the product modify() method by passing the product id and the fields you would like to modify.

    $fields = array('title' => 'My Updated Title');
    $api->product->modify(1234567, $fields);
    
## REPORTING BUGS
Email william [at] shopify [dot] com

## FREQUENTLY ASKED QUESTIONS
http://wiki.shopify.com/PHP_API_FAQ

## COPYRIGHT
jadedPixel / Shopify