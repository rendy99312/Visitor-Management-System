
/*
==========================================
Sengked.com
Visitor Login (2 langkah)

Langkah 1:
Nomor Telepon

Langkah 2:
Tujuan Kunjungan, Lantai, Nomor Rak,
ID Kartu Visitor, dan Selfie

Integrasi backend:
- Lookup menggunakan Nomor Telepon saja.
- ID Kartu Visitor dikirim saat submit check-in.
==========================================
*/

const loginForm = document.getElementById("loginForm");

const loginStep1 = document.getElementById("loginStep1");
const loginStep2 = document.getElementById("loginStep2");
const stepIndicator1 = document.getElementById("loginStepIndicator1");
const stepIndicator2 = document.getElementById("loginStepIndicator2");
const stepLine = document.getElementById("loginStepLine");

const loginPhone = document.getElementById("loginPhone");
const loginIdKartu = document.getElementById("loginIdKartu");
const loginNextButton = document.getElementById("loginNextButton");
const loginBackButton = document.getElementById("loginBackButton");

const loginVerifyError = document.getElementById("loginVerifyError");
const loginVerifyErrorText = document.getElementById("loginVerifyErrorText");
const loginFormError = document.getElementById("loginFormError");
const loginFormErrorText = document.getElementById("loginFormErrorText");

const loginPurpose = document.getElementById("loginPurpose");
const loginLantai = document.getElementById("loginLantai");
const loginRak = document.getElementById("loginRak");

const foundName = document.getElementById("foundName");
const foundCompany = document.getElementById("foundCompany");

const loginPreviousPhotoHint = document.getElementById("loginPreviousPhotoHint");
const loginPreviousPhotoImg = document.getElementById("loginPreviousPhotoImg");

const loginCameraButton = document.getElementById("loginCameraButton");
const loginCaptureButton = document.getElementById("loginCaptureButton");
const loginRetakeButton = document.getElementById("loginRetakeButton");
const loginSelfieInput = document.getElementById("loginSelfieInput");
const loginCameraStream = document.getElementById("loginCameraStream");
const loginCameraCanvas = document.getElementById("loginCameraCanvas");
const loginCameraError = document.getElementById("loginCameraError");

const loginPreviewImage = document.getElementById("loginPreviewImage");
const loginPhotoPlaceholder = document.getElementById("loginPhotoPlaceholder");

const loginSubmitButton = document.getElementById("loginSubmitButton");
const loginResetButton = document.getElementById("loginResetButton");

const loginConfirmSubmit = document.getElementById("loginConfirmSubmit");

const loginConfirmModal = new bootstrap.Modal(
    document.getElementById("loginConfirmModal")
);

const lookupUrl = loginForm.dataset.lookupUrl;

const PHONE_PATTERN = /^[0-9]{10,15}$/;
const CARD_PATTERN = /^[1-9][0-9]*$/;

const MESSAGES = {
    phoneInvalid: "Nomor telepon tidak valid. Masukkan 10 sampai 15 digit angka.",
    cardInvalid: "ID Kartu Visitor wajib diisi dengan angka bulat positif.",
    visitorNotFound: "Nomor telepon tidak ditemukan. Pastikan nomor yang digunakan sudah terdaftar.",
    network: "Tidak dapat terhubung ke server. Periksa koneksi Anda lalu coba lagi.",
    needVerify: "Silakan verifikasi nomor telepon terlebih dahulu."
};

let currentStep = 1;
let identityVerified = false;
let verifiedKey = "";
let loginPhotoUploaded = false;
let loginMediaStream = null;

const prefersReducedMotion = window.matchMedia(
    "(prefers-reduced-motion: reduce)"
).matches;

// ==============================
// HELPER UI
// ==============================

function restartAnimation(element) {
    element.style.animation = "none";
    void element.offsetWidth;
    element.style.animation = "";
}

function setAlert(box, textElement, message) {
    if (!message) {
        box.classList.add("d-none");
        return;
    }

    textElement.textContent = message;
    box.classList.remove("d-none");

    restartAnimation(box);
}

function flagField(input) {
    input.classList.remove("is-valid");
    input.classList.add("is-invalid", "field-flag");
}

function markInvalid(input) {
    input.classList.remove("is-valid", "field-flag");
    input.classList.add("is-invalid");
}

function clearField(input) {
    input.classList.remove("is-invalid", "field-flag");
}

