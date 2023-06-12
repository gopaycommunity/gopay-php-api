
# Symfony integration

Example integration with basic configuration and
[controller defined as service](http://symfony.com/doc/current/cookbook/controller/service.html).

## Step 1: Define parameters, service

```yml
parameters:
    gopay.config:
        goid: my_goid
        clientId: my_id
        clientSecret: my_secret
        gatewayUrl: 'https://gw.sandbox.gopay.com/api'
        # optional config
        scope: payment-all
        language: CS
        timeout: 30

services:
    gopay.payments:
        class: GoPay\Payments
        factory: ["GoPay\Api", payments]
        arguments:
            - %gopay.config%
```

## Step 2: Call API in controller

```
services:
    appbundle.controller.gopay:
        class: AppBundle\Controller\GoPayController
        arguments:
            - @gopay.payments
```

```php

namespace AppBundle\Controller;

use GoPay\Payments;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as SFW;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** @SFW\Route("/gopay", service="appbundle.controller.gopay") */
class GoPayController
{
    private $payments;

    public function __construct(Payments $p)
    {
        $this->payments = $p;
    }

    /**
     * @SFW\Route("/payment")
     * @SFW\Template()
     */
    public function payAction()
    {
        $response = $this->payments->createPayment([/* define your payment  */]);
        if ($response->hasSucceed()) {
            return [
                'gatewayUrl' => $response->json['gw_url'],
                'embedJs' => $this->payments->urlToEmbedJs()
            ];
        } else {
            throw new NotFoundHttpException((string) $response);
        } 
    }

    /**
     * @SFW\Route("/payment/{id}")
     * @SFW\Template()
     */
    public function statusAction($id)
    {
        $response = $this->payments->getStatus($id);
        if ($response->hasSucceed()) {
            return ['payment' => $response->json];
        } else {
            throw new NotFoundHttpException((string) $response);
        } 
    }
}
```

```twig
// src/AppBundle/Resources/views/GoPay/pay.html.twig
<!DOCTYPE html>
<html>
    <head>
        <title>Pay</title>
    </head>
    <body>
        <form action="{{ gatewayUrl }}" method="post" id="gopay-payment-button">
          <button name="pay" type="submit">Pay</button>
          <script type="text/javascript" src="{{ embedJs }}"></script>
        </form>
    </body>
</html>
```

## Optional: Register custom cache and logger

```yml
services:
    gopay.payments:
        class: GoPay\Payments
        factory: ["GoPay\Api", payments]
        arguments:
            - %gopay.config%
            - cache: @gopay.cache
              logger: @gopay.logger

    gopay.cache:
        class: GoPay\Token\InMemoryTokenCache

    gopay.logger:
        class: GoPay\Http\Log\PrintHttpRequest
```
