<?php
declare(strict_types = 1);
namespace Capci\Stream\Test;

//require_once '../vendor/autoload.php';

use Capci\Stream\Stream;
use PHPUnit\Framework\TestCase;

class StreamTest extends TestCase
{

    private $stream;

    private $emptyStream;

    public function setUp()
    {
        parent::setUp();
        $this->stream = Stream::of("a", "b", "c", "d", "e");
        $this->emptyStream = Stream::empty();
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    public function testOfIterable()
    {
        $stream = Stream::ofIterable([]);
        self::assertSame([], $stream->toArray());

        $stream = Stream::ofIterable([
            "a",
            "b",
            "c",
            "d",
            "e"
        ]);
        self::assertSame([
            "a",
            "b",
            "c",
            "d",
            "e"
        ], $stream->toArray());

        $stream = Stream::ofIterable(new \ArrayIterator([]));
        self::assertSame([], $stream->toArray());

        $stream = Stream::ofIterable(new \ArrayIterator([
            "a",
            "b",
            "c",
            "d",
            "e"
        ]));
        self::assertSame([
            "a",
            "b",
            "c",
            "d",
            "e"
        ], $stream->toArray());

        $array = [
            "A" => "a",
            "B" => "b",
            "C" => "c",
            "D" => "d",
            "E" => "e"
        ];
        $stream = Stream::ofIterable($array);
        $i = 0;
        foreach ($stream as $key => $value) {
            self::assertSame($i, $key);
            self::assertSame(array_values($array)[$i], $value);
            $i ++;
        }
        self::assertSame([
            "a",
            "b",
            "c",
            "d",
            "e"
        ], $stream->toArray());

        $array = [
            "A" => "a",
            "B" => "b",
            "C" => "c",
            "D" => "d",
            "E" => "e"
        ];
        $stream = Stream::ofIterable(new \ArrayIterator($array));
        $i = 0;
        foreach ($stream as $key => $value) {
            self::assertSame($i, $key);
            self::assertSame(array_values($array)[$i], $value);
            $i ++;
        }
        self::assertSame([
            "a",
            "b",
            "c",
            "d",
            "e"
        ], $stream->toArray());
    }

    public function testOf()
    {
        $stream = Stream::of();
        self::assertSame([], $stream->toArray());

        $stream = Stream::of("a", "b", "c", "d", "e");
        self::assertSame([
            "a",
            "b",
            "c",
            "d",
            "e"
        ], $stream->toArray());
    }

    public function testOfGenerator()
    {
        $stream = Stream::ofGenerator(function () {
            if (false) {
                yield ;
            }
        });
        self::assertSame([], $stream->toArray());

        $stream = Stream::ofGenerator(function () {
            yield "a";
            yield "b";
            yield "c";
            yield "d";
            yield "e";
        });
        self::assertSame([
            "a",
            "b",
            "c",
            "d",
            "e"
        ], $stream->toArray());

        $array = [
            "A" => "a",
            "B" => "b",
            "C" => "c",
            "D" => "d",
            "E" => "e"
        ];
        $stream = Stream::ofGenerator(function () use ($array) {
            foreach ($array as $key => $value) {
                yield $key => $value;
            }
        });
        $i = 0;
        foreach ($stream as $key => $value) {
            self::assertSame($i, $key);
            self::assertSame(array_values($array)[$i], $value);
            $i ++;
        }
        self::assertSame([
            "a",
            "b",
            "c",
            "d",
            "e"
        ], $stream->toArray());
    }

    public function testEmpty()
    {
        $stream = Stream::empty();
        self::assertSame([], $stream->toArray());
    }

    public function testConcat()
    {
        $stream1 = Stream::of();
        $stream2 = Stream::of();
        $stream = Stream::concat($stream1, $stream2);
        self::assertSame([], $stream->toArray());

        $stream1 = Stream::of();
        $stream2 = Stream::of("d", "e");
        $stream = Stream::concat($stream1, $stream2);
        self::assertSame([
            "d",
            "e"
        ], $stream->toArray());

        $stream1 = Stream::of("a", "b", "c");
        $stream2 = Stream::of();
        $stream = Stream::concat($stream1, $stream2);
        self::assertSame([
            "a",
            "b",
            "c"
        ], $stream->toArray());

        $stream1 = Stream::of("a", "b", "c");
        $stream2 = Stream::of("d", "e");
        $stream = Stream::concat($stream1, $stream2);
        self::assertSame([
            "a",
            "b",
            "c",
            "d",
            "e"
        ], $stream->toArray());

        $stream1 = Stream::of("a", "b", "c");
        $stream2 = Stream::of("d", "e");
        $stream3 = Stream::of("f", "g");
        $stream = Stream::concat($stream1, $stream2, $stream3);
        self::assertSame([
            "a",
            "b",
            "c",
            "d",
            "e",
            "f",
            "g"
        ], $stream->toArray());

        $stream = Stream::concat();
        self::assertSame([], $stream->toArray());

        $stream1 = Stream::of("a", "b", "c");
        $stream2 = Stream::of("d", "e");
        $stream = Stream::concat($stream1, $stream2);
        $i = 0;
        foreach ($stream as $key => $value) {
            self::assertSame($i, $key);
            $i ++;
        }
    }

    public function testFilter()
    {
        $stream = $this->stream->filter(function ($value) {
            return $value === "x";
        });
        self::assertSame([], $stream->toArray());

        $stream = $this->stream->filter(function ($value) {
            return $value === "b" || $value === "c";
        });
        self::assertSame([
            "b",
            "c"
        ], $stream->toArray());

        $stream = $this->stream->filter(function ($value) {
            return true;
        });
        self::assertSame([
            "a",
            "b",
            "c",
            "d",
            "e"
        ], $stream->toArray());

        $stream = $this->emptyStream->filter(function ($value) {
            return true;
        });
        self::assertSame([], $stream->toArray());
    }

    public function testMap()
    {
        $stream = $this->stream->map(function ($value) {
            return $value . "x";
        });
        self::assertSame([
            "ax",
            "bx",
            "cx",
            "dx",
            "ex"
        ], $stream->toArray());

        $stream = $this->emptyStream->map(function ($value) {
            return $value . "x";
        });
        self::assertSame([], $stream->toArray());
    }

    public function testSorted()
    {
        $stream = $this->stream->sorted(function ($value1, $value2) {
            return ord($value2) - ord($value1);
        });
        self::assertSame([
            "e",
            "d",
            "c",
            "b",
            "a"
        ], $stream->toArray());

        $stream = $this->emptyStream->sorted(function ($value1, $value2) {
            return ord($value2) - ord($value1);
        });
        self::assertSame([], $stream->toArray());
    }

    public function testFlatMap()
    {
        $stream = $this->stream->flatMap(function ($value) {
            return Stream::of($value, "x");
        });
        self::assertSame([
            "a",
            "x",
            "b",
            "x",
            "c",
            "x",
            "d",
            "x",
            "e",
            "x"
        ], $stream->toArray());

        $stream = $this->emptyStream->flatMap(function ($value) {
            return Stream::of($value, "x");
        });
        self::assertSame([], $stream->toArray());

        $stream = $this->stream->flatMap(function ($value) {
            yield strtoupper($value) => $value;
        });
        $i = 0;
        foreach ($stream as $key => $value) {
            self::assertSame($i, $key);
            $i ++;
        }
        self::assertSame([
            "a",
            "b",
            "c",
            "d",
            "e"
        ], $stream->toArray());
    }

    public function testSkip()
    {
        $stream = $this->stream->skip(- 1);
        self::assertSame([
            "a",
            "b",
            "c",
            "d",
            "e"
        ], $stream->toArray());

        $stream = $this->stream->skip(0);
        self::assertSame([
            "a",
            "b",
            "c",
            "d",
            "e"
        ], $stream->toArray());

        $stream = $this->stream->skip(1);
        self::assertSame([
            "b",
            "c",
            "d",
            "e"
        ], $stream->toArray());

        $stream = $this->stream->skip(4);
        self::assertSame([
            "e"
        ], $stream->toArray());

        $stream = $this->stream->skip(5);
        self::assertSame([], $stream->toArray());

        $stream = $this->stream->skip(6);
        self::assertSame([], $stream->toArray());

        $stream = $this->emptyStream->skip(- 1);
        self::assertSame([], $stream->toArray());

        $stream = $this->emptyStream->skip(0);
        self::assertSame([], $stream->toArray());

        $stream = $this->emptyStream->skip(1);
        self::assertSame([], $stream->toArray());
    }

    public function testSkipWhile()
    {
        $stream = $this->stream->skipWhile(function ($value) {
            return false;
        });
        self::assertSame([
            "a",
            "b",
            "c",
            "d",
            "e"
        ], $stream->toArray());

        $stream = $this->stream->skipWhile(function ($value) {
            return $value === "a";
        });
        self::assertSame([
            "b",
            "c",
            "d",
            "e"
        ], $stream->toArray());

        $stream = $this->stream->skipWhile(function ($value) {
            return $value !== "e";
        });
        self::assertSame([
            "e"
        ], $stream->toArray());

        $stream = $this->stream->skipWhile(function ($value) {
            return true;
        });
        self::assertSame([], $stream->toArray());

        $stream = $this->emptyStream->skipWhile(function ($value) {
            return false;
        });
        self::assertSame([], $stream->toArray());

        $stream = $this->emptyStream->skipWhile(function ($value) {
            return true;
        });
        self::assertSame([], $stream->toArray());
    }

    public function testLimit()
    {
        $stream = $this->stream->limit(- 1);
        self::assertSame([], $stream->toArray());

        $stream = $this->stream->limit(0);
        self::assertSame([], $stream->toArray());

        $stream = $this->stream->limit(1);
        self::assertSame([
            "a"
        ], $stream->toArray());

        $stream = $this->stream->limit(4);
        self::assertSame([
            "a",
            "b",
            "c",
            "d"
        ], $stream->toArray());

        $stream = $this->stream->limit(5);
        self::assertSame([
            "a",
            "b",
            "c",
            "d",
            "e"
        ], $stream->toArray());

        $stream = $this->stream->limit(6);
        self::assertSame([
            "a",
            "b",
            "c",
            "d",
            "e"
        ], $stream->toArray());

        $stream = $this->emptyStream->limit(- 1);
        self::assertSame([], $stream->toArray());

        $stream = $this->emptyStream->limit(0);
        self::assertSame([], $stream->toArray());

        $stream = $this->emptyStream->limit(1);
        self::assertSame([], $stream->toArray());
    }

    public function testLimitWhile()
    {
        $stream = $this->stream->limitWhile(function ($value) {
            return false;
        });
        self::assertSame([], $stream->toArray());

        $stream = $this->stream->limitWhile(function ($value) {
            return $value === "a";
        });
        self::assertSame([
            "a"
        ], $stream->toArray());

        $stream = $this->stream->limitWhile(function ($value) {
            return $value !== "e";
        });
        self::assertSame([
            "a",
            "b",
            "c",
            "d"
        ], $stream->toArray());

        $stream = $this->stream->limitWhile(function ($value) {
            return true;
        });
        self::assertSame([
            "a",
            "b",
            "c",
            "d",
            "e"
        ], $stream->toArray());

        $stream = $this->emptyStream->limitWhile(function ($value) {
            return false;
        });
        self::assertSame([], $stream->toArray());

        $stream = $this->emptyStream->limitWhile(function ($value) {
            return true;
        });
        self::assertSame([], $stream->toArray());

        $stream = Stream::ofGenerator(function () {
            $i = 0;
            while (true) {
                yield $i ++;
            }
        });
        $stream = $stream->limit(10);
        self::assertSame(range(0, 9), $stream->toArray());

        $stream = Stream::ofGenerator(function () {
            $i = 0;
            while (true) {
                yield $i ++;
            }
        });
        $stream = $stream->limitWhile(function ($value) {
            return $value < 10;
        });
        self::assertSame(range(0, 9), $stream->toArray());
    }

    public function testDistinct()
    {
        $stream = $this->stream->distinct();
        self::assertSame([
            "a",
            "b",
            "c",
            "d",
            "e"
        ], $stream->toArray());

        $stream = Stream::concat($this->stream, $this->stream);
        $stream = $this->stream->distinct();
        self::assertSame([
            "a",
            "b",
            "c",
            "d",
            "e"
        ], $stream->toArray());

        $stream = $this->emptyStream->distinct();
        self::assertSame([], $stream->toArray());

        $stream = Stream::concat($this->stream, $this->stream);
        $stream = $this->stream->distinct(function ($value1, $value2) {
            return $value1 === $value2;
        });
        self::assertSame([
            "a",
            "b",
            "c",
            "d",
            "e"
        ], $stream->toArray());

        $stream = $this->stream->distinct(function ($value1, $value2) {
            return false;
        });
        self::assertSame([
            "a",
            "b",
            "c",
            "d",
            "e"
        ], $stream->toArray());

        $stream = $this->stream->distinct(function ($value1, $value2) {
            return true;
        });
        self::assertSame([
            "a"
        ], $stream->toArray());

        $stream = $this->emptyStream->distinct(function ($value1, $value2) {
            return true;
        });
        self::assertSame([], $stream->toArray());

        $stream = $this->emptyStream->distinct(function ($value1, $value2) {
            return false;
        });
        self::assertSame([], $stream->toArray());
    }

    public function testPeek()
    {
        $array = [];
        $stream = $this->stream->peek(function ($value) use (&$array) {
            $array[] = $value;
        });
        self::assertSame([
            "a",
            "b",
            "c",
            "d",
            "e"
        ], $stream->toArray());
        self::assertSame([
            "a",
            "b",
            "c",
            "d",
            "e"
        ], $array);

        $array = [];
        $stream = $this->emptyStream->peek(function ($value) use (&$array) {
            $array[] = $value;
        });
        self::assertSame([], $stream->toArray());
        self::assertSame([], $array);
    }

    public function testForEach()
    {
        $array = [];
        $this->stream->forEach(function ($value) use (&$array) {
            $array[] = $value;
        });
        self::assertSame([
            "a",
            "b",
            "c",
            "d",
            "e"
        ], $array);

        $array = [];
        $this->emptyStream->forEach(function ($value) use (&$array) {
            $array[] = $value;
        });
        self::assertSame([], $array);
    }

    public function testToArray()
    {
        self::assertSame([
            "a",
            "b",
            "c",
            "d",
            "e"
        ], $this->stream->toArray());

        self::assertSame([], $this->emptyStream->toArray());
    }

    public function testReduce()
    {
        self::assertSame("prefix-a-b-c-d-e", $this->stream->reduce("prefix", function ($accum, $value) {
            return $accum . "-" . $value;
        }));

        self::assertSame("prefix", $this->emptyStream->reduce("prefix", function ($accum, $value) {
            return $accum . "-" . $value;
        }));
    }

    public function testCount()
    {
        self::assertSame(5, $this->stream->count());

        self::assertSame(0, $this->emptyStream->count());
    }

    public function testAllMatch()
    {
        self::assertSame(false, $this->stream->allMatch(function ($value) {
            return $value === "c";
        }));

        self::assertSame(true, $this->stream->allMatch(function ($value) {
            return true;
        }));

        self::assertSame(false, $this->stream->allMatch(function ($value) {
            return false;
        }));

        self::assertSame(true, $this->emptyStream->allMatch(function ($value) {
            return false;
        }));
    }

    public function testAnyMatch()
    {
        self::assertSame(true, $this->stream->anyMatch(function ($value) {
            return $value === "c";
        }));

        self::assertSame(true, $this->stream->anyMatch(function ($value) {
            return true;
        }));

        self::assertSame(false, $this->stream->anyMatch(function ($value) {
            return false;
        }));

        self::assertSame(false, $this->emptyStream->anyMatch(function ($value) {
            return true;
        }));
    }

    public function testNoneMatch()
    {
        self::assertSame(false, $this->stream->noneMatch(function ($value) {
            return $value === "c";
        }));

        self::assertSame(false, $this->stream->noneMatch(function ($value) {
            return true;
        }));

        self::assertSame(true, $this->stream->noneMatch(function ($value) {
            return false;
        }));

        self::assertSame(true, $this->emptyStream->noneMatch(function ($value) {
            return true;
        }));
    }

    public function testFindFirstOrDefault()
    {
        self::assertSame("a", $this->stream->findFirstOrDefault(null));

        self::assertSame(null, $this->emptyStream->findFirstOrDefault(null));
    }

    public function testFindLastOrDefault()
    {
        self::assertSame("e", $this->stream->findLastOrDefault(null));

        self::assertSame(null, $this->emptyStream->findLastOrDefault(null));
    }

    public function testMaxOrDefault()
    {
        self::assertSame("e", $this->stream->maxOrDefault(function ($value1, $value2) {
            return ord($value1) - ord($value2);
        }, null));

        self::assertSame(null, $this->emptyStream->maxOrDefault(function ($value1, $value2) {
            return ord($value1) - ord($value2);
        }, null));
    }

    public function testMinOrDefault()
    {
        self::assertSame("a", $this->stream->minOrDefault(function ($value1, $value2) {
            return ord($value1) - ord($value2);
        }, null));

        self::assertSame(null, $this->emptyStream->minOrDefault(function ($value1, $value2) {
            return ord($value1) - ord($value2);
        }, null));
    }
}