function scrollToTop() {
    window.scrollTo({
        top: 0,
        behavior: prefersReducedMotion ? "auto" : "smooth"
    });
}

// ==============================
// FOTO SELFIE
// ==============================

function showLoginCameraError(message) {
    loginCameraError.textContent = message;
    loginCameraError.classList.remove("d-none");
}

function stopLoginCamera() {
    if (loginMediaStream) {
        loginMediaStream.getTracks().forEach(track => track.stop());
        loginMediaStream = null;
    }

    loginCameraStream.srcObject = null;
    loginCameraStream.classList.add("d-none");
}

async function startLoginCamera() {
    loginCameraError.classList.add("d-none");

    try {
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            throw new Error("Kamera tidak didukung browser.");
        }

        loginMediaStream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: "user" },
            audio: false
        });

        loginCameraStream.srcObject = loginMediaStream;

        loginCameraStream.classList.remove("d-none");
        loginPreviewImage.classList.add("d-none");
        loginPhotoPlaceholder.classList.add("d-none");

        loginCameraButton.classList.add("d-none");
        loginCaptureButton.classList.remove("d-none");
        loginRetakeButton.classList.add("d-none");

    } catch (error) {
        console.error(error);

        showLoginCameraError(
            "Tidak bisa mengakses kamera. Pastikan izin kamera diaktifkan di browser."
        );
    }
}

loginCameraButton.addEventListener("click", startLoginCamera);

loginCaptureButton.addEventListener("click", function () {
    const videoWidth = loginCameraStream.videoWidth;
    const videoHeight = loginCameraStream.videoHeight;

    if (!videoWidth || !videoHeight) {
        showLoginCameraError("Kamera belum siap. Tunggu sebentar lalu coba lagi.");
        return;
    }

    loginCameraCanvas.width = videoWidth;
    loginCameraCanvas.height = videoHeight;

    const ctx = loginCameraCanvas.getContext("2d");

    ctx.save();
    ctx.translate(videoWidth, 0);
    ctx.scale(-1, 1);

    ctx.drawImage(
        loginCameraStream,
        0,
        0,
        videoWidth,
        videoHeight
    );

    ctx.restore();

    loginCameraCanvas.toBlob(function (blob) {
        if (!blob) {
            showLoginCameraError("Gagal mengambil foto.");
            return;
        }

        const file = new File(
            [blob],
            "selfie-login-" + Date.now() + ".jpg",
            { type: "image/jpeg" }
        );

        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);
        loginSelfieInput.files = dataTransfer.files;

        const imageUrl = URL.createObjectURL(blob);

        loginPreviewImage.src = imageUrl;
        loginPreviewImage.classList.remove("d-none");
        loginPhotoPlaceholder.classList.add("d-none");

        stopLoginCamera();

        loginCaptureButton.classList.add("d-none");
        loginRetakeButton.classList.remove("d-none");

        loginPhotoUploaded = true;

    }, "image/jpeg", 0.9);
});

loginRetakeButton.addEventListener("click", function () {
    loginPreviewImage.classList.add("d-none");
    loginPhotoUploaded = false;
    loginSelfieInput.value = "";

    startLoginCamera();
});

loginSelfieInput.addEventListener("change", function () {
    const file = this.files[0];

    if (!file) return;

    const reader = new FileReader();

    reader.onload = function (e) {
        loginPreviewImage.src = e.target.result;

        loginPreviewImage.classList.remove("d-none");
        loginPhotoPlaceholder.classList.add("d-none");
        loginCameraStream.classList.add("d-none");

        loginCameraButton.classList.add("d-none");
        loginCaptureButton.classList.add("d-none");
        loginRetakeButton.classList.remove("d-none");

        loginPhotoUploaded = true;
    };

    reader.readAsDataURL(file);
});

// ==============================
// LANGKAH / STEPPER
// ==============================

function resetCameraUI() {
    stopLoginCamera();

    loginCameraError.classList.add("d-none");

    if (!loginPhotoUploaded) {
        loginPreviewImage.classList.add("d-none");
        loginPhotoPlaceholder.classList.remove("d-none");

        loginCameraButton.classList.remove("d-none");
        loginCaptureButton.classList.add("d-none");
        loginRetakeButton.classList.add("d-none");
    }
}

