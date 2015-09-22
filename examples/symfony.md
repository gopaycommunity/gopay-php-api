
# Symfony integration

Example integration with basic configuration and 
[controller defined as service](http://symfony.com/doc/current/cookbook/controller/service.html).

## Step 1: Define parameters, service

```yml
parameters:
    gopay.config:
        clientId: my_id
        clientSecret: my_secret
        isProductionMode: false
        scope: payment-all
        language: CS

services:
    gopay.payments:
        class: GoPay\Payments
        factory: ["GoPay\Api", payments]
        arguments:
            - %gopay.config%

    appbundle.controller.gopay:
        class: GoPayController
        arguments:
            - @gopay.payments
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
     * @SFW\Route("/payment/{id}")
     * @SFW\Template()
     */
    public function statusAction($id)
    {
        $response = $this->payments->getStatus($id);
        if ($response->hasSucceed()) {
            return array('payment' => $response->json);
        } else {
            throw new NotFoundHttpException((string) $response);
        } 
    }
}
```