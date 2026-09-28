# Prevent Broken Integrations: Contract Testing Laravel APIs Against Frontend Clients

Your backend team renames a JSON response key from `user_id` to `author_id` to clean up internal naming conventions. All backend unit tests pass, and the pull request merges smoothly. The next morning, mobile app users open the iOS app and every screen displays blank gray cards because the Swift client expected `user_id`. The backend team did not realize they had broken a contract because their tests only tested Laravel controllers against Laravel assertions.

When mobile apps, third-party partners, or standalone Single Page Applications consume your API, internal unit tests are not enough. You need **Contract Testing**. Contract testing validates that the shape, data types, and required keys of your JSON API responses match the exact schema contract expected by consumer clients. If you enforce OpenAPI or JSON Schema contract tests in Laravel, you can catch breaking API changes in CI before they reach production.

## Why Standard Controller Tests Miss Breaking API Changes

Consider a standard Laravel API test:

```php
test('api returns user details', function () {
    $user = User::factory()->create();

    $response = $this->getJson("/api/users/{$user->id}");

    // Passes even if you renamed 'full_name' or altered date formats!
    $response->assertOk()
        ->assertJsonStructure(['data' => ['id', 'email']]);
});
```

`assertJsonStructure` checks that `id` and `email` exist. But what if:
- A timestamp format changed from ISO-8601 (`2026-09-27T12:00:00Z`) to a Unix epoch integer (`1727438400`)?
- An optional nullable field became strictly required?
- An integer `cents` balance was accidentally serialized as a float?

The backend test passes, but the strongly-typed mobile client (Swift or Kotlin) throws a JSON decoding error and crashes on user devices.

## Contract Testing with JSON Schema

A JSON Schema defines an explicit, unambiguous contract for an API payload: required fields, types, formats, and allowed values.

Create a schema file representing your contract with frontend clients:

tests/Contracts/user-response-schema.json:
```json
{
  "$schema": "http://json-schema.org/draft-07/schema#",
  "type": "object",
  "required": ["data"],
  "properties": {
    "data": {
      "type": "object",
      "required": ["id", "display_name", "email", "created_at", "tier"],
      "properties": {
        "id": { "type": "integer" },
        "display_name": { "type": "string", "minLength": 1 },
        "email": { "type": "string", "format": "email" },
        "created_at": { "type": "string", "format": "date-time" },
        "tier": { "type": "string", "enum": ["free", "pro", "enterprise"] }
      },
      "additionalProperties": false
    }
  }
}
```

Notice `"additionalProperties": false`. This enforces that the API does not inadvertently leak un-contracted internal database columns into the public response.

## Enforce the Contract in Pest Feature Tests

Using the established `justinrainbow/json-schema` validator, build a custom Pest expectation to validate responses against the schema:

tests/Pest.php:
```php
use JsonSchema\Validator;

expect()->extend('toMatchJsonContract', function (string $schemaPath) {
    $data = json_decode($this->value->getContent());
    $schema = json_decode(file_get_contents(base_path($schemaPath)));

    $validator = new Validator();
    $validator->validate($data, $schema);

    if (! $validator->isValid()) {
        $errors = collect($validator->getErrors())->map(fn ($e) => "[{$e['property']}] {$e['message']}")->implode("\n");
        throw new \Exception("Response failed JSON contract validation:\n{$errors}");
    }

    return $this;
});
```

Now, write your API feature test:

tests/Feature/Api/UserContractTest.php:
```php
use App\Models\User;

test('GET /api/users/{id} strictly conforms to the frontend contract schema', function () {
    $user = User::factory()->create([
        'tier' => 'pro',
    ]);

    $response = $this->getJson("/api/v1/users/{$user->id}");

    $response->assertOk();

    // Validates every type, date format, required key, and enum value against the contract
    expect($response)->toMatchJsonContract('tests/Contracts/user-response-schema.json');
});
```

If a developer renames `display_name` to `name`, changes `tier` to an unsupported string, or returns a null email, the test immediately fails with a detailed schema violation report.

## Consumer-Driven Contract Testing with Pact

For enterprise engineering teams where frontend and backend are developed by separate teams, look into **Pact** (Consumer-Driven Contract Testing).

In consumer-driven contract testing:
1. The mobile or frontend team writes a test defining the exact request and expected response contract in a "Pact file".
2. The Pact file is published to a shared Pact Broker.
3. The Laravel CI pipeline downloads the Pact contract and runs it against the local API controllers.
4. If the backend fails the frontend's contract, CI prevents the backend from deploying.

This eliminates integration surprises entirely without needing end-to-end staging environments.

## What Can Go Wrong

A frequent pitfall when adopting contract tests is maintaining schema files manually by hand. Developers update an API Resource, forget to update the schema file, and the test fails.

To prevent friction:
- Generate your JSON Schema or OpenAPI definitions directly from your Eloquent API Resources using packages like `dedoc/scramble` or `knuckleswtf/scribe`.
- Version your contracts (`/api/v1/`, `/api/v2/`) so that breaking changes are introduced as deliberate new versions rather than silent mutations of existing endpoints.

## Summary

Never assume that passing backend unit tests means your API is safe to deploy.

Establish explicit contract tests using JSON Schema or Pact. Validate that response payloads strictly conform to contracted field names, data types, and date formats. Prohibit undeclared additional properties to avoid accidental data leaks.

Your frontend and mobile teams can build against your APIs with total confidence, free from breaking deployment surprises.

## Further Reading

- [JSON Schema Specification](https://json-schema.org/)
- [Pact: Consumer-Driven Contract Testing](https://docs.pact.io/)
- [Scramble: Automated OpenAPI Documentation for Laravel](https://scramble.dedoc.co/)
- [Martin Fowler: Consumer-Driven Contracts](https://martinfowler.com/articles/consumerDrivenContracts.html)

How does your team coordinate API schema updates between backend and mobile engineering teams? Share your workflow in the comments below.
