<?php

namespace C01l\PhpDecoratorMemo\Tests;

use C01l\PhpDecorator\DecoratorManager;
use C01l\PhpDecoratorMemo\Memo;
use PHPUnit\Framework\TestCase;

class MemoTest extends TestCase
{
    private DecoratorManager $dm;

    protected function setUp(): void
    {
        $this->dm = new DecoratorManager();
    }


    public function testMemoWithEmptyKey()
    {
        $obj = new class {
            #[Memo]
            public function test($x)
            {
                return $x;
            }
        };

        $obj = $this->dm->decorate($obj);

        $this->assertEquals(123, $obj->test(123));
        $this->assertEquals(123, $obj->test(456));
        $this->assertEquals(123, $obj->test(789));
    }

    public function testMemoWithSimpleKey()
    {
        $obj = new class {
            public int $counter = 0;

            #[Memo(["x"])]
            public function test($x)
            {
                $this->counter++;
                return $x;
            }
        };

        $obj = $this->dm->decorate($obj);

        $this->assertEquals(123, $obj->test(123));
        $this->assertEquals(1, $obj->counter);
        $this->assertEquals(123, $obj->test(123));
        $this->assertEquals(1, $obj->counter);
        $this->assertEquals(789, $obj->test(789));
        $this->assertEquals(2, $obj->counter);
        $this->assertEquals(789, $obj->test(789));
        $this->assertEquals(2, $obj->counter);
    }

    public function testMemoWithComplexKey()
    {
        $obj = new class {
            public int $counter = 0;

            #[Memo(["x['y']"])]
            public function test($x)
            {
                $this->counter++;
                return $x;
            }
        };

        $obj = $this->dm->decorate($obj);

        $this->assertEquals(["y" => 4, "a" => 1], $obj->test(["y" => 4, "a" => 1]));
        $this->assertEquals(1, $obj->counter);
        $this->assertEquals(["y" => 4, "a" => 1], $obj->test(["y" => 4, "a" => 2]));
        $this->assertEquals(1, $obj->counter);
        $this->assertEquals(["y" => 3, "a" => 3], $obj->test(["y" => 3, "a" => 3]));
        $this->assertEquals(2, $obj->counter);
        $this->assertEquals(["y" => 3, "a" => 3], $obj->test(["y" => 3, "a" => 4]));
        $this->assertEquals(2, $obj->counter);
    }

    public function testMemoWithClassKey()
    {
        $obj = new class {
            public int $counter = 0;

            #[Memo(["x->x"])]
            public function test($x)
            {
                $this->counter++;
                return $x;
            }
        };

        $obj = $this->dm->decorate($obj);

        $te = fn($x, $y) => new class ($x, $y) {
            public function __construct(public $x, public $y)
            {
            }
        };

        $this->assertEquals($te(4, 1), $obj->test($te(4, 1)));
        $this->assertEquals(1, $obj->counter);
        $this->assertEquals($te(4, 1), $obj->test($te(4, 2)));
        $this->assertEquals(1, $obj->counter);
        $this->assertEquals($te(3, 3), $obj->test($te(3, 3)));
        $this->assertEquals(2, $obj->counter);
        $this->assertEquals($te(3, 3), $obj->test($te(3, 4)));
        $this->assertEquals(2, $obj->counter);
    }

    public function testMemoWithClassFunctionKey()
    {
        $obj = new class {
            public int $counter = 0;

            #[Memo(["x->getKey()"])]
            public function test($x)
            {
                $this->counter++;
                return $x;
            }
        };

        $obj = $this->dm->decorate($obj);

        $te = fn($x, $y) => new class ($x, $y) {
            public function __construct(public $x, public $y)
            {
            }

            public function getKey()
            {
                return $this->x;
            }
        };

        $this->assertEquals($te(4, 1), $obj->test($te(4, 1)));
        $this->assertEquals(1, $obj->counter);
        $this->assertEquals($te(4, 1), $obj->test($te(4, 2)));
        $this->assertEquals(1, $obj->counter);
        $this->assertEquals($te(3, 3), $obj->test($te(3, 3)));
        $this->assertEquals(2, $obj->counter);
        $this->assertEquals($te(3, 3), $obj->test($te(3, 4)));
        $this->assertEquals(2, $obj->counter);
    }

    public function testMemoWithMultipleParams()
    {
        $obj = new class {
            public int $counter = 0;

            #[Memo(["x", "y"])]
            public function test($x, $y, $z)
            {
                $this->counter++;
                return $z;
            }
        };

        $obj = $this->dm->decorate($obj);

        $this->assertEquals(1, $obj->test(123, 456, 1));
        $this->assertEquals(1, $obj->counter);
        $this->assertEquals(1, $obj->test(123, 456, 2));
        $this->assertEquals(1, $obj->counter);
        $this->assertEquals(3, $obj->test(789, 456, 3));
        $this->assertEquals(2, $obj->counter);
        $this->assertEquals(3, $obj->test(789, 456, 4));
        $this->assertEquals(2, $obj->counter);
        $this->assertEquals(5, $obj->test(789, 123, 5));
        $this->assertEquals(3, $obj->counter);
        $this->assertEquals(5, $obj->test(789, 123, 6));
        $this->assertEquals(3, $obj->counter);
    }
}
