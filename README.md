# SqidsBundle

[![CI](https://github.com/roukmoute/sqids-bundle/actions/workflows/unit-tests.yml/badge.svg)](https://github.com/roukmoute/sqids-bundle/actions/workflows/unit-tests.yml)
[![Latest Stable Version](https://poser.pugx.org/roukmoute/sqids-bundle/v/stable)](https://packagist.org/packages/roukmoute/sqids-bundle)
[![License](https://poser.pugx.org/roukmoute/sqids-bundle/license)](https://packagist.org/packages/roukmoute/sqids-bundle)

Integrates [Sqids](https://sqids.org/) into your Symfony project.

Sqids (pronounced "squids") generates short unique identifiers from numbers. These IDs are URL-safe, can encode several numbers, and do not contain common profanity words.

This is the official successor to [Hashids](https://hashids.org/). If you are starting a new project, use Sqids instead of Hashids.

## Installation

```bash
composer require roukmoute/sqids-bundle
```

If you're using Symfony Flex, the bundle will be automatically registered. Otherwise, add it to your `config/bundles.php`:

```php
return [
    // ...
    Roukmoute\SqidsBundle\RoukmouteSqidsBundle::class => ['all' => true],
];
```

## Configuration

Create `config/packages/roukmoute_sqids.yaml`:

```yaml
roukmoute_sqids:
    alphabet: 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'  # Custom alphabet
    min_length: 0          # Minimum length of generated sqids
    blocklist: null        # Path to a JSON file containing blocked words, or null to use defaults
    passthrough: false     # Pass decoded value to next resolver (for Doctrine integration)
    auto_convert: false    # Automatically decode route parameters matching int arguments
```

### Configuration Options

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `alphabet` | string | Default Sqids alphabet | Characters used to generate sqids. Must contain at least 3 unique characters. |
| `min_length` | int | 0 | Minimum length of generated sqids. |
| `blocklist` | string\|null | null | Path to a JSON file containing words to avoid in generated sqids. |
| `passthrough` | bool | false | When true, sets the decoded value in request attributes for the next resolver (useful with Doctrine). |
| `auto_convert` | bool | false | When true, automatically attempts to decode any route parameter matching an int argument name. |

## Usage

### Basic Encoding/Decoding

Inject `SqidsInterface` into your service or controller:

```php
use Sqids\SqidsInterface;

class MyService
{
    public function __construct(private SqidsInterface $sqids)
    {
    }

    public function encode(int $id): string
    {
        return $this->sqids->encode([$id]);
    }

    public function decode(string $sqid): int
    {
        $decoded = $this->sqids->decode($sqid);
        return $decoded[0];
    }
}
```

### Controller Argument Resolution

#### Using the `#[Sqid]` Attribute

The `#[Sqid]` attribute automatically decodes sqid route parameters:

```php
use Roukmoute\SqidsBundle\Attribute\Sqid;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class UserController
{
    #[Route('/users/{id}')]
    public function show(#[Sqid] int $id): Response
    {
        // $id is automatically decoded from the sqid in the URL
        // e.g., /users/UK → $id = 1
    }
}
```

#### Custom Parameter Name

When the route parameter name differs from the argument name:

```php
#[Route('/users/{sqid}')]
public function show(#[Sqid(parameter: 'sqid')] int $id): Response
{
    // The 'sqid' route parameter is decoded into $id
}
```

#### Using Aliases

Without any attribute, the resolver recognizes these aliases:
- `{sqid}` route parameter
- `{id}` route parameter

```php
#[Route('/users/{id}')]
public function show(int $userId): Response
{
    // If {id} contains a valid sqid, $userId receives the decoded value
}
```

Note: Aliases are consumed once per request to avoid ambiguous resolution with multiple arguments.

### Multiple Sqids in a Single Route

Use the `_sqid_` prefix to decode multiple parameters:

```php
#[Route('/users/{_sqid_user}/posts/{_sqid_post}')]
public function showPost(int $user, int $post): Response
{
    // Both $user and $post are decoded from their respective sqids
}
```

### Auto-Convert Mode

Enable `auto_convert: true` in your configuration to automatically attempt decoding all route parameters that match `int` typed controller arguments:

```yaml
roukmoute_sqids:
    auto_convert: true
```

```php
#[Route('/users/{id}')]
public function show(int $id): Response
{
    // $id is automatically decoded if it looks like a valid sqid
    // If decoding fails, the resolver silently skips (no exception)
}
```

### Passthrough Mode (Doctrine Integration)

Enable `passthrough: true` to chain with Doctrine's `EntityValueResolver`:

```yaml
roukmoute_sqids:
    passthrough: true
```

```php
use App\Entity\User;

#[Route('/users/{id}')]
public function show(User $user): Response
{
    // The sqid is first decoded, then Doctrine fetches the User entity
}
```

The resolver decodes the sqid and sets the integer value in the request attributes, allowing Doctrine's resolver to find the entity.

## Twig Integration

The bundle provides Twig filters and functions for encoding and decoding sqids.

### Filters

```twig
{# Encode a single ID #}
{{ user.id | sqids_encode }}

{# Decode a sqid #}
{{ sqid | sqids_decode | first }}
```

### Functions

```twig
{# Encode multiple IDs #}
{{ sqids_encode(1, 2, 3) }}

{# Use in a path #}
<a href="{{ path('user_show', { id: user.id | sqids_encode }) }}">
    View User
</a>
```

## License

This bundle is released under the MIT License. See the [LICENSE](LICENSE) file for details.
