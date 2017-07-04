# Serverless Laravel on AWS Lambda
Want to build PHP applications comprised of microservices that run in response to events, auto-scale for you, and only 
charge you when they run. So do we! This Laravel Pluging lowers the total cost of maintaining our apps, enabling us to 
build more logic, faster. That right, more ... faster ... Laravel ... PHP ... does it get any better!?

Our company has a significant investment in [PHP](https://secure.php.net/) development as well as 
[Amazon Web Services (AWS)](https://aws.amazon.com/). We desire the ability to run 
[Laravel](https://laravel.com/)/[Lumen](https://lumen.laravel.com/) Applications in 
[AWS Lambda](https://aws.amazon.com/lambda/) despite the fact that PHP isn't formally supported. 

We created a system for easily building [PHP Executables](https://github.com/stechstudio/php-lambda) that will run 
properly in AWS Lambda Functions. Then we set about the task of creating this plugin to allow easily integrating our
application with the Lambda Runtime in order to enjoy _serverless_ Lumen/Laravel apps.

## Requirements
PHP Version 7.1 - Because I enjoy writing PHP 7.1 code and do not want to write something that is backward compatible.

## Installation

```
composer require stechstudio/laravel-aws-lambda
```

### Register the Provider:

For Lumen services, add:

```php
$app->register(STS\Serverless\ServiceProvider::class);
```
to `bootstrap/app.php`. For Laravel applications, add:

```php
STS\Serverless\ServiceProvider::class,
```

to the `providers` array in `config/app.php`.

## Configuration
// @todo Actually fill this in.
`artisan aws-lambda:install` - Copy all the PHP executable, libraries, and node.js stuff into place.
`artisan aws-lambda:package` - Creates a zip archive that can be uploaded to AWS Lambda.

## Use It
// @todo Actually fill this in.
Local test with [lambda-local](https://www.npmjs.com/package/lambda-local) if you have installed it.

`lambda-local -l resources/nodejs/gateway.js -h handler -e resources/nodejs/event-samples/test-data.js` 