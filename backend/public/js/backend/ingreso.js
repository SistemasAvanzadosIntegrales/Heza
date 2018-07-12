$(document).ready(function(){

	$("#frm-1").validate({

		submitHandler: function(form){

            $("#es-2").removeClass('hide');
            $("#frm-1").addClass('hide');

			$(form).ajaxSubmit({
			
				success: function(respuesta){

					if(isNaN(respuesta)){

                        $("#es-1").addClass('hide');
                        $("#frm-1").removeClass('hide');

                        arreglo = respuesta.split("|");

                        _mensaje("#_mensaje-1", arreglo[1]);
                    }
                    else{

                        window.location = "/index";
                    }                    
				}, //success
                error: function(respuesta){

                    $("#es-1").addClass('hide');
                    $("#frm-1").removeClass('hide');

                    _mensaje("#_mensaje-1", "Ocurri&oacute; un error inesperado, int&eacute;ntelo de nuevo ");
                } //error
			}) //ajaxSubmit
		} //submitHandler
	}) //validate
});

function recupera(){

	$.ajax({
        
        url:"/ingreso/recupera",

        success:function result(data){
            
            $("#_dialogo-1").html(data);

            $("#frm-2").validate({
                
                submitHandler: function(form){
                    
                    $("#es-2").removeClass('hide');
                    $("#frm-2").addClass('hide');
                    $("#aceptar").addClass('hide');
                    $("#cancelar").addClass('hide');
                    
                    $(form).ajaxSubmit({
                    
                        success: function(respuesta){

                            _mensaje("#_mensaje-1", "Los datos de acceso fueron enviados a la direcci&oacute;n de correo electr&oacute;nico registrada");

                            $("#_dialogo-1").dialog("close");
                        }, //success
                        error: function(respuesta){

                            $("#es-2").addClass('hide');
                            $("#frm-2").removeClass('hide');
                            $("#aceptar").removeClass('hide');
                            $("#cancelar").removeClass('hide');

                            _mensaje("#_mensaje-1", "Ocurri&oacute; un error inesperado, int&eacute;ntelo de nuevo");
                        } //error
                    }) //ajaxSubmit
                } //submitHandler
            }) //validate
            
            $("#_dialogo-1").dialog({              
                width: "400",
                height: "auto",
                title: "Recuperar contraseña",
                resizable: false,
                draggable:false,
                modal: true,
                buttons: [              
                    {
                        id: "aceptar",
                        text: "Aceptar",
                        class: "btn btn-rojo-1",
                        click: function(){
                            
                            $("#frm-2").submit();
                        }
                    },
                    {
                        id: "cancelar",
                        text: "Cancelar",
                        class: "btn btn-rojo-1",
                        click: function(){

                            $("#_dialogo-1").dialog("close");
                        }
                    }
                ] //buttons
            }) //dialog
        }, //success
        error: function(respuesta){

            _mensaje("#_mensaje-1", "Ocurri&oacute; un error inesperado, int&eacute;ntelo de nuevo");
        } //error
    }) //$.ajax



    
}