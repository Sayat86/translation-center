console.log("script.js loaded");
window.addEventListener("scroll", () => {
  const header = document.querySelector(".header");

  if (!header) return;

  if (window.scrollY > 50) {
    header.classList.add("scrolled");
  } else {
    header.classList.remove("scrolled");
  }
});
