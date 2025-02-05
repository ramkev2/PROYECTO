document.addEventListener("DOMContentLoaded", function() {
    const form = document.getElementById("login-form");
    form.addEventListener("submit", function(event) {
        event.preventDefault();
        const usuario = document.getElementById("username").value.trim();
        const clave = document.getElementById("password").value.trim();
        if (usuario === "" || clave === "") {
            const div=document.createElement("div");
            if(document.getElementById("error")){
                form.removeChild(document.getElementById("error"));
            }
            div.id="error";
            div.textContent="Por favor, completa todos los campos.";
            div.style.color="red";
            form.appendChild(div);
           
        } else {
            form.submit();
        }
    });
});
