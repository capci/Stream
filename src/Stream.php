<?php
declare(strict_types = 1);
namespace Capci\Stream;

use Closure;
use Countable;
use IteratorAggregate;

/**
 * A sequence of elements supporting sequential aggregate operations.
 *
 * Intermediate operations are not evaluated until terminal operation is invoked (lazy evaluation).
 */
abstract class Stream implements IteratorAggregate, Countable
{

    /**
     * Returns a new stream of the given iterable object (array or Traversable object).
     *
     * The keys of the given iterable are not preserved.
     *
     * @param iterable $iterable
     *            array or Traversable object
     * @return Stream a new stream
     */
    public static function ofIterable(iterable $iterable): Stream
    {
        return new class($iterable) extends Stream {

            private $iterable;

            public function __construct(iterable $iterable)
            {
                $this->iterable = $iterable;
            }

            public function getIterator()
            {
                foreach ($this->iterable as $value) {
                    yield $value;
                }
            }
        };
    }

    /**
     * Returns a new stream of the specified values.
     *
     * @param mixed ...$values
     *            the specified values.
     * @return Stream a new stream
     */
    public static function of(...$values): Stream
    {
        return self::ofIterable($values);
    }

    /**
     * Returns a new stream of the given generator function.
     *
     * The keys of the generator are not preserved.
     *
     * @param Closure $generator
     *            a generator function
     * @return Stream a new stream
     */
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

    /**
     * Returns a new empty stream.
     *
     * @return Stream a new empty stream
     */
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

    /**
     * Returns a new concatenated stream.
     *
     * @param Stream ...$streams
     *            streams to be concatenated
     * @return Stream a new concatenated stream
     */
    public static function concat(Stream ...$streams): Stream
    {
        return new class($streams) extends Stream {

            private $streams;

            public function __construct(array $streams)
            {
                $this->streams = $streams;
            }

            public function getIterator()
            {
                foreach ($this->streams as $stream) {
                    foreach ($stream as $value) {
                        yield $value;
                    }
                }
            }
        };
    }

    /**
     * Returns a new stream consisting of the elements of this stream filtered by the given predicate function.
     *
     * @param Closure $predicate
     *            a predicate function
     * @return Stream a new filtered stream
     */
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

    /**
     * Returns a new stream consisting of the results of applying the given mapper function to the elements of this stream.
     *
     * @param Closure $mapper
     *            a mapper function
     * @return Stream a new mapped stream
     */
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

    /**
     * Returns a new stream consisting of the elements of this stream sorted by the given comparator function.
     *
     * @param Closure $comparator
     *            a comparator function
     * @return Stream a new sorted stream
     */
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

    /**
     * Returns a new stream consisting of the elements of the result Streams of applying the given mapper function to the elements of this stream.
     *
     * @param Closure $mapper
     *            a mapper function
     * @return Stream a new flat-mapped stream
     */
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

    /**
     * Returns a new stream consisting of the remaining elements of this stream after skipping the first $n elements.
     *
     * If this stream contains fewer than $n elements, an empty stream will be returned.
     *
     * If $n is 0 or negative, no elements of this stream are skipped.
     *
     * @param int $n
     *            the number of elements to skip
     * @return Stream a new skipped stream
     */
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

    /**
     * Returns a new stream consisting of the remaining elements of this stream after skipping elements while an element matches the given predicate function.
     *
     * @param Closure $predicate
     *            a predicate function
     * @return Stream a new skipped stream
     */
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

    /**
     * Returns a new stream consisting of the first $n elements of this stream.
     *
     * If this stream contains fewer than $n elements, no elements of this stream are discarded.
     *
     * If $n is 0 or negative, an empty stream will be returned.
     *
     * @param int $n
     *            the number of elements to limit
     * @return Stream a new limitted stream
     */
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

    /**
     * Returns a new stream consisting of the elements from start of this stream limited while an element matches the given predicate function.
     *
     * @param Closure $predicate
     *            a predicate function
     * @return Stream a new limitted stream
     */
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

    /**
     * Returns a stream consisting of the distinct elements according to the given equality-comparator function of this stream.
     *
     * if $equalityComparator is null or not given, the identical operator ("===") will be used to compare two elements.
     *
     * @param Closure|null $equalityComparator
     *            an equality-comparator function
     * @return Stream a new distinct stream
     */
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

    /**
     * Returns a new stream consisting of the elements of this stream, additionaly applies the given action function to each element of this stream.
     *
     * @param Closure $action
     *            an action function
     * @return Stream a new stream
     */
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

