
const form = document.getElementById("contactForm");

if (form) {

    form.addEventListener("submit", async function (e) {

        e.preventDefault();

        const button = document.getElementById("submitBtn");
        const success = document.getElementById("successMessage");

        success.style.display = "none";

        button.disabled = true;
        button.textContent = "Отправка...";

        const formData = new FormData(form);
        formData.append("page", window.location.href);

        try {

            const response = await fetch("send.php", {
                method: "POST",
                body: formData
            });

            if (!response.ok) {
                throw new Error("Ошибка сервера");
            }

            const result = await response.json();

            if (result.ok) {

                form.reset();

                success.style.display = "block";
                success.innerHTML = "✅ Спасибо! Заявка успешно отправлена.";

            } else {

                alert(result.error || "Ошибка отправки.");

            }

        } catch (error) {

            alert("Ошибка соединения с сервером.");

            console.error(error);

        }

        button.disabled = false;
        button.textContent = "Отправить заявку";

    });

}