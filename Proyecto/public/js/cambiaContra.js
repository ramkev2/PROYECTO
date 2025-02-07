document.addEventListener("DOMContentLoaded", function () {
    // Obtener las rutas de los inputs ocultos
    const rutaVerificarCorreo = document.getElementById("rutaVerificarCorreo").value;
 const rutaVerificarCodigo = document.getElementById("rutaVerificarCodigo").value;
    // const rutaCambiarContraseña = document.getElementById("rutaCambiarContraseña").value;

    const emailInput = document.getElementById("email");
   
    const passwordInput = document.getElementById("nuevaContraseña");

    const emailContainer = document.getElementById("emailContainer");
    const codigoContainer = document.getElementById("codigoContainer");
    const passwordContainer = document.getElementById("passwordContainer");

    const btnEnviarCodigo = document.getElementById("btnEnviarCodigo");
    const btnVerificarCodigo = document.getElementById("btnVerificarCodigo");
    const btnCambiarContraseña = document.getElementById("btnCambiarContraseña");
    const mensajeCorreo=document.createElement("p");
    codigoContainer.appendChild(mensajeCorreo);
    const mensajeCodigo=document.createElement("p");
    passwordContainer.appendChild(mensajeCodigo);


    

    btnEnviarCodigo.addEventListener("click", function () {
        const email = emailInput.value;

        if (!email) {
            alert("Por favor, introduce tu email.");
            return;
        }
        const codigo = Math.floor(100000 + Math.random() * 900000);

        localStorage.setItem("Email", email);
        localStorage.setItem("CodigoVerificacion", codigo);
        const xhr = new XMLHttpRequest();
        xhr.open("POST", rutaVerificarCorreo, true);
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {  // Cuando la solicitud se completa
                try {
                    const response = JSON.parse(xhr.responseText); // Parsear la respuesta JSON
                    
                    if (xhr.status === 200) {  
                       
                       
                        enviarCodigoPorCorreo(email, codigo);
                        
                    } else if (xhr.status === 400) {  
                        mensajeCorreo.textContent = response.error || "Error desconocido.";
                        mensajeCorreo.style.color = "red";
                    }
                } catch (e) {
                    console.error("Error al procesar la respuesta del servidor:", e);
                    mensajeCorreo.textContent = "Error procesando la respuesta del servidor.";
                    mensajeCorreo.style.color = "red";
                }
            }
        };
    
        xhr.send(JSON.stringify({ email: email }));
    });
   
    
    function enviarCodigoPorCorreo(email, codigo) {
        const xhr = new XMLHttpRequest();
        xhr.open("POST", rutaVerificarCodigo, true); // Ruta para enviar el código por email
        xhr.setRequestHeader("Content-Type", "application/json");
    
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (xhr.status === 200) {
                      mensajeCorreo.textContent = "Revise su email le hemos enviado un código de verificación";
                      mensajeCorreo.style.color = "green";
                        emailContainer.style.display = "none";
                        codigoContainer.style.display = "block";    
                    } else {
                        alert(response.error || "Error desconocido.");
                    }
                } catch (e) {
                    console.error("Error al procesar respuesta del servidor.");
                }
            }
        };
    
        // Enviar los datos como JSON
        xhr.send(JSON.stringify({ email: email, codigo: codigo }));
    }
    
    btnVerificarCodigo.addEventListener("click", function () { 
        const codigoGuardado = localStorage.getItem('CodigoVerificacion'); 
        const codigoIngresado = document.getElementById("codigo").value;

        alert(codigoGuardado);
        alert(codigoIngresado);
        if (codigoIngresado.trim() === '') {
            mensajeCorreo.textContent = 'El código no puede estar vacío.';
            mensajeCorreo.style.color = 'red';
            return; // Salir de la función si está vacío
        }

        if(codigoIngresado !==codigoGuardado){
            mensajeCorreo.textContent = 'El código ingresado es incorrecto.';
            mensajeCorreo.style.color = 'red';
            return;
        }else{
            mensajeCodigo.textContent = 'El código ingresado es correcto.';
            mensajeCodigo.style.color = 'green';
            codigoContainer.style.display = "none";
            passwordContainer.style.display = "block";
        }

    });   
    



//     btnCambiarContraseña.addEventListener("click", function () {
//         const email = localStorage.getItem("resetEmail");
//         const newPassword = passwordInput.value;

//         if (!newPassword) {
//             alert("Introduce tu nueva contraseña.");
//             return;
//         }

//         enviarSolicitud(rutaCambiarContraseña, { email, newPassword }, function (data, success) {
//             if (success) {
//                 alert("Contraseña cambiada con éxito. Ahora puedes iniciar sesión.");
//                 localStorage.removeItem("resetEmail");
//                 window.location.href = "/login";
//             } else {
//                 alert(data.error);
//             }
//         });
//     });
 });


