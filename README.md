# Stream

Java-like Stream API library for PHP.

Requires PHP 7.2 or newer.

## Usage

Basic usage.

```php
Stream::of("a", "b", "c", "d", "e", "x")->filter(function ($value) {
    return $value !== "x";
})->map(function($value) {
    return strtoupper($value);
})->forEach(function ($value) {
    echo $value . ", ";
});
// A, B, C, D, E,
```

### Generate Stream

* from values.

```php
$stream = Stream::of("a", "b", "c");
```

* from array or Traversable.

```php
$stream = Stream::ofIterable([
    "a",
    "b",
    "c",
]);
```

```php
$stream = Stream::ofIterable(new DirectoryIterator("."));
```

* from generator function.

```php
$stream = Stream::ofGenerator(function () {
    yield "a";
    yield "b";
    yield "c";
});
```
### Intermediate operation

* filter

```php
$stream = Stream::ofIterable(range(0, 10));
$stream = $stream->filter(function ($value) {
    return $value > 5;
});
// 6 7 8 9 10
```

* map

```php
$stream = Stream::ofIterable(range(0, 5));
$stream = $stream->map(function ($value) {
    return $value * 2;
});
// 0 2 4 6 8 10
```

* sorted

```php
$stream = Stream::ofIterable(range(0, 5));
$stream = $stream->sorted(function ($value1, $value2) {
    return $value2 - $value1;
});
// 5 4 3 2 1 0
```

* and others

flatMap, skip, skipWhile, limit, limitWhile, distinct, peek.

### Terminal operation

* forEach

```php
$stream = Stream::ofIterable(range(0, 5));
$stream->forEach(function ($value) {
    echo $value . ", ";
});
// 0, 1, 2, 3, 4, 5,
```

* toArray

```php
$stream = Stream::ofIterable(range(0, 5));
$array = $stream->toArray();
// [0, 1, 2, 3, 4, 5]
```

* reduce

```php
$stream = Stream::ofIterable(range(0, 5));
$sum = $stream->reduce(0, function ($accum, $value) {
    return $accum + $value;
});
// 15
```

* and others

count, allMatch, anyMatch, nonMatch, findFirstOrDefault, findLastOrDefault, maxOrDefault, minOrDefault.

## License

MIT
