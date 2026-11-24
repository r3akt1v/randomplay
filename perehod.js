document.addEventListener("DOMContentLoaded", function() {
    const fadeElement = document.body;
    fadeElement.classList.add('fade');
    setTimeout(() => {
        fadeElement.classList.add('in');
    }, 50);
    const links = document.querySelectorAll('a');
    links.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetUrl = this.getAttribute('href');
            fadeElement.classList.remove('in');
            setTimeout(() => {
                window.location.href = targetUrl;
            }, 200);
        });
    });
});
