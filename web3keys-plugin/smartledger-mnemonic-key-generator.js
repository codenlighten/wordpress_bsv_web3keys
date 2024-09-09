document.addEventListener("DOMContentLoaded", () => {
  const mnemonicOptionalInput = document.getElementById("mnemonicOptional");
  const derivationInputs = document.getElementsByClassName("derivations");
  const basePathElement = document.getElementById("basePath");
  const simpleKeysSection = document.getElementById("simpleKeys");
  const smartKeysSection = document.getElementById("smartKeysSection");
  const signDataSection = document.getElementById("signData");
  const verifyDataSection = document.getElementById("verifyData");
  const showAdvancedCheckbox = document.getElementById("showAdvanced");
  const simpleKeysButton = document.getElementById("generateSimpleKeys");
  const advancedOptionsDiv = document.getElementById("advancedOptions");
  const smartKeysButton = document.getElementById("generateSmartKeys");
  const signButton = document.getElementById("signButton");
  const verifyButton = document.getElementById("verifyButton");
  const storeButton = document.getElementById("store");
  const datatoHash = document.getElementById("dataToHash");
  const hashButton = document.getElementById("hashButton");
  const hashOutput = document.getElementById("hashOutput");
  let base = "m/44'/0'/0'/0/0";
  const hashData = (data) => {
    return window.smartledger.hash(data);
  };
  if (hashButton) {
    hashButton.addEventListener("click", () => {
      const data = datatoHash.value;
      if (!data) {
        alert("Please enter data to hash");
        return;
      }
      const hash = hashData(data);
      hashOutput.value = hash;
    });
  }
  // Update base path when advanced options are changed
  Array.from(derivationInputs).forEach((input) => {
    input.addEventListener("input", () => {
      const a = document.getElementById("a").value || 0;
      const b = document.getElementById("b").value || 0;
      const c = document.getElementById("c").value || 0;
      const d = document.getElementById("d").value || 0;
      base = `m/44'/${a}'/${b}'/${c}/${d}`;
      basePathElement.innerText = base;
    });
  });
  // Toggle advanced options
  if (showAdvancedCheckbox) {
    showAdvancedCheckbox.addEventListener("change", () => {
      if (showAdvancedCheckbox.checked) {
        advancedOptionsDiv.style.display = "block";
        resetDerivationPath();
      } else {
        advancedOptionsDiv.style.display = "none";
      }
    });
  }

  // Generate simple keys
  if (simpleKeysButton) {
    simpleKeysButton.addEventListener("click", async () => {
      const mnemonic = mnemonicOptionalInput.value;
      const keys = await window.smartledger.simple(mnemonic, base);
      updateSimpleKeys(keys);
    });
  }

  // Generate smart keys
  if (smartKeysButton) {
    smartKeysButton.addEventListener("click", async () => {
      const mnemonic = mnemonicOptionalInput.value;
      const keys = await window.smartledger.smart(mnemonic, base);
      displaySmartKeys(keys);
    });
  }

  // Update DOM with simple keys
  const updateSimpleKeys = (keys) => {
    document.getElementById("mnemonic").value = keys.mnemonic;
    document.getElementById("privateKey").value = keys.wif;
    document.getElementById("publicKey").value = keys.publicKey;
    document.getElementById("address").value = keys.address;
    document.getElementById("shares").value = keys.shares.join("\n");
    simpleKeysSection.style.display = "block";
    smartKeysSection.style.display = "none";
    signDataSection.style.display = "block";
    verifyDataSection.style.display = "block";
  };

  // Display smart keys
  const displaySmartKeys = (keys) => {
    smartKeysSection.innerHTML = "";
    Object.keys(keys).forEach((key) => {
      if (key === "Mnemonic") return;
      const keySection = document.createElement("div");
      keySection.innerHTML = `<h3>${key}</h3><textarea readonly>${JSON.stringify(
        keys[key],
        null,
        2
      )}</textarea>`;
      smartKeysSection.appendChild(keySection);
    });
    simpleKeysSection.style.display = "none";
    smartKeysSection.style.display = "block";
    signDataSection.style.display = "block";
    verifyDataSection.style.display = "block";
  };

  // Sign data
  if (signButton) {
    signButton.addEventListener("click", () => {
      const data = document.getElementById("data").value;
      const privateKey = document.getElementById("privateKey").value;
      if (!data || !privateKey) {
        alert("Please enter data and a private key to sign");
        return;
      }
      const signature = window.smartledger.sign(data, privateKey);
      document.getElementById("signature").value = signature;

      // Set values for verification
      if (document.getElementById("dataToVerify")) {
        document.getElementById("dataToVerify").value = data;
        document.getElementById("signatureToVerify").value = signature;
        document.getElementById("publicKeyToVerify").value =
          document.getElementById("publicKey").value;
      }
    });
  }

  // Verify data
  if (verifyButton) {
    verifyButton.addEventListener("click", () => {
      const data = document.getElementById("dataToVerify").value;
      const signature = document.getElementById("signatureToVerify").value;
      const publicKey = document.getElementById("publicKeyToVerify").value;
      if (!data || !signature || !publicKey) {
        alert("Please enter data, signature, and public key to verify");
        return;
      }
      const verified = window.smartledger.verify(data, signature, publicKey);
      document.getElementById("verified").value = verified
        ? "Verified"
        : "Not Verified";
    });
  }

  // Reset derivation path to default
  const resetDerivationPath = () => {
    document.getElementById("a").value = 0;
    document.getElementById("b").value = 0;
    document.getElementById("c").value = 0;
    document.getElementById("d").value = 0;
    base = "m/44'/0'/0'/0/0";
    basePathElement.innerText = base;
  };

  // Store mnemonic in local storage
  if (storeButton) {
    storeButton.addEventListener("click", () => {
      const mnemonic = document.getElementById("mnemonic").value;
      if (localStorage.getItem("mnemonic")) {
        const confirmOverwrite = window.confirm(
          "Mnemonic already stored. Do you want to overwrite?"
        );
        if (!confirmOverwrite) return;
      }
      localStorage.setItem("mnemonic", mnemonic);
      alert("Mnemonic stored successfully");
    });
  }

  // Reset form inputs on load
  const resetFormInputs = () => {
    document.querySelectorAll("input").forEach((input) => {
      input.value = "";
    });
    document.querySelectorAll("textarea").forEach((textarea) => {
      textarea.value = "";
    });
    if (!simpleKeysButton) return;
    //checkboxes false
    document.querySelectorAll("input[type=checkbox]").forEach((checkbox) => {
      checkbox.checked = false;
    });
    const savedMnemonic = localStorage.getItem("mnemonic");
    if (savedMnemonic) {
      mnemonicOptionalInput.value = savedMnemonic;
      document.getElementById("generateSimpleKeys").click();
      mnemonicOptionalInput.value = "";
    } else {
      document.getElementById("generateSimpleKeys").click();
    }
  };

  resetFormInputs();
});
