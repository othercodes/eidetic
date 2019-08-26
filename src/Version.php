<?php

namespace OtherCode\Eidetic;

use OtherCode\Eidetic\Exceptions\ImmutabilityIntegrityException;

/**
 * Class Version
 * @package OtherCode\Eidetic
 */
class Version extends Value
{
    /**
     * Object version
     * @var int
     */
    private $version = 0;

    /**
     * Current hash
     * @var string
     */
    private $hash;

    /**
     * Previous hash
     * @var string
     */
    private $previous_hash;

    /**
     * Timestamp
     * @var int
     */
    private $timestamp;

    /**
     * Version constructor.
     * @param mixed|null $value
     * @param int $version
     * @param string $previous_hash
     */
    public function __construct($value = null, int $version = 1, string $previous_hash = '')
    {
        parent::__construct($value);

        $this->version = $version;
        $this->previous_hash = $previous_hash;
        $this->timestamp = time();
        $this->hash = $this->hash();
    }

    /**
     * Get the current version.
     * @return int
     */
    public function getVersion(): int
    {
        return $this->version;
    }

    /**
     * Get the object hash.
     * @return string
     */
    public function getHash(): string
    {
        return $this->hash;
    }

    /**
     * Get the previous hash.
     * @return string
     */
    public function getPreviousHash(): string
    {
        return $this->previous_hash;
    }

    /**
     * Get the timestamp.
     * @return int
     */
    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    /**
     * Calculate the object hash.
     * @return string
     */
    public function hash(): string
    {
        return hash('sha256', json_encode([
            $this->getValue(),
            $this->getVersion(),
            $this->getPreviousHash(),
        ]));
    }

    /**
     * Validate the version integrity.
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->hash === $this->hash();
    }

    /**
     * Update the current value
     * @param mixed $value
     * @return Version
     */
    public function update($value): Version
    {
        return new static ($value, $this->getVersion() + 1, $this->getHash());
    }

    /**
     * Checks the object integrity after unserialize
     * @throws ImmutabilityIntegrityException
     */
    public function __wakeup()
    {
        if (!$this->isValid()) {
            throw new ImmutabilityIntegrityException('Value integrity violated.');
        }
    }
}