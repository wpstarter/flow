# WpStarter Flow

Flow package for WpStarter or Laravel framework that provides a fluent interface for handling workflow and process management.

## Requirements
- PHP 7.4 or higher
- WpStarter/Laravel framework
- Composer

## Installation

You can install the package via composer:

```bash
composer require wpstarter/flow
```
## Configuration
You can publish the configuration file using:
```bash
php artisan vendor:publish --provider="WpStarter\Flow\FlowServiceProvider"
```

## Usage
### Create Flow class extends `WpStarter\Flow\Flow`

```php
namespace App\Flow\Flows;

use WpStarter\Flow\Flow;
use WpStarter\Flow\FlowRequest;
use App\Flow\Flows\OrderFlow;

class LoginFlow extends Flow{
    function handle(FlowRequest $request){
        // TODO: Implement handle() method.
        if($this->state('is_logged_in')){
            $this->redirect(OrderFlow::class);
            return ['success'=>true];
        }else{
            if(Auth::check($request['user'],$request['pass'])){
                $this->state('is_logged_in',true);
                $this->redirect(OrderFlow::class);
                return ['success'=>true];
            }else{
                return ['success'=>false,'message'=>'invalid credentials'];
            }
        }
    }
}
```

#### Flow state management
Go to next flow
```php
    $this->redirect(NextFlowClass::class)
```
Go to previous flow
```php
    $this->back()
```
Redirect to default flow
```php
    $this->redirectToDefault()
```
### Flow Providers
Create `App\Flow\MyFlowProvider` extends `WpStarter\Flow\Support`
```php
class MyFlowProvider extends FlowProvider
{
    public function register()
    {
        $this->flows->register(LoginFlow::class);
        $this->flows->register(OrderFlow::class);
        $this->loadRoutesFrom( __DIR__ . '/routes.php');
    }
}
```
Add MyFlowProvider to `config/flow.php`
```php
<?php
return [
    'providers'=>[
        App\Flow\MyFlowProvider::class
    ]
];
```

### Flow Routes
#### Simple matching
```php
use \WpStarter\Flow\Support\Facades\Route;

//Run login flow when received message /login
Route::match('/login',LoginFlow::class);
//Run order flow when received message /order or /buy
Route::match(['/order','/buy'],OrderFlow::class);
```
#### Advanced matching
```php
use \WpStarter\Flow\Support\Facades\Route;

//Run login flow when `secret_login` is 'Abcd1234'
Route::match(function(\WpStarter\Flow\FlowRequest $request){
    if($request->get('secret_login')==='Abcd1234'){
        return true;
    }
},LoginFlow::class);
```
#### Auto register route in Flow
Route can be registered automatically by define `$route` property in Flow class
```php
class LoginFlow extends Flow{
    protected $route='logmein';
}
```
Above code will register following route
```php
Route::match('logmein',LoginFlow::class);
```
### Run Flow
#### Create Flow Request
You can create a flow request based on current http request. Normally FlowRequest required to have 2 attributes: `id` and `message`.
We compare route against `message` to find matched flow
```php
use WpStarter\Flow\FlowRequest;
$flowRequest=new FlowRequest([
    'id'=>$request['phone_number'],
    'message'=>$request->input('message'),
]);
```
#### Run flow in a controller

```php
namespace App\Http\Controllers;
use WpStarter\Flow\FlowManager;
use Illuminate\Http\Request;
class FlowController{
    function index(FlowManager $flowManager, Request $request){
        $flowRequest=new FlowRequest([
            'id'=>Auth::id(),
            'message'=>$request->input('message'),
        ]);
        return $flowManager->run($flowRequest);
    }
}
```


## License

The MIT License (MIT). Please see [License File](LICENSE) for more information. 