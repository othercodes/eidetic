<?php

namespace OtherCode\Eidetic;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use OtherCode\Eidetic\Exceptions\ImmutabilityIntegrityException;

/**
 * Class VersionControlSystem
 * Basically the VersionedValueObject is a BlockChain of immutable value
 * objects (Blocks). Each time the value is modified a new version (Block)
 * is created and pushed into the versions array.
 * @package OtherCode\Eidetic
 */
class VersionableValue implements Countable, IteratorAggregate
{
    /**
     * List of object versions
     * @var Version[]
     */
    private $versions = [];

    /**
     * VersionControlSystem constructor.
     * Create the genesis block, this is the v0 of the value. The index
     * in the array match with the version of the value
     */
    public function __construct()
    {
        $this->versions[] = new Version('', 0);
    }

    /**
     * Retrieve the selected version of the object
     * @param int|null $version
     * @return Version|null
     */
    public function getVersion(?int $version = null)
    {
        if (isset($version)) {
            return isset($this->versions[$version])
                ? $this->versions[$version]
                : null;
        }

        return $this->versions[$this->count() - 1];
    }

    /**
     * Return the required version value
     * @param int|null $version
     * @return mixed
     */
    public function getValue(?int $version = null)
    {
        return $this->getVersion($version)->getValue();
    }

    /**
     * Set a new value for the object
     * @param $value
     * @return bool
     */
    public function setValue($value)
    {
        $this->versions[] = $this->getVersion()->update($value);
        return true;
    }

    /**
     * Validate the versionable value integrity
     * @return bool
     */
    public function checkIntegrity(): bool
    {
        /**
         * @var int $i
         * @var Version $version
         */
        foreach ($this->versions as $i => $version) {
            if (!$version->isValid()) {
                return false;
            }

            if ($i !== 0 && $this->versions[$i - 1]->getHash() !== $version->getPreviousHash()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Return the external iterator.
     * @return ArrayIterator
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->versions);
    }

    /**
     * Return the total amount of versions
     * @return int
     */
    public function count()
    {
        return count($this->versions);
    }

    /**
     * Checks the object integrity after unserialize
     * @throws ImmutabilityIntegrityException
     */
    public function __wakeup()
    {
        if (!$this->checkIntegrity()) {
            throw new ImmutabilityIntegrityException('Value integrity violated.');
        }
    }

}