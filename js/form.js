
const form = document.getElementById("contactForm");

if(form){

form.addEventListener("submit", async function(e){

    e.preventDefault();

    const button=document.getElementById("submitBtn");

    button.disabled=true;
    button.textContent="Отправка...";

    const formData=new FormData(form);

    formData.append("page",window.location.href);

    try{

        const response=await fetch("send.php",{

            method:"POST",

            body:formData

        });

        const result=await response.json();

        if(result.ok){

            form.reset();

            const success=document.getElementById("successMessage");

            success.style.display="block";

            success.innerHTML="✅ Спасибо! Заявка успешно отправлена.";

        }else{

            alert("Ошибка отправки.");

        }

    }catch{

        alert("Ошибка соединения.");

    }

    button.disabled=false;

    button.textContent="Отправить заявку";

});

}