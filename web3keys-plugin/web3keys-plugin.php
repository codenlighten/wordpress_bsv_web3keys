<?php
/**
 * Plugin Name: SmartLedger App Mnemonic & Key Generator
 * Description: A WordPress plugin to generate SmartLedger App mnemonic keys, sign data, and manage SmartLedger keys.
 * Version: 0.1.6
 * Author: Gregory Ward, SmartLedger September 2024
 */
// Add a settings page to display directions for using shortcodes
function smartledger_mnemonic_key_generator_settings_page() {
  add_options_page(
      'smartledger App Mnemonic & Key Generator Settings',
      'Mnemonic Key Generator',
      'manage_options',
      'smartledger-mnemonic-key-generator',
      'smartledger_mnemonic_key_generator_settings_page_html'
  );
}
add_action('admin_menu', 'smartledger_mnemonic_key_generator_settings_page');

// HTML content for the settings page
function smartledger_mnemonic_key_generator_settings_page_html() {
  if (!current_user_can('manage_options')) {
      return;
  }
  ?>
  <div class="wrap">
      <h1>SmartLedger Mnemonic & Key Generator Settings</h1>
      <h2>How to Use the Plugin</h2>
      <p>This plugin provides two shortcodes that you can use to generate smartledger mnemonic keys and sign data:</p>
      
      <h3>1. SmartLedger Mnemonic & Key Generator</h3>
      <p>Use this shortcode to display the smartledger Mnemonic and Key Generator form:</p>
      <pre><code>[smartledger_mnemonic_key_generator]</code></pre>
      <p>This shortcode will allow users to generate mnemonic keys, set derivation paths, and store keys.</p>
      
      <h3>2. SmartLedger Signer</h3>
      <p>Use this shortcode to display the form for signing data with a private key:</p>
      <pre><code>[smartledger_mnemonic_key_signer]</code></pre>
      <p>This shortcode allows users to input data, sign it using a private key, and generate a signature.</p>
      
      <h3>How to Add Shortcodes to a Post or Page</h3>
      <p>To use these shortcodes on any page or post, follow these steps:</p>
      <ol>
          <li>Edit the page or post where you want to display the key generator or signer.</li>
          <li>Insert the shortcode <code>[smartledger_mnemonic_key_generator]</code> or <code>[smartledger_mnemonic_key_signer]</code> where you want the form to appear.</li>
          <li>Save and publish the page or post.</li>
      </ol>

      <h3>Need More Help?</h3>
      <p>If you have any issues or need further assistance, please contact support.</p>
  </div>
  <?php
}
// Enqueue the necessary JavaScript and CSS
function smartledger_mnemonic_key_generator_enqueue_scripts() {
  // Enqueue the external library (smartledger)
  wp_enqueue_script('smartledger-lib', 'https://plugins.whatsonchain.com/api/plugin/main/33b1d395fc114c7dc6a3a06d370d6356f2272076dcac903251f7b4212fd311c4', null, null, true);

  // Enqueue the custom script that handles both mnemonic generation and signing
  wp_enqueue_script('smartledger-mnemonic-key-generator', plugin_dir_url(__FILE__) . 'smartledger-mnemonic-key-generator.js', array('smartledger-lib'), null, true);
}
add_action('wp_enqueue_scripts', 'smartledger_mnemonic_key_generator_enqueue_scripts');

// Register the shortcode for the Mnemonic & Key Generator
function smartledger_mnemonic_key_generator_shortcode() {
  ob_start();
  ?>
   
   <style>
  .container {
    max-width: 1000px;
    margin: 40px auto;
    background-color: #fff;
    padding: 40px;
    border-radius: 10px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    border: 1px solid #e0e0e0;
    transition: all 0.3s ease;
  }

  .container:hover {
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
  }

  h1 {
    font-size: 2.5rem;
    text-align: center;
    color: #4caf50;
    margin-bottom: 20px;
  }

  h3 {
    font-size: 1.5rem;
    color: #333;
    margin-top: 20px;
  }

  input,
  textarea,
  button {
    width: 100%;
    padding: 12px;
    margin-top: 10px;
    margin-bottom: 20px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 1rem;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
  }

  input:focus,
  textarea:focus {
    border-color: #4caf50;
    box-shadow: 0 0 8px rgba(76, 175, 80, 0.3);
  }

  button {
    background-color: #4caf50;
    color: white;
    border: none;
    cursor: pointer;
    font-weight: bold;
    transition: background-color 0.3s ease;
  }

  button:hover {
    background-color: #45a049;
  }

  textarea {
    resize: none;
    height: 100px;
  }

  #advanced {
    margin-top: 20px;
    padding: 15px;
    border: 1px solid #ddd;
    border-radius: 5px;
    background-color: #f9f9f9;
  }

  label {
    font-size: 1.2rem;
    margin-left: 5px;
  }

  input[type="checkbox"] {
    width: auto;
    margin-right: 10px;
  }

  #smartKeysSection,
  #simpleKeys,
  #signData,
  #verifyData {
    margin-top: 20px;
  }

  #smartKeysSection textarea,
  #simpleKeys textarea {
    height: auto;
  }

  p {
    margin-bottom: 10px;
  }

  .derivations {
    width: 20%;
    display: inline-block;
    margin-right: 10px;
  }
  #reset {
        /* orange */
        background-color: #ff5400;
        color: white;
        border: none;
        cursor: pointer;
        font-weight: bold;
      }

      #reset:hover {
        background-color: #f0f0f5;
      }

  @media (max-width: 768px) {
    h1 {
      font-size: 2rem;
    }

    input,
    textarea,
    button {
      font-size: 0.9rem;
    }

    .derivations {
      width: 40%;
    }
  }
