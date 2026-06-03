document.addEventListener("DOMContentLoaded", () => {

    const loadComponent = async (selector, file) => {

        const element = document.querySelector(selector);

        if (!element) return;

        const response = await fetch(file);

        const html = await response.text();

        element.innerHTML = html;
    };

    loadComponent("#header", "components/header.html");

    loadComponent("#footer", "components/footer.html");

});