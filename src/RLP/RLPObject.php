<?php
/*
 * This file is a part of "furqansiddiqui/ethereum-php" package.
 * https://github.com/furqansiddiqui/ethereum-php
 *
 * Copyright (c) Furqan A. Siddiqui <hello@furqansiddiqui.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit following link:
 * https://github.com/furqansiddiqui/ethereum-php/blob/master/LICENSE
 */

declare(strict_types=1);

namespace FurqanSiddiqui\Ethereum\RLP {

    use FurqanSiddiqui\Ethereum\RLP;
    use FurqanSiddiqui\Ethereum\RLP\RLPObject\RLPObjectProp;

    /**
     * Class RLPObject
     * @package FurqanSiddiqui\Ethereum\RLP
     */
    class RLPObject
    {
        /** @var array */
        private array $props;

        /**
         * RLPObject constructor.
         */
        public function __construct()
        {
            $this->props = [];
        }

        /**
         * @param string $value
         * @return $this
         */
        public function encodeString(string $value): self
        {
            $this->props[] = new RLPObjectProp($value, RLPObjectProp::IS_ASCII_STRING);
            return $this;
        }

        /**
         * @param string $hex
         * @return $this
         */
        public function encodeHexString(string $hex): self
        {
            $this->props[] = new RLPObjectProp($hex, RLPObjectProp::IS_HEX_STRING);
            return $this;
        }

        /**
         * @param int|string $dec
         * @return $this
         */
        public function encodeInteger($dec): self
        {
            $this->props[] = new RLPObjectProp($dec, RLPObjectProp::IS_UINT);
            return $this;
        }

        /**
         * @param RLPObject $obj
         * @return $this
         */
        public function encodeObject(RLPObject $obj): self
        {
            $this->props[] = new RLPObjectProp($obj, RLPObjectProp::IS_RLP_OBJECT);
            return $this;
        }

        /**
         * @param RLP $rlp
         * @return RLPEncoded
         */
        public function getRLPEncoded(RLP $rlp): RLPEncoded
        {
            $encoded = [];

            /** @var RLPObjectProp $prop */
            foreach ($this->props as $prop) {
                if ($prop->value instanceof RLPObject) {
                    $encoded[] = $prop->value->getRLPEncoded($rlp)->byteArray();
                    continue;
                }

                if (is_int($prop->value) || $prop->flag === RLPObjectProp::IS_UINT) {
                    $encoded[] = $rlp->encodeInteger($prop->value)->byteArray();
                    continue;
                }

                if ($prop->flag === RLPObjectProp::IS_HEX_STRING) {
                    $encoded[] = $rlp->encodeHex($prop->value)->byteArray();
                    continue;
                }

                $encoded[] = $rlp->encodeStr($prop->value)->byteArray();
            }

            return new RLPEncoded($rlp->completeRLPEncodedObject($encoded));
        }
    }
}

namespace FurqanSiddiqui\Ethereum\RLP\RLPObject {

    use FurqanSiddiqui\Ethereum\RLP\RLPObject;

    /**
     * Class RLPObjectProp
     * @package FurqanSiddiqui\Ethereum\RLP\RLPObject
     */
    class RLPObjectProp
    {
        /** @var int Encodes given String/Int as UInt */
        public const IS_UINT = 0x08;
        /** @var int Encodes given String with 2 per byte */
        public const IS_HEX_STRING = 0x16;
        /** @var int Encodes given String */
        public const IS_ASCII_STRING = 0x20;
        /** @var int Encodes given RLPObject instance */
        public const IS_RLP_OBJECT = 0x40;

        /** @var string|int|RLPObject */
        public $value;
        /** @var int */
        public int $flag;

        /**
         * RLPObjectProp constructor.
         * @param $value
         * @param int $flag
         */
        public function __construct($value, int $flag)
        {
            $this->value = $value;
            $this->flag = $flag;
        }
    }
}
