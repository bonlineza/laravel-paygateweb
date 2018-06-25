# Laravel PayGateWeb

I needed a laravel-esque wrapper to make calls to the PayGate service, but couldn't find any. So I created one. It was specifically designed for my own personal use, but you are welcome to submit issues, and I'll look into refactoring it so that it can be used in a more general purpose fashion.

## Getting Started

### Prerequisites

This was built and tested ONLY on Laravel 5.5, although I'm sure it'll work on previous versions as well.

### Installing

```
composer require misterbrownrsa/laravel-paygateweb
```

Since Laravel 5.5 automatically includes the service provider, it won't be necessary to register it. However, if you really want to, add the following line to your `providers` array in `config/app.php`

```
MisterBrownRSA\PayGateWeb\PayGateProvider::class
```

## Usage Examples

PayGateWeb makes use of the Webv3 API for PayGate, which means it will redirect to their site for payment

```
$user = User::first();
$result = $payGateWeb->user($user)
    ->reference('test000000001')
    ->amount(100)
    ->returnURL(route('paygate.return'))
    ->notifyURL(route('paygate.notify'))
    ->initiate();

View::share('paygate', $result);
```

## Authors

* **Duwayne Brown** - *Initial work* - [MisterBrownRSA](https://github.com/MisterBrownRSA)

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details