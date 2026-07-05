<?php
// crypto_vault.php - SECURE VERSION
require_once __DIR__ . '/vendor/autoload.php';

// FIX: Load environment variables safely
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Function to securely pack and encrypt the payload
function secure_vault_encrypt($payload) {
    // Retrieve secret from environment, NOT source code
    $secret_key = $_ENV['VAULT_SECRET_KEY']; 
    
    // Generate a dynamic 12-byte Initialization Vector (IV)
    $iv = openssl_random_pseudo_bytes(12); 
    $tag = "";
    
    // Execute AES-256-GCM Encryption
    $ciphertext = openssl_encrypt($payload, 'aes-256-gcm', $secret_key, OPENSSL_RAW_DATA, $iv, $tag);
    
    // Low-Level Serialization Packing: [IV (12 bytes)] + [TAG (16 bytes)] + [CIPHERTEXT]
    return base64_encode($iv . $tag . $ciphertext);
}

// Function to safely unpack and decrypt the payload
function secure_vault_decrypt($packed_payload) {
    $secret_key = $_ENV['VAULT_SECRET_KEY'];
    $decoded = base64_decode($packed_payload);
    
    // Low-Level Deserialization Unpacking using byte offsets
    $iv = substr($decoded, 0, 12);
    $tag = substr($decoded, 12, 16);
    $ciphertext = substr($decoded, 28);
    
    // AEAD Tag verification happens automatically here.
    // If tampered, it returns false instead of causing a fatal interpreter crash.
    return openssl_decrypt($ciphertext, 'aes-256-gcm', $secret_key, OPENSSL_RAW_DATA, $iv, $tag);
}

// FIX: Safely fallback to an empty string if run from the terminal CLI environment
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $medical_payload = $_POST['payload'] ?? '';
    
    if (!empty($medical_payload)) {
        $vault_package = secure_vault_encrypt($medical_payload);
        echo json_encode(["status" => "vaulted", "data" => $vault_package]);
    }
}
?>