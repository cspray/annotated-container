<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Cli;

use Cspray\AnnotatedContainer\Helper\StreamBuffer;
use PHPUnit\Framework\TestCase;

final class StdoutTest extends TestCase {

    private $streamFilter;

    protected function setUp() : void {
        parent::setUp();
        if (!in_array('test.stream.buffer', stream_get_filters())) {
            self::assertTrue(stream_filter_register('test.stream.buffer', StreamBuffer::class));
        }
        $this->streamFilter = stream_filter_append(STDOUT, 'test.stream.buffer');
        self::assertIsResource($this->streamFilter);
        self::assertEmpty(StreamBuffer::getBuffer());
    }

    protected function tearDown() : void {
        parent::tearDown();
        StreamBuffer::clearBuffer();
        self::assertTrue(stream_filter_remove($this->streamFilter));
    }

    public function testOutputsWithNewLine() : void {
        (new Stdout())->write('This is the output we expect to receive.');

        self::assertSame('This is the output we expect to receive.' . PHP_EOL, StreamBuffer::getBuffer());
    }

    public function testOutputsWitoutNewLine() : void {
        (new Stdout())->write('Some output without a new line', false);

        self::assertSame('Some output without a new line', StreamBuffer::getBuffer());
    }

}