// form-utils.js

document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('loginForm');
    const passwordToggle = document.getElementById('passwordToggle');
    const passwordInput = document.querySelector('input[type="password"]');

    // Toggle password visibility
    passwordToggle.addEventListener('click', () => {
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
        } else {
            passwordInput.type = 'password';
        }
    });

    // Basic frontend validation
    loginForm.addEventListener('submit', (e) => {
        let valid = true;
        const email = loginForm.querySelector('input[name="email"]');
        const password = loginForm.querySelector('input[name="password"]');

        if (!email.value) {
            valid = false;
            alert('Email is required!');
        }

        if (!password.value) {
            valid = false;
            alert('Password is required!');
        }

        if (!valid) e.preventDefault();
    });
});
document.addEventListener('DOMContentLoaded', () => {
    const passwordToggle = document.getElementById('passwordToggle');
    const passwordInput = document.querySelector('input[type="password"]');

    passwordToggle.addEventListener('click', () => {
        if(passwordInput.type === 'password'){
            passwordInput.type = 'text';
        } else {
            passwordInput.type = 'password';
        }
    });
});
