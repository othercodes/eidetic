<?php

namespace Test\Unit;

use OtherCode\Eidetic\Exceptions\ImmutabilityIntegrityException;
use OtherCode\Eidetic\Value;
use OtherCode\Eidetic\Version;
use Test\TestCase;

/**
 * Class VersionTest
 * @package Test\Unit
 */
class VersionTest extends TestCase
{
    public function testInstantiate()
    {
        $x = new Version();

        $this->assertEquals(null, $x->getValue());
        $this->assertEquals(1, $x->getVersion());
        $this->assertEquals('9bce3042450fafc565e92f165e6b8a11a771170d06ac87fba708e609d4b8adcd', $x->getHash());
        $this->assertEquals('', $x->getPreviousHash());
        $this->assertIsInt($x->getTimestamp());
        $this->assertTrue($x->isValid());
    }

    public function testSerialize()
    {
        $x = new Version();
        $x_s = unserialize(serialize($x));

        $this->assertEquals(null, $x_s->getValue());
        $this->assertEquals(1, $x_s->getVersion());
        $this->assertEquals('9bce3042450fafc565e92f165e6b8a11a771170d06ac87fba708e609d4b8adcd', $x_s->getHash());
        $this->assertEquals('', $x_s->getPreviousHash());
        $this->assertIsInt($x_s->getTimestamp());
        $this->assertTrue($x_s->isValid());
    }

    public function testSerializeTamper()
    {
        $this->expectException(ImmutabilityIntegrityException::class);
        $this->expectExceptionMessage('Value integrity violated.');

        unserialize(str_replace(
            '{ONE}',
            '{TWO}',
            serialize(
                new Version('Some VALUE is {ONE}')
            )
        ));
    }

    public function testSetValue()
    {
        $x = new Version();
        $x_1 = $x->update('Some New Value');

        $this->assertInstanceOf(Version::class, $x_1);
        $this->assertEquals('Some New Value', $x_1->getValue());
        $this->assertEquals(2, $x_1->getVersion());
        $this->assertEquals(
            '58cbd75d426ce2100603ffa125b232ba11714921d61bc5be4f865df6b9eddb16',
            $x_1->getHash()
        );
        $this->assertEquals(
            '9bce3042450fafc565e92f165e6b8a11a771170d06ac87fba708e609d4b8adcd',
            $x_1->getPreviousHash()
        );
        $this->assertIsInt($x_1->getTimestamp());
        $this->assertTrue($x_1->isValid());

        $this->assertFalse($x->equals($x_1));
    }
}