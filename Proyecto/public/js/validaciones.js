document.addEventListener("DOMContentLoaded", function() {
    
    console.log("Documento cargado");
    const form = document.querySelector("#registro-form");
    const checkUsuarioUrl = document.querySelector("#check_usuario_url").value; 
    const mensajeElement = document.getElementById("mensaje");
    const registrarseUrl = document.querySelector("#registrarse").value;
    function mostrarError(mensaje) {
        limpiarError();  // Limpiamos cualquier mensaje de error anterior

        const errorElement = document.createElement("p");
        mensajeElement.appendChild(errorElement);
        errorElement.appendChild(document.createTextNode(mensaje));
        errorElement.style.color = "red";
        errorElement.style.fontSize = "16px"; 
        errorElement.style.padding = "10px";  
        errorElement.style.border = "1px solid red";  
        errorElement.id = "error";

    }
    function mostrarExito(mensaje) {
        limpiarError();
        let exitoElement = document.createElement("p");
        exitoElement.style.color = "green";
        exitoElement.style.fontSize = "16px";
        exitoElement.style.padding = "10px";
        exitoElement.style.border = "1px solid green";  
        exitoElement.id="exito";
        document.getElementById("mensaje").appendChild(exitoElement);
        exitoElement.textContent = mensaje;
       
    }
    function limpiarError() {
        const errorElement = document.getElementById("error");
        if (errorElement){
            mensajeElement.removeChild(errorElement);
        }
         
    }

    form.addEventListener("submit", function(event) {
        event.preventDefault(); 
        // limpiarError(); 

        let valid = true;
        let errorMessage = "";

        // Obtenemos los valores de los campos
        const nombre = document.querySelector("#nombre").value.trim();
        const apellido = document.querySelector("#apellido").value.trim();
        const email = document.querySelector("#email").value.trim();
        const usuario = document.querySelector("#usuario").value.trim();
        const clave = document.querySelector("#clave").value.trim();
        const edad = document.querySelector("#edad").value.trim();

        // Validamos si los campos están vacíos
        if (nombre=="" || apellido==""  || email==""  || usuario==""  || clave=="" || edad=="" ) {
            valid = false;
            errorMessage="Por favor, completa todos los campos.";
            mostrarError(errorMessage);  // Mostramos el mensaje de error
            return;
        }

        // Validar edad (por ejemplo, si es mayor a 14 años)
        if (isNaN(edad)||edad < 14) {
            valid = false;
            errorMessage = "Debes ser mayor de 14 años para registrarte.";
            mostrarError(errorMessage);
            return;
        }
        let regex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,3}$/;  // Regex para validar el email
        if (!regex.test(email)) {
            valid = false;
            errorMessage = "Por favor, ingresa un correo electrónico válido.";
            mostrarError(errorMessage);
            return;
        }

        

        // Validar que el usuario y email no existan
        fetch(checkUsuarioUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                'usuario': usuario,
                'email': email
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                mostrarError(data.error); 
            } else {
                // Si no hay errores, enviamos el formulario con fetch
                const formData = new FormData(form);
                fetch(registrarseUrl, {
                    method: "POST",
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        mostrarError(data.error);  // Mostrar cualquier error que devuelva el servidor
                    } else {
                        mostrarExito(data.success);  // Mostrar el mensaje de éxito
                    }
                })
                .catch(error => {
                    mostrarError("Hubo un problema con la solicitud.");
                    console.error("Error de solicitud AJAX:", error);
                });
            }
        })
        .catch(error => {
            mostrarError("Hubo un problema con la validación de usuario.");
            console.error("Error en la solicitud AJAX de validación de usuario:", error);
        });
    });
});
