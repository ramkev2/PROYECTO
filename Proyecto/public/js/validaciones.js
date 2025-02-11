document.addEventListener("DOMContentLoaded", function() {
    
    
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
            
            errorMessage="Por favor, completa todos los campos.";
            mostrarError(errorMessage);  // Mostramos el mensaje de error
            return;
        }

        // Validar edad (por ejemplo, si es mayor a 14 años)
        if (isNaN(edad)||edad < 14) {
            
            errorMessage = "Debes ser mayor de 14 años para registrarte.";
            mostrarError(errorMessage);
            return;
        }
        let regex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,3}$/;  // Regex para validar el email
        if (!regex.test(email)) {
           
            errorMessage = "Por favor, ingresa un correo electrónico válido.";
            mostrarError(errorMessage);
            return;
        }
    regex = /^[A-Za-z\d]{8,}$/// validar la contraseña
    if (!regex.test(clave)) {
        errorMessage = "La contraseña debe cuplir los requisitos.";
        mostrarError(errorMessage);
        return;
    }
       

        const xhr = new XMLHttpRequest();
        xhr.open("POST", checkUsuarioUrl, true);
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    // Procesar la respuesta
                    const data = JSON.parse(xhr.responseText);

                    if (data.error) {
                        mostrarError(data.error); // Mostrar el error si ya existe el usuario o el email
                    } else {

                        const formData = new FormData(form);
                        const xhrSubmit = new XMLHttpRequest();
                        xhrSubmit.open("POST", registrarseUrl, true);

                        xhrSubmit.onreadystatechange = function () {
                            if (xhrSubmit.readyState === 4) {
                                if (xhrSubmit.status === 200) {
                                    const response = JSON.parse(xhrSubmit.responseText);
                                    if (response.error) {
                                        mostrarError(response.error); // Mostrar cualquier error que devuelva el servidor
                                    } else {
                                        mostrarExito(response.success); // Mostrar mensaje de éxito
                                    }
                                }
                            }
                        };
                        xhrSubmit.send(formData);
                    }
                } else {
                    mostrarError("Hubo un problema con la validación de usuario.");
                    console.error("Error en la solicitud AJAX de validación de usuario:", xhr.statusText);
                }
            }
        };

        // Enviar la petición para verificar el usuario y email
        xhr.send(params);
    });
});