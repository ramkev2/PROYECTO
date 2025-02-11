document.addEventListener("DOMContentLoaded", function () {
    // Obtener las rutas de los inputs ocultos
    const rutaVerificarCorreo = document.getElementById("rutaVerificarCorreo").value;
    const rutaVerificarCodigo = document.getElementById("rutaVerificarCodigo").value;
    const rutaCambiarContraseña = document.getElementById("rutaCambiarContraseña").value;

    const emailInput = document.getElementById("email");
    const codigoInput = document.getElementById("codigo");
    const passwordInput = document.getElementById("nuevaContraseña");

    const emailContainer = document.getElementById("emailContainer");
    const codigoContainer = document.getElementById("codigoContainer");
    const passwordContainer = document.getElementById("passwordContainer");

    const btnEnviarCodigo = document.getElementById("btnEnviarCodigo");
    const btnVerificarCodigo = document.getElementById("btnVerificarCodigo");
    const btnCambiarContraseña = document.getElementById("btnCambiarContraseña");
    const mensajeCorreo=document.createElement("p");
    emailContainer.appendChild(mensajeCorreo);


    

    btnEnviarCodigo.addEventListener("click", function () {
        const email = emailInput.value;

        if (!email) {
            alert("Por favor, introduce tu email.");
            return;
        }

        localStorage.setItem("Email", email);
        const xhr = new XMLHttpRequest();
        xhr.open("POST", rutaVerificarCorreo, true);
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {  // Si la solicitud ha completado
                if (xhr.status === 200) {  // Si la respuesta fue exitosa
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                       codigoContainer.style.display = "block"
                        mensajeCorreo.textContent = response.success; 
                        mensajeCorreo.style.color = "green";

                    } else if (response.error) {
                        mensajeCorreo.textContent = response.error;  // Mostrar mensaje de error
                        mensajeCorreo.style.color = "red";
                    }
                } else {
                    mensajeCorreo.textContent = "Hubo un error en la solicitud.";
                    mensajeCorreo.style.color = "red";
                }
            }
        };

    });

    btnVerificarCodigo.addEventListener("click", function () {
        const email = localStorage.getItem("resetEmail");
        const codigo = codigoInput.value;

        if (!codigo) {
            alert("Introduce el código recibido.");
            return;
        }

        enviarSolicitud(rutaVerificarCodigo, { email, codigo }, function (data, success) {
            if (success) {
                alert("Código verificado. Ahora puedes cambiar tu contraseña.");
                codigoContainer.style.display = "none";
                passwordContainer.style.display = "block";
            } else {
                alert(data.error);
            }
        });
    });

    btnCambiarContraseña.addEventListener("click", function () {
        const email = localStorage.getItem("resetEmail");
        const newPassword = passwordInput.value;

        if (!newPassword) {
            alert("Introduce tu nueva contraseña.");
            return;
        }

        enviarSolicitud(rutaCambiarContraseña, { email, newPassword }, function (data, success) {
            if (success) {
                alert("Contraseña cambiada con éxito. Ahora puedes iniciar sesión.");
                localStorage.removeItem("resetEmail");
                window.location.href = "/login";
            } else {
                alert(data.error);
            }
        });
    });
});