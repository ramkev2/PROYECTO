document.addEventListener("DOMContentLoaded", function () {
    // Obtener las rutas de los inputs ocultos
    const rutaVerificarCorreo = document.getElementById("rutaVerificarCorreo").value;
    const rutaVerificarCodigo = document.getElementById("rutaVerificarCodigo").value;
    const rutaCambiarContraseña = document.getElementById("rutaCambiarContraseña").value;
    //contenedores existentes
    const emailInput = document.getElementById("email");
    const emailContainer = document.getElementById("emailContainer");
    const btnEnviarCodigo = document.getElementById("btnEnviarCodigo");
    const contenedor=document.getElementById("contenedor");
    const mensajeCorreo=document.createElement("p");
    emailContainer.appendChild(mensajeCorreo);

    

    btnEnviarCodigo.addEventListener("click", function () {
        const email = emailInput.value;

        if (!email || email.trim() === "") {
            mensajeCorreo.textContent= "Por favor, introduce tu email.";
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
    
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (xhr.status === 200) {
                        const codigoContainer = document.createElement("div");
                        const tituloCodigo = document.createElement("h3");
                        tituloCodigo.textContent = "Intrduce el codigo de verificación";
                        codigoContainer.appendChild(tituloCodigo);
                        const labelCodigo = document.createElement("label");
                        labelCodigo.textContent = "Código de verificación:";
                        codigoContainer.appendChild(labelCodigo);
                       
                        const codigoInput = document.createElement("input");
                        codigoContainer.appendChild(codigoInput);
                        const btnVerificarCodigo = document.createElement("input");
                        btnVerificarCodigo.type = "button";
                        btnVerificarCodigo.value = "Verificar código";
                        btnVerificarCodigo.id = "btnVerificarCodigo";
                        
                        codigoContainer.appendChild(btnVerificarCodigo);
                        contenedor.appendChild(codigoContainer);
                        const mensajeCorreo=document.createElement("p");
                        codigoContainer.appendChild(mensajeCorreo);
                        codigoContainer.id="codigoContainer"

                        emailContainer.remove();
                        btnVerificarCodigo.addEventListener("click", function () { 
                            const codigoGuardado = localStorage.getItem('CodigoVerificacion'); 
                            const codigoIngresado = codigoInput.value;
                            if (codigoIngresado.trim() === '') {
                                mensajeCorreo.textContent = 'El código no puede estar vacío.';
                                mensajeCorreo.style.color = 'red';
                                return; 
                            }
                    
                            if(codigoIngresado !==codigoGuardado){
                                mensajeCorreo.textContent = 'El código ingresado es incorrecto.';
                                mensajeCorreo.style.color = 'red';
                                return;
                            }else{
                              formularioClave();
                            }
                    
                        });   
                        
                      mensajeCorreo.textContent = "Revise su email le hemos enviado un código de verificación";
                      mensajeCorreo.style.color = "green";
                     codigoContainer.style.display = "block";    
                    } else {
                        alert(response.error || "Error desconocido.");
                    }
                } catch (e) {
                    console.error("Error al procesar respuesta del servidor.");
                }
            }
        };
        xhr.send(JSON.stringify({ email: email, codigo: codigo }));
    }
   
    
    function formularioClave(){
        const codigoContainer= document.getElementById("codigoContainer");
        codigoContainer.remove();
        const tituloClave = document.createElement("h3");
        tituloClave.textContent = "Introduce tu nueva contraseña";
        contenedor.appendChild(tituloClave);
        const claveContainer= document.createElement("div");
        const clave = document.createElement("input");
        clave.type = "password";
        const btnCambiarContraseña = document.createElement("input");
        const repetirClave=document.createElement("input");
        repetirClave.type="password";
        btnCambiarContraseña.type = "button";
        const labelClave = document.createElement("label");
        labelClave.textContent = "Nueva contraseña:";
        contenedor.appendChild(claveContainer);
        claveContainer.appendChild(labelClave);
        claveContainer.appendChild(clave);
        const saltoLinea = document.createElement("br"); 
        claveContainer.appendChild(saltoLinea);
        const labelRClave = document.createElement("label");
        labelRClave.textContent = "Repetir contraseña:";
        //estilos
        labelClave.style.display = "block";
clave.style.display = "block";
labelRClave.style.display = "block";
repetirClave.style.display = "block";
//agregar al contenedor
        claveContainer.appendChild(labelRClave);
        claveContainer.appendChild(repetirClave);
        claveContainer.appendChild(saltoLinea);
        claveContainer.appendChild(btnCambiarContraseña);
        btnCambiarContraseña.value = "Cambiar contraseña";
        btnCambiarContraseña.addEventListener("click", function () {
            const email = localStorage.getItem("Email");
            const newPassword = clave.value;
            const clave2=repetirClave.value;
            const mensajeClave=document.createElement("p");
            claveContainer.appendChild(mensajeClave);
            if (!newPassword) {
                mensajeClave.textContent = "Introduce tu nueva contraseña.";
                return;
            }
            if(newPassword!==clave2){
                mensajeClave.textContent = "Las contraseñas no coinciden.";
                return;
            }else{
                enviarSolicitud(email, newPassword);
            }
           
        });
    }
    function enviarSolicitud(email, newPassword ){
        const xhr = new XMLHttpRequest();
        xhr.open("POST", rutaCambiarContraseña, true);
    
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (xhr.status === 200) {
                        alert("Contraseña cambiada con éxito.");
                        window.location.href = "/login";
                    } else {
                        alert(response.error || "Error al cambiar la contraseña.");
                    }
                } catch (e) {
                    console.error("Error al procesar la respuesta del servidor:", e);
                }
            }
        };
    
        const datos = JSON.stringify({ email: email, newPassword: newPassword });
        xhr.send(datos);
    }
 });


