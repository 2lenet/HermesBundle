# APIs

The following is showing examples how you can use the differents APIs.

## Get the templates list

> **GET** /api/v1/template

Action to get a listing of all templates. The result can be filtered by tenant.

| Field      |     Type     | Required | Description                                         |
|------------|:------------:|:--------:|-----------------------------------------------------|
| `tenantId` | `int / null` |   true   | The tenant of the templates you want to search for. |

Example:
```php
$client = new HttpClient();

$response = $client->request('GET', '/api/v1/template', [
    'tenantId' => null,
]);
```