</style>

  <div class="container">
    <h1>Smartledger Mnemonic & Key Generator</h1>
    <h3>Optional Mnemonic:</h3>
    <input type="text" id="mnemonicOptional" placeholder="Enter your optional mnemonic here..." />
    <input type="checkbox" id="showAdvanced" />
    <label for="showAdvanced">Show Advanced Options</label>
    <div id="advancedOptions" style="display: none">
      <h3>Advanced Options:</h3>
      <p id="basePath"></p>
      <p>Choose Derivation Path</p>
      <div id="derivationDiv">
        <input class="derivations" type="number" id="a" value="0" min="0" max="255" />
        <input class="derivations" type="number" id="b" value="0" min="0" max="255" />
        <input class="derivations" type="number" id="c" value="0" min="0" max="255" />
        <input class="derivations" type="number" id="d" value="0" min="0" max="255" />
        <button id="reset">Reset</button>
      </div>
    </div>
    <button id="generateSimpleKeys">Generate Simple Keys</button>
    <button id="generateSmartKeys">Generate Web3 Keys & Accounts</button>
    <div id="storeKeys">
      <button id="store">Store Keys</button>
    </div>
    <div id="smartKeysSection"></div>
    <div id="simpleKeys" style="display: none">
      <h3>Mnemonic:</h3>
      <textarea id="mnemonic" readonly></textarea>
      <h3>Private Key:</h3>
      <textarea id="privateKey" readonly></textarea>
      <h3>Public Key:</h3>
      <textarea id="publicKey" readonly></textarea>
      <h3>Address:</h3>
      <textarea id="address" readonly></textarea>
      <h3>Shares:</h3>
      <textarea id="shares" readonly></textarea>
    </div>
    <div id="hashData">
      <h3>Hash Data:</h3>
      <input type="text" id="dataToHash" placeholder="Enter data to hash..." />
      <button id="hashButton">Hash Data</button>
      <h3>Hash:</h3>
      <textarea id="hashOutput" readonly></textarea>
    </div>
    <div id="signData">
      <h1>SmartLedger Key Signer</h1>
        <h3>Sign Data:</h3>
        <input type="text" id="data" placeholder="Enter data to sign..." />
        <button id="signButton">Sign Data</button>
        <h3>Signature:</h3>
        <textarea id="signature" readonly></textarea>
      </div>
      <div id="verifyData">
        <h3>Verify Data:</h3>
        <input
          type="text"
          id="dataToVerify"
          placeholder="Enter data to verify..."
        />
        <input
          type="text"
          id="signatureToVerify"
          placeholder="Enter signature to verify..."
        />
        <input
          type="text"
          id="publicKeyToVerify"
          placeholder="Enter public key to verify..."
        />
        <button id="verifyButton">Verify Data</button>
        <h3>Verified:</h3>
        <textarea id="verified" readonly></textarea>
      </div>
  </div>
  <?php
  return ob_get_clean();
}
add_shortcode('smartledger_mnemonic_key_generator', 'smartledger_mnemonic_key_generator_shortcode');

// Register the shortcode for the Key Signer
function smartledger_mnemonic_key_signer_shortcode() {
  ob_start();
  ?>
<style>
  .container {
    max-width: 1000px;
    margin: 40px auto;
    background-color: #fff;
    padding: 40px;
    border-radius: 10px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    border: 1px solid #e0e0e0;
    transition: all 0.3s ease;
  }

  .container:hover {
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
  }

  h1 {
    font-size: 2.5rem;
    text-align: center;
    color: #4caf50;
    margin-bottom: 20px;
  }

  h3 {
    font-size: 1.5rem;
    color: #333;
    margin-top: 20px;
  }

  input,
  textarea,
  button {
    width: 100%;
    padding: 12px;
    margin-top: 10px;
    margin-bottom: 20px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 1rem;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
  }

  input:focus,
  textarea:focus {
    border-color: #4caf50;
    box-shadow: 0 0 8px rgba(76, 175, 80, 0.3);
  }

  button {
    background-color: #4caf50;
    color: white;
    border: none;
    cursor: pointer;
    font-weight: bold;
    transition: background-color 0.3s ease;
  }

  button:hover {
    background-color: #45a049;
  }

  textarea {
    resize: none;
    height: 100px;
  }

  #advanced {
    margin-top: 20px;
    padding: 15px;
    border: 1px solid #ddd;
    border-radius: 5px;
    background-color: #f9f9f9;
  }

  label {
    font-size: 1.2rem;
    margin-left: 5px;
  }

  input[type="checkbox"] {
    width: auto;
    margin-right: 10px;
  }

  #smartKeysSection,
  #simpleKeys,
  #signData,
  #verifyData {
    margin-top: 20px;
  }

  #smartKeysSection textarea,
  #simpleKeys textarea {
    height: auto;
  }

  p {
    margin-bottom: 10px;
  }

  .derivations {
    width: 20%;
    display: inline-block;
    margin-right: 10px;
  }

  @media (max-width: 768px) {
    h1 {
      font-size: 2rem;
    }

    input,
    textarea,
    button {
      font-size: 0.9rem;
    }

    .derivations {
      width: 40%;
    }
  }
</style>

  <div class="container">
    <h1>SmartKey Signer</h1>
    <div id="signData">
      <h3>Sign Data:</h3>
      <h3>Private Key:</h3>
      <input id="privateKey" placeholder="Enter private key..." />
      <input type="text" id="data" placeholder="Enter data to sign..." />
      <button id="signButton">Sign Data</button>
      <h3>Signature:</h3>
      <textarea id="signature" readonly></textarea>
    </div>
  </div>
  <?php
  return ob_get_clean();
}
add_shortcode('smartledger_mnemonic_key_signer', 'smartledger_mnemonic_key_signer_shortcode');