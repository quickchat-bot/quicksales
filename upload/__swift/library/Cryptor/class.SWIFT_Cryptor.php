<?php
/**
 * @author Verem Dugeri
 *
 * Encrypts and decrypts a string of text
 */

class SWIFT_Cryptor extends SWIFT_Library
{
    private $cipherAlgo;
    private $hashAlgo;
    private $initializationVectorLength;
    private $format;

    const FORMAT_RAW = 0;
    const FORMAT_B64 = 1;
    const FORMAT_HEX = 2;

    const ERROR_UNKNOWN_CIPHER_ALGO = 'Unknown cipher algorithm ';

    const ERROR_UNKNOWN_HASH_ALGO = 'Unknown hash algorithm ';

    const NOT_A_STRONG_KEY = 'Not a strong key';

    const ENCRYPTION_FAILED = 'Encryption failed ';

    const DATA_INTEGRITY_FAIL = 'Data length is less than the initialization vector length: ';

    const DECRYPTION_FAILED = 'Decryption failed ';

    /**
     * Cryptor constructor.
     *
     * @author Verem Dugeri <verem.dugeri@crossover.com>
     *
     * @param string $cipherAlgo
     * @param string $hashAlgo
     * @param int $format
     *
     * @throws SWIFT_Exception
     */
    public function __construct($cipherAlgo = 'aes-256-ctr', $hashAlgo = 'sha256', $format = self::FORMAT_B64)
    {
        parent::__construct();

        $this->cipherAlgo = $cipherAlgo;
        $this->hashAlgo = $hashAlgo;
        $this->format = $format;

        // Throw an exception if the cipher algorithm is not recognized
        if (!in_array($cipherAlgo, openssl_get_cipher_methods(true))) {
            throw new SWIFT_Exception(self::ERROR_UNKNOWN_CIPHER_ALGO . $cipherAlgo);
        }

        // Throw an exception if the hash algorithm is not recognized
        if (!in_array($hashAlgo, openssl_get_md_methods(true))) {
            throw new SWIFT_Exception(self::ERROR_UNKNOWN_HASH_ALGO . $cipherAlgo);
        }

        $this->initializationVectorLength = openssl_cipher_iv_length($cipherAlgo);
    }

    /**
     * Encrypt a string
     *
     * @author Verem Dugeri <verem.dugeri@crossover.com>
     *
     * @param string $in String to encrypt
     * @param string $key Encryption key
     * @param bool $isStrongCrypto
     * @param int $format Optional override for the format encoding
     *
     * @return string The encrypted string
     *
     * @throws SWIFT_Exception
     */
    public function encryptString($in, $key, $isStrongCrypto = true, $format = null)
    {
        if ($format === null) {
            $format = $this->format;
        }

        // Build an initialization vector.
        $initializationVector = openssl_random_pseudo_bytes($this->initializationVectorLength, $isStrongCrypto);

        if (!$isStrongCrypto) {
            throw new SWIFT_Exception(self::NOT_A_STRONG_KEY );
        }

        // Hash the key
        $keyHash = openssl_digest($key, $this->hashAlgo, true);

        // Encrypt
        $options = OPENSSL_RAW_DATA;
        $encrypted = openssl_encrypt($in, $this->cipherAlgo, $keyHash, $options, $initializationVector);

        if ($encrypted === false) {
            throw new SWIFT_Exception(self::ENCRYPTION_FAILED . openssl_error_string());
        }

        // Result is made of initialization vector and the encrypted data.
        $result = $initializationVector . $encrypted;

        // Format the result if required
        if ($format === self::FORMAT_B64) {
            $result = base64_encode($result);
        } else if ($format === self::FORMAT_HEX) {
            $result = unpack('H*', $result)[1];
        }

        return $result;
    }

    /**
     * Decrypts a string of text
     *
     * @author Verem Dugeri <verem.dugeri@crossover.com>
     *
     * @param string $in
     * @param string $key
     * @param null $format
     * @return string
     * @throws SWIFT_Exception
     */
    public function decryptString($in, $key, $format = null)
    {
        if ($format === null) {
            $format = $this->format;
        }

        $raw = $in;

        //Restore the encrypted data if encoded
        if ($format === self::FORMAT_B64) {
            $raw = base64_decode($in);
        } else if ($format === self::FORMAT_HEX) {
            $raw = pack('H*', $in);
        }

        // Run integrity check on the size
        if (strlen($raw) < $this->initializationVectorLength) {
            throw new SWIFT_Exception(self::DATA_INTEGRITY_FAIL .$this->initializationVectorLength);
        }

        // Extract the Initialization vector and encrypted data
        $initializationVector = substr($raw, 0, $this->initializationVectorLength);
        $raw = substr($raw, $this->initializationVectorLength);

        // Hash the key
        $keyHash = openssl_digest($key, $this->hashAlgo, true);

        // Decrypt
        $options = OPENSSL_RAW_DATA;
        $result = openssl_decrypt($raw, $this->cipherAlgo, $keyHash, $options, $initializationVector);

        if ($result === false) {
            throw new SWIFT_Exception(self::DECRYPTION_FAILED . openssl_error_string());
        }

        return $result;
    }

    /**
     * Encrypt a string
     *
     * @author Verem Dugeri <verem.dugeri@crossover.com>
     *
     * @param string $in
     * @param string $key
     * @param null $format
     * @return string
     */
    public static function Encrypt($in, $key = 'WzvOhiF2IgFABdUhp3E6MwMkjMDpk+G3oB1Z3hlGoCw=', $format = null)
    {
        $cryptor = new SWIFT_Cryptor();

        return $cryptor->encryptString($in, $key, $format);
    }

    /**
     * Decrypts a string
     *
     * @author Verem Dugeri <verem.dugeri@crossover.com>
     *
     * @param string $in
     * @param string $key
     * @param null $format
     * @return string
     */
    public static function Decrypt($in, $key = 'WzvOhiF2IgFABdUhp3E6MwMkjMDpk+G3oB1Z3hlGoCw=', $format = null)
    {
        $cryptor = new SWIFT_Cryptor();

        return $cryptor->decryptString($in, $key, $format);

    }
}
