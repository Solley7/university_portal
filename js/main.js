// FILE: js/main.js (New File)
// PURPOSE: Adds subtle animations to improve the UI/UX of the portal.

document.addEventListener("DOMContentLoaded", function() {

    // --- Animation 1: Fade-in elements on scroll ---
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, {
        threshold: 0.1 // Trigger when 10% of the element is visible
    });

    // Target all elements you want to fade in
    const elementsToAnimate = document.querySelectorAll('.fade-in');
    elementsToAnimate.forEach(el => observer.observe(el));


    // --- Animation 2: Count-up numbers in stat cards ---
    const counters = document.querySelectorAll('.count-up');
    const speed = 200; // The lower the number, the faster the count

    counters.forEach(counter => {
        const animateCount = () => {
            const target = +counter.getAttribute('data-target');
            const count = +counter.innerText;

            const increment = target / speed;

            if (count < target) {
                counter.innerText = Math.ceil(count + increment);
                setTimeout(animateCount, 1);
            } else {
                counter.innerText = target;
            }
        };
        animateCount();
    });

});
