# Travers

Travers is a small PHP package meant for easily traversing arrays using tokenized path strings.

```php
$params = [
    'fruits' => [
        'apple' => 1,
        'avocado' => 1.1,
        'banana' => [
            'baby' => 'baby',
            'ice_cream' => true,
            'casual' => false,
        ],
    ],
];

Travers::get('fruits.apple', $params); // 1
Travers::get('fruits.banana.ice_cream', $params); // true
Travers::get('fruits.banana.casual', $params); // false
Travers::get('fruits.apple.red', $params); // null
```

## Installation

`composer require laurynasgadl/travers`

## Documentation
### Initializing
The library can be used either by creating the instance for the class
or using it statically.

```php
use Luur\Travers;

$data = [
    'path' => 1,
];

$travers = new Travers($data);

$val = $travers->find('path'); // 1
$val = Travers::get('path', $data); // 1
```

### Configuring
Creating an instance of `Travers` allows setting additional settings:
```php
$shouldThrowException = true;
$customDelimiter = '=';

$travers = new Travers($data, $shouldThrowException); // If a path is not found, will throw exception instead of returning null
$travers = new Travers($data, false, $customDelimiter); // Will separate path branches by the provided delimiter
```

### Setting data
Travers also allows setting data in an array:
```php
$data = [
    'path' => 1,
];

$travers = new Travers($data);

$data = $travers->change('path', 2); // ['path' => 2]
$data = Travers::set('path', 3, $data); // ['path' => 3]
```

If the path does not exist in the array, it is created:
```php
$data = Travers::set('path.new.trail', 4, $data);

/**
 * $data = [
 *      'path' => [
 *          'new' => [
 *              'trail' => 4,
 *          ],
 *      ],
 * ];
 */
```