function showStep(step, options) {
    options = options || {};

    const goingBack = !!options.back;

    currentStep = step;

    const showPanel = step === 1 ? loginStep1 : loginStep2;
    const hidePanel = step === 1 ? loginStep2 : loginStep1;

    hidePanel.classList.add("d-none");

    showPanel.classList.remove("d-none");
    showPanel.classList.toggle("is-back", goingBack);

    restartAnimation(showPanel);

    stepIndicator1.classList.toggle("is-active", step === 1);
    stepIndicator1.classList.toggle("is-done", step === 2);
    stepIndicator2.classList.toggle("is-active", step === 2);

    stepLine.classList.toggle("is-complete", step === 2);

    if (step === 1) {
        stepIndicator1.setAttribute("aria-current", "step");
        stepIndicator2.removeAttribute("aria-current");

        resetCameraUI();

    } else {
        stepIndicator2.setAttribute("aria-current", "step");
        stepIndicator1.removeAttribute("aria-current");
    }

    if (!options.silent) {
        scrollToTop();

        (step === 1 ? loginPhone : loginPurpose).focus({
            preventScroll: true
        });
    }
}

// ==============================
// INPUT IDENTITAS
// ==============================

function invalidateVerification() {
    identityVerified = false;
    verifiedKey = "";

    setAlert(loginVerifyError, loginVerifyErrorText, "");
}

loginPhone.addEventListener("input", function () {
    this.value = this.value.replace(/[^0-9]/g, "").slice(0, 15);

    clearField(this);

    this.classList.toggle(
        "is-valid",
        PHONE_PATTERN.test(this.value)
    );

    invalidateVerification();
});

// ID kartu hanya divalidasi pada tahap 2.
loginIdKartu.addEventListener("input", function () {
    this.value = this.value.replace(/[^0-9]/g, "");

    clearField(this);

    this.classList.toggle(
        "is-valid",
        CARD_PATTERN.test(this.value)
    );

    setAlert(loginFormError, loginFormErrorText, "");
});

loginPhone.addEventListener("keydown", function (e) {
    if (e.key === "Enter") {
        e.preventDefault();
        goToStepTwo();
    }
});

// ==============================
// NOMOR RAK DAN FORM
// ==============================

loginRak.addEventListener("input", function () {
    this.value = this.value.replace(/[^0-9]/g, "");

    clearField(this);

    this.classList.toggle(
        "is-valid",
        this.value.trim() !== ""
    );

    setAlert(loginFormError, loginFormErrorText, "");
});

[loginPurpose, loginLantai].forEach(function (select) {
    select.addEventListener("change", function () {
        clearField(this);

        setAlert(loginFormError, loginFormErrorText, "");
    });
});

// ==============================
// LANGKAH 1: VERIFIKASI HP SAJA
// ==============================

function setNextLoading(isLoading) {
    loginNextButton.disabled = isLoading;

    loginNextButton.innerHTML = isLoading
        ? `<span class="spinner-border spinner-border-sm me-2"></span>
           <span class="btn-label">Memeriksa...</span>`
        : `<span class="btn-label">Lanjutkan</span>
           <i class="bi bi-arrow-right ms-1"></i>`;
}

async function verifyVisitor(phone) {
    const query = new URLSearchParams({
        no_hp: phone
    });

    const response = await fetch(
        lookupUrl + "?" + query.toString(),
        {
            headers: {
                "Accept": "application/json"
            }
        }
    );

    if (!response.ok) {
        throw new Error("HTTP " + response.status);
    }

    return response.json();
}

function interpretVerification(result) {
    if (result && result.success === true) {
        return {
            ok: true,
            data: result.data || {}
        };
    }

    return {
        ok: false,
        fields: [loginPhone],
        message: MESSAGES.visitorNotFound
    };
}

