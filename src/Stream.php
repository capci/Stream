<?php
declare(strict_types = 1);
namespace Capci\Stream;

use Closure;
use Countable;
use IteratorAggregate;

abstract class Stream implements IteratorAggregate, Countable
{

    public static function ofIterable(iterable $values): Stream
    {
        return new class($values) extends Stream {

            private $values;

            public function __construct(iterable $values)
            {
                $this->values = $values;
            }

            public function getIterator()
            {
                foreach ($this->values as $value) {
                    yield $value;
                }
            }
        };
    }

    public static function of(...$values): Stream
    {
        return self::ofIterable($values);
    }

    public static function ofGenerator(Closure $generator): Stream
    {
        return new class($generator) extends Stream {

            private $generator;

            public function __construct(Closure $generator)
            {
                $this->generator = $generator;
            }

            public function getIterator()
            {
                foreach (($this->generator)() as $value) {
                    yield $value;
                }
            }
        };
    }

    public static function concat(Stream $a, Stream $b): Stream
    {
        return new class($a, $b) extends Stream {

            private $a;

            private $b;

            public function __construct(Stream $a, Stream $b)
            {
                $this->a = $a;
                $this->b = $b;
            }

            public function getIterator()
            {
                foreach ($this->a as $value) {
                    yield $value;
                }
                foreach ($this->b as $value) {
                    yield $value;
                }
            }
        };
    }

    public static function empty(): Stream
    {
        return new class() extends Stream {

            public function getIterator()
            {
                if (false) {
                    yield ;
                }
            }
        };
    }

    public function filter(Closure $predicate): Stream
    {
        return new class($this, $predicate) extends Stream {

            private $stream;

            private $predicate;

            public function __construct(Stream $stream, Closure $predicate)
            {
                $this->stream = $stream;
                $this->predicate = $predicate;
            }

            public function getIterator()
            {
                foreach ($this->stream as $value) {
                    if (($this->predicate)($value)) {
                        yield $value;
                    }
                }
            }
        };
    }

    public function map(Closure $mapper): Stream
    {
        return new class($this, $mapper) extends Stream {

            private $stream;

            private $mapper;

            public function __construct(Stream $stream, Closure $mapper)
            {
                $this->stream = $stream;
                $this->mapper = $mapper;
            }

            public function getIterator()
            {
                foreach ($this->stream as $value) {
                    yield ($this->mapper)($value);
                }
            }
        };
    }

    public function sorted(Closure $comparator): Stream
    {
        return new class($this, $comparator) extends Stream {

            private $stream;

            private $comparator;

            public function __construct(Stream $stream, Closure $comparator)
            {
                $this->stream = $stream;
                $this->comparator = $comparator;
            }

            public function getIterator()
            {
                $array = $this->stream->toArray();
                usort($array, $this->comparator);
                yield from $array;
            }
        };
    }

    public function flatMap(Closure $mapper): Stream
    {
        return new class($this, $mapper) extends Stream {

            private $stream;

            private $mapper;

            public function __construct(Stream $stream, Closure $mapper)
            {
                $this->stream = $stream;
                $this->mapper = $mapper;
            }

            public function getIterator()
            {
                foreach ($this->stream as $value) {
                    foreach (($this->mapper)($value) as $v) {
                        yield $v;
                    }
                }
            }
        };
    }

    public function skip(int $n): Stream
    {
        return new class($this, $n) extends Stream {

            private $stream;

            private $n;

            public function __construct(Stream $stream, int $n)
            {
                $this->stream = $stream;
                $this->n = $n;
            }

            public function getIterator()
            {
                $skipped = 0;
                foreach ($this->stream as $value) {
                    if ($skipped < $this->n) {
                        $skipped ++;
                        continue;
                    }
                    yield $value;
                }
            }
        };
    }

