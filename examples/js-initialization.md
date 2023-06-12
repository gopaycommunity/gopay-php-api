# [Initialization using JavaScript](https://help.gopay.com/en/s/ey)

## Server-side

```php
# /create-payment
$response = $gopay->createPayment([/* define your payment  */]);
if ($response->hasSucceed()) {
    echo json_encode($response->json);
} else {
    http_response_code(400);
}
```

```php
# /payment-status
$response = $gopay->getStatus($_GET['id']);
if ($response->hasSucceed()) {
    echo json_encode($response->json);
} else {
    http_response_code(404);
}
```

##  Client-side

```html
<?php
$embedJs = $gopay->urlToEmbedJs() ?>

<form id="payment-form" action="/create-payment">
  <script src="<?php echo $embedJs;>"></script>
  <input type="hidden" name="order_id" value="123" />
  <button type="submit">Pay</button>
</form>

<script>
  // Po odeslání formuláře dojde k asynchronnímu založení platby a následnému vyvolání inline brány
  document.addEventListener("DOMContentLoaded", () => {
    const paymentForm = document.querySelector("#payment-form");
    paymentForm.addEventListener("submit", async (event) => {
      event.preventDefault();
      const createResult = await createPayment(paymentForm);
      initInline(createResult);
    });
  });

  // Asynchronní funkce pro vytvoření platby z formuláře
  const createPayment = async (form) => {
    try {
      // Serializace dat z formuláře do objektu
      const formData = new FormData(form);
      const formObject = Object.fromEntries(formData.entries());
      // Odeslání dat na endpoint, specifikovaný jako akce formuláře.
      const createResponse = await fetch(form.action, {
        method: "POST",
        headers: {
          Accept: "application/json",
          "Content-Type": "application/json"
        },
        body: JSON.stringify(formObject)
      });
      // Předpokládáme, že backend vrací JSON, který obsahuje popis platby z API odpovědi
      // viz https://doc.gopay.cz/#zalozeni-platby
      const createResult = await createResponse.json();
      if (!createResponse.ok) {
        console.error(
          `Server returned: ${createResponse.status}: ${createResponse.statusText}:\n${createResult}`
        );
        return;
      }
      return createResult;
    } catch (err) {
      console.error(err);
    }
  };

  // Asynchronní funkce pro provedení dotazu na stav platby podle jejího ID
  const getPaymentStatus = async (paymentId) => {
    try {
      // ID platby je v příkladu předáno jako query parametr "id". Upravte podle vašich potřeb.
      const statusResponse = await fetch(`/payment-status?id=${paymentId}`, {
        method: "GET",
        headers: {
          Accept: "application/json"
        }
      });
      // Předpokládáme, že backend vrací JSON, který obsahuje popis platby z API odpovědi
      // viz https://doc.gopay.cz/#dotaz-na-stav-platby
      const paymentStatusResult = await statusResponse.json();
      if (!statusResponse.ok) {
        console.error(
          `Server returned: ${statusResponse.status}: ${statusResponse.statusText}:\n${paymentStatus}`
        );
        return;
      }
      return paymentStatusResult;
    } catch (err) {
      console.error(err);
    }
  };

  // Funkce, pro vyvolání inline brány
  const initInline = (createResult) => {
    try {
      // Vyvolání Inline brány prostřednictvím získané gw_url
      // Jako druhý parametr je předána callback funkce, která je volána při zavření brány
      _gopay.checkout({ gatewayUrl: createResult.gw_url, inline: true }, async (checkoutResult) => {
        // V objektu checkoutResult se nachází návratová URL, ID platby a její stav
        console.log(`Stav platby ${checkoutResult.id}: ${checkoutResult.state}`);
        // Pro další informace o platbě je možné zavolat dotaz na stav platby.
        const paymentStatusResult = await getPaymentStatus(checkoutResult.id);
        // A následně odpověď libovolně zpracovat
        console.log(paymentStatusResult);
      });
    } catch (err) {
      console.error(err);
    }
  };
</script>
```
