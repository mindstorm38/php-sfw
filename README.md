# PHP-SFW
PHP-SFW is an Object-Oriented PHP framework. It provide lot of utilities for a powerfull databases managing.

## Create a website with it
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