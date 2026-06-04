document.addEventListener("DOMContentLoaded", () => {
  const loadComponent = async (selector, file) => {
    const element = document.querySelector(selector);

    if (!element) return;

    const response = await fetch(file);

    const html = await response.text();

    element.innerHTML = html;
  };

  loadComponent("#header", "components/header.html").then(() => {
    const currentPage = window.location.pathname.split("/").pop();

    const navLinks = document.querySelectorAll(".nav a");

    navLinks.forEach((link) => {
      const href = link.getAttribute("href");

      if (
        href === currentPage ||
        (href === "apostille.html" && currentPage.startsWith("apostille-")) ||
        (href === "legalization.html" &&
          currentPage.startsWith("legalization-"))
      ) {
        link.classList.add("active");
      }
    });
  });

  loadComponent("#footer", "components/footer.html");
});