    /**
     * Applies the given action function to each element of this stream.
     *
     * @param Closure $action
     *            an action function
     */
    public function forEach(Closure $action): void
    {
        foreach ($this as $value) {
            $action($value);
        }
    }

    /**
     * Returns an array containing the elements of this stream.
     *
     * @return array an array containing the elements of this stream
     */
    public function toArray(): array
    {
        return iterator_to_array($this, false);
    }

    /**
     * Performs a reduction on the elements of this stream, using the given identity value and an associative accumulation function, and returns the reduced value.
     *
     * @param mixed $identity
     *            the identity value for the accumulator function
     * @param Closure $accumulator
     *            an accumulator function
     * @return mixed the result of the reduction
     */
    public function reduce($identity, Closure $accumulator)
    {
        $accum = $identity;
        foreach ($this as $value) {
            $accum = $accumulator($accum, $value);
        }
        return $accum;
    }

    /**
     * Returns the count of elements of this stream.
     *
     * @return int the count of elements of this stream.
     * @see Countable::count()
     */
    public function count(): int
    {
        return iterator_count($this);
    }

    /**
     * Returns true if all elements of this stream match the given predicate function.
     *
     * If the stream is empty, true is returned.
     *
     * @param Closure $predicate
     *            a predicate function
     * @return bool true if all elements of this stream match the given predicate function or the stream is empty
     */
    public function allMatch(Closure $predicate): bool
    {
        foreach ($this as $value) {
            if (! $predicate($value)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Returns true if any elements of this stream match the given predicate function.
     *
     * If the stream is empty, false is returned.
     *
     * @param Closure $predicate
     *            a predicate function
     * @return bool true if any elements of this stream match the given predicate function
     */
    public function anyMatch(Closure $predicate): bool
    {
        foreach ($this as $value) {
            if ($predicate($value)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Returns true if no elements of this stream match the given predicate function.
     *
     * If the stream is empty, true is returned.
     *
     * @param Closure $predicate
     *            a predicate function
     * @return bool true if no elements of this stream match the given predicate function or the stream is empty
     */
    public function noneMatch(Closure $predicate): bool
    {
        foreach ($this as $value) {
            if ($predicate($value)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Returns a first element of this stream.
     *
     * If the stream is empty, $defaultValue is returned.
     *
     * @param mixed $defaultValue
     *            if this stream is empty, this value is returned
     * @return mixed a first element or $defaultValue
     */
    public function findFirstOrDefault($defaultValue)
    {
        foreach ($this as $value) {
            return $value;
        }
        return $defaultValue;
    }

    /**
     * Returns a last element of this stream.
     *
     * If the stream is empty, $defaultValue is returned.
     *
     * @param mixed $defaultValue
     *            if this stream is empty, this value is returned
     * @return mixed a last element or $defaultValue
     */
    public function findLastOrDefault($defaultValue)
    {
        $last = $defaultValue;
        foreach ($this as $value) {
            $last = $value;
        }
        return $last;
    }

    /**
     * Returns a maximum element of this stream according to the given comparator.
     *
     * If the stream is empty, $defaultValue is returned.
     *
     * @param Closure $comparator
     *            a comparator function
     * @param mixed $defaultValue
     *            if this stream is empty, this value is returned
     * @return mixed a maximum element of this stream or $defaultValue
     */
    public function maxOrDefault(Closure $comparator, $defaultValue)
    {
        $stillNotIterated = true;
        $max = null;
        foreach ($this as $value) {
            if ($stillNotIterated) {
                $max = $value;
                $stillNotIterated = false;
                continue;
            }
            if ($comparator($max, $value) < 0) {
                $max = $value;
            }
        }

        if ($stillNotIterated) {
            return $defaultValue;
        }

        return $max;
    }

    /**
     * Returns a minimum element of this stream according to the given comparator.
     *
     * @param Closure $comparator
     *            a comparator function
     * @param mixed $defaultValue
     *            if this stream is empty, this value is returned
     * @return mixed a minimum element of this stream or $defaultValue
     */
    public function minOrDefault(Closure $comparator, $defaultValue)
    {
        $stillNotIterated = true;
        $min = null;
        foreach ($this as $value) {
            if ($stillNotIterated) {
                $min = $value;
                $stillNotIterated = false;
                continue;
            }
            if ($comparator($min, $value) > 0) {
                $min = $value;
            }
        }

        if ($stillNotIterated) {
            return $defaultValue;
        }

        return $min;
    }
}

