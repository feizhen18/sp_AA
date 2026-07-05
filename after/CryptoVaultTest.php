<?php
// CryptoVaultTest.php
use PHPUnit\Framework\TestCase;

// Include the secure script we want to test
require_once __DIR__ . '/crypto_vault.php';

class CryptoVaultTest extends TestCase
{
    /**
     * Setup environment variables before each test runs if .env loading is skipped in CLI
     */
    protected function setUp(): void
    {
        if (!isset($_ENV['VAULT_SECRET_KEY'])) {
            $_ENV['VAULT_SECRET_KEY'] = "SuperSecureComplexKey256Bit!@#";
        }
    }

    /**
     * Test 1: Verifies that valid data can be successfully encrypted and decrypted back to plaintext.
     */
    public function testEncryptionAndDecryptionSucceeds()
    {
        $originalData = "Patient Confidential Medical History: Diagnosed with Chronic Asthma.";
        
        // Execute encryption
        $encryptedPackage = secure_vault_encrypt($originalData);
        $this->assertNotEmpty($encryptedPackage);
        $this->assertNotEquals($originalData, $encryptedPackage);

        // Execute decryption
        $decryptedData = secure_vault_decrypt($encryptedPackage);
        $this->assertEquals($originalData, $decryptedData);
    }

    /**
     * Test 2: Proves the migration away from ECB mode.
     * Even with identical input blocks, the randomized Initialization Vector (IV) 
     * must yield completely different ciphertexts to prevent data pattern leakage.
     */
    public function testCiphertextIndistinguishability()
    {
        $plaintext = "CANCERCANCER"; // Identical blocks that would leak patterns in ECB mode
        
        $ciphertext1 = secure_vault_encrypt($plaintext);
        $ciphertext2 = secure_vault_encrypt($plaintext);
        
        // Assert that the two outputs are completely unique despite having identical inputs
        $this->assertNotEquals($ciphertext1, $ciphertext2, "Security Flaw: Identical plaintexts resulted in identical ciphertexts (ECB Behavior).");
    }

    /**
     * Test 3: Proves Authenticated Encryption (GCM) integrity validation.
     * If an attacker alters even a single bit of the encrypted payload stream, 
     * the system must reject it outright by returning false.
     */
    public function testIntegrityVerificationFailsOnTampering()
    {
        $originalData = "Critical Surgical Prescription Record Data";
        $encryptedPackage = secure_vault_encrypt($originalData);
        
        // Decode the base64 package down to its raw serialized bytes
        $decodedBytes = base64_decode($encryptedPackage);
        
        // Maliciously flip bits in the final byte of the ciphertext string
        $lastIndex = strlen($decodedBytes) - 1;
        $decodedBytes[$lastIndex] = chr(ord($decodedBytes[$lastIndex]) ^ 0xFF);
        
        // Re-pack the tampered payload
        $tamperedPackage = base64_encode($decodedBytes);
        
        // Attempt to decrypt the corrupted/manipulated stream
        $decryptionResult = secure_vault_decrypt($tamperedPackage);
        
        // The AES-256-GCM authentication tag verification should catch this and fail safely
        $this->assertFalse($decryptionResult, "Security Flaw: The system accepted a tampered payload stream instead of failing safely.");
    }
}