    public function skipWhile(Closure $predicate): Stream
    {
        return new class($this, $predicate) extends Stream {

            private $stream;

            private $predicate;

            public function __construct(Stream $stream, Closure $predicate)
            {
                $this->stream = $stream;
                $this->predicate = $predicate;
            }

            public function getIterator()
            {
                $skip = true;
                foreach ($this->stream as $value) {
                    if ($skip) {
                        if (! ($this->predicate)($value)) {
                            $skip = false;
                        }
                    }
                    if ($skip) {
                        continue;
                    }
                    yield $value;
                }
            }
        };
    }

    public function limit(int $n): Stream
    {
        return new class($this, $n) extends Stream {

            private $stream;

            private $n;

            public function __construct(Stream $stream, int $n)
            {
                $this->stream = $stream;
                $this->n = $n;
            }

            public function getIterator()
            {
                $limitted = 0;
                foreach ($this->stream as $value) {
                    if ($limitted >= $this->n) {
                        break;
                    }
                    $limitted ++;
                    yield $value;
                }
            }
        };
    }

    public function limitWhile(Closure $predicate): Stream
    {
        return new class($this, $predicate) extends Stream {

            private $stream;

            private $predicate;

            public function __construct(Stream $stream, Closure $predicate)
            {
                $this->stream = $stream;
                $this->predicate = $predicate;
            }

            public function getIterator()
            {
                $limit = true;
                foreach ($this->stream as $value) {
                    if ($limit) {
                        if (! ($this->predicate)($value)) {
                            $limit = false;
                        }
                    }
                    if (! $limit) {
                        break;
                    }
                    yield $value;
                }
            }
        };
    }

    public function distinct(?Closure $equalityComparator = null): Stream
    {
        if ($equalityComparator === null) {
            return new class($this) extends Stream {

                private $stream;

                public function __construct(Stream $stream)
                {
                    $this->stream = $stream;
                }

                public function getIterator()
                {
                    $yielded = [];
                    foreach ($this->stream as $value) {
                        if (in_array($value, $yielded, true)) {
                            continue;
                        }
                        $yielded[] = $value;
                        yield $value;
                    }
                }
            };
        } else {
            return new class($this, $equalityComparator) extends Stream {

                private $stream;

                private $equalityComparator;

                public function __construct(Stream $stream, Closure $equalityComparator)
                {
                    $this->stream = $stream;
                    $this->equalityComparator = $equalityComparator;
                }

                public function getIterator()
                {
                    $yielded = [];
                    foreach ($this->stream as $value) {
                        foreach ($yielded as $v) {
                            if (($this->equalityComparator)($value, $v)) {
                                continue 2;
                            }
                        }
                        $yielded[] = $value;
                        yield $value;
                    }
                }
            };
        }
    }

    public function peek(Closure $action): Stream
    {
        return new class($this, $action) extends Stream {

            private $stream;

            private $action;

            public function __construct(Stream $stream, Closure $action)
            {
                $this->stream = $stream;
                $this->action = $action;
            }

            public function getIterator()
            {
                foreach ($this->stream as $value) {
                    ($this->action)($value);
                    yield $value;
                }
            }
        };
    }

    public function forEach(Closure $action): void
    {
        foreach ($this as $value) {
            $action($value);
        }
    }

    public function toArray(): array
    {
        return iterator_to_array($this, false);
    }

    public function reduce($identity, Closure $accumulator)
    {
        $accum = $identity;
        foreach ($this as $value) {
            $accum = $accumulator($accum, $value);
        }
        return $accum;
    }

    public function count(): int
    {
        $count = 0;
        foreach ($this as $value) {
            $count ++;
        }
        return $count;
    }

    public function allMatch(Closure $predicate): bool
    {
        foreach ($this as $value) {
            if (! $predicate($value)) {
                return false;
            }
        }
        return true;
    }

    public function anyMatch(Closure $predicate): bool
    {
        foreach ($this as $value) {
            if ($predicate($value)) {
                return true;
            }
        }
        return false;
    }

    public function noneMatch(Closure $predicate): bool
    {
        foreach ($this as $value) {
            if ($predicate($value)) {
                return false;
            }
        }
        return true;
    }
}

