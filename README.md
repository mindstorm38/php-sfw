# PHP-SFW
PHP-SFW is an Object-Oriented PHP framework. It provide lot of utilities for a powerfull databases managing. Since 1.1.0 work with routes and had default pages, prototype access and more !

This README is currently written for version `v1.1.0`.

## Install SFW
This framework can only work using the `composer` PHP package manager, check [here](https://getcomposer.org/) for more informations about it.

Once you've learned the basics of composer, install the `PHP-SFW` package using `composer require mindstorm38/php-sfw ^1.1.0`.

Then, you can initialize the default website `document root` (also named `working directory` in this doc) by adding a script in your composer configuration named - for example - `sfw-website-init` and executing the PHP method `SFW\Composer::composer_init_wd`. If you don't know how to do that, check out [that](https://getcomposer.org/doc/articles/scripts.md#defining-scripts).
The script entry must be - using the name previously chosen - (in `scripts` object) `"sfw-website-init" : "SFW\\Composer::composer_init_wd"`.

Run the installer with `composer run-script sfw-website-init`, it is interactive. Just for information, the "application path" that is requested is the `document root` of Apache (or NGINX).

## Default working directory
The extracted working directory contains by default :
- `langs` : Languages files used by the `Lang` module.
- `src/lib` : All php classes for your project, you must add a `PSR-4` *(or other type of SPL class loading)* section pointing to it in your `composer.json`
- `src/pages/` : All pages of your website.
- `src/templates/` : All base templates of your website.
- `.htaccess` The apache configuration file, not valid for NGINX but the contribution of NGINX developers would be welcome.
- `common.php` The file that load composer autoloader and starting application at each client request (if using default 'routes' system).

The generated `common.php` is pre-configurated using informations given to the interactive installer.
You can now register all needed routes and pages/templates.

By default this file start the application by initalizing all directories and `ResourceHandlers`, after this method you can register all you customized pages and routes.

The main namespace of the framework is `SFW\`.

## Understanding Resources Handling
Understanding the notion of `resource` or` resource directory` is very important for understanding the rest of the documentation.

The `Core` class has a list of `ResourceHandler`, an handler contains a `base directory` and have two methods to get directories and files rela paths if they exists relatively to the base directory.

By default there is a resource handler pointing to the base directory of your application *(given in 'start_application' parameters)* and one pointing to the `mindstorm38/php-sfw` package directory in `vendor`. This allows SFW to create defaults pages, templates and static resources.

The last added resource handler has priority and, depending on the used method, overwrite SFW sources.

## Routes
In most web frameworks, there is a notion of `route`, routes are used to execute actions according to the requested URL path.

To add route, use `Core::add_route`. To optimize a maximum, create your own routes extending `Route` class.

### ExactRoute
An `ExactRoute` just check if the given path at instantiation correspond to the request path.

By default an `ExactRoute` with empty path `/` route to default home page.

### TemplateRoute
`TemplateRoute` is used to create custom path with blank value for passing arguments to the route callback.

Blank pattern are defined like this `{<type>[idx]}` where `type` defined accepted character category and `idx` the index of the parsed variable in the callback variales array.

Pattern types :
- `a` : For letters and numerals
- `n` : For numerals
- `z` : For letters only

If no index specified, index is equals to the last pattern index + 1.

You can use only one pattern for each path directory with a string before and after it.
For exemple `dir/test{n0}/{n}` is valid but `dir/ok/{a0}test{n1}` is invalid.

### StaticRoute
A `StaticRoute` is used to access resources. For example if base path is `static` and `static/styles/foo.css` requested, the variables array given to the callback contains the path `styles/foo.css`.

By default a `StaticRoute` with base path of `static` is added (and mandatory to default SFW pages).

### QueryRoute
The `QueryRoute` just avoid using a TemplateRoute `<base_dir>/{a}`.

By default a query route with base directory `query` is created.

## Pages & Templates
Pages and templates are stored in `src/pages` and `src/templates` under resource handlers base paths.
Place their files inside a directory named by their identifier.

Templates can be used as base for multiple pages and defined using `Core::set_page_template` method.

In both pages and templates scripts, a variable `$page` is defined to a `Page` instance and can be used to `require` template or page part.

By default, SFW provide a template `sfw` and two pages `error` & `home` associated to them.

## Config
Configuration of SFW website is stored on `config.json` under application root directory. This use the `Config` class and the method `Config::get` to get a value (learn more in the documented code).

## Languages
Languages use the `Lang` class and stored in `langs` directory under each resource handlers. Each language is defined by a file following the pattern `<lang>_<contry>.lang`.
A language file is a collection of language `entry`, an entry must follow the pattern `<name>=<text>` and lines starting with a `#` are not took into account.

Only language defined in the `langs.json` file are computed and registered, take a look at SFW sources for understanding this file.

Languages are not overwritten if defined in both application and SFW resource handler but merged.









---------------
---------------

## (DEPRECATED) Create a website with it
This framework is dependent of "composer" package manager, see [here](https://packagist.org/packages/mindstorm38/php-sfw) for more informations.
When installed, create a "www" *(you can use and other name, but remember it !)* directory inside the composer home folder *(were you've created the composer.json)*.
Then you've to go inside "vendor/mindstorm38/php-sfw/example" directory and copy all of its content into the created "www" folder.
For this to work, you must specify to apache *(or nginx..)* that the HTTP home directory is the "www" folder.

Some default files/folders use cases (from "www") :
- `langs/` : This folder is used by PHP-SFW to manager languages files *(SFW\Lang module)*
- `src/lib/` : All php classes for your project, you must add a `PSR-4` *(or other type of SPL class loading)* section pointing to it in your `composer.json`
- `src/pages/` : All pages of your website
- `src/templates/` : All base templates of your website
- `static/` All static resources *(scripts, images, styles ...)*
- `.htaccess` The apache configuration file, not valid for NGINX but the contribution of NGINX developers would be welcome.
- `common.php` The file that load composer autoloader and starting application
- `config.json` JSON configuration file of the framework *(SFW\Config module)*
- `page.php` Common PHP file called for all standard pages
- `query.php` Common PHP file called for all queries *(through the /query/<name> URI path)*	

> Note : By default, a utility file named "less.php" is placed in static directory, it is called by the ".htaccess" for each "x.less.css" file under static directory.