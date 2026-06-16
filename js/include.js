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

    const burger = document.getElementById("burger");
    const nav = document.querySelector(".nav");
    const overlay = document.getElementById("navOverlay");

    console.log("burger =", burger);
    console.log("nav =", nav);

    if (burger && nav) {
      burger.addEventListener("click", () => {
        console.log("CLICK");
        nav.classList.toggle("active");
        overlay.classList.toggle("active");
      });
      overlay.addEventListener("click", () => {
        // добавь
        nav.classList.remove("active"); // добавь
        overlay.classList.remove("active"); // добавь
      });
    }
  });

  loadComponent("#footer", "components/footer.html");
});
