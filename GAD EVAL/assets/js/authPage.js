function showLogin() {
    document.getElementById('signupPage').style.display = 'none';
    document.getElementById('forgotPasswordPage').style.display = 'none';
    document.getElementById('loginPage').style.display = 'flex';
    
    animateElements();
}

function showSignup() {
    document.getElementById('loginPage').style.display = 'none';
    document.getElementById('forgotPasswordPage').style.display = 'none';
    document.getElementById('signupPage').style.display = 'flex';
    
    animateElements();
}

function showForgotPassword() {
    document.getElementById('loginPage').style.display = 'none';
    document.getElementById('signupPage').style.display = 'none';
    document.getElementById('forgotPasswordPage').style.display = 'flex';
    
    document.getElementById('resetMessage').style.display = 'none';
    
    animateElements();
}

function sendResetLink() {
    const emailInput = document.querySelector('#forgotPasswordForm input[type="email"]');
    const email = emailInput.value.trim();
    
    if (email === '') {
        alert('Please enter your email address');
        return;
    }
    
    if (!isValidEmail(email)) {
        alert('Please enter a valid email address');
        return;
    }
    
    document.getElementById('resetMessage').style.display = 'block';
    
    emailInput.value = '';
}

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function animateElements() {
    const title = document.querySelector('.title');
    const divider = document.querySelector('.divider');
    const subtitle = document.querySelector('.subtitle');
    const form = document.querySelector('.form');
    
    title.style.animation = 'none';
    divider.style.animation = 'none';
    subtitle.style.animation = 'none';
    form.style.animation = 'none';
    
    void title.offsetWidth;
    
    title.style.animation = 'fadeIn 0.8s';
    divider.style.animation = 'expandWidth 0.8s';
    subtitle.style.animation = 'fadeIn 1s';
    form.style.animation = 'fadeIn 1.4s';
}

document.addEventListener('DOMContentLoaded', function() {
    animateElements();
});