async function goToStepTwo() {
    if (loginNextButton.disabled) {
        return;
    }

    setAlert(loginVerifyError, loginVerifyErrorText, "");

    const phone = loginPhone.value.trim();

    if (!PHONE_PATTERN.test(phone)) {
        markInvalid(loginPhone);
        loginPhone.focus();

        setAlert(
            loginVerifyError,
            loginVerifyErrorText,
            MESSAGES.phoneInvalid
        );

        return;
    }

    const key = phone;

    if (identityVerified && key === verifiedKey) {
        showStep(2);
        return;
    }

    setNextLoading(true);

    try {
        const result = await verifyVisitor(phone);
        const outcome = interpretVerification(result);

        if (!outcome.ok) {
            outcome.fields.forEach(flagField);

            setAlert(
                loginVerifyError,
                loginVerifyErrorText,
                outcome.message
            );

            outcome.fields[0].focus();

            return;
        }

        const data = outcome.data;

        foundName.textContent = data.nama || "-";
        foundCompany.textContent = data.perusahaan || "-";

        if (data.foto) {
            loginPreviousPhotoImg.src = data.foto;
            loginPreviousPhotoHint.classList.remove("d-none");
        } else {
            loginPreviousPhotoHint.classList.add("d-none");
        }

        identityVerified = true;
        verifiedKey = key;

        showStep(2);

    } catch (error) {
        console.error(error);

        setAlert(
            loginVerifyError,
            loginVerifyErrorText,
            MESSAGES.network
        );

    } finally {
        setNextLoading(false);
    }
}

loginNextButton.addEventListener("click", goToStepTwo);

loginBackButton.addEventListener("click", function () {
    showStep(1, { back: true });
});

// ==============================
// LANGKAH 2: VALIDASI
// ==============================

function failStepTwo(message, field) {
    setAlert(
        loginFormError,
        loginFormErrorText,
        message
    );

    if (field) {
        flagField(field);

        field.focus({
            preventScroll: true
        });
    }

    loginFormError.scrollIntoView({
        behavior: prefersReducedMotion ? "auto" : "smooth",
        block: "nearest"
    });

    return false;
}

function validateStepTwo() {
    setAlert(loginFormError, loginFormErrorText, "");

    if (!identityVerified || verifiedKey !== loginPhone.value.trim()) {
        showStep(1, { back: true });

        setAlert(
            loginVerifyError,
            loginVerifyErrorText,
            MESSAGES.needVerify
        );

        return false;
    }

    if (loginPurpose.value === "") {
        return failStepTwo(
            "Pilih tujuan kunjungan.",
            loginPurpose
        );
    }

    if (loginLantai.value === "") {
        return failStepTwo(
            "Pilih lantai terlebih dahulu.",
            loginLantai
        );
    }

    if (loginRak.value.trim() === "") {
        return failStepTwo(
            "Nomor rak wajib diisi.",
            loginRak
        );
    }

    if (!CARD_PATTERN.test(loginIdKartu.value.trim())) {
        return failStepTwo(
            MESSAGES.cardInvalid,
            loginIdKartu
        );
    }

    if (!loginPhotoUploaded || !loginSelfieInput.files.length) {
        return failStepTwo(
            "Silakan ambil foto selfie.",
            null
        );
    }

    return true;
}

// ==============================
// SUBMIT
// ==============================

loginForm.addEventListener("submit", function (e) {
    e.preventDefault();

    if (currentStep === 1) {
        goToStepTwo();
        return;
    }

    if (!validateStepTwo()) {
        return;
    }

    loginConfirmModal.show();
});

loginConfirmSubmit.addEventListener("click", function () {
    loginConfirmModal.hide();

    loginSubmitButton.disabled = true;

    loginSubmitButton.innerHTML = `
        <span class="spinner-border spinner-border-sm me-2"></span>
        Mengirim Data...
    `;

    loginForm.submit();
});

// ==============================
// RESET
// ==============================

loginForm.addEventListener("reset", function () {
    stopLoginCamera();

    loginPreviewImage.src = "";
    loginPreviewImage.classList.add("d-none");

    loginPhotoPlaceholder.classList.remove("d-none");

    loginSelfieInput.value = "";

    loginPhotoUploaded = false;

    loginCameraButton.classList.remove("d-none");
    loginCaptureButton.classList.add("d-none");
    loginRetakeButton.classList.add("d-none");
    loginCameraError.classList.add("d-none");

    loginPreviousPhotoHint.classList.add("d-none");

    identityVerified = false;
    verifiedKey = "";

    [
        loginPhone,
        loginIdKartu,
        loginPurpose,
        loginLantai,
        loginRak
    ].forEach(function (field) {
        clearField(field);
        field.classList.remove("is-valid");
    });

    setAlert(loginVerifyError, loginVerifyErrorText, "");
    setAlert(loginFormError, loginFormErrorText, "");

    showStep(1, {
        back: true,
        silent: true
    });
});
