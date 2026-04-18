document.addEventListener('DOMContentLoaded', () => {
    const loginBtn = document.querySelector('.login-btn');
    const btnText = loginBtn.querySelector('.btn-text');
    const btnLoader = loginBtn.querySelector('.btn-loader');

    loginBtn.addEventListener('click', () => {
        btnText.style.display = 'none';
        btnLoader.style.display = 'inline-block';
    });
});
