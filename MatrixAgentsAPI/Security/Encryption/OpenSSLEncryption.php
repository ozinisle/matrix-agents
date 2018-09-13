<?php namespace MatrixAgentsAPI\Security\Encryption;

use MatrixAgentsAPI\Utilities\EventLogger;

/**
 * Simple exception interface class for the Token Validator class to make
 * exceptions more specific and obvious. Extends the PHP exception class
 *
 * @author Rob Waller <rdwaller1984@gmail.com>
 */
class OpenSSLEncryption
{

    private $logger;
    const MASK_DEBUG_LOG_FLAG = true;

    public function __construct()
    {
        try {
            $this->logger = new EventLogger();
        } catch (Exception $e) {
            $this->logger
                ->errorEvent()
                ->log('Caught exception: ' . $e->getMessage() . "\n");
        }
    }

    public function CryptoJSAesEncrypt($passphrase, $requestBodyAsPlainText)
    {
        try {
            $salt = openssl_random_pseudo_bytes(256);
            $iv = openssl_random_pseudo_bytes(16);
            //on PHP7 can use random_bytes() istead openssl_random_pseudo_bytes()
            //or PHP5x see : https://github.com/paragonie/random_compat

            $iterations = 999;
            $key = hash_pbkdf2("sha512", $passphrase, $salt, $iterations, 64);

            $this->logger->debug('param $requestBodyAsPlainText >>>' . $requestBodyAsPlainText, self::MASK_DEBUG_LOG_FLAG);

            $encrypted_data = openssl_encrypt($requestBodyAsPlainText, 'aes-256-cbc', hex2bin($key), OPENSSL_RAW_DATA, $iv);

            $data = array("ciphertext" => base64_encode($encrypted_data), "iv" => bin2hex($iv), "salt" => bin2hex($salt));
            return json_encode($data);
        } catch (Exception $e) {
            $this->logger
                ->errorEvent()
                ->log('Caught exception: ' . $e->getMessage() . "\n");
        }
    }

    public function CryptoJSAesDecrypt($passphrase, $jsonString)
    {
        try {
            $jsondata = json_decode($jsonString, true);
            try {
                $salt = hex2bin($jsondata["salt"]);
                $iv = hex2bin($jsondata["iv"]);
            } catch (Exception $e) {
                return null;
            }

            $ciphertext = base64_decode($jsondata["ciphertext"]);
            $iterations = 999; //same as js encrypting 

            $key = hash_pbkdf2("sha512", $passphrase, $salt, $iterations, 64);

            $decrypted = openssl_decrypt($ciphertext, 'aes-256-cbc', hex2bin($key), OPENSSL_RAW_DATA, $iv);
            $this->logger->debug("class::OpenSSLEncryption >>> method::CryptoJSAesDecrypt >>>" . PHP_EOL .
                " log::openssl decrypted payload is >> " . $decrypted, self::MASK_DEBUG_LOG_FLAG);
            return json_decode($decrypted);
        } catch (Exception $e) {
            $this->logger
                ->errorEvent()
                ->log('Caught exception: ' . $e->getMessage() . "\n");
        }
    }
}
