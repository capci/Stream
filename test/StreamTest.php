<?php
declare(strict_types = 1);
namespace Capci\Stream\Test;

require_once '../vendor/autoload.php';

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
    }

    public function testEmpty()
    {
        $stream = Stream::empty();
        self::assertSame([], $stream->toArray());
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
    }
}

