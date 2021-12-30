# phpdecorator - memo

The phpdecorator library extension can be used to store return values in memory depending on its parameters.

This library uses the parameters of a function to build a cache key to cache function call results.

## How to use it?

1. Add `Memo` attribute to a function.
2. Specify the how the cache key is built.

```php
class TestClass
{
    #[Memo(["bar"])]
    public function foo(int $bar, int $x): int
    {
        return $x;
    }
}

$obj = new TestClass();
$obj = (new \C01l\PhpDecorator\DecoratorManager())->decorate($obj);

$obj->foo(1,2) // 2
$obj->foo(2,3) // 3
$obj->foo(1,3) // 2, because foo is not really executed again, but the cached version is used
```

## Using complex memo keys

The key can be composed of multiple values, by adding more elements to the array:
```php
#[Memo(["bar", "x"])]
public function foo($bar, $x) { return $x; }
```

Using no field will result in the function being only called once and different arguments are not considered.
```php
#[Memo]
public function foo($bar, $x) { return $x; }

...

$obj->foo(1,2); // 2
$obj->foo(1,3); // 2
$obj->foo(2,5); // 2
$obj->foo(4,6); // 2
```

You can use an arbitrary expression in the key definition.

```php
class A {
    public int $x, $y, $z;
    public function getX() { return $x; }
}

...

#[Memo(["bar->z", "bar->getX()"])]
public function foo(A $bar, $x) { return $x; }
```