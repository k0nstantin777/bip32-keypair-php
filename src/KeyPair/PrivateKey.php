<?php
/**
 * This file is a part of "furqansiddiqui/bip32-keypair-php" package.
 * https://github.com/furqansiddiqui/bip32-keypair-php
 *
 * Copyright (c) 2019 Furqan A. Siddiqui <hello@furqansiddiqui.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit following link:
 * https://github.com/furqansiddiqui/bip32-keypair-php/blob/master/LICENSE
 */

declare(strict_types=1);

namespace FurqanSiddiqui\BIP32\KeyPair;

use FurqanSiddiqui\BIP32\ECDSA\Curves;
use FurqanSiddiqui\BIP32\Extend\ExtendedKeyInterface;
use FurqanSiddiqui\BIP32\Extend\PrivateKeyInterface;
use FurqanSiddiqui\BIP32\Extend\PublicKeyInterface;
use FurqanSiddiqui\DataTypes\Binary;

/**
 * Class PrivateKey
 * @package FurqanSiddiqui\BIP32\KeyPair
 */
class PrivateKey implements PrivateKeyInterface
{
    /** @var null|ExtendedKeyInterface */
    protected $extendedKey;
    /** @var Binary */
    protected $privateKey;
    /** @var null|int */
    protected $curve;
    /** @var null|PublicKeyInterface */
    protected $publicKey;

    /**
     * PrivateKey constructor.
     * @param Binary $entropy
     * @param ExtendedKeyInterface|null $extendedKey
     */
    public function __construct(Binary $entropy, ?ExtendedKeyInterface $extendedKey = null)
    {
        $this->extendedKey = $extendedKey;
        $this->privateKey = $entropy;
        $this->privateKey->readOnly(true); // Set buffer to read-only state
    }

    /**
     * @return array
     */
    public function __debugInfo()
    {
        return [sprintf('%d-bit Private Key', $this->privateKey->length()->bits())];
    }

    /**
     * @param string $prop
     * @param $value
     * @return PrivateKey
     */
    public function set(string $prop, $value): PrivateKeyInterface
    {
        if ($prop === "curve") {
            if ($this->extendedKey) {
                throw new \DomainException('Cannot change ECDSA curve for Extended private keys');
            }

            if (!is_int($value) || !in_array($value, array_keys(Curves::INDEX))) {
                throw new \InvalidArgumentException('Cannot use an invalid ECDSA curve');
            }

            $this->curve = $value;
            return $this;
        }

        throw new \DomainException('Cannot set value of inaccessible property');
    }

    /**
     * @return int|null
     */
    public function getEllipticCurve(): ?int
    {
        if ($this->curve) {
            return $this->curve;
        }

        if ($this->extendedKey) {
            return $this->extendedKey->getEllipticCurve();
        }

        return null;
    }

    /**
     * @return Curves
     */
    public function curves(): Curves
    {
        return new Curves(function (int $curve) {
            $this->set("curve", $curve);
        });
    }

    /**
     * @return Binary
     */
    public function raw(): Binary
    {
        return $this->privateKey;
    }

    /**
     * @return PublicKeyInterface
     * @throws \FurqanSiddiqui\BIP32\Exception\PublicKeyException
     * @throws \FurqanSiddiqui\ECDSA\Exception\GenerateVectorException
     * @throws \FurqanSiddiqui\ECDSA\Exception\MathException
     */
    public function publicKey(): PublicKeyInterface
    {
        if (!$this->publicKey) {
            $this->publicKey = new PublicKey($this);
        }

        return $this->publicKey;
    }
}