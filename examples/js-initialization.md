# [Initialization using JavaScript](https://help.gopay.com/en/s/ey)

## Server-side

```php
# /eshop/create-payment
$response = $gopay->createPayment([/* define your payment  */]);
if ($response->hasSucceed()) {
    echo $response->json['gw_url'];
} else {
    http_response_code(400);
}
```

```php
# /eshop/get-payment-status
$response = $gopay->getStatus($_POST['paymentId']);
if ($response->hasSucceed()) {
    echo $response->json['state'];
} else {
    http_response_code(404);
}
```

## Client-side

```html
<?php
$embedJs = $gopay->urlToEmbedJs()
?>

<form id="payment-container" action="/eshop/create-payment">
   <script src="<?php echo $embedJs;>"></script>
   <input type="hidden" name="order_id" value="123"/>
   <button id="payment-invoke-checkout" type="submit" name="pay">Pay</button>
</form>

<script src="//code.jquery.com/jquery-1.11.3.min.js"></script>
<script>         
$(document).ready(function() {
    loadAsynchronousPayment($('#payment-container'));

    function loadAsynchronousPayment(form) {
        form.submit(function(event) {
            event.preventDefault();
            createPayment();
        });
    
        function createPayment() {
            $.ajax({
                url: form.get('action'),
                type: 'POST',
                data: form.serialize()
            })
            .done(openInlineGateway)
            .fail(paymentNotCreated);
        }

        function openInlineGateway(gwUrl) {
            // checkoutResult = {id: '...', url: '...'}
            _gopay.checkout({gatewayUrl: gwUrl, inline: true}, function(checkoutResult) {
                loadStatus();

                function loadStatus() {
                    $.ajax({
                        url: '/eshop/get-payment-status',
                        type: 'POST',
                        data: {paymentId: checkoutResult.id}    
                    })
                    .done(showStatus);
                }

                function showStatus(state) {
                    form.prepend(
                        "<p style='background-color:yellow;padding:8px;border:solid black 1px'>" +
                            "Payment status: " + state + "<br><br>" +
                            "URL: <a href='" + checkoutResult.url + "'>" + checkoutResult.url + "</a>" +
                        "</p>"
                    ); 
                }
            });
        }

        function paymentNotCreated() {
            alert('Payment not created, try it again');
        }
    }
});
</script>
```